# 🌱 Database Seeder Guide

**Created:** October 7, 2025  
**Status:** ✅ COMPLETE

---

## 📋 OVERVIEW

Seeders telah dibuat untuk populate database dengan data testing yang lengkap dan realistic.

---

## 📂 SEEDER FILES

### **1. UserSeeder.php** ✅

**Creates:**

- 2 Admin users
- 4 Regular users (operators/viewers)

**Default Credentials:**

| Email                      | Password | Role  |
| -------------------------- | -------- | ----- |
| admin@cctv.com             | admin123 | admin |
| admin2@cctv.com            | password | admin |
| operator.jakarta@cctv.com  | password | user  |
| operator.bandung@cctv.com  | password | user  |
| operator.surabaya@cctv.com | password | user  |
| viewer@cctv.com            | password | user  |

---

### **2. CompanyGroupSeeder.php** ✅

**Creates:**

- 5 Company Groups (Province level)

**Data:**

| Code   | Province    | Group Name     |
| ------ | ----------- | -------------- |
| JKT    | DKI Jakarta | Jakarta Group  |
| JABAR  | Jawa Barat  | Bandung Group  |
| JATIM  | Jawa Timur  | Surabaya Group |
| JATENG | Jawa Tengah | Semarang Group |
| BALI   | Bali        | Bali Group     |

---

### **3. CompanyBranchSeeder.php** ✅

**Creates:**

- 7 Company Branches (City level)

**Data:**

| Code   | Branch Name             | City            | Group    |
| ------ | ----------------------- | --------------- | -------- |
| JKT001 | Jakarta Central Branch  | Jakarta Pusat   | Jakarta  |
| JKT002 | Jakarta South Branch    | Jakarta Selatan | Jakarta  |
| JKT003 | Jakarta West Branch     | Jakarta Barat   | Jakarta  |
| BDG001 | Bandung City Branch     | Bandung         | Bandung  |
| BDG002 | Bandung North Branch    | Bandung Utara   | Bandung  |
| SBY001 | Surabaya Central Branch | Surabaya        | Surabaya |
| SBY002 | Surabaya East Branch    | Surabaya Timur  | Surabaya |

**Features:**

- GPS coordinates included
- Contact information (phone, email)
- Realistic addresses

---

### **4. DeviceMasterSeeder.php** ✅

**Creates:**

- 9 Devices across all branches

**Device Types:**

| Device ID       | Type     | Branch           | Description          |
| --------------- | -------- | ---------------- | -------------------- |
| CAM_JKT001_001  | camera   | Jakarta Central  | Main Entrance Camera |
| CAM_JKT001_002  | camera   | Jakarta Central  | Parking Area Camera  |
| NODE_JKT001_001 | node_ai  | Jakarta Central  | AI Detection Node    |
| CAM_JKT002_001  | cctv     | Jakarta South    | Lobby Camera         |
| CAM_JKT002_002  | camera   | Jakarta South    | Reception Camera     |
| CAM_BDG001_001  | camera   | Bandung City     | Entry Sensor Camera  |
| NODE_BDG001_001 | node_ai  | Bandung City     | AI Detection Node    |
| CAM_SBY001_001  | camera   | Surabaya Central | Main Gate Camera     |
| MIKROTIK_SBY001 | mikrotik | Surabaya Central | Network Router       |

**Features:**

- Encrypted credentials (username/password)
- RTSP URLs for cameras
- HTTP URLs for Node AI devices
- Device type variations

---

### **5. BranchEventSettingSeeder.php** ✅

**Creates:**

- Event settings for each device

**Configuration:**

- All settings enabled (send_image, send_message, send_notification)
- WhatsApp enabled for cameras and Node AI devices
- Sample WhatsApp numbers: +6281234567890, +6287654321098
- Message template with variables: {branch_name}, {device_name}, {timestamp}

---

### **6. CctvLayoutSeeder.php** ✅

**Creates:**

- 3 CCTV Layout configurations

**Layouts:**

