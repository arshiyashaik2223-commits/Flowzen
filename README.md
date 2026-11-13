# FLOWZEN – Intelligent Task Management System
### RTIH IT Internship Technical Assessment | Ratan Tata Innovation Hub, Amaravati

> **Brand Tagline:** *Find your focus. Own your workflow.*
> **Design Identity:** Nocturne Citron – Modern Editorial SaaS
> **Candidate:** Arshiya Parveen
> **Technology Stack:** Core PHP 8.3+, MySQL 26.7+ (InnoDB), PDO, HTML5, CSS3, ES6 JavaScript, Bootstrap 5.3, Chart.js 4.4, Lucide Icons

---

## 1. Executive Summary & Vision

**Flowzen** is a productivity management web application designed and built for the IT Internship Technical Assessment at the **Ratan Tata Innovation Hub (RTIH)**, Amaravati.

Flowzen provides registered knowledge workers with an intelligent, highly responsive, and secure platform to organize tasks, track deadlines, visualize productivity velocity, and maintain a historical audit trail of their work.

Flowzen was developed strictly under the non-negotiable guidelines:
- **100% Core PHP**: No Laravel, Symfony, or heavy third-party frameworks.
- **Pure Relational MySQL**: All statistics, charts, and workflows persist in real MySQL tables with InnoDB foreign keys and indexes. No fake/hardcoded dashboard numbers.
- **Multi-Tenant Data Isolation**: Complete user ownership verification enforced at the server-side controller and model layers.
- **Enterprise-Grade Web Security**: PDO prepared statements, CSRF protection tokens, XSS output sanitization, bcrypt password hashing, and session fixation safeguards.

---

## 2. Nocturne Citron Design System

Flowzen diverges from the generic blue-gradient dashboards by implementing the **Nocturne Citron** design language:

| Color Name | Hex Code | Purpose / Application |
| :--- | :--- | :--- |
| **Nocturne Plum** | `#211B2B` | Sidebar navigation, primary dark headers, brand badge |
| **Warm Porcelain** | `#F4EFE5` | Primary application canvas background |
| **Electric Citron** | `#D9F36A` | Primary calls to action, active indicators, high-contrast badges |
| **Mineral Teal** | `#5CCFC0` | Task completion indicators, progress bars, velocity chart |
| **Dusty Orchid** | `#B9A8D5` | Secondary analytics, pending status indicators |
| **Terracotta Coral**| `#E68D78` | Overdue deadline warnings, high priority badges, danger alerts |
| **Surface White** | `#FFFCF6` | Card background and high-elevation content containers |
| **Border Tone** | `#E5DDD2` | Subtle layout dividers and card borders |

- **Typography:** *Manrope* (Headings, 700/800 weight) paired with *DM Sans* (Body text, 400/500/600 weight).
- **Theme Support:** Native light and dark mode toggles with persistent session/cookie state.

---

## 3. Architecture & Separation of Concerns

Flowzen uses a modular MVC-inspired architecture:

