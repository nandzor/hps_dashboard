# 🎥 CCTV Dashboard - Complete Person Re-ID Tracking System

**A comprehensive Laravel application for CCTV monitoring with Person Re-Identification (Re-ID) tracking, multi-branch management, real-time event notifications, and secure API integration.**

---

## ✨ Overview

CCTV Dashboard adalah sistem monitoring lengkap dengan fitur:

- 🎯 **Person Re-Identification (Re-ID)** - Track individuals across multiple branches
- 📹 **Multi-Device Support** - Camera, Node AI, Mikrotik, CCTV devices
- 🏢 **Multi-tenant Architecture** - Province → City → Branch hierarchy
- 📊 **Real-time Analytics** - Detection trends, branch performance, dashboard
- 🔔 **WhatsApp Notifications** - Async notification delivery via queue
- 🎛️ **Flexible CCTV Layouts** - 4/6/8-window grid configurations (Admin configurable)
- 📡 **RESTful API** - Secure API with credential management & rate limiting
- 🔐 **Role-based Access Control** - Admin and operator roles with middleware
- 🔑 **API Credentials** - Global access credentials with 10K/hour rate limit
- 🧪 **API Testing Interface** - Built-in web interface for testing APIs

---

## 🚀 Quick Start

### **Option 1: Quick Setup (5 minutes)**

```bash
# 1. Install & Configure
composer install
cp .env.example .env
php artisan key:generate

# 2. Setup Database (edit .env first)
php artisan migrate:fresh --seed

# 3. Build & Run
npm install && npm run build
php artisan serve

# 4. Login
# URL: http://localhost:8000/login
# Email: admin@cctv.com
# Password: admin123
```

### **Option 2: Using Start Scripts**

```bash
# Linux/Mac
./START.sh

# Windows
START.bat
```

📖 **Complete Guide:** See [SETUP_GUIDE.md](SETUP_GUIDE.md)

---

## 🎯 Key Features

### **Core Modules (100% Complete)**

- ✅ **Dashboard** - Overview statistics & analytics with charts
- ✅ **Company Groups** - Province-level management (Admin only)
- ✅ **Company Branches** - City-level branch management
- ✅ **Device Masters** - CCTV devices & sensors management (encrypted credentials)
- ✅ **Person Tracking (Re-ID)** - Person re-identification across branches
- ✅ **CCTV Layouts** - Dynamic 4/6/8-window grid layouts (Admin only)
- ✅ **CCTV Live Stream** - Position-based auto-save streaming
- ✅ **Event Logs** - Real-time event monitoring with PDF export
- ✅ **Reports** - Daily & monthly analytics with detection trends & PDF export
- ✅ **User Management** - Role-based user administration
- ✅ **API Credentials** - Secure API key management (Admin only) 🆕
- ✅ **Branch Event Settings** - Per-device notification configuration 🆕
- ✅ **WhatsApp Settings** - Global WhatsApp configuration (Admin only) 🆕

### **Advanced Features**

- ✅ **Async Processing** - Queue-based background jobs (9 priority queues)
- ✅ **WhatsApp Integration** - Automated notifications via WAHA
- ✅ **Image Processing** - Auto-resize, watermark, thumbnails
- ✅ **API Integration** - Complete RESTful API (20+ detection endpoints)
- ✅ **API Credentials** - Global access, 10K/hour rate limit, test interface 🆕
- ✅ **Rate Limiting** - Per-credential rate limiting with headers 🆕
- ✅ **Performance Monitoring** - Query count, memory, execution time in responses
- ✅ **Credential Caching** - 5-minute cache for API performance 🆕
- ✅ **File Storage** - Centralized storage with registry
- ✅ **Search & Filter** - All list views with pagination
- ✅ **Export Functionality** - CSV export, PDF export, print layouts
- ✅ **Charts & Visualization** - ApexCharts with detection trends
- ✅ **Reusable Components** - 40+ Blade components (x-button, x-card, etc.)
- ✅ **Device Encryption** - Auto-encrypt/decrypt device credentials
- ✅ **Professional UI** - Inter font, smooth sidebar scroll, responsive design
- ✅ **Standardized Status** - Unified active/inactive status component 🆕
- ✅ **Form Validation** - Enhanced validation with no spaces in code fields 🆕

