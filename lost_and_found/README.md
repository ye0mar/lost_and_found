# Lost & Found Custody & Management System
### Capstone Project: System Administration and Maintenance
**Platform:** XAMPP (Apache, PHP 8.x, MySQL / MariaDB, phpMyAdmin)  
**Target Path:** `C:\xampp\htdocs\lost_and_found\`

---

## 1. Executive Summary & Architecture
The **Lost & Found Management System** is a secure, role-restricted web application engineered specifically for educational institutions, corporate campuses, and law-enforcement property custody departments. It operates on an immutable audit-logging infrastructure, session-fixation-shielded authentication, and intelligent matching algorithms to correlate reported lost belongings with recovered custody items.

### Role Separation & Permissions
* **Administrator:** Complete oversight of custody inventory, home analytics dashboard, staff user provisioning, activity audit logs, automated database backup & disaster recovery, system health monitoring, and system configuration.
* **Staff (Custody Officers):** Reporting lost belongings, registering found items, inventory search & inspection, and match verification. *Staff cannot access administrator features, system settings, user lists, or server backups under any circumstance (enforced at both UI and server routing layers).*

---

## 2. Project Directory Structure
```
lost_and_found/
├── index.php                         # Public gateway & landing (liquid glass)
├── login.php                         # Secure authentication portal with CSRF protection
├── logout.php                        # Session destruction & audit logging
├── dashboard.php                     # Admin-only home dashboard with real statistics
├── config/
│   └── database.php                  # PDO connection provider with fallback diagnostics
├── includes/
│   ├── auth.php                      # Session security & role-based gatekeepers
│   ├── csrf.php                      # Anti-CSRF token generator & validator
│   ├── functions.php                 # Core helpers, upload sanitizers, matching engine
│   ├── header.php                    # Shared HTML head, favicon, fonts & meta
│   ├── navbar.php                    # Liquid-glass top navigation with profile menu
│   ├── sidebar.php                   # Role-restricted sidebar navigation
│   ├── footer.php                    # Layered institutional footer
│   └── flash_messages.php            # Dismissible toast notification renderer
├── admin/
│   ├── settings.php                  # System parameters, retention policies, categories
│   ├── manage_users.php              # Staff provisioning, status toggle, pass reset
│   ├── activity_log.php              # Searchable audit trail with IP & timestamps
│   ├── backup_recovery.php           # Real SQL dump generator & recovery interface
│   └── system_monitor.php            # PHP/MySQL diagnostics & Windows task manager guide
├── lost_items/
│   ├── index.php                     # Filterable list of lost belongings
│   ├── report.php                    # 2-column intake form with photo attachment
│   ├── view.php                      # Dossier view with automated match recommendations
│   ├── edit.php                      # Information and status editor
│   └── process.php                   # Request processor with CSRF & file validation
├── found_items/
│   ├── index.php                     # Custody inventory list with storage locker tags
│   ├── add.php                       # Intake form with custody storage assignment
│   ├── view.php                      # Dossier with release-to-verified-owner workflow
│   ├── edit.php                      # Custody record editor
│   └── process.php                   # Found item actions processor
├── profile/
│   └── index.php                     # User profile & verified password change
├── assets/
│   ├── css/
│   │   ├── style.css                 # Base design system & liquid-glass styles
│   │   ├── dashboard.css             # Metric cards, split grid, activity feed
│   │   ├── forms.css                 # 2-column layouts, custom file dropzone
│   │   ├── auth.css                  # Centered login card with soft blue glow
│   │   └── responsive.css            # Tablet and mobile drawer breakpoints
│   ├── js/
│   │   └── main.js                   # Password toggle, mobile drawer, image previews
│   └── images/
│       ├── logo.png                  # Dedicated logo location
│       ├── logo-fallback.svg         # Built-in SVG fallback
│       └── favicon.png               # Browser tab favicon
├── picture/
│   ├── logo.jpg                      # Alternative primary logo file location
│   └── logo.svg                      # Vector emblem
├── uploads/
│   ├── .htaccess                     # Script execution disablement shield
│   └── items/                        # Secure storage for uploaded reference photos
├── storage/
│   └── backups/                      # Stored database SQL dumps
├── database/
│   └── lost_and_found_db.sql         # Complete MySQL schema & initial seed data
├── setup/
│   └── create_admin.php              # First-run admin initialization with auto-lock
└── README.md                         # Capstone documentation & maintenance manual
```

---

## 3. Local Installation & Deployment Guide (XAMPP)

### Step 1: Install & Launch XAMPP
1. Download and install **XAMPP for Windows** (with PHP 8.1+ and MySQL).
2. Open the **XAMPP Control Panel**.
3. Click **Start** for the **Apache** service.
4. Click **Start** for the **MySQL** service.
5. Confirm that both show a green "Running" status indicator.

### Step 2: Place Application in Web Root
1. Copy the entire `lost_and_found` folder into your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\lost_and_found\
   ```
