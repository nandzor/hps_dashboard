# 🚀 CCTV Dashboard - Setup Guide

**Complete Installation & Configuration Guide**

---

## 📋 PREREQUISITES

### **System Requirements:**

- **PHP:** 8.2 or higher
- **Database:** PostgreSQL 15 or higher
- **Composer:** Latest version
- **Node.js:** 18 or higher (for Vite)
- **NPM:** Latest version

### **PHP Extensions Required:**

```bash
php -m | grep -E "pdo|pgsql|mbstring|openssl|curl|json|tokenizer|xml|ctype|fileinfo|gd"
```

Required extensions:

- ✅ PDO
- ✅ pdo_pgsql
- ✅ mbstring
- ✅ openssl
- ✅ curl
- ✅ json
- ✅ tokenizer
- ✅ xml
- ✅ ctype
- ✅ fileinfo
- ✅ gd

---

## 📦 INSTALLATION

### **Step 1: Clone & Install Dependencies**

```bash
# Navigate to project directory
cd /home/nandzo/app/cctv_dashboard

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

---

### **Step 2: Environment Configuration**

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

**Edit `.env` file:**

```env
APP_NAME="CCTV Dashboard"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cctv_dashboard
DB_USERNAME=postgres
DB_PASSWORD=your_password_here

# Queue
QUEUE_CONNECTION=database

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Encryption (must be true for production)
ENCRYPT_DEVICE_CREDENTIALS=true
ENCRYPT_STREAM_CREDENTIALS=true

# WhatsApp (Optional - configure if using WhatsApp)
WHATSAPP_PROVIDER=waha
WHATSAPP_API_URL=http://localhost:3000
WHATSAPP_API_KEY=your_waha_api_key
WHATSAPP_SESSION_NAME=default
WHATSAPP_RETRY_ATTEMPTS=3
WHATSAPP_TIMEOUT=30

# Storage
FILESYSTEM_DISK=local
STORAGE_MAX_FILE_SIZE=10240
STORAGE_AUTO_CLEANUP_DAYS=90

# Performance Monitoring
DB_LOG_QUERIES=false
PERFORMANCE_MONITORING=true
SLOW_QUERY_THRESHOLD=1000
HIGH_MEMORY_THRESHOLD=128
```

---

### **Step 3: Database Setup**

```bash
# Create PostgreSQL database
createdb cctv_dashboard

# Or using psql
psql -U postgres
CREATE DATABASE cctv_dashboard;
\q

# Run migrations
php artisan migrate

# Seed database with test data
php artisan db:seed

# Or do both at once (fresh install)
php artisan migrate:fresh --seed
```

---

### **Step 4: Storage Setup**

```bash
# Create storage link
php artisan storage:link

# Set permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Create required directories
mkdir -p storage/app/logs/api_requests
mkdir -p storage/app/logs/whatsapp_messages
mkdir -p storage/app/events
```

---

### **Step 5: Build Assets**

```bash
# Development
npm run dev

# Production
npm run build
```

---

### **Step 6: Queue Workers (Development)**

```bash
# Run queue worker in development
php artisan queue:work --queue=critical,notifications,detections,images,reports,maintenance,default
```

**Or run in separate terminals:**

```bash
# Terminal 1: Detection queue
php artisan queue:work --queue=detections --tries=3

# Terminal 2: Notifications queue
php artisan queue:work --queue=notifications --tries=5

# Terminal 3: Other queues
php artisan queue:work --queue=images,reports,maintenance
```

---

### **Step 7: Start Development Server**

```bash
# Start Laravel development server
php artisan serve

# Or use specific port
php artisan serve --port=8000
```

Visit: `http://localhost:8000`

---

## 🔐 DEFAULT CREDENTIALS

### **Admin Account:**

```
Email: admin@cctv.com
Password: admin123
Role: Admin
```

**Access:** Full access to all modules

### **Operator Account:**

```
Email: operator.jakarta@cctv.com
Password: password
Role: User
```

**Access:** Limited to viewing and operations (no admin features)

---

## 🧪 TESTING THE INSTALLATION

### **1. Test Web Application**

```bash
# Login as admin
URL: http://localhost:8000/login
Email: admin@cctv.com
Password: admin123

# Verify you can access:
✅ Dashboard
✅ Company Groups
✅ Company Branches
✅ Device Masters
✅ Users
✅ CCTV Layouts
✅ Person Tracking
✅ Event Logs
✅ Reports
```

### **2. Test API Endpoints**

```bash
# First, create API credentials via web interface:
# 1. Login as admin
# 2. Create API credential (if API credential management exists)

# Or use default testing:
curl -X GET "http://localhost:8000/api/detections" \
  -H "X-API-Key: test_key" \
  -H "X-API-Secret: test_secret"
```