## 📊 Project Statistics

| Metric                  | Count | Status  |
| ----------------------- | ----- | ------- |
| **Blade Views**         | 50    | ✅ 100% |
| **Blade Components**    | 40    | ✅ 100% |
| **Controllers**         | 16    | ✅ 100% |
| **Models**              | 17    | ✅ 100% |
| **Services**            | 15    | ✅ 100% |
| **Middleware**          | 5+    | ✅ 100% |
| **Queue Jobs**          | 9     | ✅ 100% |
| **API Endpoints**       | 20+   | ✅ 100% |
| **Database Tables**     | 25    | ✅ 100% |
| **Seeders**             | 13    | ✅ 100% |
| **Documentation Files** | 25+   | ✅ 100% |

**Latest Updates:**

- ✅ **Standardized Status Component** - Unified active/inactive status across all modules
- ✅ **Form Validation Enhancement** - No spaces allowed in code/id fields
- ✅ **API Credentials Management** (Admin only)
- ✅ **Enhanced API middleware** with rate limiting
- ✅ **Simplified credential creation** (3 fields)
- ✅ **Built-in API testing interface**
- ✅ **Badge component** with all variants
- ✅ **Device encryption** implemented
- ✅ **Inter font** integrated
- ✅ **Professional sidebar** with auto-scroll

---

## 🏗️ Architecture (MVCS Pattern)

```
app/
├── Models/ (17)              # Eloquent models
│   ├── CompanyGroup, CompanyBranch
│   ├── DeviceMaster, ReIdMaster
│   ├── ReIdBranchDetection, EventLog
│   ├── BranchEventSetting, ApiCredential
│   ├── CctvLayoutSetting, CctvPositionSetting
│   ├── CctvStream, WhatsAppSettings
│   ├── ApiUsageSummary, WhatsAppDeliverySummary
│   ├── CountingReport, StorageFile
│   └── User
│
├── Http/Controllers/
│   ├── Web/ (9)              # Web controllers
│   │   ├── CompanyGroupController
│   │   ├── CompanyBranchController
│   │   ├── DeviceMasterController
│   │   ├── ReIdMasterController
│   │   ├── CctvLayoutController
│   │   ├── CctvLiveStreamController
│   │   ├── EventLogController
│   │   ├── ReportController
│   │   ├── UserController
│   │   ├── ApiCredentialController (🆕 Admin only)
│   │   ├── BranchEventSettingController
│   │   └── WhatsAppSettingsController
│   │
│   └── Api/V1/ (7)           # API controllers
│       ├── AuthController
│       ├── UserController
│       ├── DetectionController (7 endpoints)
│       ├── ApiCredentialController
│       ├── StaticAuthController
│       └── TestController
│
├── Middleware/ (5+)          # HTTP middleware
│   ├── AdminOnly            # Admin role verification
│   ├── ApiKeyAuth           # API credential authentication 🆕
│   ├── ApiResponseMiddleware
│   ├── ValidateStaticToken
│   └── HandleInertiaRequests
│
├── Services/ (15)            # Business logic layer
│   ├── CompanyGroupService
│   ├── CompanyBranchService
│   ├── DeviceMasterService
│   ├── ReIdMasterService
│   ├── CctvLayoutService
│   ├── ApiCredentialService  # 🆕 API credential management
│   ├── BranchEventSettingService
│   ├── WhatsAppSettingsService
│   ├── EventLogService
│   ├── ReportService
│   ├── UserService
│   ├── AuthService
│   ├── BaseExportService
│   ├── LoggingService
│   └── BaseService
│
├── Jobs/ (9)                 # Queue jobs
│   ├── ProcessDetectionJob
│   ├── SendWhatsAppNotificationJob
│   ├── ProcessDetectionImageJob
│   ├── UpdateDailyReportJob
│   ├── UpdateMonthlyReportJob
│   ├── CleanupOldFilesJob
│   ├── AggregateApiUsageJob
│   ├── AggregateWhatsAppDeliveryJob
│   └── ProcessCCTVData
│
└── Helpers/ (5)              # Helper functions
    ├── ApiResponseHelper
    ├── StorageHelper
    ├── EncryptionHelper
    ├── WhatsAppHelper
    └── helpers.php

resources/views/ (50 blade files)
├── auth/ (2)
├── dashboard/ (1)
├── company-groups/ (4)
├── company-branches/ (4)
├── device-masters/ (4)
├── re-id-masters/ (3)
├── cctv-layouts/ (4)
├── cctv-live-stream/ (1)
├── event-logs/ (3)
├── reports/ (6)
├── users/ (4)
├── api-credentials/ (5)
├── branch-event-settings/ (3)
├── whatsapp-settings/ (4)
├── layouts/ (2)
└── components/ (40)
```

