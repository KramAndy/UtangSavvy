# UtangSavvy

A lightweight store credit and inventory management system for small businesses — particularly sari-sari stores and resellers that let regular customers buy on credit (*utang*).

UtangSavvy combines a product inventory, customer debt ledger, and transaction reporting into one PHP/MySQL web application. Each customer has a credit tier with a spending limit, an open ledger for their current balance, and a permanent history of settled ledgers.

> **Version:** 1.0 · **Stack:** PHP · MySQL/MariaDB · HTML/CSS/JavaScript

---

## Features

### Authentication & Users
- Login and registration with hashed passwords (`password_hash` / `password_verify`).
- Session-based access control — every application page is protected by `auth.php`.
- User management: add, edit, and delete accounts.

### Inventory Management
- Product CRUD with title, description, price, stock count, category, brand, and image upload.
- Product list with live AJAX search by name, category, or brand.
- Uploaded images stored in `uploads/`.
- Category and brand management with duplicate-name checks.
- Out-of-stock products are automatically disabled in the ledger product picker.

### Customer Ledger (Store Credit)
- Add borrowers with a contact number and credit tier.
- Open a customer ledger and add products, cash loans, or both.
- Ledger books are created automatically on the first credit entry.
- **Credit limit enforcement** — a new entry is rejected if it would push the customer past their tier limit.
- Mark a ledger as paid to close it; closed ledgers remain available in the customer's history.
- Search customers by name or tier.

### Transactions & Reporting
- Filter transaction reports by products, brands, or categories.
- Date-range filtering and client-side live search.
- PDF report generation using the bundled FPDF library.

### UI
- Responsive layout with a collapsible sidebar/hamburger navigation.
- Toastr notifications for success and error feedback.

---

## Credit Tiers

| Tier | Credit Limit |
|------|-------------:|
| Bronze | ₱2,000 |
| Silver | ₱5,000 |
| Gold | ₱10,000 |
| Platinum | ₱20,000 |

Limits are defined in `process_ledger.php` and `view_ledger.php`. If you change them, update both files.

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP (procedural, `mysqli` with prepared statements) |
| Database | MySQL / MariaDB |
| Frontend | HTML5, CSS3, vanilla JavaScript, jQuery 3.6 |
| Notifications | Toastr |
| PDF export | FPDF 1.86 (bundled in `fpdf/`) |

---

## Requirements

- PHP **7.4+** (PHP 8.x recommended) with the `mysqli` extension and `mysqlnd` driver
- MySQL **5.7+** or MariaDB **10.4+**
- Apache or another web server (XAMPP/LAMP/WAMP is ideal)
- Internet access for the jQuery and Toastr CDNs (or download them locally)
- Write permission on the `uploads/` folder

---

## Installation

### 1. Place the project in your web root

For XAMPP, copy the project to:

```
C:\xampp\htdocs\UtangSavvy
```

### 2. Start Apache and MySQL

Open the XAMPP Control Panel and start **Apache** and **MySQL**.

### 3. Create the database

Open phpMyAdmin (`http://localhost/phpmyadmin`) and create a database named `inventory`, then run the schema below.

> **Note:** This repository does not ship a database dump. The schema below is reconstructed from the queries used throughout the application.

```sql
CREATE DATABASE IF NOT EXISTS inventory
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE inventory;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE category (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cat_title VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE brands (
  id INT AUTO_INCREMENT PRIMARY KEY,
  brand_title VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_title VARCHAR(150) NOT NULL UNIQUE,
  product_desc TEXT NOT NULL,
  product_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  quantity INT NOT NULL DEFAULT 0,
  product_img VARCHAR(255) DEFAULT NULL,
  product_keywords VARCHAR(255) DEFAULT NULL,
  product_cat INT NOT NULL,
  product_brand INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_product_category
    FOREIGN KEY (product_cat) REFERENCES category(id) ON DELETE RESTRICT,
  CONSTRAINT fk_product_brand
    FOREIGN KEY (product_brand) REFERENCES brands(id) ON DELETE RESTRICT
);

CREATE TABLE debtors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  contact VARCHAR(50) DEFAULT NULL,
  tier ENUM('Bronze','Silver','Gold','Platinum') NOT NULL DEFAULT 'Bronze',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ledger_books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  debtor_id INT NOT NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  closed_at DATETIME DEFAULT NULL,
  CONSTRAINT fk_book_debtor
    FOREIGN KEY (debtor_id) REFERENCES debtors(id) ON DELETE CASCADE
);

CREATE TABLE ledger_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  product_id INT DEFAULT NULL,
  description VARCHAR(255) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_entry_book
    FOREIGN KEY (book_id) REFERENCES ledger_books(id) ON DELETE CASCADE,
  CONSTRAINT fk_entry_product
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);
```