| Layout Name              | Type     | Positions | Default | Description                   |
| ------------------------ | -------- | --------- | ------- | ----------------------------- |
| Default 4-Window Layout  | 4-window | 4         | ✅ Yes  | Standard quad view            |
| Extended 6-Window Layout | 6-window | 6         | ❌ No   | Extended monitoring           |
| Maximum 8-Window Layout  | 8-window | 8         | ❌ No   | Maximum surveillance coverage |

**Position Configuration:**

- Auto-assigned branches and devices
- Quality settings (high/medium)
- Auto-switch enabled for some positions
- Switch intervals configured

---

## 🚀 USAGE

### **Run All Seeders**

```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Or run seeders only
php artisan db:seed
```

### **Run Specific Seeder**

```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CompanyGroupSeeder
php artisan db:seed --class=CompanyBranchSeeder
php artisan db:seed --class=DeviceMasterSeeder
php artisan db:seed --class=BranchEventSettingSeeder
php artisan db:seed --class=CctvLayoutSeeder
```

### **Seeding Order (Automatic)**

The `DatabaseSeeder` calls seeders in the correct dependency order:

```
1. UserSeeder              → Creates users (needed for created_by)
2. CompanyGroupSeeder      → Creates company groups
3. CompanyBranchSeeder     → Creates branches (needs groups)
4. DeviceMasterSeeder      → Creates devices (needs branches)
5. BranchEventSettingSeeder → Creates settings (needs branches + devices)
6. CctvLayoutSeeder        → Creates layouts (needs users + branches + devices)
```

---

## 📊 DATA SUMMARY

After seeding, your database will have:

| Table                  | Records | Description                     |
| ---------------------- | ------- | ------------------------------- |
| users                  | 6       | 2 admins + 4 operators          |
| company_groups         | 5       | Province-level groups           |
| company_branches       | 7       | City-level branches             |
| device_masters         | 9       | Cameras, Node AI, Mikrotik      |
| branch_event_settings  | 9       | Event settings per device       |
| cctv_layout_settings   | 3       | 4/6/8-window layouts            |
| cctv_position_settings | 18      | Position configurations (4+6+8) |

**Total Records:** ~60 records ready for testing

---

## 🧪 TESTING DATA

### **Login Credentials**

**Admin Access:**

```
Email: admin@cctv.com
Password: admin123
Role: Admin (full access)
```

**Operator Access:**

```
Email: operator.jakarta@cctv.com
Password: password
Role: User (limited access)
```

### **Sample Data Available**

**Branches:**

- 3 Jakarta branches (Central, South, West)
- 2 Bandung branches (City, North)
- 2 Surabaya branches (Central, East)

**Devices:**

- 6 Cameras (various types)
- 2 Node AI devices
- 1 Mikrotik router

**Layouts:**

- 1 Default 4-window layout (active)
- 1 Extended 6-window layout
- 1 Maximum 8-window layout

---

## 🔧 CUSTOMIZATION

### **Add More Devices**

Edit `DeviceMasterSeeder.php`:

```php
$devices[] = [
    'device_id' => 'CAM_JKT003_001',
    'device_name' => 'Jakarta West - Front Door Camera',
    'device_type' => 'camera',
    'branch_id' => $branches->where('branch_code', 'JKT003')->first()->id,
    'url' => 'rtsp://192.168.5.100:554/stream1',
    'username' => 'admin',
    'password' => 'camera123',
    'notes' => 'Front door camera',
    'status' => 'active',
];
```

### **Add More Branches**

Edit `CompanyBranchSeeder.php`:

```php
$branches[] = [
    'group_id' => $jakartaGroup->id,
    'branch_code' => 'JKT004',
    'branch_name' => 'Jakarta East Branch',
    'city' => 'Jakarta Timur',
    'address' => 'Jl. Basuki Rachmat No.10',
    'phone' => '021-99999999',
    'email' => 'jkt.east@cctv.com',
    'latitude' => -6.225000,
    'longitude' => 106.900000,
    'status' => 'active',
];
```

### **Add More Users**

Edit `UserSeeder.php`:

