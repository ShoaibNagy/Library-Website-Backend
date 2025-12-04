# Library Website Backend To-Do List

## 1. Project Setup & Configuration
- [x] **Environment Setup**
    - [x] Configure `.env` file (Database credentials, App URL, Debug mode).
    - [x] Generate Application Key (`php artisan key:generate`).
    - [x] Set up Git repository and `.gitignore`.
- [x] **Dependencies**
    - [x] Install Laravel Sanctum for API Authentication (`composer require laravel/sanctum`).
    - [x] Install specific packages (e.g., `barryvdh/laravel-dompdf` for reports, if needed).

## 2. Database Design & Migrations
- [x] **Users Table**
    - [x] Add `role` column (admin, librarian, member) or create a separate `roles` table.
    - [x] Add `status` (active, suspended).
- [x] **Books Table**
    - [x] Columns: `title`, `isbn`, `publisher`, `publication_year`, `description`, `cover_image`, `total_copies`, `available_copies`.
- [x] **Authors Table**
    - [x] Columns: `name`, `bio`.
- [x] **Categories/Genres Table**
    - [x] Columns: `name`, `slug`.
- [x] **Loans Table**
    - [x] Columns: `user_id`, `book_id`, `borrowed_at`, `due_date`, `returned_at`, `fine_amount`, `status` (borrowed, returned, overdue).
- [x] **Reservations Table** (Optional)
    - [x] Columns: `user_id`, `book_id`, `reserved_at`, `status`.

## 3. Models & Eloquent Relationships
- [x] **User Model**: `hasMany(Loan)`, `hasMany(Reservation)`.
- [x] **Book Model**: `belongsTo(Author)`, `belongsTo(Category)`, `hasMany(Loan)`.
- [x] **Author Model**: `hasMany(Book)`.
- [x] **Loan Model**: `belongsTo(User)`, `belongsTo(Book)`.

## 4. Authentication & Authorization
- [x] **Auth API**
    - [x] Login Endpoint (Issue Token).
    - [x] Register Endpoint (Member registration).
    - [x] Logout Endpoint (Revoke Token).
    - [x] Password Reset Flow.
- [x] **Middleware & Gates**
    - [x] Create `IsAdmin` middleware.
    - [x] Create `IsLibrarian` middleware.
    - [x] Define Policies for Book/Loan management (who can edit/delete).

## 5. API Endpoints (Controllers)
- [x] **Book Management** (Admin/Librarian)
    - [x] `POST /api/books` - Add new book.
    - [x] `PUT /api/books/{id}` - Update book.
    - [x] `DELETE /api/books/{id}` - Remove book.
- [x] **Public/Member Access**
    - [x] `GET /api/books` - List books (with pagination, search, filter).
    - [x] `GET /api/books/{id}` - View book details.
- [x] **Loan Operations**
    - [x] `POST /api/loans/borrow` - Borrow a book (Validate availability & user limit).
    - [x] `POST /api/loans/return` - Return a book (Calculate fines).
    - [x] `GET /api/loans/my-history` - Member's borrowing history.
    - [x] `GET /api/loans/overdue` - List overdue loans (Admin/Librarian).

## 6. Business Logic & Services
- [x] **Fine Calculation Service**: Logic to calculate fines based on overdue days.
- [x] **Inventory Management**: Auto-decrement `available_copies` on borrow, increment on return.
- [x] **Validation**: Use Form Requests for all inputs (e.g., `StoreBookRequest`, `BorrowBookRequest`).

## 7. Testing
- [x] **Unit Tests**: Test Model relationships and scopes.
- [x] **Feature Tests**: Test all API endpoints (happy path & error cases).

## 8. Documentation
- [x] Setup API Documentation (e.g., using Scribe or Swagger/OpenAPI).