```
flowzen/
│
├── app/
│   ├── config/
│   │   ├── config.php            # Environment loader (.env), session security, constants
│   │   └── database.php          # PDO Singleton connection manager (utf8mb4, real prepared statements)
│   ├── controllers/
│   │   ├── AuthController.php        # Register, login, logout, throttling & session fixation prevention
│   │   ├── TaskController.php        # Full CRUD, status toggle, search, filter, CSV export, archive
│   │   ├── DashboardController.php   # KPI calculations & Chart.js dataset generation
│   │   ├── KanbanController.php      # 3-column drag-and-drop workflow synchronization
│   │   ├── CalendarController.php    # Monthly deadline timeline navigation
│   │   ├── ActivityController.php    # Historical user action audit trail
│   │   ├── NotificationController.php# In-app deadline alerts & acknowledgment
│   │   └── SettingsController.php    # Profile updates & theme preferences
│   ├── models/
│   │   ├── User.php              # User model & bcrypt authentication
│   │   ├── Task.php              # Task CRUD, filters, metrics & ownership checks
│   │   ├── ActivityLog.php       # Action history logging (audit trail)
│   │   └── Notification.php      # Deadline & overdue notifications
│   ├── middleware/
│   │   ├── AuthMiddleware.php    # Guards authenticated routes & guest-only pages
│   │   └── CsrfMiddleware.php    # Verifies timing-safe CSRF tokens on POST/PUT/DELETE
│   └── helpers/
│       ├── View.php              # Template engine with layout support
│       ├── Security.php          # XSS escaping (e()), CSRF token generation & validation
│       ├── Validator.php         # Server-side validation rules & old input preservation
│       └── Session.php           # Secure session lifecycle & flash messaging
│
├── views/                        # Presentation layer templates
│   ├── layouts/
│   │   ├── header.php            # HTML5 head, CDN links, CSRF meta
│   │   ├── sidebar.php           # Collapsible Nocturne Plum sidebar
│   │   ├── topnav.php            # Top header, notification bell, user status
│   │   └── footer.php            # Scripts (Bootstrap, Lucide, Chart.js)
│   ├── auth/ (login.php, register.php)
│   ├── dashboard/ (index.php)
│   ├── tasks/ (index.php, create.php, edit.php, archived.php)
│   ├── kanban/ (index.php, _card.php)
│   ├── calendar/ (index.php)
│   ├── activity/ (index.php)
│   ├── notifications/ (index.php)
│   └── settings/ (index.php)
│
├── public/                       # Web Root
│   ├── assets/
│   │   ├── css/style.css         # Complete Nocturne Citron design tokens
│   │   ├── js/app.js             # Core interactions & toast dismissals
│   │   ├── js/kanban.js          # Drag-and-drop & accessible status updates
│   │   └── js/charts.js          # Chart.js initialization
│   ├── .htaccess                 # Apache rewrite engine
│   └── index.php                 # Front controller & router
│
├── database/
│   └── database.sql              # Normalized MySQL schema, foreign keys & seed data
│
├── storage/logs/                 # Error logs (excluded from git)
├── tests/test_suite.php          # 25-step automated CLI verification test runner
├── .env.example                  # Environment configuration template
├── .gitignore                    # Git ignore file
└── README.md                     # Complete project documentation
```

---

## 4. Database Schema (`flowzen_db`)

The database is fully normalized in MySQL InnoDB with explicit foreign key constraints:

### 1. `users` Table
| Column | Type | Constraints / Description |
| :--- | :--- | :--- |
| `id` | INT UNSIGNED AUTO_INCREMENT | PRIMARY KEY |
| `name` | VARCHAR(100) NOT NULL | Full name of user |
| `email` | VARCHAR(191) NOT NULL | UNIQUE index (`uk_users_email`) |
| `password` | VARCHAR(255) NOT NULL | BCRYPT password hash |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP |

### 2. `tasks` Table
| Column | Type | Constraints / Description |
| :--- | :--- | :--- |
| `id` | INT UNSIGNED AUTO_INCREMENT | PRIMARY KEY |
| `user_id` | INT UNSIGNED NOT NULL | FOREIGN KEY (`users.id`) ON DELETE CASCADE |
| `title` | VARCHAR(255) NOT NULL | Task title (indexed) |
| `description` | TEXT NULL | Detailed task description |
| `status` | ENUM('pending','in_progress','completed') | Default: 'pending' (indexed) |
| `priority` | ENUM('low','medium','high') | Default: 'medium' (indexed) |
| `due_date` | DATE NULL | Target deadline (indexed) |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP |
| `archived_at` | TIMESTAMP NULL | NULL if active; timestamp when archived |

### 3. `activity_logs` Table
- **Foreign Key Strategy:** `user_id` has `ON DELETE CASCADE`. `task_id` has `ON DELETE SET NULL`.
  *Reasoning:* When a task is deleted, the historical log entry stating *"Deleted task: 'Review Guidelines'"* must remain intact without triggering a foreign key constraint violation.

### 4. `notifications` Table
- Tracks in-app alerts for tasks due today or overdue with an unread/read boolean flag (`is_read`).

---

## 5. Security & Engineering Standards

1. **SQL Injection Defense:** Real PDO prepared statements (`PDO::ATTR_EMULATE_PREPARES => false`). Every query utilizes parameter binding. No raw SQL concatenation.
2. **Cross-Site Scripting (XSS) Prevention:** Output escaping via helper `e($string)` which wraps `htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.
3. **Cross-Site Request Forgery (CSRF) Protection:** Cryptographically secure 64-character tokens generated via `random_bytes(32)` validated with timing-safe `hash_equals()`.
4. **Session Fixation Prevention:** Calling `session_regenerate_id(true)` upon authentication.
5. **Authorization & Multi-Tenancy:** Every model operation explicitly binds `WHERE id = :id AND user_id = :user_id`. Frontend IDs are never trusted.

---

## 6. XAMPP Setup & Installation Instructions

### Prerequisites
- Windows 10/11 64-bit
- XAMPP with PHP 8.2+ installed at `C:\xampp`
- Existing `MySQL267` Windows service running on port 3306

### Step 1: Clone or Open Workspace
Ensure the folder is located at:
```
C:\Users\ARSHIYA\Downloads\flowzen
```

### Step 2: Configure Environment (`.env`)
Copy `.env.example` to `.env`:
```powershell
Copy-Item .env.example .env
```
Edit `.env` to set your MySQL password:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=flowzen_db
DB_USER=root
DB_PASS=YOUR_MYSQL_PASSWORD_HERE
```

