# Warehouse Management System (WMS)

A simple, PHP-based warehouse management app for tracking Products, Suppliers, Sales, and Users with an admin dashboard and basic reports.

## Overview

- Manage products (create, edit, delete, view)
- Manage suppliers and link them to products
- Record sales and generate a sales report
- Admin-only user management
- File uploads for product images and user avatars

## Demo Login (change after first use)

- Username: anmol
- Password: anmol

Change the password immediately after logging in for security.

## Requirements

- Windows (XAMPP/WAMP recommended) or macOS/Linux with Apache + PHP
- PHP 7.4+ (PHP 8.x recommended)
- MySQL/MariaDB
- Browser (Chrome/Edge/Firefox)

## Quick Start (Windows + XAMPP)

1. Install and start XAMPP (Apache + MySQL).
2. Clone the repo into your web root:
   ```bash
   git clone https://github.com/anm00lll/anm00lll-Warehouse-Management-System.git
   ```
   Move the folder to `C:\xampp\htdocs\warehouse-management-system` if needed.
3. Create the database and import schema:
   - Open phpMyAdmin → Databases → Create `warehouse-management-system`
   - Import [database.sql](database.sql)
4. Configure DB connection:
   - Update credentials in [includes/config.php](includes/config.php)
     - `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`
5. Visit the app:
   ```
   http://localhost/warehouse-management-system
   ```
6. Log in with the demo credentials and change the password.

## Usage Guide

- Dashboard: Overview after login.
- Products: Add, edit, delete, and view products. Images stored under [uploads/products](uploads/products).
- Suppliers: Maintain supplier info and associate with products.
- Sales: Record sales entries and review totals; see [sales_report.php](sales_report.php) for report.
- Users (Admin): Create/manage users. Avatars stored under [uploads/users](uploads/users).

## Configuration

- Database settings live in [includes/config.php](includes/config.php).
- Connection is established via [includes/db_connect.php](includes/db_connect.php).
- Common helpers are in [includes/functions.php](includes/functions.php).

## Project Structure

```
warehouse-management-system/
│— index.php
│— login.php
│— home.php
│— products.php
│— suppliers.php
│— sales.php
│— users.php
│— sales_report.php
│— database.sql
│
├─ assets/
│   ├─ css/
│   ├─ js/
│   └─ images/
│
├─ includes/
│   ├─ config.php
│   ├─ db_connect.php
│   ├─ header.php
│   ├─ footer.php
│   └─ functions.php
│
├─ _actions/
│   ├─ add_*.php / edit_*.php / delete_*.php
│   └─ process_login.php
│
└─ uploads/
    ├─ products/
    └─ users/
```

## Troubleshooting

- Cannot connect to DB: Verify host/user/pass/db in [includes/config.php](includes/config.php). Ensure MySQL is running.
- 404/500 errors: Confirm the folder is inside the web root. Check Apache error logs.
- File uploads fail: Ensure `uploads/` subfolders exist and are writable.
- Styles/scripts not loading: Check paths under [assets](assets) and that Apache can serve static files.

## Security Tips

- Change default admin password on first login.
- Use unique DB credentials and strong passwords.
- Restrict public access to `uploads/` if deploying publicly; serve only images.
- Consider HTTPS and secure cookies in production.

## License

License: MIT License

## Maintainer

Anmol Som
