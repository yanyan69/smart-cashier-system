# Smart Business Cashier and Inventory Management System

## Overview

This is a web-based system designed to help small businesses (e.g., hardware stores, sari-sari stores) manage sales, inventory, customer credits ("utang"), and more. It replaces manual tracking to reduce errors and streamline operations. The system supports multi-user roles (admin and store owners), with features for product management, sales recording, credit tracking, and reporting.

The project includes a responsive UI, PHP backend, MySQL database, and JavaScript for interactivity. It uses XAMPP (or similar) for local development.

## Features

- **User Authentication**: Login, registration, password reset, and role-based access (admin for system maintenance, owners for store operations).
- **Product Management**: Add, edit, delete products with details (name, description, category, price, stock, unit). Includes stock monitoring and low-stock alerts.
- **Sales Management**: Record cash/credit sales, calculate totals, generate receipts, and update stock automatically.
- **Credit Management ("Utang")**: Track customer debts, payments, and statuses (unpaid, partially paid, paid).
- **Customer Management**: Add/edit customers, view purchase history and credits.
- **Reporting**: View order history, filter/search transactions, generate reports (daily, weekly, etc.).
- **Admin Tools**: Manage users, database backups, system logs.
- **AI Assistant**: Floating "Ask AI" button for tutorials and queries (with modals for predefined questions and custom input).
- **Settings**: Theme toggle (light/dark mode), user preferences.
- **Notifications**: Low stock alerts, backup confirmations.
- **Multi-Device Support**: Accessible on PCs and mobile via web browsers.
- **Logging**: System events logged for auditing.
- **Theming**: Dark/light mode with local storage persistence.

## Technologies Used

- **Frontend**: HTML, CSS (with light/dark themes), JavaScript (for modals, forms, calculations).
- **Backend**: PHP (for API endpoints, database interactions).
- **Database**: MySQL (schema in `database/database.sql`).
- **Server**: Apache (via XAMPP or similar).
- **Other**: Session management for authentication, `.htaccess` for URL rewriting and error handling.

## Project Structure

- **assets/**: CSS, JS, HTML templates, images, fonts.
- **api/**: PHP API endpoints (e.g., auth/login.php).
- **auth/**: Authentication scripts (login, register, logout, password reset).
- **config/**: Database connection and functions (db.php, functions.php).
- **database/**: SQL schema (database.sql) and connection (db_connection.php).
- **includes/**: Shared components (header.php, footer.php, sidebar.php, session.php, functions.php, db_connect.php).
- **pages/**: Main pages (dashboard.php, admin_panel.php, products.php, sales.php, etc.).
- **root files**: index.php (login), .htaccess, README.md, test_db.php.

## Installation

### Prerequisites
See `requirements.txt` for a list of required software.

### Steps
1. **Clone the Repository**:
git clone https://github.com/your-username/smart-cashier-system.git
cd smart-cashier-system
text2. **Set Up Local Server**:
- Install XAMPP (or similar LAMP/WAMP stack).
- Start Apache and MySQL via XAMPP Control Panel.

3. **Database Setup**:
- Open phpMyAdmin[](http://localhost/phpmyadmin).
- Create a database named `cashier_db`.
- Import `database/database.sql` to set up tables.

4. **Configure Database Connection**:
- Edit `includes/db_connect.php` and `config/db.php` with your MySQL details (host: `127.0.0.1:3307`, user: `root`, password: empty or `admin`, db: `cashier_db`).

5. **Place Files**:
- Copy the project folder to XAMPP's `htdocs` directory (e.g., `C:\xampp\htdocs\smart-cashier-system`).

6. **Run the App**:
- Access via browser: http://localhost/smart-cashier-system/.
- Register a user or login (default admin credentials if seeded).

7. **Automated Setup (Windows)**:
- Run `setup.bat` (double-click). This assumes XAMPP is at `C:\xampp`—edit if different. It copies files, starts services, and imports DB.

## Usage

- **Login**: Go to http://localhost/smart-cashier-system/. Use registered credentials.
- **Admin Panel**: For admins, manage users/logs/backups.
- **Dashboard**: For owners, manage products/sales/credits.
- **AI Help**: Click "Ask AI" for tutorials/queries.
- **Theme**: Toggle light/dark in settings.

For detailed user interactions, see the User Manual (separate document with screenshots).

## Testing

- Test DB connection: http://localhost/smart-cashier-system/test_db.php.
- Error logs: Check `logs/app.log` or PHP error logs.

## Contributing

Fork the repo, make changes, and submit a pull request.

## License

[Add your license, e.g., MIT License]

## Acknowledgments

Developed by Techlaro Company team: Christian L. Narvaez, John Paul F. Armenta, Jerald James D. Preclaro, Marielle B. Maming.