## 💻 System Requirements

- **PHP:** 8.2 or higher
- **Database:** PostgreSQL 15 or higher
- **Composer:** Latest version
- **Node.js:** 18 or higher
- **NPM:** Latest version
- **Extensions:** pdo_pgsql, mbstring, openssl, curl, gd, fileinfo

### **Recommended:**

- **Supervisor:** For queue workers
- **Redis:** For caching (optional)
- **Nginx/Apache:** Web server
- **SSL Certificate:** For HTTPS

## Quick Start (Assets Already Built! ✅)

Assets sudah di-compile dan siap digunakan. **Anda tidak perlu menjalankan `npm run dev`** untuk menjalankan aplikasi.

---

## 🔐 Default Credentials (After Seeding)

### **Admin Account:**

```
Email: admin@cctv.com
Password: admin123
Role: Admin (Full Access)
```

**Admin Features:**

- ✅ Full CRUD on all modules
- ✅ Company Groups management
- ✅ CCTV Layout configuration
- ✅ **API Credentials management** (`/api-credentials`) 🆕
- ✅ User management
- ✅ System settings

### **Operator Account:**

```
Email: operator.jakarta@cctv.com
Password: password
Role: User (Limited Access)
```

**User Features:**

- ✅ View dashboard & reports
- ✅ View CCTV streams
- ✅ View detection data
- ❌ No admin features (groups, layouts, API credentials)

**⚠️ Change these passwords in production!**

---

## 📡 API Usage

### **1. Create API Credentials (Admin Only)**

**Web Interface:** `/api-credentials`

1. Login as admin (`admin@cctv.com`)
2. Navigate to **API Credentials**
3. Click **"Create New Credential"**
4. Fill form (only 3 fields!):
   - Credential name
   - Expiration date (optional)
   - Status (active/inactive)
5. Click **"Create"**
6. **SAVE THE API SECRET** (shown only once!)

**Auto-Generated:**

- ✅ API Key: 40-character unique identifier
- ✅ API Secret: 40-character secure secret
- ✅ Global access (all branches & devices)
- ✅ Full permissions (read, write, delete)
- ✅ Rate limit: 10,000 requests/hour

### **2. Test API (Web Interface)**

**Test Interface:** `/api-credentials/{id}/test`

Features:

- 🧪 Select endpoint and send live requests
- 📊 View response status, headers, and body
- ⏱️ Measure response time
- 🔢 Track rate limit usage
- 📋 Copy cURL commands

### **3. Use API in Your Application**

**Authentication Headers:**

```http
X-API-Key: cctv_live_abc123xyz789def456ghi789jkl012
X-API-Secret: secret_mno345pqr678stu901vwx234yz567ab
Accept: application/json
```

**Example - Detection Logging:**

```bash
curl -X POST "http://localhost:8000/api/detection/log" \
  -H "X-API-Key: cctv_live_abc123xyz789def456ghi789jkl012" \
  -H "X-API-Secret: secret_mno345pqr678stu901vwx234yz567ab" \
  -H "Content-Type: application/json" \
  -d '{
    "re_id": "person_001",
    "branch_id": 1,
    "device_id": "CAMERA_001",
    "detected_count": 1,
    "detection_data": {
      "confidence": 0.95
    }
  }'
```

**Example - Get Detections:**

