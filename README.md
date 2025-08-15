# Smart Business Cashier and Inventory Management System

## Overview

This is a web-based system designed to help small businesses manage their sales, inventory, and customer credits ("utang"). It aims to replace manual tracking methods, reduce errors, and streamline operations for businesses like hardware stores and sari-sari stores.

## Features

* **Product Management:** Create, edit, delete products with details like name, description, category, price, and stock. Search and sort products.
* **Sales and Transaction Management:** Record sales, specify quantities, automatically calculate totals, and generate itemized receipts. Supports cash and credit sales.
* **Credit Management ("Utang"):** Assign sales to customers as credit, record amounts owed, track payments, and monitor credit statuses.
* **Customer Management:** Create and update customer profiles with contact information and view their credit history and total purchases.
* **Order History and Reporting:** Record all transactions, allow searching and filtering, and generate summarized reports for various periods.
* **Administrative Maintenance:** Manage store owner accounts, perform database backups, and view system logs (Admin access only).
* **System Notifications:** Automatic low stock alerts. Confirmation of database backups.
* **User-Friendly Interface:** Simple and intuitive design for users with minimal technical background.
* **Multi-Device Access:** Accessible on Windows PCs and Android devices via web browsers.

## Technologies Used

* HTML
* CSS
* JavaScript
* PHP
* MySQL

## Installation

1.  **Prerequisites:** Ensure you have a web server environment with PHP and MySQL installed (e.g., XAMPP, WAMP).
2.  **Database Setup:**
    * Start Apache and MySQL servers.
    * Access phpMyAdmin (usually at `http://localhost/phpmyadmin`).
    * Create a new database named `cashier_db` (or your preferred name).
    * Import the `database/database.sql` file (if you have created one with your table structure) or manually create the tables as defined in your database design.
    * Update the database connection details in `config/db.php` with your MySQL username, password, and database name.
3.  **File Placement:** Place all the project files within the web server's document root (e.g., `htdocs` in XAMPP). If the project is in a subfolder (like `SMART-CASHIER-SYSTEM`), access it via `http://localhost/SMART-CASHIER-SYSTEM/`.
4.  **`.htaccess` (Optional):** Ensure that `.htaccess` is enabled on your Apache server if you intend to use the URL rewriting rules.

## Getting Started

1.  Open your web browser and navigate to the project's URL (e.g., `http://localhost/SMART-CASHIER-SYSTEM/`).
2.  You should see the login page (`index.php` or `index.html`).
3.  You might need to register an initial admin user through the registration page (`auth/register.php`).

## Further Development

[This section can include notes on future features, areas for improvement, or how to contribute.]

## License

[You can include license information here if applicable.]