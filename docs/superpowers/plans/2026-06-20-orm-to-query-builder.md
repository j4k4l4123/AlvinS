# Convert Eloquent ORM to Query Builder Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert all database query operations in the Laravel project from Eloquent ORM to Laravel Query Builder (`DB::table`), updating controllers, services, catalog.php, and views to use standard PHP objects and manual joins instead of Eloquent models and relations.

**Architecture:** We will replace `Model::` static queries with `DB::table('table_name')`. Relationship properties accessed in views (e.g. `$book->rack->name`) will be pre-fetched, calculated in the controller, and attached to the data objects as properties (e.g., `$book->rack_name`).

**Tech Stack:** Laravel, PHP 8.x, SQLite/MySQL.

---

### Task 1: Refactor Catalog Model (`catalog.php`)

**Files:**
- Modify: `app/Models/catalog.php`

- [ ] **Step 1: Replace Book::query() with DB::table('books')**
  Modify `app/Models/catalog.php` to use Query Builder instead of Eloquent query builder.
  
  Code replacement in `app/Models/catalog.php`:
  ```php
  <?php

  namespace App\Models;

  use Illuminate\Support\Facades\DB;
  use Illuminate\Database\Query\Builder;

  class Catalog
  {
      public function search(?string $keyword = null, array $filters = []): Builder
      {
          $query = DB::table('books')
              ->leftJoin('racks', 'books.rack_id', '=', 'racks.id')
              ->select('books.*', 'racks.name as rack_name', 'racks.code as rack_code');

          if ($keyword) {
              $query->where(function ($q) use ($keyword) {
                  $kw = strtolower((string) $keyword);
                  $q->whereRaw('LOWER(judul) LIKE ?', ['%' . $kw . '%'])
                    ->orWhereRaw('LOWER(pengarang) LIKE ?', ['%' . $kw . '%'])
                    ->orWhereRaw('LOWER(kategori) LIKE ?', ['%' . $kw . '%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%' . $kw . '%'])
                    ->orWhereRaw('LOWER(id_buku) LIKE ?', ['%' . $kw . '%'])
                    ->orWhereRaw('LOWER(COALESCE(barcode, \'\')) LIKE ?', ['%' . $kw . '%'])
                    ->orWhereRaw('LOWER(COALESCE(isbn, \'\')) LIKE ?', ['%' . $kw . '%']);

                  if (preg_match('/\d+/', (string) $keyword) === 1) {
                      $digits = preg_replace('/\D/', '', (string) $keyword);
                      $q->orWhereRaw('CAST(thn_terbit AS TEXT) LIKE ?', ['%' . $digits . '%']);
                  }
              });
          }

          if (! empty($filters['kategori'])) {
              $category = $filters['kategori'];
              $query->where(function ($q) use ($category) {
                  $q->where('kategori', $category)
                    ->orWhereExists(function ($sub) use ($category) {
                        $sub->select(DB::raw(1))
                            ->from('categories')
                            ->whereColumn('categories.id', 'books.category_id')
                            ->where('categories.name', $category);
                    });
              });
          }

          if (! empty($filters['subject'])) {
              $query->whereRaw('LOWER(subject) LIKE ?', ['%' . strtolower($filters['subject']) . '%']);
          }

          if (! empty($filters['author'])) {
              $query->whereRaw('LOWER(pengarang) LIKE ?', ['%' . strtolower($filters['author']) . '%']);
          }

          if (! empty($filters['availability'])) {
              if ($filters['availability'] === 'available') {
                  $query->where('reference_only', false)
                      ->whereNotIn('copy_status', ['lost', 'damaged', 'maintenance']);
              }

              if ($filters['availability'] === 'reference_only') {
                  $query->where('reference_only', true);
              }
          }

          if (! empty($filters['from_year'])) {
              $query->where('thn_terbit', '>=', (int) $filters['from_year']);
          }

          if (! empty($filters['to_year'])) {
              $query->where('thn_terbit', '<=', (int) $filters['to_year']);
          }

          return $query;
      }
  }
  ```