```bash
curl "http://localhost:8000/api/detections" \
  -H "X-API-Key: cctv_live_abc123xyz789def456ghi789jkl012" \
  -H "X-API-Secret: secret_mno345pqr678stu901vwx234yz567ab" \
  -H "Accept: application/json"
```

**Response includes rate limit headers:**

```http
X-RateLimit-Limit: 10000
X-RateLimit-Remaining: 9847
X-RateLimit-Reset: 1728399600
```

📖 **Complete API Docs:** See [docs/API_REFERENCE.md](docs/API_REFERENCE.md)

---

## 📚 Documentation

### **Getting Started:**

- 📖 **[SETUP_GUIDE.md](SETUP_GUIDE.md)** - Complete installation guide
- 📖 **[QUICK_START.md](QUICK_START.md)** - Quick start guide
- 📖 **[SEEDER_GUIDE.md](SEEDER_GUIDE.md)** - Database seeding guide

### **API Documentation:**

- 🔑 **[docs/API_REFERENCE.md](docs/API_REFERENCE.md)** - Complete API reference (Updated)
- 🔑 **[docs/API_CREDENTIALS_INTEGRATION.md](docs/API_CREDENTIALS_INTEGRATION.md)** - Integration guide (New)
- 🔑 **[docs/API_CREDENTIALS_ROUTES.md](docs/API_CREDENTIALS_ROUTES.md)** - Route reference (New)
- 📖 **[API_DETECTION_DOCUMENTATION.md](API_DETECTION_DOCUMENTATION.md)** - Detection API legacy docs
- 📖 **[API_QUICK_REFERENCE.md](API_QUICK_REFERENCE.md)** - Quick API reference

### **Architecture & Database:**

- 🏗️ **[docs/APPLICATION_PLAN.md](docs/APPLICATION_PLAN.md)** - Architecture overview (Updated)
- 🏗️ **[docs/SEQUENCE_DIAGRAMS.md](docs/SEQUENCE_DIAGRAMS.md)** - Workflow diagrams (Updated)
- 🗄️ **[docs/database_plan_en.md](docs/database_plan_en.md)** - Database design (Updated)
- 📊 **[COMPREHENSIVE_SUMMARY.md](COMPREHENSIVE_SUMMARY.md)** - Project overview

### **Implementation Guides:**

- 🔧 **[BLADE_VIEWS_IMPLEMENTATION_GUIDE.md](BLADE_VIEWS_IMPLEMENTATION_GUIDE.md)** - Frontend patterns
- 🔧 **[COMPONENT_GUIDE.md](COMPONENT_GUIDE.md)** - Reusable components
- 🔧 **[MIDDLEWARE_MIGRATION_SUMMARY.md](MIDDLEWARE_MIGRATION_SUMMARY.md)** - Middleware patterns
- 🔧 **[ENCRYPTION_GUIDE.md](ENCRYPTION_GUIDE.md)** - Device encryption guide

### **Testing & Deployment:**

- 🧪 **[TESTING_GUIDE.md](TESTING_GUIDE.md)** - Testing procedures
- 🚀 **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** - Production deployment
- 📋 **[docs/DOCS_UPDATE_SUMMARY.md](docs/DOCS_UPDATE_SUMMARY.md)** - Latest documentation changes (New)

---

## Installation

### 1. Install Composer Dependencies

```bash
composer install
```

**Note:** npm packages sudah di-build, tidak perlu `npm install` kecuali Anda ingin development/modifikasi CSS/JS.

### 2. Configure Database

Edit `.env` file and configure PostgreSQL connection:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cctv_dashboard
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Create the database:

