# Premium Lost & Found Management System
### Capstone Project: System Administration and Maintenance
Designed for local deployment in **XAMPP (Apache, PHP 8.x, MySQL, phpMyAdmin)** at:
`C:\xampp\htdocs\lost_and_found\`

---

## Deliverables Summary

1. **Complete PHP/MySQL Codebase:** Located in `/lost_and_found/`. Every PHP file uses prepared statements, session regeneration, BCrypt password hashing, and CSRF barriers.
2. **Database Import File:** `lost_and_found/database/lost_and_found_db.sql` with schema, constraints, indexes, and initial seed users/records.
3. **Interactive Capstone Live Demonstrator:** Runs live in the preview on port 3000 (`src/App.tsx`), allowing immediate role-switching, reporting, matching, user management, and backup simulation.
4. **Detailed Capstone Manual:** Complete documentation in `lost_and_found/README.md` covering XAMPP deployment, phpMyAdmin import, security walkthrough, troubleshooting matrix, and preventive maintenance schedules.

### Default Seed Accounts
- **Administrator:** `admin` / `AdminPassword123!`
- **Staff Officer:** `officer.smith` / `StaffPassword123!`
- **Staff Officer:** `officer.reyes` / `StaffPassword123!`

### Logo Placement
To replace the brand emblem with your own picture:
- Place your square image file at `lost_and_found/assets/images/logo.png` or `lost_and_found/picture/logo.jpg`.
- The system automatically detects and renders it across the browser favicon, login panel, header, and sidebar while preserving its exact square shape (`object-fit: contain`).
