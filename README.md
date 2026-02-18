# Warehouse Management System (WMS)

A simple **PHP-based Warehouse Management System** to manage products, suppliers, sales, and users via a secure admin login.

##  Default Login Credentials

- **Username:** `anmol`
- **Password:** `anmol`

*(Change these immediately after first login for security.)*

##  Features

- User Authentication (Login & Logout)
- Product Management (Add/Edit/Delete/View)
- Supplier Management
- Sales Tracking & Reporting
- User Account Management (Admin only)
- Database setup provided via `database.sql`

##  Technologies Used

- **Frontend:** HTML, CSS, JavaScript  
- **Backend:** PHP  
- **Database:** MySQL  
- **Server:** Apache (e.g., XAMPP or WAMP)

##  Installation

1. Clone the repository:  
   ```bash
   git clone https://github.com/anm00lll/warehouse-management-system.git
   ```

2. Move the project folder inside your server’s web root.  
   For example, with XAMPP:  
   `C:\xampp\htdocs\warehouse-management-system`

3. Import the database:  
   - Open **phpMyAdmin**
   - Create a database (`warehouse-management-system`)
   - Import `database.sql` from the project folder

4. Configure the database connection:  
   - Open `includes/config.php`
   - Update the DB host, username, password, and database name if needed

5. Start Apache & MySQL in your server environment

6. Visit in your browser:  
   ```
   http://localhost/warehouse-management-system
   ```

##  Project Structure

```
warehouse-management-system/
│— index.php
│— login.php
│— home.php
│— products.php
│— suppliers.php
│— sales.php
│— users.php
│— database.sql
│
├─ assets/
│   ├─ css/ (style.css)
│   ├─ js/ (script.js)
│   └─ images/
│
├─ includes/
│   ├─ config.php
│   ├─ db_connect.php
│   ├─ header.php / footer.php
│   └─ functions.php
│
├─ _actions/
│   ├─ add_product.php / edit_product.php / delete_product.php
│   ├─ add_supplier.php / ... 
│   ├─ add_sale.php / ...
│   └─ add_user.php / ...
│
└─ uploads/
    ├─ products/
    └─ users/
```

##  Owner

**Anmol Som**  