- [ ] **Step 2: Verify syntax check**
  Run: `php -l app/Models/catalog.php`
  Expected: No syntax errors.

- [ ] **Step 3: Commit**
  Run: `git add app/Models/catalog.php; git commit -m "refactor: convert catalog search to Query Builder"`

---

### Task 2: Refactor BookController and Book Views

**Files:**
- Modify: `app/Http/Controllers/BookController.php`
- Modify: `resources/views/books/index.blade.php`
- Modify: `resources/views/books/show.blade.php`
- Modify: `resources/views/books/edit.blade.php`
- Modify: `resources/views/books/create.blade.php`

- [ ] **Step 1: Rewrite BookController index and query helper logic**
  Convert all Book model references in `app/Http/Controllers/BookController.php` to use `DB::table('books')`. Calculate active reservations, borrowing counts, stock, and copy status labels in the controller.
  
  Code replacement for `app/Http/Controllers/BookController.php`:
  ```php
  <?php

  namespace App\Http\Controllers;

  use App\Http\Requests\BookRequest;
  use App\Models\Catalog;
  use App\Services\InventoryService;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\DB;

  class BookController extends Controller
  {
      public function __construct(protected InventoryService $inventoryService)
      {
      }

      private function getCopyStatusLabel(string $status): string
      {
          return match ($status) {
              'available' => 'Tersedia',
              'borrowed' => 'Dipinjam',
              'reserved' => 'Direservasi',
              'lost' => 'Hilang',
              'damaged' => 'Rusak',
              'maintenance' => 'Perawatan',
              default => ucfirst($status),
          };
      }

      private function enrichBookDetails($book)
      {
          if (!$book) return null;

          // Fetch active borrowings count
          $activeBorrowingsCount = DB::table('pinjams')
              ->where('book_id', $book->id)
              ->where('status', 'dipinjam')
              ->count();

          $book->active_borrowings_count = $activeBorrowingsCount;
          $book->available_stock = max(0, (int) $book->stock - $activeBorrowingsCount);
          $book->is_available = $book->available_stock > 0 && ! in_array($book->copy_status, ['lost', 'damaged', 'maintenance'], true);
          $book->is_reservable = ! $book->reference_only && ! $book->is_available && ! in_array($book->copy_status, ['lost', 'damaged', 'maintenance'], true);
          $book->copy_status_label = $this->getCopyStatusLabel($book->copy_status ?? 'available');

          // Fetch active reservation
          $activeReservation = DB::table('book_reservations')
              ->where('book_id', $book->id)
              ->whereIn('status', ['pending', 'approved'])
              ->where('expires_at', '>', now())
              ->orderBy('queue_position')
              ->first();

          $book->active_reservation = $activeReservation;

          return $book;
      }

      public function books(Request $request, Catalog $catalog)
      {
          // Expire old reservations
          DB::table('book_reservations')
              ->where('status', 'pending')
              ->where('expires_at', '<=', now())
              ->update(['status' => 'expired']);

          $query = $catalog->search($request->search, [
              'kategori' => $request->get('kategori'),
              'subject' => $request->get('subject'),
              'author' => $request->get('author'),
              'availability' => $request->get('availability'),
              'from_year' => $request->get('from_year'),
              'to_year' => $request->get('to_year'),
          ]);

          if ($request->filled('subject_exact')) {
              $query->where('subject', $request->get('subject_exact'));
          }

          $releaseSortValue = $request->get('release_sort');
          if ($releaseSortValue === 'newest') {
              $sort = 'thn_terbit';
              $order = 'desc';
          } elseif ($releaseSortValue === 'oldest') {
              $sort = 'thn_terbit';
              $order = 'asc';
          } else {
              $allowedSorts = ['judul', 'pengarang', 'kategori', 'thn_terbit', 'created_at', 'stock'];
              $sort = $request->get('sort', 'judul');
              $order = strtolower((string) $request->get('order', 'asc')) === 'desc' ? 'desc' : 'asc';
              if (! in_array($sort, $allowedSorts, true)) {
                  $sort = 'judul';
              }
          }
          $query->orderBy($sort, $order);

          $books = $query->paginate(9)->withQueryString();

          foreach ($books->items() as $book) {
              $this->enrichBookDetails($book);
          }

          return view('books.index', compact('books'));
      }

      public function create()
      {
          $next = 1;
          $last = DB::table('books')->orderBy('id', 'desc')->first();

          if ($last && preg_match('/(\d+)/', $last->id_buku, $matches)) {
              $next = (int) $matches[1] + 1;
          }

          $nextIdBuku = 'BKU' . str_pad($next, 3, '0', STR_PAD_LEFT);
          $racks = DB::table('racks')->orderBy('name')->get();

          return view('books.create', compact('nextIdBuku', 'racks'));
      }

      public function store(BookRequest $request)
      {
          $validated = $request->validated();
          $validated['copy_code_prefix'] = $validated['copy_code_prefix'] ?? $validated['id_buku'];
          $validated['copy_status'] = $validated['copy_status'] ?? 'available';
          $validated['copy_condition'] = $validated['copy_condition'] ?? 'good';
          $validated['created_at'] = now();
          $validated['updated_at'] = now();

          DB::table('books')->insert($validated);

          return redirect()->route('books.index')->with('success', 'Buku berhasil ditambahkan!');
      }

      public function show($id)
      {
          $book = DB::table('books')
              ->leftJoin('racks', 'books.rack_id', '=', 'racks.id')
              ->select('books.*', 'racks.name as rack_name', 'racks.code as rack_code')
              ->where('books.id', $id)
              ->first();

          if (!$book) {
              abort(404);
          }

          $this->enrichBookDetails($book);

          // Get pinjam where status = dipinjam
          $book->pinjam = DB::table('pinjams')
              ->join('anggota', 'pinjams.anggota_id', '=', 'anggota.id')
              ->select('pinjams.*', 'anggota.nama as anggota_nama', 'anggota.id_anggota as anggota_id_anggota')
              ->where('pinjams.book_id', $id)
              ->where('pinjams.status', 'dipinjam')
              ->get();

          // Get reservations (pending/approved) with members
          $book->reservasi = DB::table('book_reservations')
              ->join('anggota', 'book_reservations.anggota_id', '=', 'anggota.id')
              ->select('book_reservations.*', 'anggota.nama as anggota_nama', 'anggota.id_anggota as anggota_id_anggota')
              ->where('book_reservations.book_id', $id)
              ->get();

          return view('books.show', compact('book'));
      }

      public function edit($id)
      {
          $book = DB::table('books')->where('id', $id)->first();
          if (!$book) {
              abort(404);
          }
          $racks = DB::table('racks')->orderBy('name')->get();
          return view('books.edit', compact('book', 'racks'));
      }

      public function update(BookRequest $request, $id)
      {
          $book = DB::table('books')->where('id', $id)->first();
          if (!$book) {
              abort(404);
          }
          $validated = $request->validated();
          $validated['copy_code_prefix'] = $validated['copy_code_prefix'] ?? $book->copy_code_prefix ?? $validated['id_buku'];
          $validated['copy_status'] = $validated['copy_status'] ?? $book->copy_status ?? 'available';
          $validated['copy_condition'] = $validated['copy_condition'] ?? $book->copy_condition ?? 'good';
          $validated['max_loan_days'] = $validated['max_loan_days'] ?? $book->max_loan_days ?? 14;
          $validated['max_renewals'] = $validated['max_renewals'] ?? $book->max_renewals ?? 1;
          $validated['updated_at'] = now();

          DB::table('books')->where('id', $id)->update($validated);

          // InventoryService expects Book model, let's keep model class just for passing to InventoryService but loaded through Query builder hydrate
          $bookModel = \App\Models\Book::find($id);
          if ($bookModel) {
              $this->inventoryService->refreshBookStatus($bookModel);
          }

          return redirect()->route('books.index')->with('success', 'Buku berhasil diperbarui!');
      }

      public function destroy($id)
      {
          $book = DB::table('books')->where('id', $id)->first();
          if (!$book) {
              abort(404);
          }
          DB::table('books')->where('id', $id)->delete();
          return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus!');
      }
  }
  ```