> Categories and brands that are still referenced by products cannot be deleted under this schema. Delete or reassign the affected products first.

### 4. Configure the connection

Edit `config.php` if your database credentials differ from the defaults:

```php
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "inventory";
```

### 5. Set folder permissions

Make sure `uploads/` is writable by the web server. On Windows/XAMPP this usually works out of the box.

### 6. Run the app

Visit:

```
http://localhost/UtangSavvy/
```

Click **Register** to create the first account, then log in.

---

## Usage Workflow

1. **Set up catalog data** — add categories and brands before creating products.
2. **Add products** — provide a name, description, price, stock count, category, brand, and image.
3. **Add a borrower** — enter their name, contact number, and tier.
4. **Record credit** — open the borrower's ledger, pick products from inventory and/or enter a cash amount, then submit. A new ledger book opens automatically if none is active.
5. **Monitor limits** — the remaining credit and tier limit are shown on the ledger page. Submissions that exceed the limit are blocked.
6. **Settle a ledger** — click **Mark As Paid** to close the current book. It moves to the customer's previous-credit history.
7. **Review reports** — use **Transactions** to filter by products, brands, or categories and by date range.

---

## Main Pages

| File | Purpose |
|------|---------|
| `index.php` | Login page |
| `register.php` | Account registration |
| `dashboard.php` | Quick-link dashboard |
| `product_inventory.php` | Product list with live search |
| `add_item.php` / `edit_product.php` | Add and edit products |
| `update_product.php` | Product update handler |
| `brands.php` / `categories.php` | Manage brands and categories |
| `ledger.php` | Borrower list and add-borrower form |
| `view_ledger.php` | Active ledger, credit entry form, closed-ledger history |
| `view_closed_book.php` | Details of a settled ledger |
| `report.php` | Filterable transaction reports |
| `export_pdf.php` | PDF report generator |
| `usermanagement.php` | User list |
| `add_user.php` / `edit_user.php` | Add and edit users |

### Supporting Files

| File | Purpose |
|------|---------|
| `config.php` | MySQL connection settings |
| `auth.php` | Session guard included by protected pages |
| `helper.php` | Toastr notification helpers |
| `delete.php` | Shared delete handler for users, categories, brands, and products |
| `search_inventory.php` / `search_ledger.php` | AJAX search endpoints |
| `process_debtor.php` | Borrower creation handler |
| `process_ledger.php` | Credit entry handler and limit validation |
| `close_book.php` | Marks a ledger book as paid |
| `edit_tier.php` | Updates a borrower's tier |
| `logout.php` | Destroys the session |

### Project Structure

```
UtangSavvy/
├── *.php                  # Application pages and handlers
├── *.css                  # Per-page styles
├── auth.php               # Session authentication guard
├── config.php             # Database configuration
├── helper.php             # Toastr helper functions
├── fpdf/                  # Bundled FPDF 1.86 library
├── img/                   # Logos and navigation icons
└── uploads/               # Product images
```

---

## Notes & Limitations

- **Stock is not decremented automatically.** Products added to a ledger do not reduce `products.quantity`; inventory is tracked separately from credit transactions.
- **All users have full access.** There are no roles or permission levels — any logged-in user can manage products, users, and ledgers.
- **Registration is open.** Anyone who can reach the app can create an account. Disable or restrict `register.php` for private deployments.
- **No CSRF protection.** Forms do not include CSRF tokens.
- **Uploads use original filenames** and are not validated by type or size. Restrict this for public deployments.
- **PDF export is wired but hidden.** The export button in `report.php` is commented out; visit `export_pdf.php?filter=products` directly or uncomment the form to enable it.
- **Legacy pages.** `product_catalog.php` and `category_brand.php` are older, unlinked variants and are not part of the main navigation.
- **Production hardening.** Move `config.php` outside the web root or use environment variables, change the default database credentials, serve the app over HTTPS, and host jQuery/Toastr locally if offline use is required.

---

## Third-Party Components

- [FPDF](http://www.fpdf.org/) 1.86 — PDF generation (see `fpdf/license.txt`)
- [jQuery](https://jquery.com/) 3.6
- [Toastr](https://github.com/CodeSeven/toastr)

No license file is included for the application itself.
