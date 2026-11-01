# Spendly — Personal Expense Tracker

> **Tagline:** *Track your spending. Stay in control.*  
> **Target Environment:** XAMPP (Apache + MySQL / MariaDB + PHP 8.x)  
> **Language:** English  
> **Initial Currency:** IDR (Rp)  
> **Milestone Status:** **Milestone 6 — Design System v1 & Dashboard Redesign Implemented**

Spendly is a lightweight, responsive web application designed for personal income and expense tracking, category budgeting, and financial summaries. Built with native PHP, MySQL, vanilla JavaScript, and modern semantic CSS without external frameworks, it is clean, easy to run locally, and simple to explain for a school final project presentation.

---

## 1. Stack and Architecture

- **Backend:** Native PHP (v8.0+ recommended), PDO MySQL with prepared statements and exception handling.
- **Database:** MySQL 8.0+ or MariaDB 10.4+ (InnoDB engine, `utf8mb4` charset).
- **Frontend:** Semantic HTML5, modern responsive CSS (warm neutral theme, sage green accents), and vanilla JavaScript.
- **Local Server:** XAMPP on Windows, macOS, or Linux.
- **External Dependencies:** Zero external libraries or Composer packages.

---

## 2. Milestone 1 — Foundation

- [x] **Relational Database Schema:** 5 normalized tables (`users`, `categories`, `payment_methods`, `transactions`, `budgets`) with strict foreign keys, unique constraints, and indexes.
- [x] **Secure Database Connection:** PDO singleton with exception error mode, prepared statements, and custom error screens that hide raw SQL credentials and errors.
- [x] **User Authentication:**
  - Account registration with full name, email validation, password (min. 8 characters), and password confirmation.
  - Server-side unique email enforcement.
  - Cryptographic password hashing via `password_hash()` (`PASSWORD_DEFAULT`).
  - Secure credential verification via `password_verify()`.
  - Session fixation defense via `session_regenerate_id(true)`.
  - Safe logout clearing session variables, cookie headers, and session storage.
- [x] **Data Seeding in Transactions:**
  - When a user registers, an atomic database transaction (`PDO::beginTransaction()` / `commit()`) automatically seeds that user's default categories (4 Income, 8 Expense) and 4 default payment methods (`Cash`, `Bank Transfer`, `E-Wallet`, `Debit / Credit Card`).
- [x] **Security Hardening:**
  - **CSRF Protection:** Cryptographic CSRF tokens on all state-changing POST forms.
  - **XSS Prevention:** Context-aware HTML escaping (`htmlspecialchars` with `ENT_QUOTES`).
  - **IDOR Protection:** All queries strictly scoped by the authenticated session user ID.
  - **Authentication Guard:** Reusable `require_login()` and `require_guest()` middleware functions.
  - **Secret Hygiene:** `config/database.php` is ignored by Git; committed repository only contains `config/database.example.php`.

---

## 3. Milestone 2 — Transaction Management (New)

Milestone 2 activates complete financial transaction management:

- [x] **Create Transactions (`transaction-create.php`):**
  - Record income or expense transactions with positive amounts (`DECIMAL(14,2)`).
  - Dynamic UI synchronization: Category dropdown automatically displays only categories matching the selected transaction type (Income vs. Expense).
  - Optional payment method selection from the user's seeded payment methods.
  - Date picker defaulting to the current date (`YYYY-MM-DD`).
  - Optional description/note (max 255 characters).
- [x] **Read & Filter Transaction History (`transactions.php`):**
  - Chronological transaction log, newest first (`ORDER BY transaction_date DESC, id DESC`).
  - Multi-parameter filtering:
    - By Type (`All`, `Income`, `Expense`).
    - By Category (user-scoped categories).
    - By Date range (`start_date` to `end_date`).
  - Live summary ribbon calculating matching transaction count, filtered income, and filtered expenses.
  - Colored transaction amounts (`+ Rp ...` in green for income, `- Rp ...` in red for expenses) and category indicator dots.
- [x] **Edit Transactions (`transaction-edit.php`):**
  - Pre-filled edit form with existing transaction details.
  - Strict ownership validation: prevents editing transactions belonging to other users.