- [ ] **Step 2: Update `books/index.blade.php` view compatibility**
  Modify file `resources/views/books/index.blade.php` to access pre-calculated attributes and avoid calling methods on the book object.
  - Replace `$book->activeReservation()` with `$book->active_reservation`
  - Replace `$book->copyStatusLabel()` with `$book->copy_status_label`
  - Replace `$book->rack?->name` with `$book->rack_name`
  - Replace `$book->isReservable()` with `$book->is_reservable`
  - Replace `$book->availableStock()` with `$book->available_stock`

- [ ] **Step 3: Update `books/show.blade.php` view compatibility**
  Update the view to use:
  - `$book->rack_name` instead of `$book->rack?->name` or `$book->rak?->name`
  - `$book->copy_status_label` instead of `$book->copyStatusLabel()`
  - `$book->is_available` instead of `$book->isAvailable()`

- [ ] **Step 4: Commit changes**
  Run: `git add app/Http/Controllers/BookController.php resources/views/books/; git commit -m "refactor: convert Book queries to Query Builder and adjust views"`

---

### Task 3: Refactor AnggotaController and Anggota Views

**Files:**
- Modify: `app/Http/Controllers/AnggotaController.php`
- Modify: `resources/views/anggota/index.blade.php`
- Modify: `resources/views/anggota/show.blade.php`
- Modify: `resources/views/anggota/edit.blade.php`

