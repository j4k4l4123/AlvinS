# 🎯 Complete Gemini Prompt for PerpusKu Laravel Project

Copy and paste the block below into Gemini to get accurate, contextual answers about your views:

---

```
You are an expert Laravel + Blade developer. I have a Library Management System project called "PerpusKu" built with Laravel 11, Blade templates, and Vite. Here is the complete architecture of my views, controllers, and styling:

## 📁 PROJECT STRUCTURE
- Framework: Laravel 11 with Blade templating
- CSS: Custom CSS in resources/css/app.css (NOT Tailwind for custom components)
- Layout: resources/views/layouts/app.blade.php (main layout with sidebar)
- Build tool: Vite

## 🏗️ LAYOUT SYSTEM (resources/views/layouts/app.blade.php)
The main layout includes:
- Fixed top navbar with hamburger button and "📚 PerpusKu" brand
- Fixed left sidebar (260px) with gradient green background (#15803d to #16a34a)
- Sidebar navigation links: Buku, Anggota, Peminjaman, Pengembalian
- Active route highlighting with `request()->routeIs()`
- Main content area with white card (`content-card` id)
- Flash messages for session('success') and session('error')
- Vite asset loading
- Custom vanilla JS SPA navigation system:
  - Intercepts sidebar clicks for smooth page transitions
  - Fetch-based content swapping with exit/enter animations
  - Scroll-based navigation between routes (scroll to top/bottom edges navigates to prev/next route)
  - Browser back/forward support with popstate
  - LocalStorage for sidebar collapse state

## 🎨 SHARED CSS COMPONENTS (from resources/css/app.css)
All views use these shared CSS classes:

**Page Header:** `.page-header` (flex, green border-bottom, h1 title + `.btn-add` button)
**Search & Filter:** `.search-filter-box`, `.search-form`, `.search-input`, `.filter-select`, `.btn-search`, `.btn-reset`
**Alerts:** `.alert-success` (green), `.alert-error` (red with error-list)
**Grid & Cards:** `.items-grid` (CSS grid, minmax 300px), `.item-card` (white, rounded, shadow, hover lift)
**Card Internals:** `.item-header`, `.status-badge`, `.status-borrowed`, `.status-returned`, `.status-late`, `.status-ontime`, `.item-id`, `.item-body`, `.item-title`, `.item-detail`, `.item-desc`, `.item-user`, `.item-dates`, `.item-denda`, `.item-actions`
**Action Buttons:** `.btn-action`, `.btn-edit` (blue), `.btn-delete` (red), `.btn-view` (green)
**Empty State:** `.empty-state` (centered, dashed border, large icon)
**Forms:** `.form-container` (max-width 700px, centered, gradient green bg), `.form-header`, `.form-icon`, `.styled-form`, `.form-row` (2-column grid), `.form-group`, `.form-group.full-width`, `.form-input`, `.form-input.textarea`, `.form-actions`, `.btn-cancel` (red), `.btn-submit` (green gradient)
**Detail Views:** `.detail-container`, `.detail-header` (green gradient), `.detail-icon`, `.detail-id`, `.detail-body`, `.detail-row`, `.detail-item`, `.detail-label`, `.detail-value`, `.detail-actions`, `.btn-back`, `.btn-return`
**Special:** `.form-static` (read-only display), `.info-box` (yellow), `.denda-info` (red), `.highlight`, `.denda-text`, `.no-denda-text`

## 📚 MODULE 1: BOOKS (Buku)
**Controller:** BookController.php
**Model:** Book (fields: id_buku, judul, pengarang, penerbit, thn_terbit, kategori, keterangan)
**Routes:** /books (index, create, store, show, edit, update, destroy)

**Views:**
1. `books/index.blade.php` — Grid of book cards with:
   - Search by judul/pengarang/kategori
   - Category filter dropdown (Fiksi, Non-Fiksi, Sains, Teknologi, Sejarah, Biografi, Lainnya)
   - Each card shows: kategori badge, id_buku, judul, pengarang, penerbit, thn_terbit, keterangan (limited to 60 chars)
   - Edit and Delete actions per card
   - Pagination info (firstItem, lastItem, total)
   - Empty state when no books

2. `books/create.blade.php` — Form with:
   - Searchable dropdown to copy data from existing books (custom JS: openDropdown, filterDropdown, selectExistingBook)
   - Fields: id_buku (auto-generated, readonly), judul, pengarang, penerbit, thn_terbit (number, min 1900), kategori (select), keterangan (textarea)
   - Validation errors display
   - Inline CSS and JS for searchable select component

3. `books/edit.blade.php` — Same form layout as create but:
   - Pre-filled with $book data using `old('field', $book->field)`
   - Uses PUT method
   - No searchable existing book feature

4. `books/show.blade.php` — Detail view with:
   - Two-column detail rows: judul+kategori, pengarang+penerbit, thn_terbit+created_at, keterangan (full width)
   - Back and Edit buttons

## 👤 MODULE 2: ANGGOTA (Members)
**Controller:** AnggotaController.php
**Model:** Anggota (fields: id_anggota, nama, no_tlp, alamat, tanggal_daftar)
**Routes:** /anggota (index, create, store, show, edit, update, destroy)

**Views:**
1. `anggota/index.blade.php` — Grid of member cards with:
   - Search by nama/id_anggota/alamat
   - Each card shows: id_anggota badge, tanggal_daftar, nama, no_tlp, alamat (limited to 40 chars)
   - Edit and Delete actions
   - Member count display

2. `anggota/create.blade.php` — Form with:
   - Fields: id_anggota (text), nama, no_tlp, tanggal_daftar (date), alamat (textarea)
   - Validation errors

3. `anggota/edit.blade.php` — Form with:
   - Pre-filled data, tanggal_daftar is readonly (green background)
   - PUT method

4. `anggota/show.blade.php` — Detail view with:
   - nama+no_tlp, tanggal_daftar+created_at, alamat (full width)
   - Back and Edit buttons

## 📖 MODULE 3: PINJAM (Borrowing)
**Controller:** PinjamController.php
**Models:** Pinjam (belongs to Anggota and Book), Anggota, Book
**Routes:** /pinjam (index, create, store, show, edit, update, destroy)

**Views:**
1. `pinjam/index.blade.php` — Grid of borrowing cards with:
   - Search by judul buku or nama anggota
   - Status filter: Semua, Dipinjam, Dikembalikan
   - Each card shows: status badge (red for dipinjam, green for dikembalikan), id, book info (with relationship $p->book->judul), anggota name, tanggal_pinjam, tanggal_kembali
   - Edit and Delete actions
   - Success and error flash messages

2. `pinjam/create.blade.php` — Form with:
   - TWO searchable dropdowns with custom JS (anggota and book):
     - Hidden input stores the ID
     - Text input for searching/filtering
     - Dropdown list of options
     - Selected item display (green badge)
   - Fields: anggota_id (searchable select), book_id (searchable select), tanggal_pinjam (default today), tanggal_kembali (date)
   - Inline CSS/JS for searchable select behavior

3. `pinjam/edit.blade.php` — Form with:
   - Anggota and Buku shown as read-only static text (relationship data)
   - Hidden inputs preserve IDs
   - Editable: tanggal_pinjam, tanggal_kembali

4. `pinjam/show.blade.php` — Detail view with:
   - anggota nama+id_anggota, book judul+id_buku, tanggal_pinjam+tanggal_kembali, status (colored badge)+created_at
   - Conditional "Kembalikan" button if status is 'dipinjam'
   - Back and Edit buttons

## 📥 MODULE 4: PENGEMBALIAN (Returns)
**Controller:** PengembalianController.php
**Models:** Pengembalian (has relationships to Pinjam, Anggota, Book)
**Routes:** /pengembalian (index, create, store, show, destroy)
**Business Logic:** Denda = Rp 5,000 per day late. Auto-calculated in controller using Carbon.

**Views:**
1. `pengembalian/index.blade.php` — Grid of return cards with:
   - Search by judul buku or nama anggota
   - Two display modes: search results vs normal riwayat view
   - Each card shows: status badge (late=red, ontime=green), id, book judul, anggota nama, tanggal_pinjam, tanggal_kembali (jatuh tempo), tanggal_dikembalikan, denda amount (formatted with number_format)
   - View and Delete actions

2. `pengembalian/create.blade.php` — Form with:
   - Select dropdown of active borrowings (pinjamList with status 'dipinjam')
   - Each option shows: anggota name, book judul (limited), tanggal_kembali
   - tanggal_dikembalikan (default today)
   - Info box explaining denda calculation
   - Conditional empty state if no active borrowings

3. `pengembalian/show.blade.php` — Detail view with:
   - pinjam_id+anggota nama, id_anggota+book judul, tanggal_pinjam+tanggal_kembali (jatuh tempo), tanggal_dikembalikan (highlighted)+denda
   - Conditional denda info box showing days late calculation using Carbon::diffInDays
   - Back button only

## 🔐 AUTH MODULE
**Views:**
1. `login.blade.php` — Standalone page (no layout extension) with:
   - Custom CSS in `<style>` tag (gradient background, card design)
   - Email and password fields
   - POST to /login
   - CSRF token

2. `dashboard.blade.php` — Standalone page (no layout extension) with:
   - Custom CSS in `<style>` tag
   - Welcome message with $name variable
   - Sign Out form (POST to logout route)
   - Different design from the library system (Student Portal theme)

## 📋 OTHER VIEWS
1. `database-page.blade.php` — Simple Bootstrap-style table for Pengguna CRUD (different styling, extends app layout)
2. `welcome.blade.php` — Default Laravel welcome page with Tailwind

## 🔗 ROUTES SUMMARY
- /login, /dashboard (auth protected with custom middleware)
- /database, /pengguna/* (auth protected)
- /books/* (public)
- /anggota/* (public)
- /pinjam/* (public)
- /pengembalian/* (public)

## ⚙️ CONTROLLER PATTERNS
- All controllers use `validate()` with custom 'data tidak lengkap' required message
- Search uses `whereRaw('LOWER(field) LIKE ?', ["%{$search}%"])` for case-insensitive search
- Relationships loaded with `with(['anggota', 'book'])` for eager loading
- Redirect with `session('success')` flash messages
- BookController auto-generates id_buku as 'BKU' + padded number
- PinjamController checks if book is already borrowed before storing
- PengembalianController auto-calculates denda and updates pinjam status

## 💡 KEY BLADE DIRECTIVES USED
- @extends('layouts.app') + @section('content')
- @if, @elseif, @else, @endif
- @foreach, @endforeach
- @csrf, @method('DELETE'), @method('PUT')
- {{ route('name') }}, {{ url('/path') }}
- {{ old('field', $model->field) }}
- {{ $model->created_at->format('d-m-Y') }}
- {{ Str::limit($text, length) }}
- {{ number_format($number, 0, ',', '.') }}
- @forelse / @empty (database-page only)

---

Based on this complete architecture, my question is:
[REPLACE THIS LINE WITH YOUR QUESTION]
```