### Step 3: Import Database Schema
Open PowerShell and run the MySQL client to import `database/database.sql`:
```powershell
& 'C:\Program Files\MySQL\MySQL Server 26.7\bin\mysql.exe' -u root -p flowzen_db < database/database.sql
```
*(Or create `flowzen_db` and import via your preferred MySQL tool).*

### Step 4: Access in Browser via Apache or PHP Server
- **Option A (XAMPP Apache):**
  A directory junction points `C:\xampp\htdocs\flowzen` to `C:\Users\ARSHIYA\Downloads\flowzen`.
  Start Apache in the XAMPP Control Panel and navigate to:
  **`http://localhost/flowzen/public/`**

- **Option B (Instant PHP Built-in Server):**
  Run in PowerShell from the `flowzen` root:
  ```powershell
  & 'C:\xampp\php\php.exe' -S 127.0.0.1:8000 -t public
  ```
  Then visit: **`http://127.0.0.1:8000`**

---

## 7. Demo Evaluator Credentials

The database seed provides a pre-populated account with active tasks, charts, and activity history:

- **Email:** `demo@flowzen.com`
- **Password:** `Password123!`

*(You can also register any new account using the registration form).*

---

## 8. Quality Assurance & Testing Report

Flowzen includes an automated test suite verifying all 25 assessment criteria:

```powershell
& 'C:\xampp\php\php.exe' tests/test_suite.php
```

### Automated Verification Results:
| # | Test Case | Target Requirement | Status |
| :---: | :--- | :--- | :---: |
| 1 | `testRegistrationSuccess` | Successful registration with bcrypt hash | **PASS** |
| 2 | `testDuplicateEmailRejection` | Reject duplicate email addresses | **PASS** |
| 3 | `testLoginSuccess` | Valid credentials authentication | **PASS** |
| 4 | `testIncorrectPasswordRejection` | Safe error message on bad password | **PASS** |
| 5 | `testDashboardAccessDenialWithoutLogin` | Protected routes require session | **PASS** |
| 6 | `testTaskCreation` | Insert task record into MySQL | **PASS** |
| 7 | `testEmptyTitleRejection` | Server-side validation rejects empty title | **PASS** |
| 8 | `testTaskListing` | Retrieve tasks with user isolation | **PASS** |
| 9 | `testTaskEditing` | Update existing task attributes | **PASS** |
| 10 | `testTaskDeletion` | Remove task with confirmation | **PASS** |
| 11 | `testStatusUpdate` | Transition status across enum states | **PASS** |
| 12 | `testTitleSearch` | Substring search in task titles | **PASS** |
| 13 | `testStatusFiltering` | Filter by pending, in progress, completed | **PASS** |
| 14 | `testPriorityFiltering` | Filter by low, medium, high | **PASS** |
| 15 | `testPaginationCalculations` | Page count and limit/offset math | **PASS** |
| 16 | `testDeadlineValidation` | Date format validation (YYYY-MM-DD) | **PASS** |
| 17 | `testCsvExportStructure` | Stream relational data to CSV | **PASS** |
| 18 | `testUserA_CannotAccessUserB_Task` | Multi-tenant read authorization check | **PASS** |
| 19 | `testUserA_CannotModifyUserB_Task` | Multi-tenant update authorization check | **PASS** |
| 20 | `testCsrfValidation` | Reject state changes without valid token | **PASS** |
| 21 | `testSqlInjectionResistance` | Parameterized statements neutralize payloads | **PASS** |
| 22 | `testXssEscaping` | Prevent script tag execution via `e()` | **PASS** |
| 23 | `testLogoutSessionClearing` | Wipe session variables and destroy cookie | **PASS** |
| 24 | `testKanbanStatusPersistence` | Drag-and-drop state saved in database | **PASS** |
| 25 | `testArchiveAndRestore` | Archive task and restore without data loss | **PASS** |