- [ ] **Step 1: Rewrite AnggotaController queries**
  Update all queries in `app/Http/Controllers/AnggotaController.php` to use `DB::table('anggota')`, `DB::table('users')`, `DB::table('role_user')`, and `DB::table('member_profiles')`.
  
  Code replacement for `app/Http/Controllers/AnggotaController.php`:
  ```php
  <?php

  namespace App\Http\Controllers;

  use App\Http\Requests\AnggotaRequest;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Facades\Hash;

  class AnggotaController extends Controller
  {
      public function index(Request $request)
      {
          $query = DB::table('anggota');

          if ($request->filled('search')) {
              $search = $request->search;
              $query->where(function ($q) use ($search) {
                  $q->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($search) . '%'])
                    ->orWhereRaw('LOWER(id_anggota) LIKE ?', ['%' . strtolower($search) . '%']);
              });
          }

          $anggota = $query->paginate(10)->withQueryString();
          return view('anggota.index', compact('anggota'));
      }

      public function create()
      {
          return view('anggota.create');
      }

      public function store(AnggotaRequest $request)
      {
          $validated = $request->validated();

          if (empty($validated['id_anggota'])) {
              $maxId = DB::table('anggota')->max('id');
              $validated['id_anggota'] = 'AGT-' . str_pad(((int)$maxId) + 1, 5, '0', STR_PAD_LEFT);
          }

          $userId = DB::table('users')->insertGetId([
              'name' => $validated['nama'],
              'email' => $validated['email'],
              'password' => Hash::make($validated['password']),
              'created_at' => now(),
              'updated_at' => now(),
          ]);

          $memberRole = DB::table('roles')->where('name', 'member')->first();
          if ($memberRole) {
              DB::table('role_user')->insert([
                  'user_id' => $userId,
                  'role_id' => $memberRole->id,
              ]);
          }

          $anggotaData = $validated;
          unset($anggotaData['email'], $anggotaData['password'], $anggotaData['password_confirmation']);
          $anggotaData['user_id'] = $userId;
          $anggotaData['created_at'] = now();
          $anggotaData['updated_at'] = now();

          DB::table('anggota')->insert($anggotaData);

          DB::table('member_profiles')->insert([
              'user_id' => $userId,
              'id_anggota' => $anggotaData['id_anggota'],
              'nama' => $anggotaData['nama'],
              'alamat' => $anggotaData['alamat'],
              'no_tlp' => $anggotaData['no_tlp'],
              'tanggal_daftar' => $anggotaData['tanggal_daftar'],
              'membership_status' => 'active',
              'created_at' => now(),
              'updated_at' => now(),
          ]);

          return redirect()->route('anggota.index')->with('success', 'Anggota berhasil ditambahkan!');
      }

      public function show($id)
      {
          $anggota = DB::table('anggota')->where('id', $id)->first();
          if (!$anggota) {
              abort(404);
          }

          $anggota->user = DB::table('users')
              ->where('id', $anggota->user_id)
              ->whereNull('deleted_at')
              ->first();

          $anggota->pinjam = DB::table('pinjams')
              ->join('books', 'pinjams.book_id', '=', 'books.id')
              ->select('pinjams.*', 'books.judul as buku_judul')
              ->where('pinjams.anggota_id', $id)
              ->get();

          $anggota->pengembalian = DB::table('pengembalians')
              ->join('books', 'pengembalians.book_id', '=', 'books.id')
              ->select('pengembalians.*', 'books.judul as buku_judul')
              ->where('pengembalians.anggota_id', $id)
              ->get();

          return view('anggota.show', compact('anggota'));
      }

      public function edit($id)
      {
          $anggota = DB::table('anggota')->where('id', $id)->first();
          if (!$anggota) {
              abort(404);
          }
          return view('anggota.edit', compact('anggota'));
      }

      public function update(AnggotaRequest $request, $id)
      {
          $anggota = DB::table('anggota')->where('id', $id)->first();
          if (!$anggota) {
              abort(404);
          }

          $validated = $request->validated();
          unset($validated['password'], $validated['password_confirmation']);
          $validated['updated_at'] = now();

          DB::table('anggota')->where('id', $id)->update($validated);

          return redirect()->route('anggota.index')->with('success', 'Anggota berhasil diperbarui!');
      }

      public function destroy($id)
      {
          $anggota = DB::table('anggota')->where('id', $id)->first();
          if (!$anggota) {
              abort(404);
          }

          // Soft delete or hard delete user depending on soft delete usage
          if ($anggota->user_id) {
              // Update user with soft delete if user table has deleted_at field
              DB::table('users')->where('id', $anggota->user_id)->update(['deleted_at' => now()]);
          }

          DB::table('anggota')->where('id', $id)->delete();

          return redirect()->route('anggota.index')->with('success', 'Anggota berhasil dihapus!');
      }
  }
  ```