- [x] **Delete Transactions with Confirmation (`transaction-delete.php`):**
  - Dedicated POST action protected with CSRF verification (rejects GET requests).
  - Interactive browser confirmation prompt before deletion.
  - Scoped strictly to authenticated `user_id`.
- [x] **Live Dashboard Integration (`dashboard.php`):**
  - **Current Balance:** Live calculation: `SUM(all income) - SUM(all expenses)`.
  - **Monthly Income:** Live sum of income records for the current calendar month.
  - **Monthly Expenses:** Live sum of expense records for the current calendar month.
  - **Recent Transactions:** Displays the 5 latest transactions with direct "Edit" links and a "View All" shortcut.
  - Truthful transitions from empty state to populated records as soon as transactions are added.
- [x] **Strict Server-Side Validation & Security:**
  - Verifies that the chosen category exists, belongs to the current user, and its `type` strictly matches the transaction `type`.
  - Verifies payment method ownership.
  - Rejects zero or negative amounts.
  - All database reads and writes use PDO prepared statements.

---

## 4. Milestone 3 — Budget Management

Milestone 3 activates monthly expense budgets using the existing `budgets` table. No database migration is required.

- [x] Create one budget per expense category and calendar month.
- [x] Edit the category, month, or amount limit of an existing budget.
- [x] Delete a budget without deleting or changing transaction records.
- [x] Calculate actual expense totals from transactions in the selected month.
- [x] Show each category's spending progress, remaining amount, and over-budget status.
- [x] Display a three-category budget snapshot on the dashboard.
- [x] Enforce expense-category ownership, unique user/category/month combinations, CSRF protection, and user-scoped CRUD operations.
- [x] Preserve IDR decimal values using the existing `DECIMAL(14,2)` database column.

Budget status rules:
- **On track:** below 80% of the limit.
- **Near limit:** 80% through 100% of the limit.
- **Over budget:** actual spending is greater than the limit.

The budget summary includes only categories with a budget configured for the selected month. Expenses in unbudgeted categories are not included in the tracked spending total.

---

## 5. Milestone 4 — Categories & Payment Methods

Milestone 4 allows each user to manage the categories and payment method labels used in transaction records.

- [x] List and separate income and expense categories.
- [x] Create categories with a name, type, and color.
- [x] Edit category names and colors.
- [x] Change category type only while no transactions or budgets reference it.
- [x] Delete a category only when no transactions or budgets reference it.
- [x] Create, list, rename, and delete payment methods.
- [x] Protect payment methods referenced by existing transactions from deletion.
- [x] Enforce unique names per user and category type.
- [x] Use authenticated user ownership checks, PDO prepared statements, and CSRF-protected POST forms.
- [x] Reuse the existing categories and payment_methods tables; no schema migration is required.

Existing transactions keep their linked category/payment method IDs when labels are renamed. Deleting a category or payment method that is in use is blocked to preserve financial history.

---

## 6. Milestone 5 — Analytics & Reports

Milestone 5 provides a read-only monthly financial report calculated from the authenticated user's saved transactions.

- [x] Month selector with validation and current-month shortcut.
- [x] Monthly income, expenses, net cash flow, transaction count, and savings rate.
- [x] Six-month income-versus-expense trend ending in the selected month.
- [x] Expense breakdown by category with amounts, transaction counts, and percentage share.
- [x] Income source breakdown by category.
- [x] Expense totals grouped by payment method, including an Unspecified group.
- [x] Budget performance summary for the selected month.
- [x] Honest empty states when no transactions or budgets exist.
- [x] User-scoped PDO queries; no schema migration or external chart library required.

The savings rate is calculated as `(income - expenses) / income × 100`. It is not shown when the selected month has no income. Payment method analysis includes expense transactions only. Budget performance includes only expense categories with a budget configured for the selected month.

---

## 7. Design System

Spendly uses a **Material 3-inspired component foundation** with semantic color roles, tonal surfaces, rounded controls, a persistent navigation drawer, and restrained elevation.