---

## 9. Final RTIH Technical Assignment Compliance Audit

| Requirement | Implementation File | Database Component | Testing Evidence | Status |
| :--- | :--- | :--- | :--- | :---: |
| **Database Connectivity** | `app/config/database.php` | MySQL `flowzen_db` | PDO connection handshake | **PASS** |
| **Registration & Login** | `app/controllers/AuthController.php` | `users` table | `testRegistrationSuccess`, `testLoginSuccess` | **PASS** |
| **Password Security** | `app/models/User.php` | `password` (BCRYPT) | `password_hash()` and `password_verify()` | **PASS** |
| **Session Auth & Logout** | `app/helpers/Session.php` | PHP Sessions | Session regeneration & cookie expiration | **PASS** |
| **Task CRUD** | `app/controllers/TaskController.php` | `tasks` table | `testTaskCreation`, `testTaskEditing`, `testTaskDeletion` | **PASS** |
| **Status Management** | `app/models/Task.php` | `status` ENUM | Pending, In Progress, Completed | **PASS** |
| **Search & Filtering** | `app/controllers/TaskController.php` | `tasks` indexes | `testTitleSearch`, `testStatusFiltering` | **PASS** |
| **Input Validation** | `app/helpers/Validator.php` | Server validation | Empty title rejection & old input preservation | **PASS** |
| **Authorization & Ownership** | `app/models/Task.php` | `user_id` FK | Strict tenant isolation verification | **PASS** |
| **CSRF Protection** | `app/middleware/CsrfMiddleware.php` | Session token | Timing-safe token verification | **PASS** |
| **XSS Protection** | `app/helpers/Security.php` | HTML escaping | `htmlspecialchars(..., ENT_QUOTES)` | **PASS** |
| **Productivity Dashboard** | `app/controllers/DashboardController.php` | Dynamic aggregates | 6 real-time KPIs, Chart.js visualizations | **PASS** |
| **Priorities & Deadlines** | `app/models/Task.php` | `priority`, `due_date` | Low/Med/High & overdue tracking | **PASS** |
| **Kanban Board** | `views/kanban/index.php`, `kanban.js` | Backend sync | Drag-and-drop & accessible dropdown | **PASS** |
| **Calendar Schedule** | `views/calendar/index.php` | `due_date` mapping | Monthly navigation with deadline markers | **PASS** |
| **Activity Timeline** | `app/models/ActivityLog.php` | `activity_logs` | Audit trail with `ON DELETE SET NULL` FK | **PASS** |
| **Notification Center** | `app/models/Notification.php` | `notifications` | In-app alerts for due today & overdue tasks | **PASS** |
| **CSV Export** | `app/controllers/TaskController.php` | Output buffer stream | Verified CSV headers & export format | **PASS** |
| **Archive & Restore** | `app/models/Task.php` | `archived_at` | Soft archive with restore & purge | **PASS** |
| **Theme Switching** | `public/assets/css/style.css` | Cookie/Session state | Nocturne Citron light & dark palettes | **PASS** |

---

## 10. AI Coding Assistant Disclosure & Engineering Reflection

- **Tool Used:** Antigravity AI Assistant (Google DeepMind).
- **Role of AI:** Assisted in scaffolding the modular directory architecture, compiling the normalized SQL schema, and generating boilerplate HTML structures adhering to the Nocturne Citron brand palette.
- **Architectural Adjustment After Review:**
  *Initial AI Suggestion:* Proposed placing foreign key constraint `ON DELETE CASCADE` on `activity_logs.task_id`.
  *Human Engineering Correction:* Recognized that if a task is deleted, having its audit history cascade-deleted would erase the proof of that deletion from the user's timeline. We modified the schema to `task_id INT NULL DEFAULT NULL` with `ON DELETE SET NULL`. This ensures the audit record *"Deleted task: 'Review Guidelines'"* is safely preserved forever without causing a foreign key constraint violation.

---

## 11. Database Backup & Restore Guide

### To Backup:
```powershell
& 'C:\Program Files\MySQL\MySQL Server 26.7\bin\mysqldump.exe' -u root -p flowzen_db > backup_flowzen.sql
```

### To Restore:
```powershell
& 'C:\Program Files\MySQL\MySQL Server 26.7\bin\mysql.exe' -u root -p flowzen_db < backup_flowzen.sql
```

---

*Flowzen – Developed for Ratan Tata Innovation Hub (RTIH) IT Internship Assessment.*