2. Verify that `C:\xampp\htdocs\lost_and_found\index.php` exists.

### Step 3: Database Creation via phpMyAdmin
1. Open your web browser and navigate to:
   ```
   http://localhost/phpmyadmin/
   ```
2. Click on the **Databases** tab.
3. Enter database name: `lost_and_found_db` and collation: `utf8mb4_unicode_ci`.
4. Click **Create**.
5. Select `lost_and_found_db` in the left panel.
6. Click the **Import** tab at the top.
7. Click **Choose File** and browse to:
   ```
   C:\xampp\htdocs\lost_and_found\database\lost_and_found_db.sql
   ```
8. Click **Import** at the bottom of the page.
9. You should see a success message: *"Import has been successfully finished, 8 queries executed."*

### Step 4: Accessing the Application
Open your browser and navigate to:
```
http://localhost/lost_and_found/
```
*Note for Non-Default Apache Ports:*  
If your Apache runs on port 8080 (e.g., due to IIS or Skype conflicts), navigate to:
```
http://localhost:8080/lost_and_found/
```
The application dynamically detects the host and port via `config/database.php`.

---

## 4. Default Seed Accounts for Testing

| Role | Username | Password | Email |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `AdminPassword123!` | `admin@organization.edu` |
| **Staff Officer** | `officer.smith` | `StaffPassword123!` | `staff.smith@organization.edu` |
| **Staff Officer** | `officer.reyes` | `StaffPassword123!` | `staff.reyes@organization.edu` |

*(All passwords hashed using `password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12])`)*.

---

## 5. Logo Integration & Favicon Setup

### How to Insert Your Own Custom Logo:
Place your square logo image in either of these two configured locations:
1. `C:\xampp\htdocs\lost_and_found\assets\images\logo.png`  
   **OR**
2. `C:\xampp\htdocs\lost_and_found\picture\logo.jpg`

### Visual Integrity Rules Enforced:
* **Preserves Square Shape:** The CSS container utilizes `object-fit: contain;` and explicitly forbids `border-radius: 50%` to ensure your logo is never cropped into a circle.
* **Automatic Fallback:** If your file is missing or in transit, `includes/functions.php` automatically falls back to `assets/images/logo-fallback.svg` without breaking page layout.
* **Favicon Synchronization:** The browser tab favicon dynamically references your logo file via `get_favicon_url()` across all pages.

---

## 6. Role Testing Procedures for Capstone Demonstration

### Demonstration Test A: Administrator Role
1. Sign in as `admin` (`AdminPassword123!`).
2. Verify landing on `dashboard.php` showing real metrics: Total Lost, Total Found, Items Matched, Items Returned.
3. Access **Manage Users** (`admin/manage_users.php`):
   - Create a new staff account (e.g. `officer.test`).
   - Toggle status to Inactive and confirm lockout safeguard prevents self-deactivation.
4. Access **Backup & Recovery** (`admin/backup_recovery.php`):
   - Click **Generate Immediate Live Backup**.
   - Download the generated `.sql` file to verify valid SQL syntax.
5. Access **System Monitor** (`admin/system_monitor.php`):
   - Verify live MySQL latency, PHP memory limits, and disk space calculation.