- **Color roles:** forest-green primary, primary and secondary containers, neutral surface containers, and distinct income, expense, warning, and information roles.
- **Surface hierarchy:** white cards and data surfaces on a softly tinted application canvas; deeper tonal containers provide grouping without excessive borders.
- **Typography:** system-first Roboto / Inter / sans-serif stack, clear heading levels, and tabular-friendly financial figures.
- **Shape:** pill buttons and navigation indicators, 10px inputs, and 16px cards.
- **Navigation:** icon-led desktop sidebar with an accessible mobile drawer; translucent treatment is reserved for navigation.
- **Interaction:** visible keyboard focus, selected and disabled states, short transitions, and reduced-motion support.
- **Dashboard:** three tonal financial metrics, compact budget tracking, recent activity, and account setup summaries.

The full token map and component guidance live in `design-system.md`. No external UI framework, font CDN, icon package, or chart dependency is required.

## 8. Database Schema Overview

The database `spendly_db` contains five tables:

```text
       users (id, full_name, email, password_hash, created_at, updated_at)
       ├── categories (id, user_id, name, type, color, created_at)
       │    └── transactions (id, user_id, category_id, payment_method_id, type, amount, date, ...)
       │    └── budgets (id, user_id, category_id, budget_month, amount_limit, ...)
       └── payment_methods (id, user_id, name, created_at)
            └── transactions
```

### Table Relationships & Key Constraints:
1. **`users`**:
   - `email`: UNIQUE constraint (`uq_users_email`).
2. **`categories`**:
   - Belongs to `users` (`ON DELETE CASCADE`).
   - Unique per user, name, and type: `uq_categories_user_name_type (user_id, name, type)`.
3. **`payment_methods`**:
   - Belongs to `users` (`ON DELETE CASCADE`).
   - Unique per user and name: `uq_payment_methods_user_name (user_id, name)`.
4. **`transactions`**:
   - Belongs to `users` (`ON DELETE CASCADE`).
   - References `categories` with `ON DELETE RESTRICT` (prevents accidental deletion of categories that have transaction records).
   - References `payment_methods` with `ON DELETE RESTRICT`.
   - Money stored as `DECIMAL(14,2)` for precision.
5. **`budgets`**:
   - Belongs to `users` and an expense `category` (`ON DELETE RESTRICT`).
   - `budget_month` stored as the 1st of the month (`YYYY-MM-01`).
   - Unique per user, category, and month: `uq_budgets_user_category_month`.

---

## 9. XAMPP Local Setup Instructions

Follow these step-by-step instructions to run Spendly on your computer using XAMPP:

### Step 1: Copy Project to XAMPP Web Root
1. Download or clone this repository.
2. Move the `spendly` folder into your XAMPP `htdocs` directory:
   - **Windows:** `C:\xampp\htdocs\spendly`
   - **macOS:** `/Applications/XAMPP/xamppfiles/htdocs/spendly`
   - **Linux:** `/opt/lampp/htdocs/spendly`

### Step 2: Start Services in XAMPP Control Panel
1. Open the **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. Click **Start** for **MySQL**.

### Step 3: Create Database & Import Schema
1. Open your browser and navigate to phpMyAdmin:
   ```text
   http://localhost/phpmyadmin/
   ```
2. Click the **Databases** tab.
3. In "Create database", enter `spendly_db`, choose `utf8mb4_unicode_ci` (or `utf8mb4_general_ci`), and click **Create**.
4. Click on the newly created `spendly_db` database in the left sidebar.
5. Click the **Import** tab at the top.
6. Under **File to import**, click **Choose File** and select:
   ```text
   spendly/database/schema.sql
   ```
7. Click **Import** (or **Go**) at the bottom. You should see a green success message confirming that the tables were created.

### Step 4: Configure Database Credentials
1. In your `spendly/config/` folder, make a copy of `database.example.php` named `database.php`:
   ```bash
   cp config/database.example.php config/database.php
   ```
   *(On Windows, you can simply right-click `database.example.php`, copy, paste, and rename it to `database.php`).*
2. Open `config/database.php` in any text editor. The default XAMPP settings are already filled in:
   ```php
   return [
       'host'     => '127.0.0.1',
       'port'     => 3306,
       'dbname'   => 'spendly_db',
       'username' => 'root',
       'password' => '', // Default XAMPP MySQL root has no password
       'charset'  => 'utf8mb4',
   ];
   ```
3. If your XAMPP MySQL has a root password, fill it in under `'password'`.

### Step 5: Launch Spendly
1. In your web browser, navigate to:
   ```text
   http://localhost/spendly/
   ```