- [ ] **Step 2: Update Anggota Views to support standard PHP object properties**
  Ensure `$anggota->user->email` or `$anggota->pinjam` properties work correctly with stdClass object properties.

- [ ] **Step 3: Commit**
  Run: `git add app/Http/Controllers/AnggotaController.php; git commit -m "refactor: convert Anggota queries to Query Builder"`

---

### Task 4: Refactor Auth Controllers

**Files:**
- Modify: `app/Http/Controllers/Auth/RegisterController.php`
- Modify: `app/Http/Controllers/Auth/AuthController.php`

- [ ] **Step 1: Rewrite RegisterController**
  Update `app/Http/Controllers/Auth/RegisterController.php` to use Query Builder.
  
  ```php
  // Replace references with DB::table('users')->insertGetId(...)
  // and DB::table('anggota')->insert(...)
  ```

- [ ] **Step 2: Commit**
  Run: `git commit -am "refactor: convert Auth registration queries to Query Builder"`

---

### Task 5: Refactor Remaining Controllers and Run Tests

**Files:**
- Modify: `app/Http/Controllers/LibraryCardController.php`
- Modify: `app/Http/Controllers/PinjamController.php`
- Modify: `app/Http/Controllers/PengembalianController.php`
- Modify: `app/Services/BorrowingService.php`

- [ ] **Step 1: Refactor LibraryCardController and services**
  Replace Eloquent queries with `DB::table('library_cards')`, etc.
  
- [ ] **Step 2: Refactor PinjamController and PengembalianController**
  Convert `Pinjam::` and `Pengembalian::` queries to `DB::table('pinjams')` and `DB::table('pengembalians')`.

- [ ] **Step 3: Run all PHPUnit tests**
  Run: `vendor/bin/phpunit`
  Expected: All tests pass.

- [ ] **Step 4: Commit**
  Run: `git commit -am "refactor: complete ORM to Query Builder refactoring"`