### Demonstration Test B: Staff Role
1. Sign in as `officer.smith` (`StaffPassword123!`).
2. Verify that:
   - Sidebar displays **only** 4 items: *Lost Items, Report Lost Item, Found Items, Add Found Item*.
   - The *Administration* section is completely hidden.
3. Attempt to access `http://localhost/lost_and_found/admin/manage_users.php` manually in the URL bar.
   - **Verification:** The server blocks the request with an HTTP 403 / redirect with flash notice *"Access Denied: You do not possess administrator credentials"*.
4. Report a new lost item with an image attachment.
5. In the lost item dossier, examine the **Suggested Found Matches** cross-referencing candidate items.
6. Confirm a match or mark a custody item as released to owner.

---

## 7. System Administration Troubleshooting Matrix

| Issue | Potential Cause | Diagnostic Steps | Solution & Expected Result |
| :--- | :--- | :--- | :--- |
| **Apache Port Conflict (Port 80/443 in use)** | World Wide Web Publishing Service (IIS), Skype, or VMware Workstation already occupying port 80. | Open cmd and run: `netstat -ano \| findstr :80`. Note the PID occupying the port. | In XAMPP, click **Config** > `httpd.conf`. Change `Listen 80` to `Listen 8080`. Change `ServerName localhost:80` to `localhost:8080`. Apache will start successfully. |
| **MySQL Service Fails to Start** | A standalone MySQL instance is already running or port 3306 is locked. | Check XAMPP logs; run `netstat -ano \| findstr :3306`. | Open Windows Services (`services.msc`), stop any standalone MySQL service, or change port in `my.ini` to 3307 and update `config/database.php`. |
| **Database Connection Error (Red screen)** | MySQL not started in XAMPP or `lost_and_found_db` not imported. | Visit application in browser. Read the diagnostic card output. | Start MySQL in XAMPP and import `database/lost_and_found_db.sql` via phpMyAdmin. |
| **"Uploads Failed" on Item Image** | PHP temporary folder permission or upload size limit exceeded in `php.ini`. | Check `upload_max_filesize` in `admin/system_monitor.php`. | Adjust `upload_max_filesize = 10M` and `post_max_size = 12M` in `C:\xampp\php\php.ini`, then restart Apache. |
| **403 Forbidden on CSRF Action** | Stale session token or form submitted after session expiration. | Check PHP session storage directory; reload page. | Forms contain `<?php csrf_field(); ?>` ensuring single-origin authenticity. Refreshing updates the token. |

---

## 8. Preventive Maintenance Schedule

* **Daily:**
  - Verify Apache & MySQL service status in XAMPP.
  - Review `activity_logs` for failed authentication spikes or abnormal custody status changes.
* **Weekly:**
  - Generate a full SQL backup through `admin/backup_recovery.php` and archive off-site.
  - Inspect `uploads/items/` for orphan images not associated with database records.
* **Monthly:**
  - Review retention policies: Identify unclaimed items older than 90 days for lawful disposal/donation.
  - Deactivate accounts of departed staff officers.
  - Perform a trial database restoration on a secondary testing schema.
* **As-Needed:**
  - Apply PHP security patches and XAMPP service updates.
  - Rotate database root passwords in production deployments.

---

## 9. Security Hardening Summary
1. **Password Encryption:** State-of-the-art BCrypt (`PASSWORD_BCRYPT`, Cost Factor 12).
2. **SQL Injection Defense:** 100% prepared statements via PDO with parameter binding.
3. **Cross-Site Scripting (XSS):** Context-safe escaping using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
4. **Cross-Site Request Forgery (CSRF):** Cryptographic `bin2hex(random_bytes(32))` tokens verified via timing-attack-safe `hash_equals()`.
5. **Session Fixation Defense:** `session_regenerate_id(true)` called upon every authentication transition.
6. **Uploads Execution Barrier:** `uploads/.htaccess` disables PHP execution engines and blocks server-side scripting extensions.
7. **Principle of Least Privilege:** Role verification at both UI presentation and backend controller execution layers.