---

## 💡 Example Questions You Can Paste

**To modify styling:**
```
I want to change the entire color scheme from green to blue across all views. Which CSS variables/classes should I modify in app.css, and do any inline styles in the Blade files need updating?
```

**To add a feature:**
```
I want to add a "Print" button to every detail view (books/show, anggota/show, pinjam/show, pengembalian/show). What's the best way to implement this without duplicating code?
```

**To fix a bug:**
```
The searchable dropdown in pinjam/create.blade.php and books/create.blade.php has inline CSS and JS. I want to extract these into reusable components. How should I structure partial Blade files for the searchable-select component?
```

**To optimize:**
```
The pinjam/index.blade.php loads all records with $query->get(). I want to add pagination like books/index.blade.php. What changes are needed in both the controller and view?
```

**To understand data flow:**
```
Explain how the denda calculation flows from the PengembalianController to the pengembalian/show.blade.php view, including all variables passed and formatted.
```

**To add validation:**
```
I want to add phone number format validation to the anggota/create and anggota/edit forms. The no_tlp field should only accept Indonesian mobile numbers starting with 08. Show me the controller changes and how to display the error in the view.
```

**To modify layout:**
```
I want to add a footer to the main layout (layouts/app.blade.php) that shows the current logged-in user name and a logout link. How do I access session data in the layout?
```

**To add export:**
```
I want to add an "Export to PDF" button on books/index.blade.php that generates a PDF of the current filtered results. What packages and view modifications do I need?
```

---

## 📌 Tips for Best Results on Gemini

1. **Be specific about the file:** Mention `books/create.blade.php` instead of "the book form"
2. **Mention the CSS class names:** Reference `.form-container`, `.item-card`, etc.
3. **Specify Laravel concepts:** Say "Blade directive" or "Eloquent relationship" when relevant
4. **Include the goal:** "I want to..." + "without breaking..."
5. **Ask for comparisons:** "What's the difference between how Pinjam and Book handle..."
6. **Request full file output:** Say "Provide the complete updated file content"


