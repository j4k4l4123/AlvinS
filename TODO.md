# TODO - Library Management System (Laravel 11)

## Step 1 — Auth/Security Refactor (priority)
- [ ] Install/configure Laravel Breeze (auth) for Laravel `users` table
- [ ] Remove/disable insecure custom login (`PageController@handleLogin`) and session-flag middleware (`CustomAuth`, `RedirectIfAuthenticated`)
- [ ] Implement role system (librarian/member) via `roles` + pivot (recommended)
- [ ] Add role middleware: `librarian`, `member`
- [ ] Update `routes/web.php` to use Breeze `auth` + role middleware
- [ ] Add policies/gates where needed (authorization)
- [ ] Add CSRF protection + form validation via Form Requests

## Step 2 — Member Access System
- [ ] Create member dashboard route/view (member-only)
- [ ] Restrict existing functions so members can only access their own data (borrows, returns, fines)

## Step 3 — Library Card Feature
- [ ] Add migrations/models for `library_cards`
- [ ] Generate unique member card number during member registration
- [ ] Create digital/printable library card views
- [ ] Add card status + expiration logic

## Step 4 — Membership Cancellation Feature
- [ ] Add `membership_requests` migration/model with approval workflow
- [ ] Add member cancellation request UI
- [ ] Add librarian approval endpoints
- [ ] Prevent cancellation when member has active borrows or unpaid fines
- [ ] Soft-disable membership / update status

## Step 5 — Issue/Book/Borrow Flow
- [ ] Add/verify `borrowings` structure aligns with current `pinjam`
- [ ] Add due date + borrowing limits + stock checking
- [ ] Implement fine generation on return using due date vs return date
- [ ] Add borrowing history + overdue calculation

## Step 6 — Architecture/Quality Pass
- [ ] Convert large controllers to smaller RESTful resource controllers
- [ ] Introduce Form Requests for each action
- [ ] Introduce services (e.g., `BorrowingService`, `FineService`)
- [ ] Apply pagination to listings
- [ ] Add error pages (403/404/500)
- [ ] Ensure relationships/constraints are correct in models

