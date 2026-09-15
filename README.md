# Campus Event Registration System

A complete, database-driven web application that allows college students to discover campus events and register for them online.

**DBMS Mini Project | PHP + MySQL | Bootstrap 5**

---

## Problem Statement

Students often miss college events due to lack of a centralised information and registration system.  
This project solves that by providing a web-based portal where events are listed with full details and participants can register online.

---

## Objective

Build a web application that demonstrates the complete flow:

> **HTML Form → PHP Server-side Processing → MySQL Database → SELECT Query → Browser Output**

---

## Features

| Feature | Description |
|---|---|
| Event Listing | Dynamically loaded from MySQL with seat counts |
| Event Details | Full event info with registration stats |
| Online Registration | Form with HTML + PHP server-side validation |
| Confirmation Page | Displays full registration record using JOIN |
| Registration Records | Table with all registrations |
| Search & Filter | Filter by event; search by participant name |
| Admin Panel | Add / Edit / Delete events (CRUD) |
| Responsive Design | Mobile-friendly with Bootstrap 5 |
| SQL Security | All user input uses prepared statements |

---

## Technologies Used

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5, Bootstrap Icons |
| Backend | Core PHP (no frameworks) |
| Database | MySQL via MySQLi (prepared statements) |
| Hosting | InfinityFree (PHP + MySQL) |

---

## Project Structure

```
campus-event/
├── index.php                  Home page (stats + featured events)
├── events.php                 All events listing (student view)
├── event_details.php          Single event details
├── register.php               Registration form + PHP processing
├── registration_success.php   Confirmation card
├── registrations.php          Registration list + search/filter
├── db.php                     Database connection (configured for local XAMPP)
│
├── admin/
│   ├── login.php              Admin login form with password_verify
│   ├── logout.php             Session termination handler
│   ├── dashboard.php          Admin dashboard (stats + event management table)
│   ├── add_event.php          Add new event (admin only)
│   ├── edit_event.php         Edit existing event (admin only)
│   ├── delete_event.php       Safe delete handler (admin only, protects student data)
│   └── auth.php               Server-side PHP session protection guard
│
├── css/
│   └── style.css              Custom CSS + design tokens
│
├── images/                    Static assets (if any)
│
└── database/
    └── campus_event_db.sql    Full schema + sample events, registrations & admin
```

---

## Admin Credentials (Demo)

| Field | Value |
|---|---|
| **Username** | `admin` |
| **Password** | `admin123` |
| **Storage** | Encrypted with PHP `password_hash()` (Bcrypt) in `admins` table |

---

## Database Structure

### Table: `admins`

| Column | Type | Description |
|---|---|---|
| id | INT AUTO_INCREMENT PK | Unique Admin ID |
| username | VARCHAR(50) UNIQUE | Admin login username |
| password | VARCHAR(255) | Bcrypt hashed password |
| created_at | TIMESTAMP | Record creation time |

### Table: `events`

| Column | Type | Description |
|---|---|---|
| id | INT AUTO_INCREMENT PK | Unique event ID |
| event_name | VARCHAR(150) | Name of the event |
| description | TEXT | Full event description |
| event_date | DATE | Date of event |
| event_time | TIME | Start time |
| venue | VARCHAR(200) | Location/venue |
| organizer | VARCHAR(150) | Organising dept |
| max_participants | INT | Seat capacity |
| status | ENUM | upcoming/ongoing/completed/cancelled |
| created_at | TIMESTAMP | Record creation time |

### Table: `registrations`

| Column | Type | Description |
|---|---|---|
| id | INT AUTO_INCREMENT PK | Registration ID |
| participant_name | VARCHAR(100) | Student name |
| email | VARCHAR(150) | Email address |
| roll_no | VARCHAR(30) | College roll number |
| department | VARCHAR(50) | Student department |
| year | VARCHAR(20) | Year of study |
| event_id | INT FK → events(id) | Linked event |
| registered_at | TIMESTAMP | Registration timestamp |

**Relationship:** One event → Many registrations (1:N)

---

## SQL Concepts Demonstrated

