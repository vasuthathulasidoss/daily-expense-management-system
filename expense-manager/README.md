# Daily Expense Management System

A production-ready, secure, and responsive web application built with **HTML5, CSS3, Vanilla JavaScript (ES6+), PHP 8+ (PDO), and MySQL** to manage personal income, daily expenditures, category budgets, and financial reporting.

---

## 🚀 Key Features

1. **Authentication & Session Security**
   - User registration and login with `password_hash()` (bcrypt) and `password_verify()`.
   - `session_regenerate_id()` on login to prevent session fixation.
   - Strict user data isolation: each user only sees and modifies their own financial data.
   - Cross-Site Request Forgery (CSRF) protection on every `POST` request.

2. **Executive Dashboard**
   - Summary cards: **Total Income**, **Total Expense**, **Net Savings (Period)**, and **All-Time Lifetime Balance**.
   - Period filtering: Today, This Week, This Month, and Custom Date Range.
   - Interactive Visualizations with Chart.js:
     - **Expense by Category Doughnut Chart** with percentages.
     - **Daily Cashflow Bar Chart** (Income vs. Expense per day for the current month).
   - Real-time lists of **Today's Transactions** and **Recent 5 Transactions**.

3. **Income Management**
   - Log income entries with Amount, Source/Category, Date, Payment Method, and Notes.
   - Full CRUD: Add, Edit, Delete (with confirmation modal), and List.

4. **Expense Management**
   - Log daily expenses with Amount, Category, Date, Payment Method (Cash, UPI, Card, Bank), and Notes.
   - Visual red indicators and formatted currency everywhere.

5. **Category Management**
   - Pre-seeded with standard categories (Food, Transport, Rent, Bills, Shopping, Health, Education, Entertainment, Salary, Freelance, Investments, Other).
   - Create, edit, and delete custom categories.
   - **Accidental Deletion Protection**: Categories linked to existing transactions cannot be deleted until transactions are reassigned or removed.

6. **Transactions Ledger**
   - Combined chronological record of both income and expenses.
   - Multi-criteria filtering: Search notes/category keyword, Type filter, Category filter, Payment method filter, and Date ranges.
   - Column sorting (Date, Amount, Category) and pagination (10 entries per page).
   - **One-Click Export to CSV** with Excel UTF-8 BOM encoding.

7. **Monthly Category Budgets**
   - Set monthly spending caps per expense category.
   - Dynamic visual progress bars:
     - 🟢 0% - 74%: Normal
     - 🟡 75% - 99%: Approaching Limit
     - 🔴 &ge; 100%: Over-Budget Alert Badge & Highlight

8. **Financial Reports & Analytics**
   - Monthly summary with Category-wise percentage distribution.
   - **Month-over-Month Variance Analysis** comparing the active month with the previous month (deltas and savings rates).
   - Dedicated print-friendly layout (`window.print()` / PDF export).

9. **Settings & Profile**
   - Update display name, email, and password.
   - Configurable currency symbol (default **₹**, with support for $, €, £, ¥, AED, SAR, etc.).
   - Light/Dark mode toggle saved across sessions in `localStorage`.

---

## 🛠️ Project Structure

```
expense-manager/
├── config/
│   └── db.php                  # PDO singleton connection & XAMPP defaults
├── includes/
│   ├── functions.php           # Security, CSRF, flash messages & helpers
│   ├── auth_check.php          # Route guard & user session loader
│   ├── header.php              # Responsive sidebar & navigation
│   └── footer.php              # Delete modal dialog & script loader
├── auth/
│   ├── login.php               # User login with demo auto-fill
│   ├── register.php            # User registration & default category seeding
│   └── logout.php              # Secure session termination
├── dashboard.php               # Overview cards, charts & quick tables
├── income/
│   ├── index.php               # Income list
│   ├── add.php                 # Record income
│   ├── edit.php                # Modify income
│   └── delete.php              # Secure income deletion
├── expenses/
│   ├── index.php               # Expense list
│   ├── add.php                 # Record expense
│   ├── edit.php                # Modify expense
│   └── delete.php              # Secure expense deletion
├── categories/
│   ├── index.php               # Dual category panels & transaction guards
│   ├── save.php                # Category upsert
│   └── delete.php              # Category deletion with constraint check
├── transactions.php            # Combined ledger, search, filters & CSV export
├── budgets.php                 # Monthly category budget progress bars
├── reports.php                 # Monthly breakdown, variances & print view
├── profile.php                 # Profile, password & currency settings
├── api/
│   ├── chart_data.php          # JSON endpoint for dynamic charts
│   └── filter_transactions.php # JSON endpoint for AJAX ledger filtering
├── assets/
│   ├── css/style.css           # CSS variables, dark/light theme, responsive grid
│   ├── js/app.js               # Theme switcher, drawer, modal handlers
│   └── js/charts.js            # Chart.js initialization & responsive updates
├── database.sql                # MySQL schema, foreign keys & seed data
├── index.php                   # Redirects to dashboard/login
└── README.md                   # Documentation & setup guide
```

---

## 💻 XAMPP / WAMP Setup Instructions

Follow these 4 simple steps to run this application on your local machine:

### Step 1: Copy to your Web Root
Copy or extract the `expense-manager` folder into your Apache document root:
- **XAMPP on Windows:** `C:\xampp\htdocs\expense-manager`
- **XAMPP on macOS:** `/Applications/XAMPP/xamppfiles/htdocs/expense-manager`
- **WAMP on Windows:** `C:\wamp64\www\expense-manager`
- **Linux (LAMP):** `/var/www/html/expense-manager`

### Step 2: Start Apache & MySQL
1. Open the **XAMPP Control Panel** (or WAMP).
2. Click **Start** for both **Apache** and **MySQL**.
3. Verify that both show green status indicators.

### Step 3: Import the Database (`database.sql`)
1. Open your web browser and go to `http://localhost/phpmyadmin`.
2. Click the **"New"** database button on the left panel.
3. Enter database name: `expense_manager` and collation `utf8mb4_unicode_ci`, then click **Create**.
4. Select the newly created `expense_manager` database in the left sidebar.
5. Click the **"Import"** tab in the top navigation.
6. Click **"Choose File"** and select `expense-manager/database.sql`.
7. Scroll down and click **"Import"**. All tables, foreign key constraints, indexes, and demo data will be created!

### Step 4: Verify Database Connection (`config/db.php`)
Open `config/db.php` in any text editor. By default, XAMPP uses:
```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'expense_manager');
define('DB_USER', 'root');
define('DB_PASS', ''); // default in XAMPP is empty password
```
*(If your local MySQL uses a custom password or port, update `DB_PASS` or `DB_PORT` accordingly).*

### Step 5: Launch in Browser
Open your browser and navigate to:
```
http://localhost/expense-manager/
```

---

## 🔑 Default Demo Credentials

The database seed automatically creates a demo user with realistic transactions:

- **Email:** `demo@example.com`
- **Password:** `password123`

You can also click the **"Use Demo Account"** quick-fill button on the login screen, or create a brand new account via the **"Create Account"** tab!

---

## 🛡️ Security Implementation Details
- **Prepared Statements Everywhere:** All queries utilize PDO prepared statements with parameter binding. No raw SQL concatenation.
- **XSS Prevention:** All output rendered into HTML templates passes through `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` via the `e()` helper function.
- **CSRF Token:** Tokens are generated cryptographically (`bin2hex(random_bytes(32))`), stored in session, and verified using timing-attack-safe `hash_equals()`.
- **Ownership Verification:** Every edit and delete operation strictly filters by `user_id = :user_id`, preventing unauthorized cross-user modifications.