### **3. Test Detection Logging**

```bash
# POST detection event
curl -X POST "http://localhost:8000/api/detection/log" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret" \
  -H "Content-Type: application/json" \
  -d '{
    "re_id": "test_person_001",
    "branch_id": 1,
    "device_id": "CAM_JKT001_001",
    "detected_count": 1,
    "detection_data": {
      "confidence": 0.95
    }
  }'
```

---

## 🔧 COMMON ISSUES & SOLUTIONS

### **Issue: Database Connection Failed**

```bash
# Check PostgreSQL is running
sudo systemctl status postgresql

# Start PostgreSQL
sudo systemctl start postgresql

# Verify database exists
psql -U postgres -l | grep cctv_dashboard
```

### **Issue: Permission Denied on Storage**

```bash
# Fix storage permissions
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
```

### **Issue: Queue Jobs Not Processing**

```bash
# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### **Issue: Routes Not Found**

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerate caches
php artisan config:cache
php artisan route:cache
```

### **Issue: Assets Not Loading**

```bash
# Rebuild assets
npm run build

# Check storage link exists
ls -la public/storage

# Recreate storage link
php artisan storage:link --force
```

---

## 🚀 PRODUCTION DEPLOYMENT

### **1. Environment Configuration**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=your_db_host
DB_PORT=5432
DB_DATABASE=cctv_dashboard_prod
DB_USERNAME=cctv_user
DB_PASSWORD=strong_password_here

# Disable query logging in production
DB_LOG_QUERIES=false

# Enable encryption
ENCRYPT_DEVICE_CREDENTIALS=true
ENCRYPT_STREAM_CREDENTIALS=true

# Configure actual WhatsApp credentials
WHATSAPP_PROVIDER=waha
WHATSAPP_API_URL=https://your-whatsapp-api.com
WHATSAPP_API_KEY=your_production_key
```

### **2. Optimize for Production**

```bash
# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Build production assets
npm run build

# Set permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### **3. Setup Supervisor (Queue Workers)**

Create `/etc/supervisor/conf.d/cctv-workers.conf`:

```ini
[program:cctv-worker-detections]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work database --queue=detections --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=5
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker-detections.log

[program:cctv-worker-notifications]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work database --queue=notifications --sleep=3 --tries=5 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker-notifications.log
```

```bash
# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### **4. Setup Cron Jobs**

```bash
# Edit crontab
crontab -e

# Add Laravel scheduler
* * * * * cd /path/to/cctv_dashboard && php artisan schedule:run >> /dev/null 2>&1
```

### **5. Setup HTTPS (Nginx Example)**

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    root /path/to/cctv_dashboard/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

---

## ✅ POST-DEPLOYMENT CHECKLIST

- [ ] APP_DEBUG=false in production
- [ ] APP_ENV=production
- [ ] Strong APP_KEY generated
- [ ] Database credentials secure
- [ ] HTTPS/SSL configured
- [ ] Firewall configured
- [ ] Cron jobs running
- [ ] Queue workers running (Supervisor)
- [ ] Storage permissions correct (755)
- [ ] Logs rotating properly
- [ ] Backups configured
- [ ] Monitoring tools setup
- [ ] Change default passwords!
- [ ] Create real API credentials
- [ ] Configure actual WhatsApp provider
- [ ] Test all critical features

---

## 📖 DOCUMENTATION REFERENCE

- **API Documentation:** `API_DETECTION_DOCUMENTATION.md`
- **API Quick Reference:** `API_QUICK_REFERENCE.md`
- **Frontend Guide:** `BLADE_VIEWS_IMPLEMENTATION_GUIDE.md`
- **Database Plan:** `database_plan_en.md`
- **Application Plan:** `APPLICATION_PLAN.md`
- **Seeder Guide:** `SEEDER_GUIDE.md`
- **Middleware Guide:** `MIDDLEWARE_MIGRATION_SUMMARY.md`

---

## 🆘 SUPPORT

### **Logs Location:**

```bash
# Application logs
storage/logs/laravel.log

# Queue worker logs
storage/logs/worker-*.log

# API request logs
storage/app/logs/api_requests/YYYY-MM-DD.log

# WhatsApp message logs
storage/app/logs/whatsapp_messages/YYYY-MM-DD.log
```

### **Debug Commands:**

```bash
# Check application status
php artisan about

# List all routes
php artisan route:list

# Check queue status
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed

# View logs
tail -f storage/logs/laravel.log
```

---

**Setup Guide Version:** 1.0  
**Last Updated:** October 7, 2025

_End of Setup Guide_