```php
User::create([
    'name' => 'New User Name',
    'email' => 'newuser@cctv.com',
    'password' => Hash::make('password'),
    'role' => 'user', // or 'admin'
]);
```

---

## 🔄 RESET & RESEED

### **Complete Reset**

```bash
# Warning: This will delete ALL data!
php artisan migrate:fresh --seed
```

### **Refresh Specific Data**

```bash
# Truncate and reseed specific tables
php artisan db:seed --class=UserSeeder --force
```

### **Add More Data (Without Deleting)**

```bash
# Run seeder multiple times (might cause duplicates)
php artisan db:seed --class=DeviceMasterSeeder
```

---

## ✅ VERIFICATION

### **After Seeding, Verify:**

```bash
# Check record counts
php artisan tinker

>>> \App\Models\User::count()
=> 6

>>> \App\Models\CompanyGroup::count()
=> 5

>>> \App\Models\CompanyBranch::count()
=> 7

>>> \App\Models\DeviceMaster::count()
=> 9

>>> \App\Models\BranchEventSetting::count()
=> 9

>>> \App\Models\CctvLayoutSetting::count()
=> 3

>>> \App\Models\CctvPositionSetting::count()
=> 18
```

### **Test Admin Login**

```bash
# Visit http://your-domain.com/login
# Email: admin@cctv.com
# Password: admin123

# Should see all menu items:
# - Dashboard
# - Company Groups (admin only)
# - Branches
# - Devices
# - Users
# - CCTV Layouts (admin only)
# - Person Tracking
# - Event Logs
# - Analytics
# - Daily Reports
# - Monthly Reports
```

### **Test Regular User Login**

```bash
# Visit http://your-domain.com/login
# Email: operator.jakarta@cctv.com
# Password: password

# Should NOT see:
# - Company Groups (403 Forbidden)
# - CCTV Layouts (403 Forbidden)
```

---

## 📝 SEEDER GUIDELINES

### **Best Practices:**

1. ✅ **Dependency Order** - Seed in correct order (users → groups → branches → devices)
2. ✅ **Realistic Data** - Use real-world examples
3. ✅ **Relationships** - Ensure foreign keys are valid
4. ✅ **Variety** - Different types, statuses, configurations
5. ✅ **Security** - Hash passwords, encrypt credentials
6. ✅ **Documentation** - Document default credentials

### **Do NOT:**

- ❌ Seed in production environment
- ❌ Use weak passwords in production
- ❌ Hard-code sensitive data
- ❌ Create duplicate unique values
- ❌ Skip dependency checks

---

## 🎯 NEXT STEPS

### **After Seeding:**

1. **Login & Test:**

   - Login as admin
   - Test all CRUD operations
   - Verify middleware works
   - Check navigation menu

2. **Test API:**

   - Create API credentials
   - Test detection logging
   - Test detection queries

3. **Test Features:**
   - Create events
   - Test WhatsApp (configure first)
   - Test layouts
   - Generate reports

---

## 📚 RELATED COMMANDS

```bash
# Drop all tables and re-migrate
php artisan migrate:fresh

# Migrate and seed
php artisan migrate:fresh --seed

# Seed only
php artisan db:seed

# Seed specific class
php artisan db:seed --class=UserSeeder

# Rollback
php artisan migrate:rollback

# Reset everything
php artisan migrate:reset

# Check migration status
php artisan migrate:status

# List all seeders
php artisan db:seed --help
```

---

## ✨ FEATURES

### **Smart Defaults:**

- ✅ Realistic company structure (5 provinces, 7 branches)
- ✅ Mixed device types (cameras, Node AI, Mikrotik)
- ✅ Pre-configured event settings
- ✅ Ready-to-use CCTV layouts
- ✅ Admin and regular user accounts
- ✅ All relationships properly linked

### **Production Ready:**

- ✅ Encrypted device credentials
- ✅ Hashed user passwords
- ✅ Valid email formats
- ✅ Realistic phone numbers
- ✅ GPS coordinates for branches

---

**Seeder Version:** 1.0  
**Last Updated:** October 7, 2025  
**Total Seeders:** 6 files

_End of Seeder Guide_