| Concept | Location |
|---|---|
| `CREATE TABLE` | `database/campus_event_db.sql` |
| `INSERT` | `register.php`, `admin/add_event.php` |
| `SELECT` | All pages |
| `UPDATE` | `admin/edit_event.php` |
| `DELETE` | `admin/delete_event.php` |
| `WHERE` | `event_details.php`, `registrations.php` |
| `LIKE` | `registrations.php` (name search) |
| `JOIN` | `registration_success.php`, `registrations.php` |
| `COUNT()` | `index.php` (statistics), event cards |
| `ORDER BY` | All listing queries |
| Prepared Statements | All user-input queries |

---

## How PHP Connects to MySQL

```php
// db.php
$conn = mysqli_connect($host, $username, $password, $database);

// Prepared statement example (register.php)
$stmt = mysqli_prepare($conn,
    "INSERT INTO registrations (participant_name, email, roll_no, department, year, event_id)
     VALUES (?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, 'sssssi', $name, $email, $roll, $dept, $year, $event_id);
mysqli_stmt_execute($stmt);
```

---

## How to Run Locally

### Requirements
- PHP 7.4+ (or PHP 8.x)
- MySQL 5.7+ (or MariaDB)
- XAMPP / WAMP / Laragon (recommended)

### Steps

1. Copy the `campus-event/` folder to `htdocs/` (XAMPP) or `www/` (WAMP).

2. Open **phpMyAdmin** → create database `campus_event_db`.

3. Import the SQL file:
   - Click the database → **Import** tab
   - Select `database/campus_event_db.sql`
   - Click **Go**

4. Open `db.php` and set:
   ```php
   $host     = 'localhost';
   $username = 'root';
   $password = '';          // leave blank for XAMPP default
   $database = 'campus_event_db';
   ```

5. Visit: `http://localhost/campus-event/`

---

## How to Deploy to InfinityFree

### Step 1 — Create InfinityFree Account
- Sign up at [infinityfree.com](https://infinityfree.com)
- Create a hosting account (free)

### Step 2 — Create MySQL Database
- In the InfinityFree control panel → **MySQL Databases**
- Create a new database
- Note the **Database Host**, **Database Name**, **Username**, and **Password**
  - Host is usually something like `sql123.infinityfree.com`

### Step 3 — Import Database
- Open **phpMyAdmin** from the InfinityFree control panel
- Select your database
- Click **Import** → choose `database/campus_event_db.sql` → **Go**

### Step 4 — Update `db.php`
```php
$host     = 'sql123.infinityfree.com';   // from InfinityFree
$username = 'if0_xxxxxxxx';              // your DB username
$password = 'your_db_password';          // your DB password
$database = 'if0_xxxxxxxx_campus_event'; // your DB name
```
> ⚠ Never commit real passwords to version control.

### Step 5 — Upload Files
- Use the InfinityFree **File Manager** or an FTP client (FileZilla)
- Upload the entire `campus-event/` folder contents to `/htdocs/`
  - The root of your site should contain `index.php`, `events.php`, etc.

### Step 6 — Test
- Visit your InfinityFree subdomain (e.g., `https://yoursite.epizy.com`)
- Check all pages work correctly

---

## Faculty Demo Flow (5 minutes)

1. Open homepage → show statistics loaded from DB
2. Navigate to **Events** → show event cards with seat bars
3. Click **Details** on any event
4. Click **Register** → fill the form → submit
5. Show **Registration Successful** confirmation card with ID
6. Navigate to **Registrations** → find the new record
7. Use the **Search/Filter** to filter by event
8. Open phpMyAdmin → show the `registrations` table row

---

## Security Measures

- All user input queries use **prepared statements** (`mysqli_prepare`, `mysqli_stmt_bind_param`)
- All database values displayed in HTML use `htmlspecialchars()`
- Server-side validation on all form fields (not just HTML5)
- Database credentials are in `db.php` only — never shown in the UI
- Generic error messages — no SQL or PHP errors exposed to users
- DELETE handler only accepts POST requests
- No sensitive personal data collected (no mobile numbers, IDs, etc.)

---

## Sample Events Included

1. Tech Fest 2026 — CSE Department
2. Hackathon 2026 — CSE AIML Department
3. Coding Competition — IT Department
4. Project Exhibition — All Departments
5. Volleyball Tournament — Physical Education
6. Cultural Fest – Utsav 2026 — Student Council

---

*Campus Event Registration System — DBMS Mini Project*