```bash
createdb cctv_dashboard
# or via psql
psql -U postgres -c "CREATE DATABASE cctv_dashboard;"
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Run Migrations & Seeders

```bash
php artisan migrate --seed
```

This will create:

- **Admin user:** `admin@cctv.com` / `admin123`
- **Operator user:** `operator.jakarta@cctv.com` / `password`
- Sample company groups and branches
- Sample devices with encrypted credentials
- Sample detection data for dashboard
- Sample event logs

### 5. Start Server (Assets Already Built!)

Assets production sudah tersedia di `public/build/`. Langsung jalankan:

```bash
php artisan serve
```

**Optional - Hanya untuk Development CSS/JS:**
Jika ingin modifikasi styling/JavaScript:

```bash
npm install  # sekali saja
npm run dev  # untuk hot reload
```

### 6. Access Application

Visit: http://localhost:8000/login

**Default Login:**

- **Admin:** `admin@cctv.com` / `admin123`
- **Operator:** `operator.jakarta@cctv.com` / `password`

**Admin Dashboard:** http://localhost:8000/dashboard  
**API Credentials:** http://localhost:8000/api-credentials (Admin only)

## 🛣️ Routes

### Web Routes (routes/web.php)

**Public:**

- `GET /login` - Login page
- `POST /login` - Login action

**Authenticated:**

- `POST /logout` - Logout action
- `GET /dashboard` - Dashboard overview
- Resource routes: `/company-groups`, `/company-branches`, `/device-masters`, `/re-id-masters`, `/event-logs`, `/users`

**Admin Only (middleware: 'admin'):**

- Resource routes: `/company-groups` - Company group management
- Resource routes: `/cctv-layouts` - CCTV layout configuration
- Resource routes: `/api-credentials` - API credential management 🆕
- `GET /api-credentials/{id}/test` - API testing interface 🆕

**Reports:**

- `GET /reports/dashboard` - Reports dashboard
- `GET /reports/daily` - Daily reports
- `GET /reports/monthly` - Monthly reports

**CCTV:**

- `GET /cctv-live-stream` - Live CCTV streaming page

### API Routes (routes/api.php)

Aplikasi memiliki **3 jenis API authentication**:

#### 1. API Key Authentication (Primary - Detection API)

**Protected by `api.key` middleware:**

**Detection Logging:**

- `POST /api/detection/log` - Log new detection
- `GET /api/detection/status/{jobId}` - Check detection status

**Detection Queries:**

- `GET /api/detections` - List all detections
- `GET /api/detection/summary` - Detection summary

**Person (Re-ID) Queries:**

- `GET /api/person/{reId}` - Get person details
- `GET /api/person/{reId}/detections` - Person detection history

**Branch Queries:**

- `GET /api/branch/{branchId}/detections` - Branch detections

**Authentication:**

```http
X-API-Key: cctv_live_abc123...
X-API-Secret: secret_mno345...
Accept: application/json
```

**Features:**

- 🌐 Global access (all branches & devices)
- 🔑 Full permissions (read, write, delete)
- ⚡ 10,000 requests/hour rate limit
- 📊 Rate limit headers in response
- 🔒 Timing-safe secret verification

#### 2. Sanctum API (`/api/*`) - User Management

**Public:**

- `POST /api/register` - Register & get token
- `POST /api/login` - Login & get token

**Protected (requires Sanctum Bearer token):**

- `POST /api/logout` - Logout
- `GET /api/me` - Get authenticated user
- `GET /api/users` - List users
- `POST /api/users` - Create user
- `GET /api/users/{id}` - Show user
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user

#### 3. Static Token API (`/api/static/*`) - Legacy Testing

**Protected (requires Static Bearer token):**

- `GET /api/static/validate` - Validate token
- `GET /api/static/test` - Test endpoint
- Various test endpoints

## 🔐 API Authentication Methods

### A. API Key Authentication (Recommended for External Systems)

**Create Credentials (Admin Only):**

1. Login as admin → Navigate to `/api-credentials`
2. Click "Create New Credential" → Fill 3 fields → Create
3. **Save the API secret** (shown once!)

**Use in Requests:**

```bash
# JavaScript
const response = await fetch('/api/detections', {
  headers: {
    'X-API-Key': 'cctv_live_abc123...',
    'X-API-Secret': 'secret_mno345...',
    'Accept': 'application/json'
  }
});

# Check rate limit
const remaining = response.headers.get('X-RateLimit-Remaining');
console.log(`Requests remaining: ${remaining}/10000`);
```

```bash
# cURL
curl -X GET "http://localhost:8000/api/detections" \
  -H "X-API-Key: cctv_live_abc123..." \
  -H "X-API-Secret: secret_mno345..." \
  -H "Accept: application/json"
```

```php
# PHP/Laravel
$response = Http::withHeaders([
    'X-API-Key' => env('CCTV_API_KEY'),
    'X-API-Secret' => env('CCTV_API_SECRET'),
])->get('http://localhost:8000/api/detections');
```

**Features:**

- ✅ 10,000 requests/hour rate limit
- ✅ Global access (all branches & devices)
- ✅ Rate limit headers in response
- ✅ Timing-safe authentication
- ✅ Built-in test interface

### B. Sanctum Authentication (For User Management)

**1. Login and get token:**

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@cctv.com","password":"admin123"}'
```

Response:

```json
{
  "success": true,
  "data": {
    "user": {...},
    "token": "1|xxxxxxxxxxxxx"
  }
}
```

**2. Use token for authenticated requests:**

```bash
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxx"
```

### C. Static Token Authentication (Legacy)

**Set token in `.env`:**

```env
API_STATIC_TOKEN=your-secret-static-token-here
```

**Use static token:**

```bash
curl -X GET http://localhost:8000/api/static/test \
  -H "Authorization: Bearer your-secret-static-token-here"
```

📖 **Complete API Documentation:** [docs/API_REFERENCE.md](docs/API_REFERENCE.md)

## 👥 User Roles & Permissions

### **Admin** (System Administrator)

**Full Access:**

- ✅ Full CRUD on all modules
- ✅ **Company Groups** management (create/edit/delete)
- ✅ **CCTV Layouts** configuration (4/6/8-window)
- ✅ **API Credentials** management (`/api-credentials`) 🆕
- ✅ User management & role assignment
- ✅ System settings & configuration
- ✅ All reports & analytics
- ✅ Event configuration

**Admin-Only Routes:**

- `/company-groups/*` (middleware: admin)
- `/cctv-layouts/*` (middleware: admin)
- `/api-credentials/*` (middleware: admin) 🆕

### **Operator** (Standard User)

**Limited Access:**

- ✅ View dashboard & statistics
- ✅ View CCTV live streams
- ✅ View detection data
- ✅ View reports
- ✅ View device information
- ❌ Cannot manage company groups
- ❌ Cannot configure CCTV layouts
- ❌ Cannot manage API credentials
- ❌ Cannot manage users

## Development Commands

```bash
# Run development server
php artisan serve

# Watch and compile assets
npm run dev

# Build for production
npm run build

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migration with seed
php artisan migrate:fresh --seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Architecture & Best Practices

### MVCS Pattern

1. **Models** (`app/Models/`)

   - Data structure and database relationships
   - Business rules related to data

2. **Views** (`resources/views/`)

   - Blade templates for UI
   - Presentation logic only

3. **Controllers** (`app/Http/Controllers/`)

   - Handle HTTP requests
   - Validate input
   - Delegate business logic to Services
   - Return responses

4. **Services** (`app/Services/`)
   - Business logic layer
   - Reusable operations
   - Data manipulation
   - Keep controllers thin

### Code Quality

- **DRY Principle**: Service layer prevents code duplication
- **Single Responsibility**: Each class has one clear purpose
- **Dependency Injection**: Services injected into controllers
- **Type Hints**: Full PHP type declarations
- **Clean Code**: Descriptive names, proper formatting

## 🔒 Security Features

### **Web Security**

- ✅ CSRF protection on all forms
- ✅ Password hashing with bcrypt
- ✅ Session-based authentication
- ✅ Role-based access control (admin middleware)
- ✅ XSS protection in Blade templates
- ✅ SQL injection protection via Eloquent ORM

### **API Security**

- ✅ **API Key Authentication** with timing-safe comparison (`hash_equals`) 🆕
- ✅ **Rate Limiting** - 10,000 requests/hour per credential 🆕
- ✅ **Request Logging** - Failed attempts logged with IP 🆕
- ✅ **Credential Caching** - 5-minute cache for performance 🆕
- ✅ Sanctum token authentication for user API
- ✅ Static token for legacy systems
- ✅ API secret shown only once (one-time display)
- ✅ Credential expiration checks

### **Data Security**

- ✅ **Device Credential Encryption** - Auto-encrypt device passwords 🆕
- ✅ Encrypted fields (username, password) in device_masters
- ✅ Secure file storage with access control
- ✅ Environment-based encryption configuration

## 🎨 UI Components & Design

### **Design System**

- ✅ **Inter Font** - Modern, professional typography 🆕
- ✅ **Tailwind CSS v4.1** - Utility-first CSS framework
- ✅ **Responsive Design** - Mobile, tablet, desktop optimized
- ✅ **Dark Mode Ready** - Prepared for dark theme

### **Reusable Components (24)**

- ✅ `x-button` - Styled buttons (primary, secondary, success, danger, warning)
- ✅ `x-card` - Card containers with optional titles
- ✅ `x-input` - Form inputs with labels & hints
- ✅ `x-select` - Dropdown selects
- ✅ `x-textarea` - Text areas
- ✅ `x-checkbox` - Checkboxes with labels
- ✅ `x-badge` - Status badges (9 variants including secondary) 🆕
- ✅ `x-alert` - Alert messages
- ✅ `x-modal` - Modal dialogs
- ✅ `x-company-branch-select` - Branch dropdown
- ✅ And 14 more specialized components...

### **Navigation**

- ✅ **Professional Sidebar** - Smooth scroll with auto-scroll to active item 🆕
- ✅ **Breadcrumbs** - Current page context
- ✅ **Search & Filter** - All list views
- ✅ **Pagination** - Consistent across all tables

### **Data Display**

- ✅ **Tables** - Sortable, filterable with pagination
- ✅ **Charts** - ApexCharts integration for trends
- ✅ **Statistics Cards** - Dashboard metrics
- ✅ **Horizontal Scroll** - For wide tables 🆕

### **Forms**

- ✅ **Validation** - Client & server-side
- ✅ **Error Messages** - Inline field errors
- ✅ **Success Notifications** - Toast/alert messages
- ✅ **Auto-save** - CCTV position auto-save 🆕

---

## 🆕 Latest Features (December 2024)

### **UI/UX Standardization**

- ✅ **Standardized Status Component** - Unified active/inactive status across all modules
- ✅ **Form Validation Enhancement** - No spaces allowed in code/id fields (device_id, province_code, branch_code, email)
- ✅ **Consistent Component Usage** - All modules now use standardized components
- ✅ **Enhanced User Experience** - Better form validation and user feedback

### **API Credentials Management**

- ✅ Web interface at `/api-credentials` (Admin only)
- ✅ Simplified creation (3 fields: name, expiry, status)
- ✅ Auto-generated 40-character keys & secrets
- ✅ Global access (all branches & devices)
- ✅ Full permissions by default
- ✅ 10,000 requests/hour rate limit
- ✅ One-time secret display (security)
- ✅ Test interface for API validation
- ✅ Timing-safe authentication
- ✅ Request logging with IP tracking
- ✅ Credential caching (5 minutes)
- ✅ Rate limit headers in responses

### **Enhanced Middleware**

- ✅ `ApiKeyAuth` middleware with rate limiting
- ✅ Middleware in routes file (not controller)
- ✅ `AdminOnly` middleware for admin routes
- ✅ Clean controller structure

### **UI/UX Improvements**

- ✅ Inter font for professional typography
- ✅ Professional sidebar with smooth scroll
- ✅ Auto-scroll to active menu item
- ✅ Badge component with 9 variants
- ✅ Horizontal scroll for wide tables
- ✅ Detection trend charts (Last 7 Days)
- ✅ Comparison percentage for detections

### **Security Enhancements**

- ✅ Device credential encryption
- ✅ Timing-safe secret comparison
- ✅ Failed attempt logging with IP
- ✅ One-time secret display
- ✅ Expiration checks

### **Performance**

- ✅ Credential caching (5 min)
- ✅ Async last_used_at updates
- ✅ Rate limiting with Cache
- ✅ Optimized queries

---

## 📦 Technology Stack

### **Backend**

- **Framework:** Laravel 11
- **Language:** PHP 8.2+
- **Database:** PostgreSQL 15+
- **Cache:** Redis (recommended) or File
- **Queue:** Database driver with Supervisor
- **Authentication:** Laravel Sanctum + Custom API Key

### **Frontend**

- **Templating:** Laravel Blade
- **CSS:** Tailwind CSS v4.1
- **JavaScript:** Alpine.js (lightweight)
- **Charts:** ApexCharts
- **Icons:** Heroicons (SVG)
- **Font:** Inter

### **External Services**

- **WhatsApp:** WAHA (WhatsApp HTTP API)
- **Image Processing:** Intervention/Image
- **Storage:** Local/S3 compatible

---

## 🧪 Testing

### **Web Testing**

```bash
# Login and navigate
# Test all CRUD operations
# Test API credentials creation
# Test API with test interface
```

### **API Testing**

```bash
# Use built-in test interface
http://localhost:8000/api-credentials/{id}/test

# Or use cURL
curl -X GET "http://localhost:8000/api/detections" \
  -H "X-API-Key: YOUR_KEY" \
  -H "X-API-Secret: YOUR_SECRET"
```

### **Check Routes**

```bash
php artisan route:list --path=api-credentials
php artisan route:list --path=api/detection
```

---

## 🚀 Production Deployment

### **Pre-deployment Checklist**

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate strong `APP_KEY`
- [ ] Configure PostgreSQL production database
- [ ] Set up Redis for caching (recommended)
- [ ] Configure queue workers with Supervisor
- [ ] Set up SSL certificate
- [ ] Configure CORS properly
- [ ] Set up file backups
- [ ] Configure log rotation
- [ ] Test API credentials creation
- [ ] Test rate limiting
- [ ] Change default passwords
- [ ] Configure WhatsApp API (optional)
- [ ] Set up monitoring

### **Queue Workers**

```bash
# Start queue workers
php artisan queue:work --queue=detections,notifications,images,reports,maintenance
```

### **Supervisor Configuration**

See **[database_plan_en.md](docs/core/database_plan_en.md)** for complete supervisor setup and queue worker configuration.

---

## 📞 Support & Resources

### **📚 Complete Documentation**

#### **Core Documentation (docs/core/)**

- 📡 **[API_REFERENCE.md](docs/core/API_REFERENCE.md)** - Complete API reference with all endpoints, authentication, rate limiting, and examples
- 📱 **[APPLICATION_PLAN.md](docs/core/APPLICATION_PLAN.md)** - Comprehensive application architecture, workflows, and user roles
- 🗄️ **[database_plan_en.md](docs/core/database_plan_en.md)** - Database schema, migrations, indexes, and optimization
- 🔄 **[SEQUENCE_DIAGRAMS.md](docs/core/SEQUENCE_DIAGRAMS.md)** - System workflows and sequence diagrams

#### **API Documentation**

- 🎯 **[API_DETECTION_DOCUMENTATION.md](docs/API_DETECTION_DOCUMENTATION.md)** - Detection API legacy docs with complete examples
- 🔑 **[API_CREDENTIALS_INTEGRATION.md](docs/API_CREDENTIALS_INTEGRATION.md)** - API credential management guide

#### **Technical Documentation**

- 🏗️ **[BACKEND_COMPLETION_SUMMARY.md](docs/BACKEND_COMPLETION_SUMMARY.md)** - Backend development summary and features
- 🧩 **[COMPONENTS.md](docs/COMPONENTS.md)** - Blade components documentation
- 📊 **[COMPREHENSIVE_SUMMARY.md](docs/COMPREHENSIVE_SUMMARY.md)** - Complete project overview and statistics

### **Quick Links**

- **Dashboard:** `/dashboard`
- **API Credentials:** `/api-credentials` (Admin)
- **CCTV Layouts:** `/cctv-layouts` (Admin)
- **Company Groups:** `/company-groups` (Admin)
- **Live Stream:** `/cctv-live-stream`
- **Reports:** `/reports/dashboard`

### **Commands**

```bash
# Check application status
php artisan route:list
php artisan queue:monitor

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Monitor logs
tail -f storage/logs/laravel.log
```

---

## License

This project is open-sourced software.
