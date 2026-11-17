# 🏙️ Smart City Citizen Complaint Portal

A full-featured web application that lets citizens register, submit, and track civic complaints, with a complete admin dashboard for city officials.

---

## Tech Stack

| Layer    | Technology            |
|----------|-----------------------|
| Frontend | HTML5, CSS3, JavaScript (vanilla) |
| Backend  | PHP 7.4+ (PDO, sessions, password_hash) |
| Database | MySQL 5.7+ / MariaDB 10.x |
| Server   | Apache (XAMPP)        |

---

## Project Structure

```
smart-city-portal/
├── index.php                  Homepage
├── register.php               Citizen registration
├── login.php                  Citizen login
├── logout.php                 Citizen logout
├── dashboard.php              Citizen dashboard
├── complaint.php              File a new complaint
├── track.php                  Track complaint by ID
├── my-complaints.php          View & filter own complaints
├── install.php                One-time setup helper
├── database.sql               Database schema + seed data
├── .htaccess                  Apache security rules
│
├── admin/
│   ├── login.php              Admin login
│   ├── logout.php             Admin logout
│   ├── dashboard.php          Admin overview & stats
│   ├── complaints.php         Manage & update complaints
│   ├── citizens.php           Manage citizen accounts
│   └── includes/
│       ├── admin_header.php
│       └── admin_footer.php
│
├── includes/
│   ├── config.php             DB config & constants
│   ├── db.php                 PDO singleton
│   ├── functions.php          Helpers, session, upload
│   ├── header.php             Public navbar
│   └── footer.php             Public footer
│
├── assets/
│   ├── css/
│   │   ├── style.css          Main stylesheet
│   │   └── admin.css          Admin overrides
│   ├── js/
│   │   └── main.js            Client-side JS
│   └── images/
│       └── favicon.svg
│
└── uploads/                   User-uploaded images
    └── .htaccess              Blocks PHP execution in uploads
```

---

## Installation (XAMPP)

### Prerequisites
- XAMPP (or any Apache + PHP + MySQL stack)
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+

### Step 1 – Copy files
Place the `smart-city-portal` folder inside your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\smart-city-portal\
```

### Step 2 – Start XAMPP services
Open the XAMPP Control Panel and start:
- **Apache**
- **MySQL**

### Step 3 – Import the database

**Option A – phpMyAdmin (recommended)**
1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click **Import**
3. Choose the file `database.sql` from the project folder
4. Click **Go**

**Option B – MySQL CLI**
```bash
mysql -u root -p < database.sql
```

### Step 4 – Configure database credentials
Open `includes/config.php` and update if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // your MySQL root password
define('DB_NAME', 'smart_city_portal');
```

### Step 5 – Set admin password
Open your browser and go to:
```
http://localhost/smart-city-portal/install.php
```
1. Click **Run Database Setup** (Step 1)
2. Set a secure admin password (Step 2)
3. **Delete `install.php`** after completing setup

### Step 6 – Open the portal
```
http://localhost/smart-city-portal/
```

---

## Default Credentials

### Admin
| Field    | Value              |
|----------|--------------------|
| URL      | `/admin/login.php` |
| Username | `admin`            |
| Password | Set via install.php (default in DB: `password`) |

### Demo Citizen
| Field    | Value                  |
|----------|------------------------|
| URL      | `/login.php`           |
| Email    | `citizen@example.com`  |
| Password | `password`             |

> ⚠️ Change both passwords before deploying to any live environment.

---

## Features

### Citizen Portal
- Register / Login / Logout with secure sessions
- File civic complaints with photo upload
- Unique **Tracking ID** generated per complaint
- Track complaint status publicly (no login needed)
- Dashboard with personal stats
- Filter & view all own complaints with pagination

### Complaint Form Fields
- Citizen Name, Email, Mobile
- Category (11 types), Title, Description
- City Area / Address, Complaint Date
- Photo upload (JPG/PNG/GIF/WebP, max 5 MB)

### Admin Panel
- Overview dashboard with live statistics
- Bar chart of monthly complaint trends
- Category-wise breakdown
- Manage all complaints with search/filter/pagination
- Update complaint status (Pending → In Progress → Resolved / Rejected)
- Add admin remarks visible to the citizen
- Manage citizens (view, activate/deactivate accounts)

---

## Security Highlights
- All DB queries use **PDO prepared statements** – no SQL injection
- Passwords hashed with **`password_hash()` / `password_verify()`** (bcrypt, cost 12)
- PHP sessions with **`session_regenerate_id(true)`** on login
- Image uploads validated by **MIME type via `finfo`** (not just extension)
- Uploaded files stored in a directory protected by `.htaccess`
- HTML output escaped with **`htmlspecialchars()`** everywhere
- `includes/` directory blocked from direct HTTP access

---

## Complaint Status Flow

```
Pending → In Progress → Resolved
                    ↘ Rejected
```

---

## License
For educational / internship project use.