2. You will be automatically redirected to the **Sign In** page.
3. Click **Create an account** (or visit `http://localhost/spendly/register.php`).
4. Register a new user:
   - **Full Name:** e.g., `Mayang Zahra`
   - **Email:** e.g., `mayang@example.com`
   - **Password:** e.g., `password123`
   - **Confirm Password:** e.g., `password123`
5. Click **Register Account**.
6. Sign in with your new email and password.
7. You will be taken to your **Dashboard**, which displays a clean, honest empty state with `Rp 0` totals and your newly seeded default categories and payment methods!

---

## 10. Project Directory Structure

```text
spendly/
├── assets/
│   ├── css/
│   │   └── app.css             # Semantic styles, responsive grid, light theme
│   ├── js/
│   │   └── app.js              # Vanilla JS: mobile menu toggle, alert dismiss, password helper
│   └── images/                 # Static visual assets
├── config/
│   ├── database.example.php    # Committed template with placeholders
│   └── database.php            # Local configuration (ignored by Git)
├── database/
│   └── schema.sql              # Clean DDL schema (5 tables, InnoDB, utf8mb4)
├── includes/
│   ├── auth.php                # Authentication logic, password hashing, session guards, seed helper
│   ├── csrf.php                # CSRF token generation and validation helpers
│   ├── db.php                  # PDO connection manager with graceful error screens
│   ├── functions.php           # Security helpers (e()), currency formatting (IDR), flash messages
│   ├── transactions.php        # Transaction database operations and input validation
│   ├── budgets.php             # Monthly budget database operations and validation
│   ├── catalog.php             # Category and payment method CRUD operations
│   ├── reports.php             # Monthly analytics query helpers
│   ├── header.php              # Shared sidebar shell, guest header, and flash alerts
│   └── footer.php              # Shared footer layout and JS inclusion
├── dashboard.php               # Protected dashboard with real DB aggregates and refreshed design
├── design-system.md            # Spendly visual tokens, component rules, and glass usage guidelines
├── index.php                   # Entry router (redirects to dashboard or login)
├── login.php                   # Sign-in form with server validation & CSRF
├── logout.php                  # Safe session destruction action
├── register.php                # Registration form with DB transaction seeding & CSRF
├── transactions.php            # Transaction history listing, search & multi-parameter filtering
├── transaction-create.php      # Form to record income/expense with type-synced categories
├── transaction-edit.php        # Form to modify existing transaction with strict ownership check
├── transaction-delete.php      # Action to delete transaction with CSRF & ownership validation
├── budgets.php                  # Monthly budget overview and progress
├── budget-create.php            # Create a category budget
├── budget-edit.php              # Edit a category budget
├── budget-delete.php            # POST-only budget deletion with CSRF protection
├── categories.php               # Income and expense category management
├── category-create.php          # Create a category
├── category-edit.php            # Edit a category
├── category-delete.php          # Protected category deletion
├── payment-methods.php          # Payment method management
├── payment-method-create.php    # Create a payment method
├── payment-method-edit.php      # Rename a payment method
├── payment-method-delete.php    # Protected payment method deletion
├── reports.php                   # Monthly analytics and financial reports
├── .gitignore                  # Excludes local secrets (config/database.php), logs, OS files
└── README.md                   # Project documentation and setup guide
```

---

## 11. School Presentation Talking Points

If presenting this project to a teacher or examiner:
1. **Security-First Foundation:** Explain that passwords are never stored in plain text (`password_hash` with bcrypt), every query uses PDO prepared statements to block SQL injection, CSRF tokens prevent cross-site request forgery, and `htmlspecialchars()` stops cross-site scripting (XSS).
2. **Database Normalization & Integrity:** Show the 5 tables in phpMyAdmin. Highlight foreign keys and explain why `ON DELETE RESTRICT` was chosen for categories and payment methods (to prevent orphaned transactions).
3. **Database Transactions:** Explain how registration uses `BEGIN TRANSACTION` / `COMMIT` to ensure user registration and default category seeding either both succeed or cleanly roll back together.
4. **No Fake Data:** Point out that when a user registers, their dashboard shows an honest `Rp 0` balance and empty state rather than hard-coded decorative placeholder numbers.
