# Spec: Converting Laravel ORM to Query Builder

This document specifies the design for converting database query logic in the Tugas Laravel project from Eloquent ORM to Laravel Query Builder (`DB::table`).

## 1. Overview & Goal

The project currently uses Eloquent ORM classes (e.g., `Book::query()`, `Anggota::where(...)`) to perform all CRUD operations and relationship loading. The goal is to refactor these operations to use Laravel's Query Builder (`DB::table`) for direct, performant SQL generation, and manually handle relations and model-level helper properties before sending data to views.

## 2. Query Translation Strategy

Every model-based query will be refactored to use `DB::table('table_name')`.

### CRUD Mappings

- **Select All:**
  - Before: `Book::all()`
  - After: `DB::table('books')->get()`
- **Find by ID:**
  - Before: `Book::find($id)`
  - After: `DB::table('books')->where('id', $id)->first()`
- **Find or Fail:**
  - Before: `Book::findOrFail($id)`
  - After: 
    ```php
    $book = DB::table('books')->where('id', $id)->first();
    if (!$book) {
        abort(404);
    }
    ```
- **Insert / Create:**
  - Before: `User::create($data)`
  - After: `$userId = DB::table('users')->insertGetId($data)`
- **Update:**
  - Before: `$book->update($data)`
  - After: `DB::table('books')->where('id', $id)->update($data)`
- **Delete:**
  - Before: `$book->delete()`
  - After: `DB::table('books')->where('id', $id)->delete()`
- **Soft Deletes Handling (e.g., for Users):**
  - We must ensure soft-deleted records are filtered out: `->whereNull('deleted_at')`
  - Soft deleting: `DB::table('users')->where('id', $id)->update(['deleted_at' => now()])`
  - Force deleting: `DB::table('users')->where('id', $id)->delete()`

## 3. Resolving Relationships & Eager Loading

Instead of Eloquent relationships (like `with('rack')` or `$book->rack`), we will use joins or manual query binding.

### Joins for 1:N or 1:1 Relations

When fetching a book with its rack details:
```php
$books = DB::table('books')
    ->leftJoin('racks', 'books.rack_id', '=', 'racks.id')
    ->select('books.*', 'racks.name as rack_name', 'racks.code as rack_code')
    ->paginate(9);
```

### Manual Eager Loading for Collections (Avoid N+1)

For complex relation trees, such as books with reservations and members:
1. Fetch the books.
2. Collect the book IDs.
3. Query reservations for those book IDs using `DB::table('book_reservations')`.
4. Query anggota for those reservation member IDs using `DB::table('anggota')`.
5. Associate them in memory:
   ```php
   $reservations = DB::table('book_reservations')
       ->join('anggota', 'book_reservations.anggota_id', '=', 'anggota.id')
       ->select('book_reservations.*', 'anggota.nama as anggota_nama', 'anggota.id_anggota as anggota_id_anggota')
       ->whereIn('book_id', $bookIds)
       ->get()
       ->groupBy('book_id');
   
   foreach ($books as $book) {
       $book->reservations = $reservations->get($book->id, collect());
   }
   ```

## 4. Handling Model Helper Methods & Views Compatibilities

Views reference methods like `$book->activeReservation()` or properties like `$book->rack->name`. We will pre-calculate these fields in controllers or services and set them as attributes on standard stdClass objects:

- **Rack Name:** `$book->rack_name` (instead of `$book->rack->name`)
- **Category Name:** `$book->category_name` (instead of `$book->kategori_relasi->name`)
- **Copy Status Label:** We will add `$book->copy_status_label = getCopyStatusLabel($book->copy_status)` where `getCopyStatusLabel` mimics `copyStatusLabel()`.
- **Active Reservation:** We will fetch the active reservation and attach it as `$book->active_reservation` (replacing `$book->activeReservation()`).
- **Availability Calculations:**
  - `$book->active_borrowings_count = DB::table('pinjams')->where('book_id', $book->id)->where('status', 'dipinjam')->count()`
  - `$book->available_stock = max(0, (int)$book->stock - $book->active_borrowings_count)`
  - `$book->is_available = $book->available_stock > 0 && !in_array($book->copy_status, ['lost', 'damaged', 'maintenance'])`
  - `$book->is_reservable = !$book->reference_only && !$book->is_available && !in_array($book->copy_status, ['lost', 'damaged', 'maintenance'])`

## 5. Scope & Target Files

We will convert all controller files in `app/Http/Controllers/` and supporting service files in `app/Services/`.
Specifically:
1. `app/Models/catalog.php`: Convert `search` to use query builder.
2. `app/Http/Controllers/BookController.php`: Convert all book retrieval, creation, deletion, and update operations.
3. `app/Http/Controllers/AnggotaController.php`
4. `app/Http/Controllers/LibraryCardController.php`
5. `app/Http/Controllers/MemberBorrowingController.php`
6. `app/Http/Controllers/MembershipRequestController.php`
7. `app/Http/Controllers/PinjamController.php`
8. `app/Http/Controllers/RackController.php`
9. `app/Http/Controllers/RenewalRequestController.php`
10. `app/Http/Controllers/reservationapprovalcontroller.php`
11. `app/Services/BorrowingService.php`
12. `app/Services/FineService.php`
13. `app/Services/InventoryService.php`
14. `app/Services/LibraryCardService.php`
15. blade templates that consume the above models.
