# 📊 CCTV Dashboard - Complete Database Plan (English)

## 🏗️ Database Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    CCTV Dashboard Database                      │
├─────────────────────────────────────────────────────────────────┤
│  Company Groups (Province) → Company Branches (City)           │
│                              ↓                                 │
│  Device Master (re_id) → Device Branch Detection              │
│                              ↓                                 │
│  Branch Event Settings → Event Logs                           │
│                              ↓                                 │
│  API Credentials → CCTV Streams            │
└─────────────────────────────────────────────────────────────────┘
```

## 📋 Complete Database Structure (14 Tables)

### **1. Core Hierarchy Tables (5 tables)**

#### **1.1 company_groups** (Province Level)

```sql
CREATE TABLE company_groups (
    id BIGSERIAL PRIMARY KEY,
    province_code VARCHAR(10) UNIQUE NOT NULL,
    province_name VARCHAR(100) NOT NULL,
    group_name VARCHAR(150) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PostgreSQL indexes
CREATE INDEX idx_company_groups_province_code ON company_groups(province_code);
CREATE INDEX idx_company_groups_status ON company_groups(status);

-- PostgreSQL trigger for updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_company_groups_updated_at BEFORE UPDATE ON company_groups
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Master data for company groups organized by province
**Key Fields:**

- `province_code`: Unique code (e.g., "JKT", "BDG", "SBY")
- `province_name`: Full province name
- `group_name`: Company group name
- `status`: Active/inactive status

#### **1.2 company_branches** (City Level)

```sql
CREATE TABLE company_branches (
    id BIGSERIAL PRIMARY KEY,
    group_id BIGINT NOT NULL,
    branch_code VARCHAR(10) UNIQUE NOT NULL,
    branch_name VARCHAR(150) NOT NULL,
    city_name VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_company_branches_group FOREIGN KEY (group_id)
        REFERENCES company_groups(id) ON DELETE CASCADE
);

-- PostgreSQL indexes
CREATE INDEX idx_company_branches_group_id ON company_branches(group_id);
CREATE INDEX idx_company_branches_branch_code ON company_branches(branch_code);
CREATE INDEX idx_company_branches_status ON company_branches(status);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_company_branches_updated_at BEFORE UPDATE ON company_branches
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Individual branch locations with GPS coordinates
**Key Fields:**

- `group_id`: Foreign key to company_groups
- `branch_code`: Unique code (e.g., "JKT001", "BDG001")
- `branch_name`: Full branch name
- `city_name`: City location
- `latitude`, `longitude`: GPS coordinates

#### **1.3 device_masterss** (Device Registry)

```sql
CREATE TABLE device_masterss (
    id BIGSERIAL PRIMARY KEY,
    device_id VARCHAR(50) UNIQUE NOT NULL,
    device_name VARCHAR(150),
    device_type VARCHAR(20) DEFAULT 'camera' CHECK (device_type IN ('camera', 'node_ai', 'mikrotik', 'cctv')),
    branch_id BIGINT NOT NULL,
    url VARCHAR(500),  -- ✅ Device URL/IP (e.g., rtsp://192.168.1.100:554/stream1)
    username VARCHAR(100),  -- ✅ Device username for authentication
    password VARCHAR(255),  -- ✅ Device password (encrypted)
    notes TEXT,  -- ✅ Additional notes/description
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_device_masters_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE
);

-- PostgreSQL indexes
CREATE INDEX idx_device_masterss_device_id ON device_masterss(device_id);
CREATE INDEX idx_device_masterss_device_type ON device_masterss(device_type);
CREATE INDEX idx_device_masterss_branch_id ON device_masterss(branch_id);
CREATE INDEX idx_device_masterss_status ON device_masterss(status);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_device_masterss_updated_at BEFORE UPDATE ON device_masterss
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Master registry of all devices with authentication credentials
**Key Fields:**

- `device_id`: Unique device identifier
- `device_name`: Device/sensor name
- `device_type`: **Camera, Node AI, Mikrotik, CCTV** (enhanced types)
- `branch_id`: Which branch this device belongs to
- `url`: **Device URL/IP address** (for connection)
- `username`: **Device authentication username**
- `password`: **Device authentication password** (should be encrypted)
- `notes`: **Additional notes/description** (configuration, location details, etc.)

#### **1.4 re_id_masterss** (Person Re-Identification Registry)

```sql
CREATE TABLE re_id_masterss (
    id BIGSERIAL PRIMARY KEY,
    re_id VARCHAR(100) NOT NULL,  -- ✅ Re-identification ID (e.g., "person_001_abc123", "RE_20240116_001")
    detection_date DATE NOT NULL,  -- ✅ Date of detection (for daily tracking)
    detection_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- ✅ Exact detection timestamp
    person_name VARCHAR(150),  -- Optional: if person is identified/registered
    appearance_features JSONB,  -- ✅ PostgreSQL JSONB for better performance
    first_detected_at TIMESTAMP,  -- When this person was first detected on this date
    last_detected_at TIMESTAMP,  -- When this person was last detected on this date
    total_detection_branch_count INTEGER DEFAULT 0,  -- ✅ Total number of branches that detected this person
    total_actual_count INTEGER DEFAULT 0,  -- Total actual detection count from all branches
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- ✅ Unique constraint: One record per person per day
    CONSTRAINT unique_re_id_date UNIQUE (re_id, detection_date)
);

-- PostgreSQL indexes
CREATE INDEX idx_re_id_masters_re_id ON re_id_masters(re_id);
CREATE INDEX idx_re_id_masters_detection_date ON re_id_masters(detection_date);
CREATE INDEX idx_re_id_masters_detection_time ON re_id_masters(detection_time);
CREATE INDEX idx_re_id_masters_first_detected ON re_id_masters(first_detected_at);
CREATE INDEX idx_re_id_masters_last_detected ON re_id_masters(last_detected_at);
CREATE INDEX idx_re_id_masters_status ON re_id_masters(status);

-- ✅ PostgreSQL composite index for daily queries
CREATE INDEX idx_re_id_masters_reid_date ON re_id_masters(re_id, detection_date);

-- ✅ PostgreSQL composite index for time-based queries
CREATE INDEX idx_re_id_masters_date_time ON re_id_masters(detection_date, detection_time);

-- ✅ PostgreSQL GIN index for JSONB queries
CREATE INDEX idx_re_id_masters_appearance_features ON re_id_masters USING GIN (appearance_features);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_re_id_masters_updated_at BEFORE UPDATE ON re_id_masters
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Daily registry of persons with Re-Identification ID (one record per person per day)
**Key Fields:**

- `re_id`: **Re-identification ID** (string identifier for person tracking)
- `detection_date`: **Date of detection** (for daily tracking)
- `detection_time`: **Exact detection timestamp** (first detection time of the day)
- `person_name`: Person name (if identified/registered)
- `appearance_features`: JSON with appearance data (colors, features, etc.)
- `total_detection_branch_count`: **Total number of branches that detected this person today**
- `total_actual_count`: Total actual detection count from all branches
- `status`: **Active/Inactive** (for tracking control, privacy, soft delete)

**Status Field Usage:**

- `active`: Person tracking enabled, appears in reports and dashboards
- `inactive`: Person tracking disabled, hidden from active tracking (soft delete, privacy opt-out, whitelist/blacklist)

#### **1.5 re_id_branch_detections** (Person Detection Logs)

```sql
CREATE TABLE re_id_branch_detections (
    id BIGSERIAL PRIMARY KEY,
    re_id VARCHAR(100) NOT NULL,  -- ✅ Re-identification ID reference
    branch_id BIGINT NOT NULL,
    device_id VARCHAR(50) NOT NULL,  -- Which device detected this person
    detected_count INTEGER NOT NULL DEFAULT 1,  -- Usually 1 per detection
    detection_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- ✅ When detection occurred
    detection_data JSONB,  -- ✅ PostgreSQL JSONB for better performance
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_re_id_branch_detections_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_re_id_branch_detections_re_id FOREIGN KEY (re_id)
        REFERENCES re_id_masters(re_id) ON DELETE CASCADE,
    CONSTRAINT fk_re_id_branch_detections_device FOREIGN KEY (device_id)
        REFERENCES device_masters(device_id) ON DELETE CASCADE
);

-- PostgreSQL indexes
CREATE INDEX idx_re_id_branch_detections_re_id ON re_id_branch_detections(re_id);
CREATE INDEX idx_re_id_branch_detections_branch_id ON re_id_branch_detections(branch_id);
CREATE INDEX idx_re_id_branch_detections_device_id ON re_id_branch_detections(device_id);
CREATE INDEX idx_re_id_branch_detections_timestamp ON re_id_branch_detections(detection_timestamp);

-- ✅ PostgreSQL composite indexes
CREATE INDEX idx_re_id_branch_detections_branch_date ON re_id_branch_detections(branch_id, detection_timestamp);
CREATE INDEX idx_re_id_branch_detections_reid_date ON re_id_branch_detections(re_id, detection_timestamp);

-- ✅ PostgreSQL GIN index for JSONB queries
CREATE INDEX idx_re_id_branch_detections_data ON re_id_branch_detections USING GIN (detection_data);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_re_id_branch_detections_updated_at BEFORE UPDATE ON re_id_branch_detections
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Detection logs per Re-ID (person) per branch per device - **Multiple records allowed per day**
**Key Fields:**

- `re_id`: **Re-identification ID reference** (person tracking ID)
- `branch_id`: Branch where detection occurred
- `device_id`: Device that detected the person
- `detected_count`: Usually 1 per detection
- `detection_timestamp`: **When detection occurred**
- `detection_data`: JSON with additional detection info (confidence, bounding box, etc.)

---

### **2. Event Management Tables (2 tables)**

#### **2.1 branch_event_settings** (Event Configuration)

```sql
CREATE TABLE branch_event_settings (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL,
    device_id VARCHAR(50) NOT NULL,  -- Device configuration
    is_active BOOLEAN DEFAULT true,
    send_image BOOLEAN DEFAULT true,
    send_message BOOLEAN DEFAULT true,
    send_notification BOOLEAN DEFAULT true,
    whatsapp_enabled BOOLEAN DEFAULT false,  -- ✅ Simple ON/OFF
    whatsapp_numbers JSONB,  -- ✅ PostgreSQL JSONB array: ["+628123456789"]
    message_template TEXT,
    notification_template TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_branch_event_settings_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_branch_event_settings_device FOREIGN KEY (device_id)
        REFERENCES device_masters(device_id) ON DELETE CASCADE,
    CONSTRAINT unique_branch_device UNIQUE (branch_id, device_id)
);

-- PostgreSQL indexes
CREATE INDEX idx_branch_event_settings_branch_id ON branch_event_settings(branch_id);
CREATE INDEX idx_branch_event_settings_device_id ON branch_event_settings(device_id);
CREATE INDEX idx_branch_event_settings_is_active ON branch_event_settings(is_active);

-- ✅ PostgreSQL GIN index for JSONB array
CREATE INDEX idx_branch_event_settings_whatsapp_numbers ON branch_event_settings USING GIN (whatsapp_numbers);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_branch_event_settings_updated_at BEFORE UPDATE ON branch_event_settings
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Event configuration per branch per device
**Key Fields:**

- `device_id`: **Device reference** (which device to monitor)
- `branch_id`: Branch configuration
- `whatsapp_enabled`: **Simple ON/OFF** (true/false)
- `whatsapp_numbers`: JSON array of phone numbers
- `message_template`: Custom message template

#### **2.2 event_logs** (Event Activity Log)

```sql
CREATE TABLE event_logs (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL,
    device_id VARCHAR(50) NOT NULL,  -- Device that triggered event
    re_id VARCHAR(100),  -- ✅ Re-identification ID (person detected, nullable)
    event_type VARCHAR(20) DEFAULT 'detection' CHECK (event_type IN ('detection', 'alert', 'motion', 'manual')),
    detected_count INTEGER DEFAULT 0,
    image_path VARCHAR(255),
    image_sent BOOLEAN DEFAULT false,
    message_sent BOOLEAN DEFAULT false,
    notification_sent BOOLEAN DEFAULT false,  -- ✅ Simple boolean
    event_data JSONB,  -- ✅ PostgreSQL JSONB
    event_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_event_logs_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_event_logs_device FOREIGN KEY (device_id)
        REFERENCES device_masters(device_id) ON DELETE CASCADE,
    CONSTRAINT fk_event_logs_re_id FOREIGN KEY (re_id)
        REFERENCES re_id_masters(re_id) ON DELETE SET NULL
);

-- PostgreSQL indexes
CREATE INDEX idx_event_logs_branch_id ON event_logs(branch_id);
CREATE INDEX idx_event_logs_device_id ON event_logs(device_id);
CREATE INDEX idx_event_logs_re_id ON event_logs(re_id);
CREATE INDEX idx_event_logs_event_type ON event_logs(event_type);
CREATE INDEX idx_event_logs_event_timestamp ON event_logs(event_timestamp);

-- ✅ PostgreSQL composite index
CREATE INDEX idx_event_logs_branch_event_timestamp ON event_logs(branch_id, event_type, event_timestamp);

-- ✅ PostgreSQL GIN index for JSONB
CREATE INDEX idx_event_logs_event_data ON event_logs USING GIN (event_data);

-- ✅ PostgreSQL partial index (only active events)
CREATE INDEX idx_event_logs_notification_sent ON event_logs(notification_sent) WHERE notification_sent = true;

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_event_logs_updated_at BEFORE UPDATE ON event_logs
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Complete event activity log
**Key Fields:**

- `device_id`: **Device reference** (which device triggered event)
- `re_id`: **Re-identification ID** (person detected, nullable)
- `branch_id`: Branch where event occurred
- `event_type`: Detection, alert, motion, manual
- `notification_sent`: **Simple boolean** (true/false)

---

---

### **3. API Security Tables (3 tables)**

#### **3.1 api_credentials** (API Key Management - Simplified Global Access)

```sql
CREATE TABLE api_credentials (
    id BIGSERIAL PRIMARY KEY,
    credential_name VARCHAR(150) NOT NULL,
    api_key VARCHAR(255) UNIQUE NOT NULL,
    api_secret VARCHAR(255) NOT NULL,
    branch_id BIGINT DEFAULT NULL,  -- Always NULL = global access
    device_id VARCHAR(50) DEFAULT NULL,  -- Always NULL = all devices
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'expired')),
    permissions JSONB DEFAULT '{"read": true, "write": true, "delete": true}',  -- Always full permissions
    rate_limit INTEGER DEFAULT 10000,  -- Default 10,000 requests/hour
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_api_credentials_created_by FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE SET NULL
);

-- PostgreSQL indexes
CREATE INDEX idx_api_credentials_api_key ON api_credentials(api_key);
CREATE INDEX idx_api_credentials_status ON api_credentials(status);
CREATE INDEX idx_api_credentials_expires_at ON api_credentials(expires_at);
CREATE INDEX idx_api_credentials_last_used_at ON api_credentials(last_used_at);
CREATE INDEX idx_api_credentials_created_by ON api_credentials(created_by);

-- ✅ PostgreSQL GIN index for JSONB permissions (for future flexibility)
CREATE INDEX idx_api_credentials_permissions ON api_credentials USING GIN (permissions);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_api_credentials_updated_at BEFORE UPDATE ON api_credentials
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Simplified API credential management with global access only
**Key Fields:**

- `credential_name`: Descriptive name (e.g., "Mobile App API Key")
- `api_key`: Auto-generated 40-character unique key
- `api_secret`: Auto-generated 40-character secure secret
- `branch_id`: Always `NULL` (global access to all branches)
- `device_id`: Always `NULL` (global access to all devices)
- `status`: `active`, `inactive`, or `expired`
- `permissions`: Always full permissions `{"read": true, "write": true, "delete": true}`
- `rate_limit`: Default 10,000 requests/hour
- `last_used_at`: Tracks last API usage
- `expires_at`: Optional expiration date

**Simplified Design:**

- ✅ All credentials have global scope (no branch/device restrictions)
- ✅ Full permissions by default (read, write, delete)
- ✅ High rate limit (10,000/hour) suitable for production
- ✅ Managed via web interface (`/api-credentials`) - admin only
- ✅ Middleware: `api.key` (registered in `bootstrap/app.php`)
- ✅ Security: Timing-safe secret comparison, request logging
- ✅ Performance: Credential caching (5 min), async last_used_at updates

#### **3.2 api_usage_summary** (API Usage Summary - Aggregated Data Only)

```sql
CREATE TABLE api_usage_summary (
    id BIGSERIAL PRIMARY KEY,
    api_credential_id BIGINT NOT NULL,
    summary_date DATE NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL CHECK (method IN ('GET', 'POST', 'PUT', 'DELETE', 'PATCH')),
    total_requests INTEGER DEFAULT 0,
    success_requests INTEGER DEFAULT 0,  -- Status 200-299
    error_requests INTEGER DEFAULT 0,    -- Status 400-599
    avg_response_time_ms INTEGER,
    max_response_time_ms INTEGER,
    min_response_time_ms INTEGER,
    avg_query_count INTEGER,
    max_query_count INTEGER,
    avg_memory_usage BIGINT,  -- in bytes
    max_memory_usage BIGINT,  -- in bytes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_api_usage_summary_credential FOREIGN KEY (api_credential_id)
        REFERENCES api_credentials(id) ON DELETE CASCADE,
    CONSTRAINT unique_summary UNIQUE (api_credential_id, summary_date, endpoint, method)
);

-- PostgreSQL indexes
CREATE INDEX idx_api_usage_summary_credential ON api_usage_summary(api_credential_id);
CREATE INDEX idx_api_usage_summary_date ON api_usage_summary(summary_date);
CREATE INDEX idx_api_usage_summary_endpoint ON api_usage_summary(endpoint);

-- ✅ PostgreSQL composite index
CREATE INDEX idx_api_usage_summary_credential_date ON api_usage_summary(api_credential_id, summary_date);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_api_usage_summary_updated_at BEFORE UPDATE ON api_usage_summary
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Daily aggregated API usage statistics (not raw logs)
**Key Fields:**

- `summary_date`: Daily aggregation
- `total_requests`: Total requests for the day
- `success_requests`: Count of successful requests (200-299)
- `error_requests`: Count of failed requests (400-599)
- `avg_response_time_ms`: Average response time
- `avg_query_count`: Average database queries per request
- `avg_memory_usage`: Average memory consumption

**Note:** Raw request/response logs stored in **daily log files**, not database tables

#### **3.3 users** (User Management - Existing Laravel)

```sql
-- Existing Laravel users table
-- Used for created_by foreign key in api_credentials
```

---

### **4. CCTV Streaming (1 table)**

#### **4.1 cctv_streams** (Stream Management)

```sql
CREATE TABLE cctv_streams (
    id BIGSERIAL PRIMARY KEY,
    branch_id BIGINT NOT NULL,
    device_id VARCHAR(50) NOT NULL,  -- ✅ Device reference
    stream_name VARCHAR(150) NOT NULL,
    stream_url VARCHAR(500) NOT NULL,
    stream_type VARCHAR(20) DEFAULT 'rtsp' CHECK (stream_type IN ('rtsp', 'rtmp', 'hls', 'http', 'websocket')),
    stream_username VARCHAR(100),
    stream_password VARCHAR(255),  -- Encrypted
    stream_port INTEGER,
    is_active BOOLEAN DEFAULT true,
    position INTEGER DEFAULT 1 CHECK (position BETWEEN 1 AND 4),  -- Position in 4-window grid (1-4)
    resolution VARCHAR(20),  -- "1920x1080"
    fps INTEGER DEFAULT 30,
    bitrate INTEGER,  -- in kbps
    last_checked_at TIMESTAMP NULL,
    status VARCHAR(20) DEFAULT 'offline' CHECK (status IN ('online', 'offline', 'error')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cctv_streams_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_cctv_streams_device FOREIGN KEY (device_id)
        REFERENCES device_masters(device_id) ON DELETE CASCADE
);

-- PostgreSQL indexes
CREATE INDEX idx_cctv_streams_branch_id ON cctv_streams(branch_id);
CREATE INDEX idx_cctv_streams_device_id ON cctv_streams(device_id);
CREATE INDEX idx_cctv_streams_is_active ON cctv_streams(is_active);
CREATE INDEX idx_cctv_streams_position ON cctv_streams(position);
CREATE INDEX idx_cctv_streams_status ON cctv_streams(status);

-- ✅ PostgreSQL composite index for grid queries
CREATE INDEX idx_cctv_streams_branch_position ON cctv_streams(branch_id, position);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_cctv_streams_updated_at BEFORE UPDATE ON cctv_streams
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** CCTV stream configuration with device reference
**Key Fields:**

- `device_id`: **Device reference** (which device stream)
- `branch_id`: Branch location
- `position`: 4-window grid position (1-4)
- `stream_url`: Full stream URL
- `status`: Online/offline/error

---

### **5. Reporting (1 table)**

#### **5.1 counting_reports** (Report Cache)

```sql
CREATE TABLE counting_reports (
    id BIGSERIAL PRIMARY KEY,
    report_type VARCHAR(20) NOT NULL CHECK (report_type IN ('daily', 'weekly', 'monthly', 'yearly')),
    report_date DATE NOT NULL,
    branch_id BIGINT,  -- NULL = global report
    total_devices INTEGER DEFAULT 0,
    total_detections INTEGER DEFAULT 0,
    total_events INTEGER DEFAULT 0,
    unique_device_count INTEGER DEFAULT 0,
    unique_person_count INTEGER DEFAULT 0,  -- ✅ Unique Re-ID count
    report_data JSONB,  -- ✅ PostgreSQL JSONB for detailed statistics
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_counting_reports_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT unique_report UNIQUE (report_type, report_date, branch_id)
);

-- PostgreSQL indexes
CREATE INDEX idx_counting_reports_report_type ON counting_reports(report_type);
CREATE INDEX idx_counting_reports_report_date ON counting_reports(report_date);
CREATE INDEX idx_counting_reports_branch_id ON counting_reports(branch_id);

-- ✅ PostgreSQL composite index
CREATE INDEX idx_counting_reports_type_date ON counting_reports(report_type, report_date);

-- ✅ PostgreSQL GIN index for JSONB
CREATE INDEX idx_counting_reports_data ON counting_reports USING GIN (report_data);
```

**Purpose:** Pre-computed report cache
**Key Fields:**

- `report_type`: Daily, weekly, monthly, yearly
- `branch_id`: Branch-specific or global (NULL)
- `report_data`: JSON with detailed breakdown

#### **6.1 whatsapp_delivery_summary** (WhatsApp Daily Summary - Aggregated Data Only)

```sql
CREATE TABLE whatsapp_delivery_summary (
    id BIGSERIAL PRIMARY KEY,
    summary_date DATE NOT NULL,
    branch_id BIGINT NOT NULL,
    device_id VARCHAR(50) NOT NULL,
    total_sent INTEGER DEFAULT 0,
    total_delivered INTEGER DEFAULT 0,
    total_failed INTEGER DEFAULT 0,
    total_pending INTEGER DEFAULT 0,
    avg_delivery_time_ms INTEGER,  -- Average time to send
    unique_recipients INTEGER DEFAULT 0,  -- Unique phone numbers
    messages_with_image INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_whatsapp_summary_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_whatsapp_summary_device FOREIGN KEY (device_id)
        REFERENCES device_masters(device_id) ON DELETE CASCADE,
    CONSTRAINT unique_whatsapp_summary UNIQUE (summary_date, branch_id, device_id)
);

-- PostgreSQL indexes
CREATE INDEX idx_whatsapp_summary_date ON whatsapp_delivery_summary(summary_date);
CREATE INDEX idx_whatsapp_summary_branch ON whatsapp_delivery_summary(branch_id);
CREATE INDEX idx_whatsapp_summary_device ON whatsapp_delivery_summary(device_id);

-- ✅ PostgreSQL composite index
CREATE INDEX idx_whatsapp_summary_branch_date ON whatsapp_delivery_summary(branch_id, summary_date);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_whatsapp_delivery_summary_updated_at
BEFORE UPDATE ON whatsapp_delivery_summary
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Daily aggregated WhatsApp delivery statistics (not raw message logs)
**Key Fields:**

- `summary_date`: Daily aggregation
- `branch_id`: Branch that sent messages
- `device_id`: Device that triggered messages
- `total_sent`: Total messages sent
- `total_delivered`: Successfully delivered messages
- `total_failed`: Failed deliveries
- `unique_recipients`: Count of unique phone numbers

**Note:** Raw WhatsApp message logs stored in **daily log files** (`storage/logs/whatsapp/YYYY-MM-DD.log`), not database table

#### **6.2 storage_files** (File Storage Registry)

```sql
CREATE TABLE storage_files (
    id BIGSERIAL PRIMARY KEY,
    file_path VARCHAR(500) UNIQUE NOT NULL,  -- Full file path (e.g., 'events/2024/01/16/event_001.jpg')
    file_name VARCHAR(255) NOT NULL,  -- Original file name
    file_type VARCHAR(50),  -- MIME type (e.g., 'image/jpeg', 'image/png')
    file_size BIGINT,  -- File size in bytes
    storage_disk VARCHAR(50) DEFAULT 'local',  -- Storage disk (local, s3, public)
    related_table VARCHAR(100),  -- Related table name (e.g., 'event_logs', 'whatsapp_message_logs')
    related_id BIGINT,  -- Related record ID
    uploaded_by BIGINT,  -- User who uploaded (nullable)
    is_public BOOLEAN DEFAULT false,
    metadata JSONB,  -- Additional metadata (dimensions, duration, etc.)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_storage_files_uploaded_by FOREIGN KEY (uploaded_by)
        REFERENCES users(id) ON DELETE SET NULL
);

-- PostgreSQL indexes
CREATE INDEX idx_storage_files_path ON storage_files(file_path);
CREATE INDEX idx_storage_files_type ON storage_files(file_type);
CREATE INDEX idx_storage_files_disk ON storage_files(storage_disk);
CREATE INDEX idx_storage_files_related ON storage_files(related_table, related_id);
CREATE INDEX idx_storage_files_uploaded_by ON storage_files(uploaded_by);
CREATE INDEX idx_storage_files_created_at ON storage_files(created_at);

-- ✅ PostgreSQL composite index for file queries
CREATE INDEX idx_storage_files_disk_path ON storage_files(storage_disk, file_path);

-- ✅ PostgreSQL GIN index for JSONB metadata
CREATE INDEX idx_storage_files_metadata ON storage_files USING GIN (metadata);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_storage_files_updated_at
BEFORE UPDATE ON storage_files
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Centralized file storage registry and tracking
**Key Fields:**

- `file_path`: Unique file path in storage
- `file_name`: Original filename
- `file_type`: MIME type for validation
- `file_size`: Size in bytes for quota management
- `storage_disk`: Where file is stored (local, s3, public)
- `related_table`: Link to source table (event_logs, whatsapp_message_logs)
- `related_id`: ID in related table
- `metadata`: JSON for image dimensions, video duration, etc.

---

### **7. CCTV Layout Management (2 tables)**

#### **7.1 cctv_layout_settings** (Layout Configuration)

```sql
CREATE TABLE cctv_layout_settings (
    id BIGSERIAL PRIMARY KEY,
    layout_name VARCHAR(150) NOT NULL,
    layout_type VARCHAR(20) NOT NULL CHECK (layout_type IN ('4-window', '6-window', '8-window')),
    total_positions INTEGER NOT NULL CHECK (total_positions IN (4, 6, 8)),
    is_default BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    description TEXT,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cctv_layout_settings_created_by FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT unique_default_layout UNIQUE (is_default) DEFERRABLE INITIALLY DEFERRED
);

-- PostgreSQL indexes
CREATE INDEX idx_cctv_layout_settings_layout_type ON cctv_layout_settings(layout_type);
CREATE INDEX idx_cctv_layout_settings_is_default ON cctv_layout_settings(is_default);
CREATE INDEX idx_cctv_layout_settings_is_active ON cctv_layout_settings(is_active);
CREATE INDEX idx_cctv_layout_settings_created_by ON cctv_layout_settings(created_by);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_cctv_layout_settings_updated_at BEFORE UPDATE ON cctv_layout_settings
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Master layout configuration for CCTV grid views
**Key Fields:**

- `layout_type`: 4-window, 6-window, 8-window
- `total_positions`: Number of positions (4, 6, or 8)
- `is_default`: Only one layout can be default
- `created_by`: Admin user who created the layout

#### **7.2 cctv_position_settings** (Position Configuration)

```sql
CREATE TABLE cctv_position_settings (
    id BIGSERIAL PRIMARY KEY,
    layout_id BIGINT NOT NULL,
    position_number INTEGER NOT NULL CHECK (position_number BETWEEN 1 AND 8),
    branch_id BIGINT NOT NULL,
    device_id VARCHAR(50) NOT NULL,
    position_name VARCHAR(150) NOT NULL,
    is_enabled BOOLEAN DEFAULT true,
    auto_switch BOOLEAN DEFAULT false,
    switch_interval INTEGER DEFAULT 30 CHECK (switch_interval BETWEEN 10 AND 300), -- seconds
    resolution VARCHAR(20) DEFAULT '1920x1080',
    quality VARCHAR(20) DEFAULT 'high' CHECK (quality IN ('low', 'medium', 'high')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cctv_position_settings_layout FOREIGN KEY (layout_id)
        REFERENCES cctv_layout_settings(id) ON DELETE CASCADE,
    CONSTRAINT fk_cctv_position_settings_branch FOREIGN KEY (branch_id)
        REFERENCES company_branches(id) ON DELETE CASCADE,
    CONSTRAINT fk_cctv_position_settings_device FOREIGN KEY (device_id)
        REFERENCES device_masters(device_id) ON DELETE CASCADE,
    CONSTRAINT unique_layout_position UNIQUE (layout_id, position_number)
);

-- PostgreSQL indexes
CREATE INDEX idx_cctv_position_settings_layout_id ON cctv_position_settings(layout_id);
CREATE INDEX idx_cctv_position_settings_position_number ON cctv_position_settings(position_number);
CREATE INDEX idx_cctv_position_settings_branch_id ON cctv_position_settings(branch_id);
CREATE INDEX idx_cctv_position_settings_device_id ON cctv_position_settings(device_id);
CREATE INDEX idx_cctv_position_settings_is_enabled ON cctv_position_settings(is_enabled);

-- ✅ PostgreSQL composite index for layout queries
CREATE INDEX idx_cctv_position_settings_layout_position ON cctv_position_settings(layout_id, position_number);

-- PostgreSQL trigger for updated_at
CREATE TRIGGER update_cctv_position_settings_updated_at BEFORE UPDATE ON cctv_position_settings
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

**Purpose:** Individual position configuration within each layout
**Key Fields:**

- `layout_id`: Reference to layout configuration
- `position_number`: Position in grid (1-8)
- `branch_id`: Which branch this position monitors
- `device_id`: Which device this position shows
- `auto_switch`: Enable automatic device switching
- `switch_interval`: How often to switch devices (seconds)
- `quality`: Stream quality setting

---

## 🎛️ CCTV Layout Management System

### **Layout Types Supported:**

| Layout Type  | Positions | Grid Layout | Description        |
| ------------ | --------- | ----------- | ------------------ |
| **4-window** | 4         | 2x2         | Standard quad view |
| **6-window** | 6         | 2x3         | Extended view      |
| **8-window** | 8         | 2x4         | Maximum view       |

### **Position Configuration Example:**

```sql
-- Sample data for 4-window layout
INSERT INTO cctv_layout_settings VALUES
(1, 'Default 4-Window Layout', '4-window', 4, true, true, 'Standard quad view layout', 1, NOW(), NOW());

-- Position configurations for 4-window layout
INSERT INTO cctv_position_settings VALUES
-- Position 1: Jakarta Central - Main Entrance
(1, 1, 1, 1, 'CAMERA_001', 'Main Entrance', true, false, 30, '1920x1080', 'high', NOW(), NOW()),

-- Position 2: Jakarta Central - Parking Area
(2, 1, 2, 1, 'CAMERA_002', 'Parking Area', true, false, 30, '1920x1080', 'high', NOW(), NOW()),

-- Position 3: Jakarta South - Lobby
(3, 1, 3, 2, 'CAMERA_003', 'Lobby View', true, true, 60, '1280x720', 'medium', NOW(), NOW()),

-- Position 4: Bandung - Entry Sensor
(4, 1, 4, 3, 'SENSOR_001', 'Entry Sensor', true, false, 30, '640x480', 'low', NOW(), NOW());
```

### **API Endpoints for Layout Management:**

#### **GET /api/admin/cctv/layouts**

Get all available layouts

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "layout_name": "Default 4-Window Layout",
      "layout_type": "4-window",
      "total_positions": 4,
      "is_default": true,
      "is_active": true,
      "positions": [
        {
          "position_number": 1,
          "branch_name": "Jakarta Central Branch",
          "device_name": "Main Entrance Camera",
          "device_id": "CAMERA_001",
          "position_name": "Main Entrance",
          "is_enabled": true,
          "auto_switch": false,
          "quality": "high"
        }
      ]
    }
  ]
}
```

#### **POST /api/admin/cctv/layouts**

Create new layout configuration

**Payload:**

```json
{
  "layout_name": "Custom 6-Window Layout",
  "layout_type": "6-window",
  "description": "Extended view for monitoring",
  "positions": [
    {
      "position_number": 1,
      "branch_id": 1,
      "device_id": "CAMERA_001",
      "position_name": "Main Entrance",
      "is_enabled": true,
      "auto_switch": false,
      "quality": "high"
    },
    {
      "position_number": 2,
      "branch_id": 1,
      "device_id": "CAMERA_002",
      "position_name": "Parking Area",
      "is_enabled": true,
      "auto_switch": true,
      "switch_interval": 30,
      "quality": "high"
    }
  ]
}
```

#### **PUT /api/admin/cctv/layouts/{layout_id}/positions/{position_number}**

Update specific position configuration

**Payload:**

```json
{
  "branch_id": 2,
  "device_id": "CAMERA_003",
  "position_name": "Updated Lobby View",
  "is_enabled": true,
  "auto_switch": true,
  "switch_interval": 45,
  "quality": "medium"
}
```

#### **POST /api/admin/cctv/layouts/{layout_id}/set-default**

Set layout as default

**Response:**

```json
{
  "success": true,
  "message": "Layout set as default successfully",
  "data": {
    "layout_id": 1,
    "layout_name": "Default 4-Window Layout",
    "is_default": true
  }
}
```

### **Frontend Integration Example:**

```javascript
// Get current layout configuration
async function loadCCTVLayout() {
  const response = await fetch("/api/admin/cctv/layouts/default");
  const layout = await response.json();

  // Render grid based on layout type
  renderCCTVGrid(layout.data);
}

function renderCCTVGrid(layout) {
  const container = document.getElementById("cctv-grid");

  // Clear existing grid
  container.innerHTML = "";

  // Set grid CSS class based on layout type
  container.className = `cctv-grid ${layout.layout_type}`;

  // Render each position
  layout.positions.forEach((position) => {
    const positionElement = createPositionElement(position);
    container.appendChild(positionElement);
  });
}

function createPositionElement(position) {
  const div = document.createElement("div");
  div.className = "cctv-position";
  div.innerHTML = `
    <div class="position-header">
      <h4>Position ${position.position_number}</h4>
      <span class="status ${position.is_enabled ? "online" : "offline"}">
        ${position.is_enabled ? "●" : "○"}
      </span>
    </div>
    <div class="position-config">
      <select class="branch-select" data-position="${position.position_number}">
        <option value="${position.branch_id}">${position.branch_name}</option>
      </select>
      <select class="device-select" data-position="${position.position_number}">
        <option value="${position.device_id}">${position.device_name}</option>
      </select>
    </div>
    <div class="position-stream">
      <video autoplay muted>
        <source src="/api/stream/${position.device_id}" type="video/mp4">
      </video>
    </div>
  `;

  return div;
}
```

### **CSS Grid Layouts:**

```css
/* 4-Window Grid (2x2) */
.cctv-grid.4-window {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  gap: 10px;
  height: 100vh;
}

/* 6-Window Grid (2x3) */
.cctv-grid.6-window {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  gap: 10px;
  height: 100vh;
}

/* 8-Window Grid (2x4) */
.cctv-grid.8-window {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  gap: 10px;
  height: 100vh;
}

.cctv-position {
  border: 2px solid #333;
  border-radius: 8px;
  background: #1a1a1a;
  color: white;
  padding: 10px;
  display: flex;
  flex-direction: column;
}

.position-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.status.online {
  color: #4ade80;
}
.status.offline {
  color: #ef4444;
}

.position-config {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
}

.position-config select {
  background: #333;
  color: white;
  border: 1px solid #555;
  padding: 5px;
  border-radius: 4px;
}

.position-stream {
  flex: 1;
  background: #000;
  border-radius: 4px;
  overflow: hidden;
}

.position-stream video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
```

---

## 🔄 Enhanced Counting Logic with RE_ID

### **Daily Tracking Model:**

- ✅ **One record per person per day** in `re_id_masters`
- ✅ Unique constraint: `(re_id, detection_date)`
- ✅ Each day = new record for the same person

### **Scenario Example (2024-01-16):**

**Person:** `person_001_abc123` (John Doe)

- **Branch A detects**: 10 times → Contributes **1** to branch count
- **Branch B detects**: 5 times → Contributes **1** to branch count
- **Total Detection Branch Count**: **2** (Branch A + Branch B)
- **Total Actual Count**: **15** (10 + 5)

**Rule:** Each branch that detects a person = **1 count**, regardless of how many times detected

### **Rule 1: Daily Record Creation**

- ✅ **One record per day** in `re_id_masters` for each person
- ✅ `detection_date` = today's date
- ✅ New day = new record (same `re_id`, different `detection_date`)
- ✅ Multiple detection logs in `re_id_branch_detections` per day

### **Rule 2: Branch Detection Logic**

- Each branch that detects a Re-ID (person) is counted as **1** in `total_detection_branch_count`
- Regardless of actual detected count (10 or 5 times)
- Focus on **"how many branches detected this person"** not **"how many times detected"**
- Multiple detection records allowed per day with different timestamps in `re_id_branch_detections`

### **Rule 3: Status Field Usage**

**`status` field controls tracking behavior:**

- **`active`**: Person tracking enabled

  - ✅ Detection logs are processed
  - ✅ Appears in dashboard and reports
  - ✅ Events are triggered
  - ✅ Notifications are sent

- **`inactive`**: Person tracking disabled
  - ❌ Detection logs are skipped (or marked as inactive)
  - ❌ Hidden from active tracking dashboard
  - ✅ Historical data preserved for audit
  - ✅ Can be reactivated anytime

**Use Cases:**

1. **Soft Delete**: Deactivate person without losing history
2. **Privacy Compliance**: User opt-out from tracking
3. **Whitelist/Blacklist**: Control which persons to track
4. **Temporary Disable**: Pause tracking for specific period

### **Rule 4: API Counting Logic**

```php
// Enhanced API counting logic with Re-ID (Person Re-identification)
function countReIdByBranch($re_id, $branch_id, $device_id, $detected_count = 1, $detection_data = null) {
    $today = now()->toDateString();

    // 1. Ensure Re-ID (person) exists in master table for today
    $reIdMaster = ReIdMaster::firstOrCreate(
        [
            're_id' => $re_id,
            'detection_date' => $today
        ],
        [
            'detection_time' => now(),
            'first_detected_at' => now(),
            'last_detected_at' => now(),
            'total_actual_count' => 0,
            'total_detection_branch_count' => 0,
            'status' => 'active'
        ]
    );

    // 1.1. Check if person tracking is active
    if ($reIdMaster->status !== 'active') {
        return [
            'success' => false,
            'message' => 'Person tracking is disabled',
            're_id' => $re_id,
            'status' => 'inactive'
        ];
    }

    // 2. Update master tracking data for today
    $reIdMaster->increment('total_actual_count', $detected_count);
    $reIdMaster->update(['last_detected_at' => now()]);

    // 3. Create new detection record (multiple records allowed per day)
    $detection = ReIdBranchDetection::create([
        're_id' => $re_id,
        'branch_id' => $branch_id,
        'device_id' => $device_id,
        'detected_count' => $detected_count,
        'detection_timestamp' => now(),
        'detection_data' => $detection_data  // JSON with confidence, bounding box, etc.
    ]);

    // 4. Count unique branches that detected this person today (after new detection)
    $uniqueBranchCount = ReIdBranchDetection::where('re_id', $re_id)
        ->whereDate('detection_timestamp', $today)
        ->distinct('branch_id')
        ->count('branch_id');

    $reIdMaster->update(['total_detection_branch_count' => $uniqueBranchCount]);

    // 5. Return counting summary for this branch
    return [
        'success' => true,
        're_id' => $re_id,
        'detection_date' => $today,
        'detection_time' => $reIdMaster->detection_time,
        'branch_id' => $branch_id,
        'device_id' => $device_id,
        'branch_count' => 1, // Always 1 for this branch
        'detected_count' => $detected_count, // Usually 1 per detection
        'total_actual_count' => $reIdMaster->total_actual_count,
        'total_detection_branch_count' => $reIdMaster->total_detection_branch_count,
        'first_detected_at' => $reIdMaster->first_detected_at,
        'last_detected_at' => $reIdMaster->last_detected_at,
        'status' => $reIdMaster->status
    ];
}
```

---

## 📡 API Response Standards

### **Base Response Structure**

All API responses follow this standardized structure:

#### **Success Response Format:**

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Actual response data
  },
  "meta": {
    "timestamp": "2024-01-16T14:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 5,
    "memory_usage": "2.5 MB",
    "execution_time": "0.125s"
  }
}
```

#### **Error Response Format:**

```json
{
  "success": false,
  "message": "Error message here",
  "error": {
    "code": "ERROR_CODE",
    "details": "Detailed error information",
    "field": "field_name" // For validation errors
  },
  "meta": {
    "timestamp": "2024-01-16T14:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 3,
    "memory_usage": "1.8 MB",
    "execution_time": "0.085s"
  }
}
```

#### **Paginated Response Format:**

```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [
    // Array of items
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  },
  "meta": {
    "timestamp": "2024-01-16T14:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 8,
    "memory_usage": "3.2 MB",
    "execution_time": "0.245s"
  }
}
```

---

### **API Response Helper**

```php
// app/Helpers/ApiResponseHelper.php
namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ApiResponseHelper
{
    /**
     * Success response
     */
    public static function success(
        $data = null,
        string $message = 'Operation completed successfully',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => self::getMeta()
        ], $statusCode);
    }

    /**
     * Error response
     */
    public static function error(
        string $message = 'An error occurred',
        string $errorCode = 'GENERAL_ERROR',
        $details = null,
        int $statusCode = 400,
        ?string $field = null
    ): JsonResponse {
        $error = [
            'code' => $errorCode,
            'details' => $details
        ];

        if ($field) {
            $error['field'] = $field;
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $error,
            'meta' => self::getMeta()
        ], $statusCode);
    }

    /**
     * Validation error response
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'details' => $errors
            ],
            'meta' => self::getMeta()
        ], 422);
    }

    /**
     * Paginated response
     */
    public static function paginated(
        $items,
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $items->items(),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem()
            ],
            'meta' => self::getMeta()
        ], 200);
    }

    /**
     * Not found response
     */
    public static function notFound(
        string $message = 'Resource not found',
        ?string $resource = null
    ): JsonResponse {
        return self::error(
            $message,
            'NOT_FOUND',
            $resource ? "Resource '{$resource}' not found" : null,
            404
        );
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(
        string $message = 'Unauthorized access'
    ): JsonResponse {
        return self::error(
            $message,
            'UNAUTHORIZED',
            'Valid authentication credentials required',
            401
        );
    }

    /**
     * Forbidden response
     */
    public static function forbidden(
        string $message = 'Access forbidden'
    ): JsonResponse {
        return self::error(
            $message,
            'FORBIDDEN',
            'You do not have permission to access this resource',
            403
        );
    }

    /**
     * Rate limit exceeded response
     */
    public static function rateLimitExceeded(
        int $retryAfter = 3600
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => 'Rate limit exceeded',
            'error' => [
                'code' => 'RATE_LIMIT_EXCEEDED',
                'details' => 'Too many requests',
                'retry_after' => $retryAfter
            ],
            'meta' => self::getMeta()
        ], 429);
    }

    /**
     * Server error response
     */
    public static function serverError(
        string $message = 'Internal server error',
        ?\Throwable $exception = null
    ): JsonResponse {
        $details = null;

        if ($exception && config('app.debug')) {
            $details = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => 'SERVER_ERROR',
                'details' => $details
            ],
            'meta' => self::getMeta()
        ], 500);
    }

    /**
     * Get meta information
     */
    private static function getMeta(): array
    {
        // Calculate performance metrics
        $queryCount = \DB::getQueryLog() ? count(\DB::getQueryLog()) : 0;
        $memoryUsage = round(memory_get_usage() / 1024 / 1024, 2) . ' MB';
        $executionTime = round(microtime(true) - LARAVEL_START, 3) . 's';

        return [
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0'),
            'request_id' => Str::uuid()->toString(),
            'query_count' => $queryCount,
            'memory_usage' => $memoryUsage,
            'execution_time' => $executionTime
        ];
    }
}
```

---

### **HTTP Status Codes Reference**

| Code    | Name                  | Usage                                      |
| ------- | --------------------- | ------------------------------------------ |
| **200** | OK                    | Successful GET, PUT, PATCH requests        |
| **201** | Created               | Successful POST request (resource created) |
| **204** | No Content            | Successful DELETE request                  |
| **400** | Bad Request           | Invalid request format                     |
| **401** | Unauthorized          | Authentication required                    |
| **403** | Forbidden             | Insufficient permissions                   |
| **404** | Not Found             | Resource not found                         |
| **422** | Unprocessable Entity  | Validation failed                          |
| **429** | Too Many Requests     | Rate limit exceeded                        |
| **500** | Internal Server Error | Server error                               |
| **503** | Service Unavailable   | Service temporarily unavailable            |

---

### **Error Codes Reference**

| Code                   | HTTP Status | Description                 |
| ---------------------- | ----------- | --------------------------- |
| `VALIDATION_ERROR`     | 422         | Input validation failed     |
| `NOT_FOUND`            | 404         | Resource not found          |
| `UNAUTHORIZED`         | 401         | Authentication required     |
| `FORBIDDEN`            | 403         | Insufficient permissions    |
| `RATE_LIMIT_EXCEEDED`  | 429         | Too many requests           |
| `SERVER_ERROR`         | 500         | Internal server error       |
| `DUPLICATE_ENTRY`      | 400         | Duplicate record            |
| `INVALID_CREDENTIALS`  | 401         | Invalid API key/secret      |
| `EXPIRED_CREDENTIALS`  | 401         | API credentials expired     |
| `RESOURCE_CONFLICT`    | 409         | Resource conflict           |
| `DEVICE_OFFLINE`       | 503         | Device not responding       |
| `WHATSAPP_SEND_FAILED` | 503         | WhatsApp delivery failed    |
| `FILE_UPLOAD_FAILED`   | 400         | File upload error           |
| `ENCRYPTION_FAILED`    | 500         | Encryption/decryption error |

---

### **Enable Query Logging**

Add to `AppServiceProvider` or middleware to enable query counting:

```php
// app/Providers/AppServiceProvider.php
public function boot()
{
    // Enable query logging for production monitoring
    if (config('app.debug') || config('app.log_queries', false)) {
        \DB::enableQueryLog();
    }
}

// OR add to config/database.php
'log_queries' => env('DB_LOG_QUERIES', false),

// .env
DB_LOG_QUERIES=true  // Enable in development/staging
```

### **Usage Examples**

```php
use App\Helpers\ApiResponseHelper;

// ✅ SUCCESS (200)
return ApiResponseHelper::success(
    ['user' => $user],
    'User retrieved successfully'
);
// Response includes: query_count: 2, memory_usage: "1.5 MB", execution_time: "0.045s"

// ✅ CREATED (201)
return ApiResponseHelper::success(
    ['device' => $device],
    'Device created successfully',
    201
);
// Response includes: query_count: 3, memory_usage: "1.8 MB", execution_time: "0.067s"

// ✅ ERROR (400)
return ApiResponseHelper::error(
    'Invalid device ID format',
    'VALIDATION_ERROR',
    'Device ID must be alphanumeric',
    400,
    'device_id'
);
// Response includes: query_count: 1, memory_usage: "1.2 MB", execution_time: "0.012s"

// ✅ NOT FOUND (404)
return ApiResponseHelper::notFound(
    'Device not found',
    'device_masters'
);

// ✅ UNAUTHORIZED (401)
return ApiResponseHelper::unauthorized('Invalid API key');

// ✅ FORBIDDEN (403)
return ApiResponseHelper::forbidden('Insufficient permissions');

// ✅ VALIDATION ERROR (422)
return ApiResponseHelper::validationError(
    $validator->errors()->toArray(),
    'Input validation failed'
);

// ✅ PAGINATED (200)
$devices = DeviceMaster::paginate(15);
return ApiResponseHelper::paginated($devices, 'Devices retrieved');
// Response includes: query_count: 8, memory_usage: "3.5 MB", execution_time: "0.234s"

// ✅ RATE LIMIT (429)
return ApiResponseHelper::rateLimitExceeded(3600);

// ✅ SERVER ERROR (500)
try {
    // ... operation
} catch (\Exception $e) {
    return ApiResponseHelper::serverError('Failed to process', $e);
}
```

---

### **Controller Implementation Example**

```php
// app/Http/Controllers/Api/DetectionController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponseHelper;
use App\Http\Requests\StoreDetectionRequest;
use Illuminate\Http\JsonResponse;

class DetectionController extends Controller
{
    /**
     * Log detection event
     */
    public function store(StoreDetectionRequest $request): JsonResponse
    {
        try {
            // Validate already done via FormRequest
            $validated = $request->validated();

            // Process detection
            $result = $this->detectionService->logDetection(
                $validated['re_id'],
                $validated['branch_id'],
                $validated['device_id'],
                $validated['detected_count'],
                $validated['detection_data'] ?? null
            );

            // Check if tracking is active
            if (!$result['success']) {
                return ApiResponseHelper::error(
                    $result['message'],
                    'TRACKING_DISABLED',
                    'Person tracking is disabled for this re_id',
                    403
                );
            }

            // Return success
            return ApiResponseHelper::success(
                $result,
                'Detection logged successfully',
                201
            );

        } catch (\Exception $e) {
            return ApiResponseHelper::serverError(
                'Failed to log detection',
                $e
            );
        }
    }

    /**
     * Get person info
     */
    public function show(string $reId): JsonResponse
    {
        try {
            $person = ReIdMaster::where('re_id', $reId)
                ->where('detection_date', now()->toDateString())
                ->first();

            if (!$person) {
                return ApiResponseHelper::notFound(
                    'Person not found for today',
                    're_id_masters'
                );
            }

            return ApiResponseHelper::success(
                $person,
                'Person retrieved successfully'
            );

        } catch (\Exception $e) {
            return ApiResponseHelper::serverError(
                'Failed to retrieve person',
                $e
            );
        }
    }

    /**
     * Get detections (paginated)
     */
    public function index(): JsonResponse
    {
        try {
            $detections = ReIdBranchDetection::with(['branch', 'device', 'reId'])
                ->whereDate('detection_timestamp', now()->toDateString())
                ->paginate(request('per_page', 15));

            return ApiResponseHelper::paginated(
                $detections,
                'Detections retrieved successfully'
            );

        } catch (\Exception $e) {
            return ApiResponseHelper::serverError(
                'Failed to retrieve detections',
                $e
            );
        }
    }
}
```

---

### **Exception Handler (Global Error Handling)**

```php
// app/Exceptions/Handler.php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use App\Helpers\ApiResponseHelper;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // API requests only
        if ($request->is('api/*')) {

            // Validation Exception (422)
            if ($e instanceof ValidationException) {
                return ApiResponseHelper::validationError(
                    $e->errors(),
                    'Validation failed'
                );
            }

            // Authentication Exception (401)
            if ($e instanceof AuthenticationException) {
                return ApiResponseHelper::unauthorized(
                    'Authentication required'
                );
            }

            // Authorization Exception (403)
            if ($e instanceof AuthorizationException) {
                return ApiResponseHelper::forbidden(
                    $e->getMessage()
                );
            }

            // Model Not Found (404)
            if ($e instanceof ModelNotFoundException) {
                return ApiResponseHelper::notFound(
                    'Resource not found',
                    class_basename($e->getModel())
                );
            }

            // Not Found Exception (404)
            if ($e instanceof NotFoundHttpException) {
                return ApiResponseHelper::notFound(
                    'Endpoint not found'
                );
            }

            // Method Not Allowed (405)
            if ($e instanceof MethodNotAllowedHttpException) {
                return ApiResponseHelper::error(
                    'Method not allowed',
                    'METHOD_NOT_ALLOWED',
                    'The requested HTTP method is not supported for this endpoint',
                    405
                );
            }

            // Default Server Error (500)
            return ApiResponseHelper::serverError(
                'Internal server error',
                $e
            );
        }

        return parent::render($request, $e);
    }
}
```

---

### **Middleware for API Response**

```php
// app/Http/Middleware/ApiResponseMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiResponseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Enable query logging for this request
        if (config('app.log_queries', false)) {
            \DB::enableQueryLog();
        }

        $response = $next($request);

        // Add standard headers for API responses
        if ($request->is('api/*')) {
            $response->headers->set('X-API-Version', config('app.version', '1.0'));
            $response->headers->set('X-Request-ID', $request->header('X-Request-ID', \Str::uuid()));
            $response->headers->set('X-RateLimit-Limit', '1000');
            $response->headers->set('X-RateLimit-Remaining', '999');

            // Add performance metrics to headers
            $queryLog = \DB::getQueryLog();
            $response->headers->set('X-Query-Count', count($queryLog));
            $response->headers->set('X-Memory-Usage', round(memory_get_usage() / 1024 / 1024, 2) . 'MB');
            $response->headers->set('X-Execution-Time', round(microtime(true) - LARAVEL_START, 3) . 's');
        }

        return $response;
    }
}

// Register in app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        \App\Http\Middleware\ApiResponseMiddleware::class,
        // ...
    ],
];
```

### **Performance Metrics Configuration**

```php
// config/app.php
return [
    // ... other configs

    // Enable query logging (use with caution in production)
    'log_queries' => env('DB_LOG_QUERIES', false),

    // Performance monitoring
    'performance_monitoring' => [
        'enabled' => env('PERFORMANCE_MONITORING', true),
        'include_in_response' => env('PERFORMANCE_IN_RESPONSE', true),
        'include_in_headers' => env('PERFORMANCE_IN_HEADERS', true),
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000), // ms
        'high_memory_threshold' => env('HIGH_MEMORY_THRESHOLD', 128), // MB
    ],
];
```

```env
# .env - Performance Monitoring
DB_LOG_QUERIES=true  # Enable query logging (development/staging only)
PERFORMANCE_MONITORING=true
PERFORMANCE_IN_RESPONSE=true  # Include in JSON response meta
PERFORMANCE_IN_HEADERS=true   # Include in HTTP headers
SLOW_QUERY_THRESHOLD=1000     # Log queries slower than 1000ms
HIGH_MEMORY_THRESHOLD=128     # Alert if memory usage > 128MB
```

### **HTTP Request/Response Interceptor Middleware (File-based Daily Logs)**

```php
// app/Http/Middleware/RequestResponseInterceptor.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\AggregateApiUsageJob;
use App\Models\ApiCredential;

class RequestResponseInterceptor
{
    public function handle(Request $request, Closure $next)
    {
        // Start time for execution tracking
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Enable query logging
        if (config('app.log_queries', false)) {
            DB::enableQueryLog();
        }

        // Process request
        $response = $next($request);

        // Only log API requests
        if ($request->is('api/*')) {
            $this->logApiRequest($request, $response, $startTime, $startMemory);
        }

        return $response;
    }

    /**
     * Log API request to daily log file
     */
    private function logApiRequest($request, $response, $startTime, $startMemory)
    {
        try {
            // Calculate performance metrics
            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // ms
            $memoryUsage = memory_get_usage() - $startMemory;
            $queryLog = DB::getQueryLog();
            $queryCount = count($queryLog);

            // Get API credential (if authenticated via API key)
            $apiCredential = $this->getApiCredential($request);

            // Prepare log data
            $logData = [
                'timestamp' => now()->toIso8601String(),
                'api_credential_id' => $apiCredential?->id,
                'api_key' => $apiCredential?->api_key ? substr($apiCredential->api_key, 0, 10) . '***' : null,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'request_payload' => $this->sanitizePayload($request->all()),
                'response_status' => $response->getStatusCode(),
                'response_time_ms' => (int) $executionTime,
                'query_count' => $queryCount,
                'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            // ✅ Write to daily log file (JSON Lines format)
            $this->writeToDaily LogFile('api_requests', $logData);

            // ✅ Dispatch daily aggregation job (runs once per day)
            if ($this->shouldAggregate()) {
                AggregateApiUsageJob::dispatch(now()->yesterday()->toDateString())
                    ->onQueue('reports')
                    ->delay(now()->addMinutes(5));
            }

            // Alert for slow requests
            if ($executionTime > config('app.performance_monitoring.slow_query_threshold', 1000)) {
                Log::warning('Slow API request detected', array_merge($logData, [
                    'slow_queries' => $this->getSlowQueries($queryLog)
                ]));
            }

            // Alert for high memory usage
            $memoryUsageMB = $memoryUsage / 1024 / 1024;
            if ($memoryUsageMB > config('app.performance_monitoring.high_memory_threshold', 128)) {
                Log::warning('High memory usage detected', $logData);
            }

        } catch (\Exception $e) {
            // Silently fail - don't break the request
            Log::error('Failed to log API request', [
                'error' => $e->getMessage(),
                'endpoint' => $request->path()
            ]);
        }
    }

    /**
     * Write log entry to daily log file (JSON Lines format)
     */
    private function writeToDailyLogFile(string $logType, array $logData): void
    {
        $date = now()->toDateString(); // YYYY-MM-DD
        $logPath = "logs/{$logType}/{$date}.log";

        // Convert to JSON (one line per request)
        $jsonLine = json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // Append to file
        Storage::disk('local')->append($logPath, $jsonLine);
    }

    /**
     * Check if should dispatch aggregation job
     */
    private function shouldAggregate(): bool
    {
        // Only aggregate once at the start of new day
        $lastRun = cache()->get('last_api_aggregation_run');
        $today = now()->toDateString();

        if ($lastRun !== $today && now()->hour === 1) {
            cache()->put('last_api_aggregation_run', $today, now()->addDay());
            return true;
        }

        return false;
    }

    /**
     * Get API credential from request
     */
    private function getApiCredential($request): ?ApiCredential
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return null;
        }

        return ApiCredential::where('api_key', $apiKey)->first();
    }

    /**
     * Sanitize sensitive data from payload
     */
    private function sanitizePayload(array $payload): array
    {
        $sensitiveFields = ['password', 'api_secret', 'token', 'credit_card', 'stream_password'];

        foreach ($sensitiveFields as $field) {
            if (isset($payload[$field])) {
                $payload[$field] = '***REDACTED***';
            }
        }

        return $payload;
    }

    /**
     * Get queries slower than threshold
     */
    private function getSlowQueries(array $queryLog, int $threshold = 100): array
    {
        return array_map(function ($query) {
            return [
                'query' => $query['query'],
                'time' => $query['time'] . 'ms'
            ];
        }, array_filter($queryLog, function ($query) use ($threshold) {
            return $query['time'] > $threshold;
        }));
    }
}
```

---

### **Daily Aggregation Job (Process Log Files → Database Summary)**

```php
// app/Jobs/AggregateApiUsageJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ApiUsageSummary;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AggregateApiUsageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600;  // 10 minutes for aggregation
    public $backoff = [30, 60, 120];

    public string $date;  // YYYY-MM-DD

    /**
     * Create a new job instance.
     */
    public function __construct(string $date)
    {
        $this->date = $date;
        $this->onQueue('reports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $logPath = "logs/api_requests/{$this->date}.log";

            if (!Storage::disk('local')->exists($logPath)) {
                Log::info('No API log file found for date', ['date' => $this->date]);
                return;
            }

            // Read log file (JSON Lines format)
            $logContent = Storage::disk('local')->get($logPath);
            $lines = explode(PHP_EOL, trim($logContent));

            // Parse and aggregate
            $aggregated = [];

            foreach ($lines as $line) {
                if (empty($line)) continue;

                $logEntry = json_decode($line, true);
                if (!$logEntry) continue;

                $key = $logEntry['api_credential_id'] . '|' .
                       $logEntry['endpoint'] . '|' .
                       $logEntry['method'];

                if (!isset($aggregated[$key])) {
                    $aggregated[$key] = [
                        'api_credential_id' => $logEntry['api_credential_id'],
                        'endpoint' => $logEntry['endpoint'],
                        'method' => $logEntry['method'],
                        'requests' => [],
                        'total' => 0,
                        'success' => 0,
                        'error' => 0,
                    ];
                }

                $aggregated[$key]['requests'][] = [
                    'response_time' => $logEntry['response_time_ms'],
                    'query_count' => $logEntry['query_count'],
                    'memory_usage' => $logEntry['memory_usage_mb'] * 1024 * 1024,  // Convert to bytes
                    'status' => $logEntry['response_status']
                ];

                $aggregated[$key]['total']++;

                if ($logEntry['response_status'] >= 200 && $logEntry['response_status'] < 300) {
                    $aggregated[$key]['success']++;
                } elseif ($logEntry['response_status'] >= 400) {
                    $aggregated[$key]['error']++;
                }
            }

            // Save aggregated data to database
            foreach ($aggregated as $data) {
                $requests = $data['requests'];

                $responseTimes = array_column($requests, 'response_time');
                $queryCounts = array_column($requests, 'query_count');
                $memoryUsages = array_column($requests, 'memory_usage');

                ApiUsageSummary::updateOrCreate(
                    [
                        'api_credential_id' => $data['api_credential_id'],
                        'summary_date' => $this->date,
                        'endpoint' => $data['endpoint'],
                        'method' => $data['method'],
                    ],
                    [
                        'total_requests' => $data['total'],
                        'success_requests' => $data['success'],
                        'error_requests' => $data['error'],
                        'avg_response_time_ms' => (int) round(array_sum($responseTimes) / count($responseTimes)),
                        'max_response_time_ms' => max($responseTimes),
                        'min_response_time_ms' => min($responseTimes),
                        'avg_query_count' => (int) round(array_sum($queryCounts) / count($queryCounts)),
                        'max_query_count' => max($queryCounts),
                        'avg_memory_usage' => (int) round(array_sum($memoryUsages) / count($memoryUsages)),
                        'max_memory_usage' => max($memoryUsages),
                    ]
                );
            }

            Log::info('API usage aggregation completed', [
                'date' => $this->date,
                'total_entries' => count($lines),
                'unique_endpoints' => count($aggregated)
            ]);

        } catch (\Exception $e) {
            Log::error('API usage aggregation failed', [
                'date' => $this->date,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get the tags for the job.
     */
    public function tags(): array
    {
        return ['aggregation', 'api-usage', 'date:' . $this->date];
    }
}
```

---

### **Flexible Logging Service (File-based + Database Summary)**

```php
// app/Services/LoggingService.php
namespace App\Services;

use App\Models\WhatsAppDeliverySummary;
use App\Models\ApiUsageSummary;
use App\Models\StorageFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LoggingService
{
    /**
     * Log WhatsApp message to daily log file
     */
    public static function logWhatsAppMessage(array $data): bool
    {
        try {
            $logData = [
                'timestamp' => now()->toIso8601String(),
                'event_log_id' => $data['event_log_id'] ?? null,
                'branch_id' => $data['branch_id'],
                'device_id' => $data['device_id'],
                're_id' => $data['re_id'] ?? null,
                'phone_number' => $data['phone_number'],
                'message_text' => $data['message_text'],
                'image_path' => $data['image_path'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'provider_response' => $data['provider_response'] ?? null,
                'error_message' => $data['error_message'] ?? null,
                'retry_count' => $data['retry_count'] ?? 0,
            ];

            // ✅ Write to daily log file
            self::writeToDailyLogFile('whatsapp_messages', $logData);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to log WhatsApp message', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Log storage file to database (files need database tracking)
     */
    public static function logStorageFile(array $data): ?StorageFile
    {
        try {
            return StorageFile::create([
                'file_path' => $data['file_path'],
                'file_name' => $data['file_name'],
                'file_type' => $data['file_type'] ?? null,
                'file_size' => $data['file_size'] ?? null,
                'storage_disk' => $data['storage_disk'] ?? 'local',
                'related_table' => $data['related_table'] ?? null,
                'related_id' => $data['related_id'] ?? null,
                'uploaded_by' => $data['uploaded_by'] ?? null,
                'is_public' => $data['is_public'] ?? false,
                'metadata' => $data['metadata'] ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log storage file', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Write log entry to daily log file (JSON Lines format)
     */
    private static function writeToDailyLogFile(string $logType, array $logData): void
    {
        $date = now()->toDateString(); // YYYY-MM-DD
        $logPath = "logs/{$logType}/{$date}.log";

        // Convert to JSON (one line per entry)
        $jsonLine = json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // Append to file
        Storage::disk('local')->append($logPath, $jsonLine);
    }

    /**
     * Get API usage statistics from summary table
     */
    public static function getApiUsageStats(array $filters = []): array
    {
        $query = ApiUsageSummary::query();

        // Apply filters
        if (isset($filters['endpoint'])) {
            $query->where('endpoint', 'like', '%' . $filters['endpoint'] . '%');
        }

        if (isset($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        if (isset($filters['api_credential_id'])) {
            $query->where('api_credential_id', $filters['api_credential_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('summary_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('summary_date', '<=', $filters['date_to']);
        }

        $summaries = $query->get();

        $totalRequests = $summaries->sum('total_requests');
        $totalSuccess = $summaries->sum('success_requests');
        $totalErrors = $summaries->sum('error_requests');

        return [
            'total_requests' => $totalRequests,
            'total_success' => $totalSuccess,
            'total_errors' => $totalErrors,
            'avg_response_time_ms' => round($summaries->avg('avg_response_time_ms'), 2),
            'max_response_time_ms' => $summaries->max('max_response_time_ms'),
            'avg_query_count' => round($summaries->avg('avg_query_count'), 2),
            'success_rate' => $totalRequests > 0 ? round(($totalSuccess / $totalRequests) * 100, 2) : 0,
            'error_rate' => $totalRequests > 0 ? round(($totalErrors / $totalRequests) * 100, 2) : 0,
        ];
    }

    /**
     * Get WhatsApp delivery statistics from summary table
     */
    public static function getWhatsAppDeliveryStats(array $filters = []): array
    {
        $query = WhatsAppDeliverySummary::query();

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['device_id'])) {
            $query->where('device_id', $filters['device_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('summary_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('summary_date', '<=', $filters['date_to']);
        }

        $summaries = $query->get();

        return [
            'total_sent' => $summaries->sum('total_sent'),
            'total_delivered' => $summaries->sum('total_delivered'),
            'total_failed' => $summaries->sum('total_failed'),
            'total_pending' => $summaries->sum('total_pending'),
            'unique_recipients' => $summaries->sum('unique_recipients'),
            'messages_with_image' => $summaries->sum('messages_with_image'),
            'avg_delivery_time_ms' => round($summaries->avg('avg_delivery_time_ms'), 2),
            'delivery_rate' => $summaries->sum('total_sent') > 0
                ? round(($summaries->sum('total_delivered') / $summaries->sum('total_sent')) * 100, 2)
                : 0,
        ];
    }
}
```

---

### **Enhanced WhatsApp Helper with File-based Logging**

```php
// app/Helpers/WhatsAppHelper.php (Enhanced with file-based logging)
namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\LoggingService;
use App\Jobs\AggregateWhatsAppDeliveryJob;

class WhatsAppHelper
{
    /**
     * Send WhatsApp message with automatic file-based logging
     */
    public static function sendMessage(
        string $phoneNumber,
        string $message,
        ?string $imagePath = null,
        array $metadata = []
    ): array {
        $startTime = microtime(true);

        try {
            $apiUrl = env('WHATSAPP_API_URL');
            $apiKey = env('WHATSAPP_API_KEY');
            $sessionName = env('WHATSAPP_SESSION_NAME', 'default');

            // Prepare payload
            $payload = [
                'chatId' => self::formatPhoneNumber($phoneNumber),
                'text' => $message,
                'session' => $sessionName
            ];

            // Add image if provided
            if ($imagePath && file_exists(storage_path('app/' . $imagePath))) {
                $payload['file'] = [
                    'mimetype' => 'image/jpeg',
                    'filename' => basename($imagePath),
                    'data' => base64_encode(file_get_contents(storage_path('app/' . $imagePath)))
                ];
            }

            // Send request to WAHA API
            $response = Http::withHeaders([
                'X-Api-Key' => $apiKey,
                'Content-Type' => 'application/json'
            ])
            ->timeout(env('WHATSAPP_TIMEOUT', 30))
            ->post($apiUrl . '/api/sendText', $payload);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // ms

            // ✅ Log to daily log file using LoggingService
            LoggingService::logWhatsAppMessage([
                'event_log_id' => $metadata['event_log_id'] ?? null,
                'branch_id' => $metadata['branch_id'],
                'device_id' => $metadata['device_id'],
                're_id' => $metadata['re_id'] ?? null,
                'phone_number' => $phoneNumber,
                'message_text' => $message,
                'image_path' => $imagePath,
                'status' => $response->successful() ? 'sent' : 'failed',
                'provider_response' => array_merge($response->json() ?? [], [
                    'execution_time_ms' => $executionTime,
                    'http_status' => $response->status()
                ]),
                'error_message' => $response->failed() ? $response->body() : null,
                'retry_count' => 0
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->successful() ? 'sent' : 'failed',
                'response' => $response->json(),
                'execution_time' => $executionTime . 'ms'
            ];

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('WhatsApp send failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'execution_time' => $executionTime . 'ms'
            ]);

            // Log failed attempt to daily log file
            LoggingService::logWhatsAppMessage([
                'event_log_id' => $metadata['event_log_id'] ?? null,
                'branch_id' => $metadata['branch_id'],
                'device_id' => $metadata['device_id'],
                're_id' => $metadata['re_id'] ?? null,
                'phone_number' => $phoneNumber,
                'message_text' => $message,
                'image_path' => $imagePath,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'retry_count' => 0
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time' => $executionTime . 'ms'
            ];
        }
    }

    /**
     * Format phone number for WhatsApp
     */
    private static function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (!str_starts_with($phone, '62')) {
            $phone = '62' . ltrim($phone, '0');
        }

        return $phone . '@c.us';
    }
}
```

---

### **WhatsApp Delivery Aggregation Job**

```php
// app/Jobs/AggregateWhatsAppDeliveryJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WhatsAppDeliverySummary;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AggregateWhatsAppDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600;
    public $backoff = [30, 60, 120];

    public string $date;

    public function __construct(string $date)
    {
        $this->date = $date;
        $this->onQueue('reports');
    }

    public function handle(): void
    {
        try {
            $logPath = "logs/whatsapp_messages/{$this->date}.log";

            if (!Storage::disk('local')->exists($logPath)) {
                Log::info('No WhatsApp log file found for date', ['date' => $this->date]);
                return;
            }

            // Read and parse log file
            $logContent = Storage::disk('local')->get($logPath);
            $lines = explode(PHP_EOL, trim($logContent));

            // Aggregate by branch_id + device_id
            $aggregated = [];

            foreach ($lines as $line) {
                if (empty($line)) continue;

                $logEntry = json_decode($line, true);
                if (!$logEntry) continue;

                $key = $logEntry['branch_id'] . '|' . $logEntry['device_id'];

                if (!isset($aggregated[$key])) {
                    $aggregated[$key] = [
                        'branch_id' => $logEntry['branch_id'],
                        'device_id' => $logEntry['device_id'],
                        'messages' => [],
                        'recipients' => [],
                    ];
                }

                $aggregated[$key]['messages'][] = $logEntry;
                $aggregated[$key]['recipients'][] = $logEntry['phone_number'];
            }

            // Save aggregated data
            foreach ($aggregated as $data) {
                $messages = $data['messages'];
                $deliveryTimes = [];

                $totals = [
                    'sent' => 0,
                    'delivered' => 0,
                    'failed' => 0,
                    'pending' => 0,
                    'with_image' => 0,
                ];

                foreach ($messages as $msg) {
                    $status = $msg['status'];
                    if (isset($totals[$status])) {
                        $totals[$status]++;
                    }

                    if (!empty($msg['image_path'])) {
                        $totals['with_image']++;
                    }

                    if (isset($msg['provider_response']['execution_time_ms'])) {
                        $deliveryTimes[] = $msg['provider_response']['execution_time_ms'];
                    }
                }

                WhatsAppDeliverySummary::updateOrCreate(
                    [
                        'summary_date' => $this->date,
                        'branch_id' => $data['branch_id'],
                        'device_id' => $data['device_id'],
                    ],
                    [
                        'total_sent' => $totals['sent'],
                        'total_delivered' => $totals['delivered'],
                        'total_failed' => $totals['failed'],
                        'total_pending' => $totals['pending'],
                        'avg_delivery_time_ms' => !empty($deliveryTimes)
                            ? (int) round(array_sum($deliveryTimes) / count($deliveryTimes))
                            : 0,
                        'unique_recipients' => count(array_unique($data['recipients'])),
                        'messages_with_image' => $totals['with_image'],
                    ]
                );
            }

            Log::info('WhatsApp delivery aggregation completed', [
                'date' => $this->date,
                'total_entries' => count($lines),
                'unique_devices' => count($aggregated)
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp delivery aggregation failed', [
                'date' => $this->date,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function tags(): array
    {
        return ['aggregation', 'whatsapp', 'date:' . $this->date];
    }
}
```

---

### **Performance Monitoring Middleware (Alerts Only)**

```php
// app/Http/Middleware/PerformanceMonitoringMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerformanceMonitoringMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only for API requests
        if (!$request->is('api/*')) {
            return $response;
        }

        // Calculate metrics
        $queryLog = \DB::getQueryLog();
        $queryCount = count($queryLog);
        $memoryUsage = memory_get_usage() / 1024 / 1024; // MB
        $executionTime = (microtime(true) - LARAVEL_START) * 1000; // ms

        // Alert for slow queries
        if ($executionTime > config('app.performance_monitoring.slow_query_threshold', 1000)) {
            Log::warning('Slow API request detected', [
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'execution_time' => $executionTime . 'ms',
                'query_count' => $queryCount,
                'memory_usage' => round($memoryUsage, 2) . 'MB'
            ]);
        }

        // Alert for high memory usage
        if ($memoryUsage > config('app.performance_monitoring.high_memory_threshold', 128)) {
            Log::warning('High memory usage detected', [
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'memory_usage' => round($memoryUsage, 2) . 'MB',
                'query_count' => $queryCount,
                'execution_time' => $executionTime . 'ms'
            ]);
        }

        // Log slow queries details
        if ($executionTime > 1000 && config('app.debug')) {
            foreach ($queryLog as $query) {
                if ($query['time'] > 100) { // Queries slower than 100ms
                    Log::debug('Slow query', [
                        'query' => $query['query'],
                        'bindings' => $query['bindings'],
                        'time' => $query['time'] . 'ms'
                    ]);
                }
            }
        }

        return $response;
    }
}

// Register in app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        \App\Http\Middleware\RequestResponseInterceptor::class,  // ✅ Universal logger
        \App\Http\Middleware\ApiResponseMiddleware::class,
        \App\Http\Middleware\PerformanceMonitoringMiddleware::class,  // ✅ Alert only
        // ...
    ],
];
```

---

### **Middleware Logging Flow Diagram (File-based)**

```
┌──────────────────────────────────────────────────────────────────────────┐
│         HTTP Request/Response Interceptor Flow (File-based Logs)          │
└──────────────────────────────────────────────────────────────────────────┘

Request → RequestResponseInterceptor (Start Timer)
    │
    ├── 1. Initialize Tracking
    │   ├── $startTime = microtime(true)
    │   ├── $startMemory = memory_get_usage()
    │   └── DB::enableQueryLog() (if enabled)
    │
    ├── 2. Process Request
    │   ├── $response = $next($request)
    │   └── Execute controller logic
    │
    ├── 3. Calculate Metrics (After Response)
    │   ├── execution_time = microtime(true) - $startTime
    │   ├── memory_usage = memory_get_usage() - $startMemory
    │   ├── query_count = count(DB::getQueryLog())
    │   └── response_status = $response->getStatusCode()
    │
    ├── 4. Prepare Log Data
    │   ├── Get API credential (X-API-Key header)
    │   ├── Sanitize sensitive fields (password, token, api_secret, etc.)
    │   ├── Collect request metadata (IP, User-Agent)
    │   └── Build JSON log entry
    │
    ├── 5. ✅ Write to Daily Log File (JSON Lines)
    │   ├── File: storage/app/logs/api_requests/YYYY-MM-DD.log
    │   ├── Format: One JSON object per line
    │   ├── Append mode (no truncate)
    │   └── Instant write (no queue delay)
    │
    ├── 6. Performance Alerts (If Threshold Exceeded)
    │   ├── Slow Request Alert (> 1000ms)
    │   │   └── Log::warning() with full metrics
    │   ├── High Memory Alert (> 128MB)
    │   │   └── Log::warning() with memory details
    │   └── Slow Query Details (debug mode)
    │       └── Log each query > 100ms to Laravel log
    │
    └── 7. Return Response (With Performance Headers)
        ├── X-Query-Count: 5
        ├── X-Memory-Usage: 2.5MB
        ├── X-Execution-Time: 0.125s
        └── JSON meta with same metrics

Daily Aggregation (Scheduled at 01:30):
    ├── AggregateApiUsageJob
    │   ├── Read: storage/app/logs/api_requests/YYYY-MM-DD.log
    │   ├── Parse: JSON Lines format
    │   ├── Aggregate: By api_credential_id + endpoint + method
    │   ├── Calculate: avg/max/min response_time, query_count, memory_usage
    │   └── Save: To api_usage_summary table
    │
    └── AggregateWhatsAppDeliveryJob
        ├── Read: storage/app/logs/whatsapp_messages/YYYY-MM-DD.log
        ├── Parse: JSON Lines format
        ├── Aggregate: By branch_id + device_id
        ├── Calculate: total_sent/delivered/failed, unique_recipients
        └── Save: To whatsapp_delivery_summary table
```

**Key Features:**

- ✅ **File-based Logs**: Raw logs in daily files (JSON Lines format), not database
- ✅ **Instant Write**: No queue delay, writes immediately to file
- ✅ **Scalable**: Prevents database bloat for high-volume operations
- ✅ **Universal**: Works for all API requests (authenticated or not)
- ✅ **Flexible**: Can log API, WhatsApp via LoggingService
- ✅ **Performance Tracking**: query_count, memory_usage, execution_time
- ✅ **Security**: Sanitizes sensitive fields (password, token, api_secret)
- ✅ **Automatic Alerts**: Logs warnings for slow/memory-intensive requests
- ✅ **Daily Aggregation**: Scheduled jobs process log files → database summaries
- ✅ **Dual Output**: Performance metrics in HTTP headers + JSON meta

---

### **Usage Examples**

#### **1. Automatic API Logging (via Middleware)**

```php
// ✅ ALL API requests are automatically logged to daily files
// No manual code needed - middleware handles everything

GET /api/person/person_001_abc123
→ Middleware intercepts
→ Processes request
→ Writes to storage/app/logs/api_requests/2024-01-16.log (instant)
→ Returns response with performance metrics

// Log file format (JSON Lines):
{"timestamp":"2024-01-16T14:30:00Z","api_credential_id":2,"endpoint":"api/person/person_001_abc123","method":"GET","response_status":200,"response_time_ms":156,"query_count":6,"memory_usage_mb":2.8,"ip_address":"192.168.1.100"}
```

#### **2. WhatsApp Logging (via LoggingService)**

```php
use App\Services\LoggingService;

// ✅ Log WhatsApp message to daily file
LoggingService::logWhatsAppMessage([
    'branch_id' => 1,
    'device_id' => 'CAMERA_001',
    'phone_number' => '+628123456789',
    'message_text' => 'Alert: Person detected',
    'status' => 'sent',
    'provider_response' => [
        'message_id' => 'ABC123',
        'execution_time_ms' => 234
    ]
]);

// ✅ Written to: storage/app/logs/whatsapp_messages/2024-01-16.log
```

#### **3. Storage File Logging (Database - Files Need Tracking)**

```php
use App\Services\LoggingService;

// ✅ Storage files still use database (need persistent tracking)
$storageFile = LoggingService::logStorageFile([
    'file_path' => 'events/2024/01/16/image.jpg',
    'file_name' => 'detection_001.jpg',
    'file_type' => 'image/jpeg',
    'file_size' => 245678,
    'storage_disk' => 'local',
    'related_table' => 'event_logs',
    'related_id' => 123,
    'metadata' => ['width' => 1920, 'height' => 1080]
]);

// ✅ Saved to: storage_files table (database)
```

#### **4. Get API Statistics (From Summary Table)**

```php
use App\Services\LoggingService;

// ✅ Get aggregated API usage statistics
$stats = LoggingService::getApiUsageStats([
    'endpoint' => 'detection/log',
    'method' => 'POST',
    'date_from' => '2024-01-16',
    'date_to' => '2024-01-16'
]);

/*
Result (from api_usage_summary table):
{
    "total_requests": 1523,
    "total_success": 1498,
    "total_errors": 25,
    "avg_response_time_ms": 156.45,
    "max_response_time_ms": 2345,
    "avg_query_count": 5.2,
    "success_rate": 98.5,  // %
    "error_rate": 1.5      // %
}
*/
```

#### **5. Get WhatsApp Delivery Statistics**

```php
use App\Services\LoggingService;

// ✅ Get aggregated WhatsApp delivery statistics
$stats = LoggingService::getWhatsAppDeliveryStats([
    'branch_id' => 1,
    'device_id' => 'CAMERA_001',
    'date_from' => '2024-01-16',
    'date_to' => '2024-01-16'
]);

/*
Result (from whatsapp_delivery_summary table):
{
    "total_sent": 245,
    "total_delivered": 238,
    "total_failed": 7,
    "total_pending": 0,
    "unique_recipients": 45,
    "messages_with_image": 189,
    "avg_delivery_time_ms": 234.5,
    "delivery_rate": 97.1  // %
}
*/
```

---

### **Logging Architecture Summary**

```
┌──────────────────────────────────────────────────────────────────────┐
│                      Logging Architecture                             │
├──────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │  1. HTTP Request/Response Interceptor (Middleware)          │    │
│  │     → Automatic for ALL API requests                        │    │
│  │     → Logs to: api_request_logs (UUID)                      │    │
│  │     → Queue: maintenance (async, non-blocking)              │    │
│  │     → Tracks: query_count, memory_usage, execution_time     │    │
│  └─────────────────────────────────────────────────────────────┘    │
│                                                                       │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │  2. WhatsApp Logging (via LoggingService)                   │    │
│  │     → Called from WhatsAppHelper                            │    │
│  │     → Logs to: whatsapp_message_logs                        │    │
│  │     → Tracks: status, provider_response, retry_count        │    │
│  │     → Includes: execution_time in provider_response         │    │
│  └─────────────────────────────────────────────────────────────┘    │
│                                                                       │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │  3. Storage Logging (via LoggingService)                    │    │
│  │     → Called from StorageHelper                             │    │
│  │     → Logs to: storage_files                                │    │
│  │     → Tracks: file_size, metadata (JSONB)                   │    │
│  │     → Includes: related_table, related_id for linking       │    │
│  └─────────────────────────────────────────────────────────────┘    │
│                                                                       │
└──────────────────────────────────────────────────────────────────────┘
```

**Logging Strategy:**

| Log Type        | Table                   | Trigger                | Async                 | Includes Performance                        |
| --------------- | ----------------------- | ---------------------- | --------------------- | ------------------------------------------- |
| **API Request** | api_request_logs        | Middleware (automatic) | ✅ Yes (queue)        | ✅ query_count, memory_usage, response_time |
| **WhatsApp**    | whatsapp_message_logs   | Helper call (manual)   | ❌ No (direct)        | ✅ execution_time in provider_response      |
| **Storage**     | storage_files           | Helper call (manual)   | ❌ No (direct)        | ✅ file_size, metadata                      |
| **Event**       | event_logs              | Service (automatic)    | ❌ No (transactional) | ❌ No                                       |
| **Detection**   | re_id_branch_detections | Job (automatic)        | ✅ Yes (queue)        | ❌ No                                       |

**Best Practices:**

1. ✅ **API Logging**: Always async via queue (non-blocking)
2. ✅ **Sensitive Data**: Always sanitize (passwords, tokens, secrets)
3. ✅ **Performance Metrics**: Track for optimization insights
4. ✅ **Alerts**: Auto-alert for slow/memory-intensive requests
5. ✅ **UUID**: Use UUID for high-volume logs (api_request_logs)
6. ✅ **Indexes**: Create partial indexes for slow/error requests
7. ✅ **Retention**: Auto-cleanup old logs (configurable)

---

## 📡 Complete API Endpoints

### **1. Device Counting APIs**

#### **POST /api/detection/log**

Log person detection from device

**Payload:**

```json
{
  "re_id": "person_001_abc123",
  "branch_id": 1,
  "device_id": "CAMERA_001",
  "detected_count": 1,
  "detection_data": {
    "confidence": 0.95,
    "bounding_box": { "x": 120, "y": 150, "width": 80, "height": 200 }
  }
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "re_id": "person_001_abc123",
    "detection_date": "2024-01-16",
    "detection_time": "2024-01-16 08:30:00",
    "branch_id": 1,
    "device_id": "CAMERA_001",
    "branch_count": 1,
    "detected_count": 1,
    "total_actual_count": 15,
    "total_detection_branch_count": 2,
    "first_detected_at": "2024-01-16 08:30:00",
    "last_detected_at": "2024-01-16 16:45:00",
    "status": "active",
    "detection_timestamp": "2024-01-16 14:30:15",
    "branch_info": {
      "branch_name": "Jakarta Central Branch",
      "city_name": "Central Jakarta"
    }
  }
}
```

#### **GET /api/person/{re_id}**

Get info for specific person (RE_ID) across all branches (daily summary)

**Query Parameters:**

- `date` (optional): Specific date (default: today)

**Response:**

```json
{
  "success": true,
  "data": {
    "re_id": "person_001_abc123",
    "detection_date": "2024-01-16",
    "detection_time": "2024-01-16 08:30:00",
    "person_name": "John Doe",
    "total_detection_branch_count": 2,
    "total_actual_count": 15,
    "first_detected_at": "2024-01-16 08:30:00",
    "last_detected_at": "2024-01-16 16:45:00",
    "status": "active",
    "appearance_features": {
      "clothing_colors": ["blue", "white"],
      "height": "medium"
    },
    "detected_branches": [
      {
        "branch_id": 1,
        "branch_name": "Jakarta Central",
        "detection_count": 10
      },
      {
        "branch_id": 2,
        "branch_name": "Jakarta South",
        "detection_count": 5
      }
    ]
  }
}
```

#### **GET /api/branch/{branch_id}/detections**

Get all person detections for specific branch

#### **GET /api/detection/summary**

Get global detection summary

### **2. Event Management APIs**

#### **POST /api/event/settings**

Configure event settings for branch/device

**Payload:**

```json
{
  "branch_id": 1,
  "device_id": "CAMERA_001",
  "is_active": true,
  "send_image": true,
  "send_message": true,
  "send_notification": true,
  "whatsapp_enabled": true,
  "whatsapp_numbers": ["+628123456789", "+628987654321"],
  "message_template": "Alert from {branch_name}: Person detected at {device_name}"
}
```

#### **POST /api/event/log**

Log an event occurrence

**Payload:**

```json
{
  "branch_id": 1,
  "device_id": "CAMERA_001",
  "re_id": "person_001_abc123",
  "event_type": "detection",
  "detected_count": 1,
  "image_path": "/storage/events/2024/01/16/event_001.jpg",
  "event_data": {
    "confidence": 0.95,
    "person_detected": true,
    "bounding_box": { "x": 120, "y": 150 }
  }
}
```

### **3. CCTV Stream Management APIs**

#### **POST /api/stream/create**

Create/register CCTV stream

**Payload:**

```json
{
  "branch_id": 1,
  "device_id": "CAMERA_001",
  "stream_name": "Main Entrance Camera",
  "stream_url": "rtsp://192.168.1.100:554/stream1",
  "stream_type": "rtsp",
  "stream_username": "admin",
  "stream_password": "password123",
  "position": 1,
  "resolution": "1920x1080",
  "fps": 30
}
```

#### **GET /api/stream/branch/{branch_id}**

Get all streams for a branch

**Response:**

```json
{
  "success": true,
  "data": {
    "branch_id": 1,
    "branch_name": "Jakarta Central Branch",
    "streams": [
      {
        "id": 1,
        "device_id": "CAMERA_001",
        "stream_name": "Main Entrance",
        "stream_url": "rtsp://192.168.1.100:554/stream1",
        "position": 1,
        "status": "online",
        "resolution": "1920x1080",
        "fps": 30
      }
    ]
  }
}
```

### **4. API Credential Management**

#### **POST /api/credentials/create**

Create new API credential

**Payload:**

```json
{
  "credential_name": "Branch Jakarta API Key",
  "branch_id": 1,
  "device_id": null,
  "re_id": null,
  "permissions": {
    "read": true,
    "write": true,
    "delete": false
  },
  "rate_limit": 1000,
  "expires_at": "2025-12-31T23:59:59Z"
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 2,
    "credential_name": "Branch Jakarta API Key",
    "api_key": "cctv_live_jkt001branch",
    "api_secret": "secret_jkt001secret",
    "branch_id": 1,
    "device_id": null,
    "re_id": null,
    "permissions": { "read": true, "write": true, "delete": false },
    "rate_limit": 1000,
    "expires_at": "2025-12-31 23:59:59"
  }
}
```

---

## 📊 Sample Data Structure

### **Company Groups**

```sql
INSERT INTO company_groups VALUES
(1, 'JKT', 'DKI Jakarta', 'Jakarta Group', 'Jl. Sudirman No.1, Jakarta', '021-12345678', 'jakarta@group.com', 'active', NOW(), NOW()),
(2, 'BDG', 'West Java', 'Bandung Group', 'Jl. Asia Afrika No.1, Bandung', '022-87654321', 'bandung@group.com', 'active', NOW(), NOW()),
(3, 'SBY', 'East Java', 'Surabaya Group', 'Jl. Tunjungan No.1, Surabaya', '031-11223344', 'surabaya@group.com', 'active', NOW(), NOW());
```

### **Company Branches**

```sql
INSERT INTO company_branches VALUES
(1, 1, 'JKT001', 'Jakarta Central Branch', 'Central Jakarta', 'Jl. Thamrin No.1', '021-11111111', 'jakarta.central@branch.com', -6.200000, 106.816666, 'active', NOW(), NOW()),
(2, 1, 'JKT002', 'Jakarta South Branch', 'South Jakarta', 'Jl. Sudirman No.100', '021-22222222', 'jakarta.south@branch.com', -6.261493, 106.810600, 'active', NOW(), NOW()),
(3, 2, 'BDG001', 'Bandung City Branch', 'Bandung', 'Jl. Asia Afrika No.50', '022-33333333', 'bandung.city@branch.com', -6.917464, 107.619125, 'active', NOW(), NOW()),
(4, 3, 'SBY001', 'Surabaya Central Branch', 'Surabaya', 'Jl. Tunjungan No.25', '031-44444444', 'surabaya.central@branch.com', -7.250445, 112.768845, 'active', NOW(), NOW());
```

### **Device Master**

```sql
INSERT INTO device_masters VALUES
-- Camera devices
(1, 'CAMERA_001', 'Jakarta Central - Main Entrance Camera', 'camera', 1, 'rtsp://192.168.1.100:554/stream1', 'admin', 'encrypted_password_001', 'Main entrance 24/7 monitoring', 'active', NOW(), NOW()),
(2, 'CAMERA_002', 'Jakarta Central - Parking Area Camera', 'camera', 1, 'rtsp://192.168.1.101:554/stream1', 'admin', 'encrypted_password_002', 'Parking area camera with night vision', 'active', NOW(), NOW()),
(3, 'CAMERA_003', 'Jakarta South - Lobby Camera', 'cctv', 2, 'rtsp://192.168.2.100:554/stream1', 'admin', 'encrypted_password_003', 'Lobby CCTV with PTZ control', 'active', NOW(), NOW()),

-- Node AI devices
(4, 'NODE_AI_001', 'Bandung - AI Detection Node', 'node_ai', 3, 'http://192.168.3.50:8080/api', 'api_user', 'encrypted_api_key_001', 'AI-powered person detection node', 'active', NOW(), NOW()),

-- Mikrotik devices
(5, 'MIKROTIK_001', 'Surabaya - Network Router', 'mikrotik', 4, 'https://192.168.4.1', 'admin', 'encrypted_mikrotik_pass', 'Main network router for CCTV network', 'active', NOW(), NOW());
```

### **Re-ID Master** (Person Registry - Daily Records)

```sql
INSERT INTO re_id_masters VALUES
-- person_001_abc123 on 2024-01-16 (detected by 2 branches)
(1, 'person_001_abc123', '2024-01-16', '2024-01-16 08:30:00', 'John Doe', '{"clothing_colors": ["blue", "white"], "height": "medium"}', '2024-01-16 08:30:00', '2024-01-16 16:45:00', 2, 15, 'active', NOW(), NOW()),

-- person_002_def456 on 2024-01-16 (detected by 1 branch)
(2, 'person_002_def456', '2024-01-16', '2024-01-16 09:00:00', 'Jane Smith', '{"clothing_colors": ["red", "black"], "height": "tall"}', '2024-01-16 09:00:00', '2024-01-16 15:30:00', 1, 8, 'active', NOW(), NOW()),

-- person_003_ghi789 on 2024-01-16 (detected by 1 branch)
(3, 'person_003_ghi789', '2024-01-16', '2024-01-16 10:15:00', NULL, '{"clothing_colors": ["green", "white"], "height": "short"}', '2024-01-16 10:15:00', '2024-01-16 14:20:00', 1, 12, 'active', NOW(), NOW()),

-- person_001_abc123 on 2024-01-17 (next day, new record)
(4, 'person_001_abc123', '2024-01-17', '2024-01-17 09:00:00', 'John Doe', '{"clothing_colors": ["blue", "white"], "height": "medium"}', '2024-01-17 09:00:00', '2024-01-17 17:00:00', 3, 20, 'active', NOW(), NOW()),

-- person_003_ghi789 on 2024-01-17 (inactive - stopped tracking)
(5, 'person_003_ghi789', '2024-01-17', '2024-01-17 10:00:00', NULL, '{"clothing_colors": ["green", "white"], "height": "short"}', '2024-01-17 10:00:00', '2024-01-17 14:00:00', 2, 8, 'inactive', NOW(), NOW());
```

### **Re-ID Branch Detection** (Person Detection Logs)

```sql
INSERT INTO re_id_branch_detections VALUES
-- person_001_abc123 detected at Branch 1 (Jakarta Central)
(1, 'person_001_abc123', 1, 'CAMERA_001', 1, '2024-01-16 08:30:15', '{"confidence": 0.95, "bounding_box": {"x": 120, "y": 150, "width": 80, "height": 200}}', 'active', NOW(), NOW()),
(2, 'person_001_abc123', 1, 'CAMERA_001', 1, '2024-01-16 14:22:30', '{"confidence": 0.92, "bounding_box": {"x": 130, "y": 160, "width": 75, "height": 195}}', 'active', NOW(), NOW()),
(3, 'person_001_abc123', 1, 'CAMERA_002', 1, '2024-01-16 16:45:10', '{"confidence": 0.89, "bounding_box": {"x": 140, "y": 140, "width": 82, "height": 205}}', 'active', NOW(), NOW()),

-- person_001_abc123 detected at Branch 2 (Jakarta South)
(4, 'person_001_abc123', 2, 'CAMERA_003', 1, '2024-01-16 10:15:20', '{"confidence": 0.93, "bounding_box": {"x": 115, "y": 155, "width": 78, "height": 198}}', 'active', NOW(), NOW()),
(5, 'person_001_abc123', 2, 'CAMERA_003', 1, '2024-01-16 15:30:45', '{"confidence": 0.91, "bounding_box": {"x": 125, "y": 148, "width": 80, "height": 202}}', 'active', NOW(), NOW()),

-- person_002_def456 detected at Branch 1 only
(6, 'person_002_def456', 1, 'CAMERA_001', 1, '2024-01-16 09:00:10', '{"confidence": 0.94, "bounding_box": {"x": 200, "y": 180, "width": 70, "height": 210}}', 'active', NOW(), NOW()),
(7, 'person_002_def456', 1, 'CAMERA_002', 1, '2024-01-16 13:45:20', '{"confidence": 0.88, "bounding_box": {"x": 210, "y": 175, "width": 72, "height": 208}}', 'active', NOW(), NOW()),

-- person_003_ghi789 detected at Branch 3 (Bandung)
(8, 'person_003_ghi789', 3, 'SENSOR_001', 1, '2024-01-16 10:15:30', '{"confidence": 0.87, "bounding_box": {"x": 90, "y": 120, "width": 65, "height": 180}}', 'active', NOW(), NOW()),
(9, 'person_003_ghi789', 3, 'SENSOR_001', 1, '2024-01-16 14:20:45', '{"confidence": 0.90, "bounding_box": {"x": 95, "y": 125, "width": 68, "height": 185}}', 'active', NOW(), NOW());
```

### **Branch Event Settings**

```sql
INSERT INTO branch_event_settings VALUES
(1, 1, 'CAMERA_001', true, true, true, true, true, '["+628123456789", "+628987654321"]', 'Alert from {branch_name}: Person detected at {device_name}', 'Person detected at {device_name}', NOW(), NOW()),
(2, 1, 'CAMERA_002', true, true, false, true, false, NULL, 'Motion detected at {device_name}', NULL, NOW(), NOW()),
(3, 2, 'CAMERA_003', true, false, true, true, true, '["+628111222333"]', 'Camera alert: Person detected', 'Alert notification', NOW(), NOW()),
(4, 3, 'SENSOR_001', true, true, true, false, false, NULL, 'Sensor triggered at {device_name}', 'Sensor notification', NOW(), NOW());
```

### **Event Logs**

```sql
INSERT INTO event_logs VALUES
(1, 1, 'CAMERA_001', 'person_001_abc123', 'detection', 1, '/storage/events/2024/01/16/event_001.jpg', true, true, true, '{"confidence": 0.95, "person_detected": true, "bounding_box": {"x": 120, "y": 150}}', '2024-01-16 14:30:00', NOW()),
(2, 1, 'CAMERA_002', NULL, 'motion', 0, '/storage/events/2024/01/16/event_002.jpg', true, true, false, '{"confidence": 0.82, "motion_area": "left_side"}', '2024-01-16 15:15:00', NOW()),
(3, 2, 'CAMERA_003', 'person_001_abc123', 'detection', 1, '/storage/events/2024/01/16/event_003.jpg', false, true, true, '{"confidence": 0.93, "person_detected": true}', '2024-01-16 16:20:00', NOW()),
(4, 1, 'CAMERA_001', 'person_002_def456', 'alert', 1, '/storage/events/2024/01/16/event_004.jpg', true, true, true, '{"confidence": 0.94, "alert_type": "unauthorized_person"}', '2024-01-16 17:30:00', NOW());
```

### **API Credentials** (Simplified - Global Access Only)

```sql
INSERT INTO api_credentials VALUES
-- All credentials have global access (branch_id=NULL, device_id=NULL)
(1, 'Mobile App API Key', 'cctv_live_abc123xyz789def456ghi789jkl012', 'secret_mno345pqr678stu901vwx234yz567ab', NULL, NULL, 'active', '{"read": true, "write": true, "delete": true}', 10000, '2024-01-16 15:30:00', NULL, 1, NOW(), NOW()),
(2, 'Web Dashboard API', 'cctv_live_web001dashboard234567890abcdef', 'secret_web001xyz123abc456def789ghi012jk', NULL, NULL, 'active', '{"read": true, "write": true, "delete": true}', 10000, '2024-01-16 14:20:00', '2025-12-31 23:59:59', 1, NOW(), NOW()),
(3, 'External Integration', 'cctv_live_ext001integration789abcdef12', 'secret_ext001mno345pqr678stu901vwx234', NULL, NULL, 'active', '{"read": true, "write": true, "delete": true}', 10000, '2024-01-16 13:10:00', '2024-12-31 23:59:59', 1, NOW(), NOW()),
(4, 'Testing API Key', 'cctv_live_test001api456def789ghi012jklm', 'secret_test001yz567abc890def123ghi456jk', NULL, NULL, 'inactive', '{"read": true, "write": true, "delete": true}', 10000, NULL, '2024-12-31 23:59:59', 1, NOW(), NOW());
```

**Notes:**

- ✅ All credentials have **global access** (NULL branch_id, NULL device_id)
- ✅ All credentials have **full permissions** (read, write, delete)
- ✅ Default **rate limit**: 10,000 requests/hour
- ✅ Managed via web interface at `/api-credentials` (admin only)
- ✅ API secret shown **only once** after creation (must be saved!)
- ✅ Middleware `api.key` handles authentication and rate limiting
- ✅ Test interface available at `/api-credentials/{id}/test`

### **CCTV Streams**

```sql
INSERT INTO cctv_streams VALUES
(1, 1, 'CAMERA_001', 'Jakarta Central - Main Entrance', 'rtsp://192.168.1.100:554/stream1', 'rtsp', 'admin', 'encrypted_password_123', 554, true, 1, '1920x1080', 30, 4096, '2024-01-16 16:00:00', 'online', NOW(), NOW()),
(2, 1, 'CAMERA_002', 'Jakarta Central - Parking Area', 'rtsp://192.168.1.101:554/stream1', 'rtsp', 'admin', 'encrypted_password_456', 554, true, 2, '1280x720', 25, 2048, '2024-01-16 16:00:05', 'online', NOW(), NOW()),
(3, 2, 'CAMERA_003', 'Jakarta South - Lobby', 'rtsp://192.168.2.100:554/stream1', 'rtsp', 'admin', 'encrypted_password_789', 554, true, 1, '1920x1080', 30, 4096, '2024-01-16 16:00:10', 'online', NOW(), NOW()),
(4, 3, 'SENSOR_001', 'Bandung - Entry Sensor (No Stream)', NULL, NULL, NULL, NULL, NULL, false, 3, NULL, NULL, NULL, '2024-01-16 15:55:00', 'offline', NOW(), NOW()),
(5, 4, 'THERMO_001', 'Surabaya - Thermal Camera', 'rtsp://192.168.4.100:554/stream1', 'rtsp', 'admin', 'encrypted_password_321', 554, true, 4, '640x480', 15, 1024, '2024-01-16 16:00:15', 'online', NOW(), NOW());
```

### **Counting Reports**

```sql
INSERT INTO counting_reports VALUES
(1, 'daily', '2024-01-16', NULL, 5, 35, 10, 3, 3, '{"top_persons":[{"re_id":"person_001_abc123","count":15}],"top_branches":[{"branch_id":1,"detections":20}],"peak_hour":"14:00"}', '2024-01-16 23:59:00', NOW()),
(2, 'daily', '2024-01-16', 1, 2, 20, 7, 2, 2, '{"persons":[{"re_id":"person_001_abc123","count":15},{"re_id":"person_002_def456","count":5}],"peak_hour":"14:00"}', '2024-01-16 23:59:05', NOW()),
(3, 'daily', '2024-01-16', 2, 1, 5, 2, 1, 1, '{"persons":[{"re_id":"person_001_abc123","count":5}],"peak_hour":"15:00"}', '2024-01-16 23:59:10', NOW()),
(4, 'daily', '2024-01-16', 3, 1, 10, 1, 1, 1, '{"persons":[{"re_id":"person_003_ghi789","count":10}],"peak_hour":"12:00"}', '2024-01-16 23:59:15', NOW());
```

### **CCTV Layout Settings**

```sql
INSERT INTO cctv_layout_settings VALUES
(1, 'Default 4-Window Layout', '4-window', 4, true, true, 'Standard quad view layout for main monitoring', 1, NOW(), NOW()),
(2, 'Extended 6-Window Layout', '6-window', 6, false, true, 'Extended view for comprehensive monitoring', 1, NOW(), NOW()),
(3, 'Maximum 8-Window Layout', '8-window', 8, false, true, 'Maximum view for complete surveillance coverage', 1, NOW(), NOW());
```

### **CCTV Position Settings**

```sql
INSERT INTO cctv_position_settings VALUES
-- Default 4-Window Layout Positions
(1, 1, 1, 1, 'CAMERA_001', 'Main Entrance', true, false, 30, '1920x1080', 'high', NOW(), NOW()),
(2, 1, 2, 1, 'CAMERA_002', 'Parking Area', true, false, 30, '1920x1080', 'high', NOW(), NOW()),
(3, 1, 3, 2, 'CAMERA_003', 'Lobby View', true, true, 60, '1280x720', 'medium', NOW(), NOW()),
(4, 1, 4, 3, 'SENSOR_001', 'Entry Sensor', true, false, 30, '640x480', 'low', NOW(), NOW()),

-- Extended 6-Window Layout Positions
(5, 2, 1, 1, 'CAMERA_001', 'Main Entrance', true, false, 30, '1920x1080', 'high', NOW(), NOW()),
(6, 2, 2, 1, 'CAMERA_002', 'Parking Area', true, false, 30, '1920x1080', 'high', NOW(), NOW()),
(7, 2, 3, 2, 'CAMERA_003', 'Lobby View', true, true, 60, '1280x720', 'medium', NOW(), NOW()),
(8, 2, 4, 3, 'SENSOR_001', 'Entry Sensor', true, false, 30, '640x480', 'low', NOW(), NOW()),
(9, 2, 5, 4, 'THERMO_001', 'Thermal Camera', true, false, 30, '640x480', 'medium', NOW(), NOW()),
(10, 2, 6, 1, 'CAMERA_001', 'Main Entrance (Alt)', true, true, 45, '1920x1080', 'high', NOW(), NOW()),

-- Maximum 8-Window Layout Positions
(11, 3, 1, 1, 'CAMERA_001', 'Main Entrance', true, false, 30, '1920x1080', 'high', NOW(), NOW()),
(12, 3, 2, 1, 'CAMERA_002', 'Parking Area', true, false, 30, '1920x1080', 'high', NOW(), NOW()),
(13, 3, 3, 2, 'CAMERA_003', 'Lobby View', true, true, 60, '1280x720', 'medium', NOW(), NOW()),
(14, 3, 4, 3, 'SENSOR_001', 'Entry Sensor', true, false, 30, '640x480', 'low', NOW(), NOW()),
(15, 3, 5, 4, 'THERMO_001', 'Thermal Camera', true, false, 30, '640x480', 'medium', NOW(), NOW()),
(16, 3, 6, 1, 'CAMERA_001', 'Main Entrance (Alt)', true, true, 45, '1920x1080', 'high', NOW(), NOW()),
(17, 3, 7, 2, 'CAMERA_003', 'Lobby View (Alt)', true, true, 90, '1280x720', 'medium', NOW(), NOW()),
(18, 3, 8, 3, 'SENSOR_001', 'Entry Sensor (Alt)', true, false, 30, '640x480', 'low', NOW(), NOW());
```

### **WhatsApp Message Logs**

```sql
INSERT INTO whatsapp_message_logs VALUES
-- Successful message with image
(1, 1, 1, 'CAMERA_001', 'person_001_abc123', '+628123456789', 'Alert: Person detected at Main Entrance', 'events/2024/01/16/event_001.jpg', '2024-01-16 14:30:10', 'delivered', '{"message_id": "ABC123", "status": "delivered"}', NULL, 0, NOW(), NOW()),

-- Successful message without image
(2, 2, 1, 'CAMERA_002', NULL, '+628987654321', 'Motion detected at Parking Area', NULL, '2024-01-16 15:15:05', 'sent', '{"message_id": "DEF456", "status": "sent"}', NULL, 0, NOW(), NOW()),

-- Failed message (will retry)
(3, 3, 2, 'CAMERA_003', 'person_001_abc123', '+628111222333', 'Alert: Person detected at Lobby', 'events/2024/01/16/event_003.jpg', '2024-01-16 16:20:00', 'failed', NULL, 'Connection timeout', 2, NOW(), NOW()),

-- Read status
(4, 1, 1, 'CAMERA_001', 'person_002_def456', '+628123456789', 'Alert: Unauthorized person detected', 'events/2024/01/16/event_004.jpg', '2024-01-16 17:30:00', 'read', '{"message_id": "GHI789", "status": "read", "read_at": "2024-01-16 17:31:00"}', NULL, 0, NOW(), NOW());
```

### **Storage Files**

```sql
INSERT INTO storage_files VALUES
-- Event detection images
(1, 'events/2024/01/16/1705394400_abc123.jpg', 'detection_001.jpg', 'image/jpeg', 245678, 'local', 'event_logs', 1, NULL, false, '{"width": 1920, "height": 1080, "orientation": "landscape"}', NOW(), NOW()),
(2, 'events/2024/01/16/1705398000_def456.jpg', 'detection_002.jpg', 'image/jpeg', 198234, 'local', 'event_logs', 2, NULL, false, '{"width": 1280, "height": 720, "orientation": "landscape"}', NOW(), NOW()),
(3, 'events/2024/01/16/1705401600_ghi789.jpg', 'detection_003.jpg', 'image/jpeg', 312456, 'local', 'event_logs', 3, NULL, false, '{"width": 1920, "height": 1080, "orientation": "landscape"}', NOW(), NOW()),

-- WhatsApp sent images (same as event images, different related_table)
(4, 'events/2024/01/16/1705394400_abc123.jpg', 'detection_001.jpg', 'image/jpeg', 245678, 'local', 'whatsapp_message_logs', 1, NULL, false, '{"width": 1920, "height": 1080, "orientation": "landscape"}', NOW(), NOW()),

-- User uploaded files
(5, 'uploads/2024/01/16/user_upload_001.png', 'screenshot.png', 'image/png', 567890, 'public', 'users', 1, 1, true, '{"width": 2560, "height": 1440, "orientation": "landscape"}', NOW(), NOW());
```

---

## 🔍 Advanced Query Examples

### **Get RE_ID Summary (Daily)**

```sql
SELECT
    rim.re_id,
    rim.detection_date,
    rim.detection_time,
    rim.person_name,
    rim.total_actual_count,
    rim.total_detection_branch_count,
    rim.status,
    rim.first_detected_at,
    rim.last_detected_at
FROM re_id_masters rim
WHERE rim.detection_date = CURRENT_DATE
  AND rim.status = 'active'
ORDER BY rim.total_detection_branch_count DESC, rim.detection_time ASC;
```

### **Get RE_ID History (Multi-day)**

```sql
SELECT
    rim.re_id,
    rim.detection_date,
    rim.detection_time,
    rim.person_name,
    rim.total_actual_count,
    rim.total_detection_branch_count,
    rim.status,
    rim.first_detected_at,
    rim.last_detected_at
FROM re_id_masters rim
WHERE rim.re_id = 'person_001_abc123'
  AND rim.detection_date BETWEEN '2024-01-01' AND '2024-01-31'
ORDER BY rim.detection_date DESC, rim.detection_time DESC;
```

### **Get Hourly Detection Pattern (Today)**

```sql
SELECT
    DATE_TRUNC('hour', rim.detection_time) as hour,
    COUNT(DISTINCT rim.re_id) as unique_persons,
    SUM(rim.total_actual_count) as total_detections,
    AVG(rim.total_detection_branch_count) as avg_branch_count
FROM re_id_masters rim
WHERE rim.detection_date = CURRENT_DATE
  AND rim.status = 'active'
GROUP BY DATE_TRUNC('hour', rim.detection_time)
ORDER BY hour DESC;
```

### **Get Branch Performance**

```sql
SELECT
    cb.branch_name,
    cb.city_name,
    COUNT(DISTINCT rbd.re_id) as unique_re_id_count,
    SUM(rbd.detected_count) as total_detected_count
FROM company_branches cb
LEFT JOIN re_id_branch_detections rbd ON cb.id = rbd.branch_id
    AND DATE(rbd.detection_timestamp) = CURDATE()
WHERE cb.status = 'active'
GROUP BY cb.id, cb.branch_name, cb.city_name;
```

### **Get Complete Event Report**

```sql
SELECT
    el.id as event_id,
    el.event_type,
    el.detected_count,
    cb.branch_name,
    dm.device_name,
    dm.device_id,
    rim.re_id,
    rim.person_name,
    el.event_timestamp,
    el.image_path,
    el.image_sent,
    el.message_sent,
    el.notification_sent,
    bes.whatsapp_enabled,
    bes.whatsapp_numbers
FROM event_logs el
JOIN company_branches cb ON el.branch_id = cb.id
JOIN device_masters dm ON el.device_id = dm.device_id
LEFT JOIN re_id_masters rim ON el.re_id = rim.re_id
LEFT JOIN branch_event_settings bes ON el.branch_id = bes.branch_id AND el.device_id = bes.device_id
WHERE el.event_timestamp >= CURDATE()
ORDER BY el.event_timestamp DESC
LIMIT 50;
```

### **Get Branch Performance with Events**

```sql
SELECT
    cb.branch_name,
    cb.city_name,
    COUNT(DISTINCT rbd.device_id) as unique_devices,
    COUNT(DISTINCT rbd.re_id) as unique_persons,
    SUM(rbd.detected_count) as total_detections,
    COUNT(DISTINCT el.id) as total_events,
    SUM(CASE WHEN el.notification_sent = true THEN 1 ELSE 0 END) as notifications_sent
FROM company_branches cb
LEFT JOIN re_id_branch_detections rbd ON cb.id = rbd.branch_id
    AND DATE(rbd.detection_timestamp) = CURDATE()
LEFT JOIN event_logs el ON cb.id = el.branch_id
    AND DATE(el.event_timestamp) = CURDATE()
WHERE cb.status = 'active'
GROUP BY cb.id, cb.branch_name, cb.city_name
ORDER BY total_detections DESC;
```

### **Get Branch Detection Counts (NEW - Branch Detection Summary)**

```sql
-- ✅ Get branch detection counts for a specific person and date
-- Used in ReIdMasterService::getBranchDetectionCounts()
SELECT
    cb.id as branch_id,
    cb.branch_name,
    cb.branch_code,
    COUNT(rbd.id) as detection_count,
    SUM(rbd.detected_count) as total_detected_count,
    MIN(rbd.detection_timestamp) as first_detection,
    MAX(rbd.detection_timestamp) as last_detection
FROM re_id_branch_detections rbd
JOIN company_branches cb ON rbd.branch_id = cb.id
WHERE rbd.re_id = 'person_001_abc123'
  AND DATE(rbd.detection_timestamp) = '2024-01-16'
GROUP BY cb.id, cb.branch_name, cb.branch_code
ORDER BY total_detected_count DESC;
```

**Purpose:** Provides aggregated branch detection statistics for the Branch Detection Summary table in `/re-id-masters/` detail page.

**Fields:**
- `detection_count`: Number of detection events per branch
- `total_detected_count`: Sum of all detected_count values per branch
- `first_detection`: Earliest detection timestamp per branch
- `last_detection`: Latest detection timestamp per branch

### **Get API Usage Statistics**

```sql
SELECT
    ac.credential_name,
    ac.api_key,
    cb.branch_name,
    dm.device_id,
    rim.re_id,
    COUNT(arl.id) as total_requests,
    AVG(arl.response_time_ms) as avg_response_time,
    SUM(CASE WHEN arl.response_status = 200 THEN 1 ELSE 0 END) as successful_requests,
    SUM(CASE WHEN arl.response_status >= 400 THEN 1 ELSE 0 END) as failed_requests,
    ac.rate_limit,
    ac.last_used_at
FROM api_credentials ac
LEFT JOIN company_branches cb ON ac.branch_id = cb.id
LEFT JOIN device_masters dm ON ac.device_id = dm.device_id
LEFT JOIN re_id_masters rim ON ac.re_id = rim.re_id
LEFT JOIN api_request_logs arl ON ac.id = arl.api_credential_id
    AND arl.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
WHERE ac.is_active = true
GROUP BY ac.id, ac.credential_name, ac.api_key, cb.branch_name, dm.device_id, rim.re_id, ac.rate_limit, ac.last_used_at
ORDER BY total_requests DESC;
```

### **Get CCTV Stream Status Dashboard**

```sql
SELECT
    cb.branch_name,
    cs.stream_name,
    cs.device_id,
    cs.stream_type,
    cs.position,
    cs.status,
    cs.resolution,
    cs.fps,
    cs.last_checked_at,
    TIMESTAMPDIFF(MINUTE, cs.last_checked_at, NOW()) as minutes_since_check,
    dm.device_type
FROM cctv_streams cs
JOIN company_branches cb ON cs.branch_id = cb.id
JOIN device_masters dm ON cs.device_id = dm.device_id
WHERE cs.is_active = true
ORDER BY cb.branch_name, cs.position;
```

### **Get Hourly Detection Trend**

```sql
SELECT
    DATE(event_timestamp) as date,
    HOUR(event_timestamp) as hour,
    COUNT(*) as event_count,
    SUM(detected_count) as total_detections,
    COUNT(DISTINCT re_id) as unique_devices,
    COUNT(DISTINCT branch_id) as active_branches
FROM event_logs
WHERE event_timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY DATE(event_timestamp), HOUR(event_timestamp)
ORDER BY date DESC, hour DESC;
```

---

## 🎯 Key Features Summary

### **✅ RE_ID Integration**

- **device_masters**: Primary RE_ID registry
- **device_branch_detection**: RE_ID detection logs
- **branch_event_settings**: RE_ID event configuration
- **event_logs**: RE_ID event tracking
- **api_credentials**: RE_ID scoping
- **cctv_streams**: RE_ID stream management

### **✅ WhatsApp Simple ON/OFF**

- **branch_event_settings**: `whatsapp_enabled` boolean
- **whatsapp_settings**: Provider configuration
- **event_logs**: `notification_sent` boolean
- **No delivery tracking**: Fire and forget approach

### **✅ Enhanced Counting Logic**

- Each branch that can read RE_ID = 1 count
- Actual detected count tracked separately
- Total actual count aggregated in master table

### **✅ Complete API Coverage**

- Device counting with RE_ID
- Event management with RE_ID
- CCTV stream management with RE_ID
- API credential scoping with RE_ID

### **✅ Performance Optimizations**

- Indexed queries for all foreign keys
- Report caching for fast retrieval
- Partitioning strategy for large tables
- Archiving strategy for old data

---

## 📊 Database Summary

| Category     | Tables | Key Features                                       |
| ------------ | ------ | -------------------------------------------------- |
| **Core**     | 5      | Groups → Branches → Devices + Re-ID → Detection    |
| **Events**   | 2      | Event settings + Event logs (with RE_ID)           |
| **Security** | 2      | API credentials + users (api_usage_summary)        |
| **CCTV**     | 1      | Stream management (with RE_ID)                     |
| **Reports**  | 1      | Pre-computed report cache                          |
| **WhatsApp** | 1      | WhatsApp daily summary (whatsapp_delivery_summary) |
| **Storage**  | 1      | File storage registry (images, videos)             |
| **Layout**   | 2      | CCTV layout management (4/6/8 windows)             |
| **Queue**    | 2      | jobs + failed_jobs (Laravel default)               |
| **TOTAL**    | **17** | **Optimized System with File-based Logs**          |

**Notes:**

- ✅ **Raw Logs**: API requests and WhatsApp messages stored in **daily log files**, not database
- ✅ **Database**: Only stores **aggregated summaries** (api_usage_summary, whatsapp_delivery_summary)
- ✅ **Encryption**: Sensitive data encrypted via `.env` (ENV-based), not database table
- ✅ **Scalability**: File-based logs prevent database bloat for high-volume operations

---

## 🔐 Encryption Implementation (ENV-based)

### **Environment Configuration**

Add to `.env` file:

```env
# Encryption Settings
ENCRYPT_DEVICE_CREDENTIALS=true
ENCRYPT_STREAM_CREDENTIALS=true
ENCRYPTION_METHOD=AES-256-CBC
```

### **Laravel Encryption Helper**

```php
// app/Helpers/EncryptionHelper.php
namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;

class EncryptionHelper
{
    /**
     * Check if encryption is enabled for specific table
     */
    private static function isEncryptionEnabled(string $tableName): bool
    {
        return match($tableName) {
            'device_masters' => env('ENCRYPT_DEVICE_CREDENTIALS', true),
            'cctv_streams' => env('ENCRYPT_STREAM_CREDENTIALS', true),
            default => false
        };
    }

    /**
     * Get encrypted columns for table
     */
    private static function getEncryptedColumns(string $tableName): array
    {
        return match($tableName) {
            'device_masters' => ['password', 'username'],
            'cctv_streams' => ['stream_password', 'stream_username'],
            default => []
        };
    }

    /**
     * Encrypt sensitive data based on ENV settings
     */
    public static function encryptField(string $tableName, string $columnName, ?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }

        // Check if encryption is enabled via ENV
        if (!self::isEncryptionEnabled($tableName)) {
            return $value; // Encryption disabled in ENV
        }

        // Check if column should be encrypted
        $encryptedColumns = self::getEncryptedColumns($tableName);

        if (!in_array($columnName, $encryptedColumns)) {
            return $value; // Column not in encrypted list
        }

        // Encrypt using Laravel's Crypt facade (uses APP_KEY from .env)
        return Crypt::encryptString($value);
    }

    /**
     * Decrypt sensitive data
     */
    public static function decryptField(string $tableName, string $columnName, ?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }

        try {
            // Check if encryption is enabled via ENV
            if (!self::isEncryptionEnabled($tableName)) {
                return $value; // Encryption disabled in ENV
            }

            // Check if column should be decrypted
            $encryptedColumns = self::getEncryptedColumns($tableName);

            if (!in_array($columnName, $encryptedColumns)) {
                return $value; // Column not in encrypted list
            }

            // Decrypt using Laravel's Crypt facade (uses APP_KEY from .env)
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            // Log error and return null for security
            \Log::error('Decryption failed', [
                'table' => $tableName,
                'column' => $columnName,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
```

### **Model with Auto-Encryption**

```php
// app/Models/DeviceMaster.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\EncryptionHelper;

class DeviceMaster extends Model
{
    protected $table = 'device_masters';

    protected $fillable = [
        'device_id', 'device_name', 'device_type', 'branch_id',
        'url', 'username', 'password', 'notes', 'status'
    ];

    // ✅ Auto-encrypt when saving
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = EncryptionHelper::encryptField(
            'device_masters',
            'password',
            $value
        );
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = EncryptionHelper::encryptField(
            'device_masters',
            'username',
            $value
        );
    }

    // ✅ Auto-decrypt when reading
    public function getPasswordAttribute($value)
    {
        return EncryptionHelper::decryptField(
            'device_masters',
            'password',
            $value
        );
    }

    public function getUsernameAttribute($value)
    {
        return EncryptionHelper::decryptField(
            'device_masters',
            'username',
            $value
        );
    }
}
```

```php
// app/Models/CctvStream.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\EncryptionHelper;

class CctvStream extends Model
{
    protected $table = 'cctv_streams';

    protected $fillable = [
        'branch_id', 'device_id', 'stream_name', 'stream_url',
        'stream_type', 'stream_username', 'stream_password',
        'stream_port', 'is_active', 'position', 'resolution',
        'fps', 'bitrate', 'status'
    ];

    // ✅ Auto-encrypt when saving
    public function setStreamPasswordAttribute($value)
    {
        $this->attributes['stream_password'] = EncryptionHelper::encryptField(
            'cctv_streams',
            'stream_password',
            $value
        );
    }

    public function setStreamUsernameAttribute($value)
    {
        $this->attributes['stream_username'] = EncryptionHelper::encryptField(
            'cctv_streams',
            'stream_username',
            $value
        );
    }

    // ✅ Auto-decrypt when reading
    public function getStreamPasswordAttribute($value)
    {
        return EncryptionHelper::decryptField(
            'cctv_streams',
            'stream_password',
            $value
        );
    }

    public function getStreamUsernameAttribute($value)
    {
        return EncryptionHelper::decryptField(
            'cctv_streams',
            'stream_username',
            $value
        );
    }
}
```

### **Usage Examples**

```php
// ✅ CREATE - Auto-encrypt
$device = DeviceMaster::create([
    'device_id' => 'CAMERA_001',
    'device_name' => 'Main Entrance Camera',
    'username' => 'admin',  // ← Will be encrypted automatically
    'password' => 'secret123',  // ← Will be encrypted automatically
    'url' => 'rtsp://192.168.1.100:554/stream1'
]);

// ✅ READ - Auto-decrypt
$device = DeviceMaster::find(1);
echo $device->username;  // ← Returns decrypted value: "admin"
echo $device->password;  // ← Returns decrypted value: "secret123"

// ✅ UPDATE - Auto-encrypt
$device->update([
    'password' => 'new_password'  // ← Will be encrypted automatically
]);

// ✅ Toggle Encryption ON/OFF via .env
// ENCRYPT_DEVICE_CREDENTIALS=false  ← Disable in .env
// ENCRYPT_STREAM_CREDENTIALS=false  ← Disable in .env
// Then run: php artisan config:clear
```

### **Configuration Check**

```php
// Check if encryption is enabled
$deviceEncryption = env('ENCRYPT_DEVICE_CREDENTIALS', true);
$streamEncryption = env('ENCRYPT_STREAM_CREDENTIALS', true);

// View current settings (admin only)
Route::get('/admin/encryption/status', function() {
    return response()->json([
        'device_credentials_encrypted' => env('ENCRYPT_DEVICE_CREDENTIALS', true),
        'stream_credentials_encrypted' => env('ENCRYPT_STREAM_CREDENTIALS', true),
        'encryption_method' => env('ENCRYPTION_METHOD', 'AES-256-CBC'),
        'app_key_set' => !empty(config('app.key'))
    ]);
});
```

---

## 📱 WhatsApp Implementation

### **Environment Configuration**

Add to `.env` file:

```env
# WhatsApp Provider Settings
WHATSAPP_PROVIDER=waha  # or 'twilio', 'fonnte', etc.
WHATSAPP_API_URL=http://localhost:3000
WHATSAPP_API_KEY=your_waha_api_key_here
WHATSAPP_SESSION_NAME=default
WHATSAPP_RETRY_ATTEMPTS=3
WHATSAPP_TIMEOUT=30
```

### **WhatsApp Helper**

```php
// app/Helpers/WhatsAppHelper.php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsAppMessageLog;
use App\Models\StorageFile;

class WhatsAppHelper
{
    /**
     * Send WhatsApp message with optional image
     */
    public static function sendMessage(
        string $phoneNumber,
        string $message,
        ?string $imagePath = null,
        array $metadata = []
    ): array {
        try {
            $apiUrl = env('WHATSAPP_API_URL');
            $apiKey = env('WHATSAPP_API_KEY');
            $sessionName = env('WHATSAPP_SESSION_NAME', 'default');

            // Prepare payload
            $payload = [
                'chatId' => self::formatPhoneNumber($phoneNumber),
                'text' => $message,
                'session' => $sessionName
            ];

            // Add image if provided
            if ($imagePath && file_exists(storage_path('app/' . $imagePath))) {
                $payload['file'] = [
                    'mimetype' => 'image/jpeg',
                    'filename' => basename($imagePath),
                    'data' => base64_encode(file_get_contents(storage_path('app/' . $imagePath)))
                ];
            }

            // Send request to WAHA API
            $response = Http::withHeaders([
                'X-Api-Key' => $apiKey,
                'Content-Type' => 'application/json'
            ])
            ->timeout(env('WHATSAPP_TIMEOUT', 30))
            ->post($apiUrl . '/api/sendText', $payload);

            // Log the message
            $log = WhatsAppMessageLog::create([
                'event_log_id' => $metadata['event_log_id'] ?? null,
                'branch_id' => $metadata['branch_id'],
                'device_id' => $metadata['device_id'],
                're_id' => $metadata['re_id'] ?? null,
                'phone_number' => $phoneNumber,
                'message_text' => $message,
                'image_path' => $imagePath,
                'sent_at' => now(),
                'status' => $response->successful() ? 'sent' : 'failed',
                'provider_response' => $response->json(),
                'error_message' => $response->failed() ? $response->body() : null,
                'retry_count' => 0
            ]);

            return [
                'success' => $response->successful(),
                'message_log_id' => $log->id,
                'status' => $log->status,
                'response' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp send failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            // Log failed attempt
            $log = WhatsAppMessageLog::create([
                'event_log_id' => $metadata['event_log_id'] ?? null,
                'branch_id' => $metadata['branch_id'],
                'device_id' => $metadata['device_id'],
                're_id' => $metadata['re_id'] ?? null,
                'phone_number' => $phoneNumber,
                'message_text' => $message,
                'image_path' => $imagePath,
                'sent_at' => now(),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'retry_count' => 0
            ]);

            return [
                'success' => false,
                'message_log_id' => $log->id,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number for WhatsApp
     */
    private static function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if not present (Indonesia: 62)
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . ltrim($phone, '0');
        }

        return $phone . '@c.us';  // WhatsApp format
    }

    /**
     * Retry failed messages
     */
    public static function retryFailed(int $messageLogId): array
    {
        $log = WhatsAppMessageLog::findOrFail($messageLogId);

        if ($log->retry_count >= env('WHATSAPP_RETRY_ATTEMPTS', 3)) {
            return [
                'success' => false,
                'message' => 'Max retry attempts reached'
            ];
        }

        // Increment retry count
        $log->increment('retry_count');

        // Resend message
        return self::sendMessage(
            $log->phone_number,
            $log->message_text,
            $log->image_path,
            [
                'event_log_id' => $log->event_log_id,
                'branch_id' => $log->branch_id,
                'device_id' => $log->device_id,
                're_id' => $log->re_id
            ]
        );
    }
}
```

### **Usage Examples**

```php
use App\Helpers\WhatsAppHelper;

// Send simple text message
WhatsAppHelper::sendMessage(
    '+628123456789',
    'Alert: Person detected at Main Entrance',
    null,
    [
        'branch_id' => 1,
        'device_id' => 'CAMERA_001',
        're_id' => 'person_001_abc123',
        'event_log_id' => 123
    ]
);

// Send message with image
WhatsAppHelper::sendMessage(
    '+628123456789',
    'Alert: Person detected at Main Entrance',
    'events/2024/01/16/detection_001.jpg',  // Image path
    [
        'branch_id' => 1,
        'device_id' => 'CAMERA_001',
        're_id' => 'person_001_abc123',
        'event_log_id' => 123
    ]
);

// Retry failed message
WhatsAppHelper::retryFailed(456);  // message_log_id
```

---

## 📦 Storage Implementation

### **Environment Configuration**

Add to `.env` file:

```env
# Storage Settings
FILESYSTEM_DISK=local  # or 's3', 'public'
STORAGE_MAX_FILE_SIZE=10240  # KB (10MB)
STORAGE_ALLOWED_TYPES=jpg,jpeg,png,mp4,avi
STORAGE_AUTO_CLEANUP_DAYS=90  # Auto-delete files older than 90 days
```

### **Storage Helper**

```php
// app/Helpers/StorageHelper.php
namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\StorageFile;
use Intervention\Image\Facades\Image;

class StorageHelper
{
    /**
     * Store file and create database record
     */
    public static function storeFile(
        UploadedFile $file,
        string $directory,
        string $relatedTable,
        int $relatedId,
        ?int $uploadedBy = null
    ): StorageFile {
        // Generate unique filename
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Organize by date
        $datePath = date('Y/m/d');
        $fullPath = "$directory/$datePath/$fileName";

        // Store file
        $disk = env('FILESYSTEM_DISK', 'local');
        Storage::disk($disk)->put($fullPath, file_get_contents($file));

        // Get metadata
        $metadata = self::getFileMetadata($file, $fullPath);

        // Create database record
        $storageFile = StorageFile::create([
            'file_path' => $fullPath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'storage_disk' => $disk,
            'related_table' => $relatedTable,
            'related_id' => $relatedId,
            'uploaded_by' => $uploadedBy,
            'is_public' => $disk === 'public',
            'metadata' => $metadata
        ]);

        return $storageFile;
    }

    /**
     * Get file metadata (dimensions, duration, etc.)
     */
    private static function getFileMetadata(UploadedFile $file, string $path): array
    {
        $metadata = [];

        // Image metadata
        if (str_starts_with($file->getMimeType(), 'image/')) {
            try {
                $image = Image::make($file);
                $metadata = [
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'orientation' => $image->width() > $image->height() ? 'landscape' : 'portrait'
                ];
            } catch (\Exception $e) {
                // Skip if image processing fails
            }
        }

        return $metadata;
    }

    /**
     * Delete file and database record
     */
    public static function deleteFile(int $storageFileId): bool
    {
        $storageFile = StorageFile::find($storageFileId);

        if (!$storageFile) {
            return false;
        }

        // Delete physical file
        Storage::disk($storageFile->storage_disk)->delete($storageFile->file_path);

        // Delete database record
        $storageFile->delete();

        return true;
    }

    /**
     * Get file URL
     */
    public static function getFileUrl(string $filePath, string $disk = 'local'): string
    {
        if ($disk === 'public') {
            return Storage::disk('public')->url($filePath);
        }

        if ($disk === 's3') {
            return Storage::disk('s3')->temporaryUrl(
                $filePath,
                now()->addMinutes(30)  // Temporary URL valid for 30 minutes
            );
        }

        // Local disk - generate route
        return route('storage.file', ['path' => encrypt($filePath)]);
    }

    /**
     * Clean up old files (run via cron)
     */
    public static function cleanupOldFiles(): int
    {
        $days = env('STORAGE_AUTO_CLEANUP_DAYS', 90);
        $cutoffDate = now()->subDays($days);

        $oldFiles = StorageFile::where('created_at', '<', $cutoffDate)->get();

        $deletedCount = 0;
        foreach ($oldFiles as $file) {
            if (self::deleteFile($file->id)) {
                $deletedCount++;
            }
        }

        Log::info("Storage cleanup completed", [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate
        ]);

        return $deletedCount;
    }

    /**
     * Get storage statistics
     */
    public static function getStorageStats(): array
    {
        return [
            'total_files' => StorageFile::count(),
            'total_size_mb' => round(StorageFile::sum('file_size') / 1024 / 1024, 2),
            'by_type' => StorageFile::selectRaw('file_type, COUNT(*) as count, SUM(file_size) as size')
                ->groupBy('file_type')
                ->get(),
            'by_disk' => StorageFile::selectRaw('storage_disk, COUNT(*) as count, SUM(file_size) as size')
                ->groupBy('storage_disk')
                ->get()
        ];
    }
}
```

### **Usage Examples**

```php
use App\Helpers\StorageHelper;

// ✅ STORE FILE
$request->validate([
    'image' => 'required|image|max:10240'  // Max 10MB
]);

$storageFile = StorageHelper::storeFile(
    $request->file('image'),
    'events',  // Directory
    'event_logs',  // Related table
    $eventLog->id,  // Related ID
    auth()->id()  // Uploaded by
);

// ✅ GET FILE URL
$url = StorageHelper::getFileUrl($storageFile->file_path, $storageFile->storage_disk);

// ✅ DELETE FILE
StorageHelper::deleteFile($storageFile->id);

// ✅ CLEANUP OLD FILES (cron job)
// Schedule in app/Console/Kernel.php:
// $schedule->call(fn() => StorageHelper::cleanupOldFiles())->daily();

// ✅ GET STORAGE STATS
$stats = StorageHelper::getStorageStats();
/*
{
    "total_files": 1250,
    "total_size_mb": 3450.75,
    "by_type": [...],
    "by_disk": [...]
}
*/
```

### **File Controller (Secure File Access)**

```php
// app/Http/Controllers/StorageController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\StorageFile;

class StorageController extends Controller
{
    /**
     * Serve file securely
     */
    public function show(Request $request)
    {
        try {
            $filePath = decrypt($request->path);

            $storageFile = StorageFile::where('file_path', $filePath)->firstOrFail();

            // Check authorization (optional)
            // if (!$this->canAccessFile($storageFile)) {
            //     abort(403);
            // }

            $disk = $storageFile->storage_disk;

            if (!Storage::disk($disk)->exists($filePath)) {
                abort(404, 'File not found');
            }

            return response()->file(
                Storage::disk($disk)->path($filePath),
                [
                    'Content-Type' => $storageFile->file_type,
                    'Content-Disposition' => 'inline; filename="' . $storageFile->file_name . '"'
                ]
            );

        } catch (\Exception $e) {
            abort(404, 'File not found');
        }
    }
}

// routes/web.php
Route::get('/storage/file', [StorageController::class, 'show'])
    ->name('storage.file')
    ->middleware('auth');
```

### **Integration with Event Processing**

```php
// Process detection event with WhatsApp notification
use App\Helpers\WhatsAppHelper;
use App\Helpers\StorageHelper;

public function processDetection($reId, $branchId, $deviceId, $image)
{
    // 1. Store image
    $storageFile = StorageHelper::storeFile(
        $image,
        'events',
        'event_logs',
        $eventLog->id,
        null  // System upload
    );

    // 2. Update event log with image path
    $eventLog->update([
        'image_path' => $storageFile->file_path,
        'image_sent' => false
    ]);

    // 3. Get WhatsApp settings
    $settings = BranchEventSetting::where('branch_id', $branchId)
        ->where('device_id', $deviceId)
        ->first();

    if ($settings && $settings->whatsapp_enabled) {
        $phoneNumbers = $settings->whatsapp_numbers ?? [];

        foreach ($phoneNumbers as $phone) {
            // 4. Send WhatsApp with image
            $result = WhatsAppHelper::sendMessage(
                $phone,
                $settings->message_template,
                $storageFile->file_path,
                [
                    'event_log_id' => $eventLog->id,
                    'branch_id' => $branchId,
                    'device_id' => $deviceId,
                    're_id' => $reId
                ]
            );

            if ($result['success']) {
                $eventLog->update([
                    'notification_sent' => true,
                    'image_sent' => true
                ]);
            }
        }
    }
}
```

---

## 🔄 Queue Jobs Implementation

### **Queue Jobs Architecture**

All heavy operations should be processed asynchronously using Laravel Queue system:

```
┌─────────────────────────────────────────────────────────────────┐
│                    Queue Jobs Architecture                       │
├─────────────────────────────────────────────────────────────────┤
│  API Request → Validate → Dispatch Job → Return Response        │
│                              ↓                                   │
│                    Queue Worker Process Job                      │
│                              ↓                                   │
│              Update Database → Trigger Events                    │
│                              ↓                                   │
│              Send Notifications (WhatsApp, etc.)                 │
└─────────────────────────────────────────────────────────────────┘
```

---

### **1. Detection Processing Job**

```php
// app/Jobs/ProcessDetectionJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ReIdMaster;
use App\Models\ReIdBranchDetection;
use App\Models\EventLog;
use App\Jobs\SendWhatsAppNotificationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Job configuration
     */
    public $tries = 3;  // Retry 3 times if failed
    public $timeout = 120;  // Timeout after 2 minutes
    public $backoff = [10, 30, 60];  // Retry delays (10s, 30s, 60s)
    public $maxExceptions = 3;  // Max exceptions before failing

    /**
     * Job properties
     */
    public string $reId;
    public int $branchId;
    public string $deviceId;
    public int $detectedCount;
    public ?array $detectionData;
    public ?string $imagePath;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $reId,
        int $branchId,
        string $deviceId,
        int $detectedCount = 1,
        ?array $detectionData = null,
        ?string $imagePath = null
    ) {
        $this->reId = $reId;
        $this->branchId = $branchId;
        $this->deviceId = $deviceId;
        $this->detectedCount = $detectedCount;
        $this->detectionData = $detectionData;
        $this->imagePath = $imagePath;

        // Set queue and priority
        $this->onQueue('detections');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();

        try {
            $today = now()->toDateString();

            // 1. Create or update re_id_master (person) for today
            $reIdMaster = ReIdMaster::firstOrCreate(
                [
                    're_id' => $this->reId,
                    'detection_date' => $today
                ],
                [
                    'detection_time' => now(),
                    'first_detected_at' => now(),
                    'last_detected_at' => now(),
                    'total_actual_count' => 0,
                    'total_detection_branch_count' => 0,
                    'status' => 'active'
                ]
            );

            // Check if tracking is active
            if ($reIdMaster->status !== 'active') {
                Log::info('Detection skipped - tracking disabled', [
                    're_id' => $this->reId,
                    'status' => $reIdMaster->status
                ]);
                DB::rollBack();
                return;
            }

            // 2. Update master tracking data
            $reIdMaster->increment('total_actual_count', $this->detectedCount);
            $reIdMaster->update(['last_detected_at' => now()]);

            // 3. Create detection record
            $detection = ReIdBranchDetection::create([
                're_id' => $this->reId,
                'branch_id' => $this->branchId,
                'device_id' => $this->deviceId,
                'detected_count' => $this->detectedCount,
                'detection_timestamp' => now(),
                'detection_data' => $this->detectionData,
                'status' => 'active'
            ]);

            // 4. Update unique branch count
            $uniqueBranchCount = ReIdBranchDetection::where('re_id', $this->reId)
                ->whereDate('detection_timestamp', $today)
                ->distinct('branch_id')
                ->count('branch_id');

            $reIdMaster->update(['total_detection_branch_count' => $uniqueBranchCount]);

            // 5. Create event log
            $eventLog = EventLog::create([
                'branch_id' => $this->branchId,
                'device_id' => $this->deviceId,
                're_id' => $this->reId,
                'event_type' => 'detection',
                'detected_count' => $this->detectedCount,
                'image_path' => $this->imagePath,
                'image_sent' => false,
                'message_sent' => false,
                'notification_sent' => false,
                'event_data' => $this->detectionData,
                'event_timestamp' => now()
            ]);

            DB::commit();

            // 6. Dispatch WhatsApp notification job (async)
            SendWhatsAppNotificationJob::dispatch(
                $eventLog->id,
                $this->branchId,
                $this->deviceId,
                $this->reId
            )->onQueue('notifications');

            // 7. Dispatch report update job (async)
            UpdateDailyReportJob::dispatch(
                $this->branchId,
                $today
            )->onQueue('reports')->delay(now()->addMinutes(5));

            Log::info('Detection processed successfully', [
                're_id' => $this->reId,
                'branch_id' => $this->branchId,
                'device_id' => $this->deviceId,
                'detection_id' => $detection->id,
                'event_log_id' => $eventLog->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Detection processing failed', [
                're_id' => $this->reId,
                'branch_id' => $this->branchId,
                'device_id' => $this->deviceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Detection job failed after retries', [
            're_id' => $this->reId,
            'branch_id' => $this->branchId,
            'device_id' => $this->deviceId,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);

        // Optionally: Send alert to admin
        // NotifyAdminJob::dispatch('Detection processing failed', $exception);
    }

    /**
     * Get the tags for the job (for monitoring).
     */
    public function tags(): array
    {
        return [
            'detection',
            're_id:' . $this->reId,
            'branch:' . $this->branchId,
            'device:' . $this->deviceId
        ];
    }
}
```

---

### **2. WhatsApp Notification Job**

```php
// app/Jobs/SendWhatsAppNotificationJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\EventLog;
use App\Models\BranchEventSetting;
use App\Helpers\WhatsAppHelper;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;  // More retries for external API
    public $timeout = 60;
    public $backoff = [30, 60, 120, 300, 600];  // Exponential backoff

    public int $eventLogId;
    public int $branchId;
    public string $deviceId;
    public ?string $reId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $eventLogId,
        int $branchId,
        string $deviceId,
        ?string $reId = null
    ) {
        $this->eventLogId = $eventLogId;
        $this->branchId = $branchId;
        $this->deviceId = $deviceId;
        $this->reId = $reId;

        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // 1. Get event log
            $eventLog = EventLog::find($this->eventLogId);

            if (!$eventLog) {
                Log::warning('Event log not found', ['event_log_id' => $this->eventLogId]);
                return;
            }

            // 2. Get WhatsApp settings
            $settings = BranchEventSetting::where('branch_id', $this->branchId)
                ->where('device_id', $this->deviceId)
                ->first();

            if (!$settings || !$settings->whatsapp_enabled) {
                Log::info('WhatsApp disabled for this device', [
                    'branch_id' => $this->branchId,
                    'device_id' => $this->deviceId
                ]);
                return;
            }

            // 3. Get phone numbers
            $phoneNumbers = $settings->whatsapp_numbers ?? [];

            if (empty($phoneNumbers)) {
                Log::warning('No WhatsApp numbers configured', [
                    'branch_id' => $this->branchId,
                    'device_id' => $this->deviceId
                ]);
                return;
            }

            // 4. Prepare message
            $message = $this->prepareMessage($settings->message_template, $eventLog);

            // 5. Send to each number
            foreach ($phoneNumbers as $phone) {
                $result = WhatsAppHelper::sendMessage(
                    $phone,
                    $message,
                    $eventLog->image_path,
                    [
                        'event_log_id' => $this->eventLogId,
                        'branch_id' => $this->branchId,
                        'device_id' => $this->deviceId,
                        're_id' => $this->reId
                    ]
                );

                if ($result['success']) {
                    Log::info('WhatsApp sent successfully', [
                        'event_log_id' => $this->eventLogId,
                        'phone' => $phone,
                        'message_log_id' => $result['message_log_id']
                    ]);
                } else {
                    Log::warning('WhatsApp send failed', [
                        'event_log_id' => $this->eventLogId,
                        'phone' => $phone,
                        'error' => $result['error'] ?? 'Unknown error'
                    ]);
                }
            }

            // 6. Update event log
            $eventLog->update([
                'notification_sent' => true,
                'image_sent' => !empty($eventLog->image_path)
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp notification job failed', [
                'event_log_id' => $this->eventLogId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Prepare message from template
     */
    private function prepareMessage(string $template, EventLog $eventLog): string
    {
        $branch = $eventLog->branch;
        $device = $eventLog->device;
        $reIdMaster = $eventLog->reIdMaster;

        $replacements = [
            '{branch_name}' => $branch->branch_name ?? 'Unknown Branch',
            '{device_name}' => $device->device_name ?? 'Unknown Device',
            '{device_id}' => $this->deviceId,
            '{re_id}' => $this->reId ?? 'N/A',
            '{person_name}' => $reIdMaster->person_name ?? 'Unknown Person',
            '{detected_count}' => $eventLog->detected_count,
            '{timestamp}' => $eventLog->event_timestamp->format('Y-m-d H:i:s'),
            '{date}' => $eventLog->event_timestamp->format('Y-m-d'),
            '{time}' => $eventLog->event_timestamp->format('H:i:s')
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('WhatsApp notification job failed permanently', [
            'event_log_id' => $this->eventLogId,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);
    }

    /**
     * Get the tags for the job.
     */
    public function tags(): array
    {
        return [
            'whatsapp',
            'notification',
            'event:' . $this->eventLogId,
            'branch:' . $this->branchId
        ];
    }
}
```

---

### **3. Image Processing Job**

```php
// app/Jobs/ProcessDetectionImageJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\StorageHelper;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessDetectionImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 180;  // 3 minutes for image processing
    public $backoff = [10, 30, 60];

    public string $imagePath;
    public int $eventLogId;
    public array $options;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $imagePath,
        int $eventLogId,
        array $options = []
    ) {
        $this->imagePath = $imagePath;
        $this->eventLogId = $eventLogId;
        $this->options = array_merge([
            'thumbnail' => true,
            'watermark' => true,
            'optimize' => true,
            'max_width' => 1920,
            'max_height' => 1080,
            'quality' => 85
        ], $options);

        $this->onQueue('images');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!Storage::disk('local')->exists($this->imagePath)) {
                Log::error('Image file not found', ['path' => $this->imagePath]);
                return;
            }

            $fullPath = Storage::disk('local')->path($this->imagePath);
            $image = Image::make($fullPath);

            // 1. Resize if too large
            if ($image->width() > $this->options['max_width'] ||
                $image->height() > $this->options['max_height']) {
                $image->resize(
                    $this->options['max_width'],
                    $this->options['max_height'],
                    function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    }
                );
            }

            // 2. Add watermark (timestamp + branch info)
            if ($this->options['watermark']) {
                $image->text(
                    now()->format('Y-m-d H:i:s'),
                    10,
                    $image->height() - 10,
                    function ($font) {
                        $font->file(public_path('fonts/arial.ttf'));
                        $font->size(16);
                        $font->color('#ffffff');
                        $font->align('left');
                        $font->valign('bottom');
                    }
                );
            }

            // 3. Optimize and save
            $image->save($fullPath, $this->options['quality']);

            // 4. Create thumbnail
            if ($this->options['thumbnail']) {
                $thumbnailPath = $this->createThumbnail($fullPath);

                Log::info('Thumbnail created', [
                    'original' => $this->imagePath,
                    'thumbnail' => $thumbnailPath
                ]);
            }

            // 5. Update file size in database
            $newSize = Storage::disk('local')->size($this->imagePath);

            Log::info('Image processed successfully', [
                'path' => $this->imagePath,
                'event_log_id' => $this->eventLogId,
                'original_size' => filesize($fullPath),
                'new_size' => $newSize,
                'dimensions' => $image->width() . 'x' . $image->height()
            ]);

        } catch (\Exception $e) {
            Log::error('Image processing failed', [
                'path' => $this->imagePath,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Create thumbnail
     */
    private function createThumbnail(string $fullPath): string
    {
        $pathInfo = pathinfo($this->imagePath);
        $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
        $thumbnailFullPath = Storage::disk('local')->path($thumbnailPath);

        $thumbnail = Image::make($fullPath);
        $thumbnail->fit(320, 240);
        $thumbnail->save($thumbnailFullPath, 80);

        return $thumbnailPath;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Image processing job failed', [
            'path' => $this->imagePath,
            'event_log_id' => $this->eventLogId,
            'error' => $exception->getMessage()
        ]);
    }

    /**
     * Get the tags for the job.
     */
    public function tags(): array
    {
        return ['image', 'processing', 'event:' . $this->eventLogId];
    }
}
```

---

### **4. Daily Report Update Job**

```php
// app/Jobs/UpdateDailyReportJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CountingReport;
use App\Models\ReIdBranchDetection;
use App\Models\EventLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateDailyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;  // 5 minutes for report generation
    public $backoff = [30, 60, 120];

    public int $branchId;
    public string $reportDate;

    /**
     * Create a new job instance.
     */
    public function __construct(int $branchId, string $reportDate)
    {
        $this->branchId = $branchId;
        $this->reportDate = $reportDate;

        $this->onQueue('reports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // 1. Calculate statistics
            $stats = $this->calculateStatistics();

            // 2. Generate report data
            $reportData = $this->generateReportData();

            // 3. Update or create report
            CountingReport::updateOrCreate(
                [
                    'report_type' => 'daily',
                    'report_date' => $this->reportDate,
                    'branch_id' => $this->branchId
                ],
                [
                    'total_devices' => $stats['total_devices'],
                    'total_detections' => $stats['total_detections'],
                    'total_events' => $stats['total_events'],
                    'unique_device_count' => $stats['unique_devices'],
                    'unique_person_count' => $stats['unique_persons'],
                    'report_data' => $reportData,
                    'generated_at' => now()
                ]
            );

            Log::info('Daily report updated', [
                'branch_id' => $this->branchId,
                'report_date' => $this->reportDate,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Daily report update failed', [
                'branch_id' => $this->branchId,
                'report_date' => $this->reportDate,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(): array
    {
        return [
            'total_devices' => DB::table('re_id_branch_detections')
                ->where('branch_id', $this->branchId)
                ->whereDate('detection_timestamp', $this->reportDate)
                ->distinct('device_id')
                ->count('device_id'),

            'total_detections' => DB::table('re_id_branch_detections')
                ->where('branch_id', $this->branchId)
                ->whereDate('detection_timestamp', $this->reportDate)
                ->count(),

            'total_events' => DB::table('event_logs')
                ->where('branch_id', $this->branchId)
                ->whereDate('event_timestamp', $this->reportDate)
                ->count(),

            'unique_devices' => DB::table('device_masters')
                ->where('branch_id', $this->branchId)
                ->where('status', 'active')
                ->count(),

            'unique_persons' => DB::table('re_id_branch_detections')
                ->where('branch_id', $this->branchId)
                ->whereDate('detection_timestamp', $this->reportDate)
                ->distinct('re_id')
                ->count('re_id')
        ];
    }

    /**
     * Generate detailed report data
     */
    private function generateReportData(): array
    {
        // Top persons detected
        $topPersons = DB::table('re_id_branch_detections')
            ->select('re_id', DB::raw('COUNT(*) as count'))
            ->where('branch_id', $this->branchId)
            ->whereDate('detection_timestamp', $this->reportDate)
            ->groupBy('re_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->toArray();

        // Hourly breakdown
        $hourlyBreakdown = DB::table('re_id_branch_detections')
            ->select(
                DB::raw('EXTRACT(HOUR FROM detection_timestamp) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->where('branch_id', $this->branchId)
            ->whereDate('detection_timestamp', $this->reportDate)
            ->groupBy(DB::raw('EXTRACT(HOUR FROM detection_timestamp)'))
            ->orderBy('hour')
            ->get()
            ->toArray();

        // Device breakdown
        $deviceBreakdown = DB::table('re_id_branch_detections')
            ->join('device_masters', 're_id_branch_detections.device_id', '=', 'device_masters.device_id')
            ->select(
                're_id_branch_detections.device_id',
                'device_masters.device_name',
                DB::raw('COUNT(*) as count')
            )
            ->where('re_id_branch_detections.branch_id', $this->branchId)
            ->whereDate('re_id_branch_detections.detection_timestamp', $this->reportDate)
            ->groupBy('re_id_branch_detections.device_id', 'device_masters.device_name')
            ->orderByDesc('count')
            ->get()
            ->toArray();

        // Peak hour
        $peakHour = collect($hourlyBreakdown)->sortByDesc('count')->first();

        return [
            'top_persons' => $topPersons,
            'hourly_breakdown' => $hourlyBreakdown,
            'device_breakdown' => $deviceBreakdown,
            'peak_hour' => $peakHour->hour ?? null,
            'peak_hour_count' => $peakHour->count ?? 0
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Daily report job failed', [
            'branch_id' => $this->branchId,
            'report_date' => $this->reportDate,
            'error' => $exception->getMessage()
        ]);
    }

    /**
     * Get the tags for the job.
     */
    public function tags(): array
    {
        return ['report', 'daily', 'branch:' . $this->branchId];
    }
}
```

---

### **5. Storage Cleanup Job**

```php
// app/Jobs/CleanupOldFilesJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\StorageHelper;
use Illuminate\Support\Facades\Log;

class CleanupOldFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;  // Only try once
    public $timeout = 600;  // 10 minutes

    public int $days;

    /**
     * Create a new job instance.
     */
    public function __construct(int $days = null)
    {
        $this->days = $days ?? env('STORAGE_AUTO_CLEANUP_DAYS', 90);
        $this->onQueue('maintenance');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting file cleanup', ['days' => $this->days]);

            $deletedCount = StorageHelper::cleanupOldFiles();

            Log::info('File cleanup completed', [
                'deleted_count' => $deletedCount,
                'days' => $this->days
            ]);

        } catch (\Exception $e) {
            Log::error('File cleanup failed', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Cleanup job failed', [
            'error' => $exception->getMessage()
        ]);
    }

    /**
     * Get the tags for the job.
     */
    public function tags(): array
    {
        return ['cleanup', 'maintenance', 'storage'];
    }
}
```

---

### **6. Retry Failed WhatsApp Messages Job**

```php
// app/Jobs/RetryFailedWhatsAppMessagesJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WhatsAppMessageLog;
use App\Helpers\WhatsAppHelper;
use Illuminate\Support\Facades\Log;

class RetryFailedWhatsAppMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get failed messages that can be retried
            $failedMessages = WhatsAppMessageLog::where('status', 'failed')
                ->where('retry_count', '<', env('WHATSAPP_RETRY_ATTEMPTS', 3))
                ->where('created_at', '>=', now()->subHours(24))  // Only last 24 hours
                ->orderBy('created_at', 'asc')
                ->limit(100)  // Process 100 at a time
                ->get();

            $retriedCount = 0;
            $successCount = 0;

            foreach ($failedMessages as $message) {
                $result = WhatsAppHelper::retryFailed($message->id);

                $retriedCount++;

                if ($result['success']) {
                    $successCount++;
                }
            }

            Log::info('Failed WhatsApp messages retry completed', [
                'total_retried' => $retriedCount,
                'successful' => $successCount,
                'failed' => $retriedCount - $successCount
            ]);

        } catch (\Exception $e) {
            Log::error('Failed messages retry job failed', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get the tags for the job.
     */
    public function tags(): array
    {
        return ['whatsapp', 'retry', 'maintenance'];
    }
}
```

---

### **7. Queue Configuration**

```php
// config/queue.php
return [
    'default' => env('QUEUE_CONNECTION', 'database'),

    'connections' => [
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
        ],
    ],

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'failed_jobs',
    ],
];
```

---

### **8. Queue Priorities**

```bash
# Run queue workers with priorities (highest to lowest)
php artisan queue:work --queue=critical,notifications,detections,images,reports,maintenance,default
```

**Queue Priority Order:**

1. **critical** - System critical operations
2. **notifications** - WhatsApp, Email notifications
3. **detections** - Real-time detection processing
4. **images** - Image processing
5. **reports** - Report generation
6. **maintenance** - Cleanup, optimization
7. **default** - Other operations

---

### **9. Supervisor Configuration**

```ini
# /etc/supervisor/conf.d/cctv-worker.conf

[program:cctv-worker-critical]
process_name=%(program_name)s_%(process_num)02d
command=php /home/nandzo/app/cctv_dashboard/artisan queue:work database --queue=critical --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/home/nandzo/app/cctv_dashboard/storage/logs/worker-critical.log
stopwaitsecs=3600

[program:cctv-worker-notifications]
process_name=%(program_name)s_%(process_num)02d
command=php /home/nandzo/app/cctv_dashboard/artisan queue:work database --queue=notifications --sleep=3 --tries=5 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/home/nandzo/app/cctv_dashboard/storage/logs/worker-notifications.log
stopwaitsecs=3600

[program:cctv-worker-detections]
process_name=%(program_name)s_%(process_num)02d
command=php /home/nandzo/app/cctv_dashboard/artisan queue:work database --queue=detections --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=5
redirect_stderr=true
stdout_logfile=/home/nandzo/app/cctv_dashboard/storage/logs/worker-detections.log
stopwaitsecs=3600

[program:cctv-worker-images]
process_name=%(program_name)s_%(process_num)02d
command=php /home/nandzo/app/cctv_dashboard/artisan queue:work database --queue=images --sleep=3 --tries=3 --max-time=3600 --timeout=180
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/home/nandzo/app/cctv_dashboard/storage/logs/worker-images.log
stopwaitsecs=3600

[program:cctv-worker-reports]
process_name=%(program_name)s_%(process_num)02d
command=php /home/nandzo/app/cctv_dashboard/artisan queue:work database --queue=reports --sleep=10 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/home/nandzo/app/cctv_dashboard/storage/logs/worker-reports.log
stopwaitsecs=3600

[program:cctv-worker-default]
process_name=%(program_name)s_%(process_num)02d
command=php /home/nandzo/app/cctv_dashboard/artisan queue:work database --queue=default,maintenance --sleep=5 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/home/nandzo/app/cctv_dashboard/storage/logs/worker-default.log
stopwaitsecs=3600

[group:cctv-workers]
programs=cctv-worker-critical,cctv-worker-notifications,cctv-worker-detections,cctv-worker-images,cctv-worker-reports,cctv-worker-default
```

**Supervisor Commands:**

```bash
# Reload supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start all workers
sudo supervisorctl start cctv-workers:*

# Stop all workers
sudo supervisorctl stop cctv-workers:*

# Restart all workers
sudo supervisorctl restart cctv-workers:*

# Check status
sudo supervisorctl status
```

---

### **10. Scheduled Jobs (Cron)**

```php
// app/Console/Kernel.php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\CleanupOldFilesJob;
use App\Jobs\RetryFailedWhatsAppMessagesJob;
use App\Jobs\UpdateDailyReportJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Cleanup old files (daily at 2 AM)
        $schedule->job(new CleanupOldFilesJob())
            ->daily()
            ->at('02:00')
            ->name('cleanup-old-files')
            ->withoutOverlapping();

        // Retry failed WhatsApp messages (every 30 minutes)
        $schedule->job(new RetryFailedWhatsAppMessagesJob())
            ->everyThirtyMinutes()
            ->name('retry-failed-whatsapp')
            ->withoutOverlapping();

        // Generate daily reports for all branches (daily at 1 AM)
        $schedule->call(function () {
            $branches = \App\Models\CompanyBranch::where('status', 'active')->get();
            $yesterday = now()->subDay()->toDateString();

            foreach ($branches as $branch) {
                UpdateDailyReportJob::dispatch($branch->id, $yesterday)
                    ->onQueue('reports');
            }
        })
        ->daily()
        ->at('01:00')
        ->name('generate-daily-reports')
        ->withoutOverlapping();

        // Clear old failed jobs (weekly on Sunday at 3 AM)
        $schedule->command('queue:prune-failed --hours=168')
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->name('prune-failed-jobs');

        // Monitor queue size (every 5 minutes)
        $schedule->call(function () {
            $size = \DB::table('jobs')->count();
            if ($size > 10000) {
                \Log::warning('Queue size exceeds threshold', ['size' => $size]);
            }
        })
        ->everyFiveMinutes()
        ->name('monitor-queue-size');

        // Clear Laravel logs older than 7 days (daily at 4 AM)
        $schedule->command('log:clear --days=7')
            ->daily()
            ->at('04:00')
            ->name('clear-old-logs');
    }
}
```

**Add to crontab:**

```bash
* * * * * cd /home/nandzo/app/cctv_dashboard && php artisan schedule:run >> /dev/null 2>&1
```

---

### **11. Controller Usage Example**

```php
// app/Http/Controllers/Api/DetectionController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponseHelper;
use App\Http\Requests\StoreDetectionRequest;
use App\Jobs\ProcessDetectionJob;
use App\Jobs\ProcessDetectionImageJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DetectionController extends Controller
{
    /**
     * Log detection event (async)
     */
    public function store(StoreDetectionRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // 1. Handle image upload if present
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('events/' . date('Y/m/d'), 'local');
            }

            // 2. Dispatch detection processing job (async)
            $job = ProcessDetectionJob::dispatch(
                $validated['re_id'],
                $validated['branch_id'],
                $validated['device_id'],
                $validated['detected_count'] ?? 1,
                $validated['detection_data'] ?? null,
                $imagePath
            )->onQueue('detections');

            // 3. Dispatch image processing job if image exists (async)
            if ($imagePath) {
                ProcessDetectionImageJob::dispatch(
                    $imagePath,
                    0,  // Will be updated later
                    [
                        'thumbnail' => true,
                        'watermark' => true,
                        'optimize' => true
                    ]
                )->onQueue('images')->delay(now()->addSeconds(5));
            }

            // 4. Return immediate response
            return ApiResponseHelper::success(
                [
                    're_id' => $validated['re_id'],
                    'branch_id' => $validated['branch_id'],
                    'device_id' => $validated['device_id'],
                    'status' => 'processing',
                    'job_id' => $job->id ?? null,
                    'message' => 'Detection queued for processing'
                ],
                'Detection submitted successfully',
                202  // 202 Accepted (processing asynchronously)
            );

        } catch (\Exception $e) {
            Log::error('Detection submission failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return ApiResponseHelper::serverError(
                'Failed to submit detection',
                $e
            );
        }
    }
}
```

---

### **12. Queue Monitoring Commands**

```php
// app/Console/Commands/MonitorQueueCommand.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorQueueCommand extends Command
{
    protected $signature = 'queue:monitor';
    protected $description = 'Monitor queue jobs and statistics';

    public function handle()
    {
        $this->info('Queue Monitoring Dashboard');
        $this->line('');

        // Pending jobs by queue
        $pendingJobs = DB::table('jobs')
            ->select('queue', DB::raw('COUNT(*) as count'))
            ->groupBy('queue')
            ->orderByDesc('count')
            ->get();

        $this->table(
            ['Queue', 'Pending Jobs'],
            $pendingJobs->map(fn($job) => [$job->queue, $job->count])
        );

        // Failed jobs summary
        $failedJobs = DB::table('failed_jobs')
            ->select(
                DB::raw('DATE(failed_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('failed_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw('DATE(failed_at)'))
            ->orderByDesc('date')
            ->get();

        $this->line('');
        $this->info('Failed Jobs (Last 7 Days)');
        $this->table(
            ['Date', 'Failed Count'],
            $failedJobs->map(fn($job) => [$job->date, $job->count])
        );

        // Queue statistics
        $stats = [
            'Total Pending' => DB::table('jobs')->count(),
            'Total Failed' => DB::table('failed_jobs')->count(),
            'Failed Today' => DB::table('failed_jobs')
                ->whereDate('failed_at', now()->toDateString())
                ->count(),
        ];

        $this->line('');
        $this->info('Statistics');
        $this->table(
            ['Metric', 'Value'],
            collect($stats)->map(fn($value, $key) => [$key, $value])
        );

        return 0;
    }
}
```

---

## 🔧 Performance Optimization

### **1. Query Optimization**

#### **Composite Indexes (PostgreSQL Best Practice)**

```sql
-- ✅ Already created in table definitions above, but here are additional examples:

-- ✅ BEST PRACTICE: Covering index for frequently accessed columns
CREATE INDEX idx_re_id_branch_detections_covering
ON re_id_branch_detections(re_id, branch_id, detection_timestamp, detected_count);

-- ✅ BEST PRACTICE: Partial index for active records only (PostgreSQL specific)
CREATE INDEX idx_device_masters_active_devices
ON device_masters(status, branch_id)
WHERE status = 'active';

-- ✅ BEST PRACTICE: Partial index for today's detections
CREATE INDEX idx_re_id_branch_detections_today
ON re_id_branch_detections(branch_id, re_id, detected_count)
WHERE detection_timestamp >= CURRENT_DATE;

-- ✅ PostgreSQL B-tree index for sorting
CREATE INDEX idx_event_logs_timestamp_desc
ON event_logs(event_timestamp DESC);

-- ✅ PostgreSQL multi-column index for complex queries
CREATE INDEX idx_event_logs_complex
ON event_logs(branch_id, device_id, event_type, event_timestamp)
WHERE notification_sent = true;
```

**PostgreSQL Index Types:**

- **B-tree** (default): For equality and range queries
- **GIN**: For JSONB, arrays, full-text search
- **GiST**: For geometric data, range types
- **BRIN**: For very large tables with natural ordering
- **Hash**: For equality comparisons only

#### **Query Optimization Examples**

**1. Use Eager Loading to Avoid N+1:**

```php
// BAD - N+1 query problem
$detections = ReIdBranchDetection::all();
foreach ($detections as $detection) {
    echo $detection->branch->branch_name;  // N queries
}

// GOOD - Eager loading
$detections = ReIdBranchDetection::with(['branch', 'device', 'reId'])
                                 ->get();
```

**2. Use Select to Reduce Data Transfer:**

```php
// BAD - Fetch all columns
$users = User::all();

// GOOD - Only fetch needed columns
$users = User::select('id', 'name', 'email')->get();
```

**3. Use Chunking for Large Datasets:**

```php
// Process large datasets in chunks
ReIdBranchDetection::whereDate('detection_timestamp', now()->subDays(30))
    ->chunk(1000, function ($detections) {
        foreach ($detections as $detection) {
            // Process each detection
        }
    });
```

**4. Use Query Builder for Complex Queries:**

```php
// More efficient than Eloquent for aggregations
$summary = DB::table('re_id_branch_detections')
    ->select(
        'branch_id',
        DB::raw('COUNT(DISTINCT re_id) as unique_persons'),
        DB::raw('COUNT(*) as total_detections')
    )
    ->whereDate('detection_timestamp', now()->toDateString())
    ->groupBy('branch_id')
    ->get();
```

**5. Use Database Transactions (BEST PRACTICE):**

```php
// ✅ BEST PRACTICE: Wrap multiple operations in transaction
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($reId, $branchId, $deviceId, $detectedCount) {
    // 1. Update or create re_id_masters
    $reIdMaster = ReIdMaster::firstOrCreate(
        ['re_id' => $reId],
        ['first_detected_at' => now()]
    );

    // 2. Update counts
    $reIdMaster->increment('total_detection_count', 1);
    $reIdMaster->increment('total_actual_count', $detectedCount);
    $reIdMaster->update(['last_detected_at' => now()]);

    // 3. Create detection record
    ReIdBranchDetection::create([
        're_id' => $reId,
        'branch_id' => $branchId,
        'device_id' => $deviceId,
        'detected_count' => $detectedCount,
        'detection_timestamp' => now(),
    ]);

    // 4. Log event if configured
    if ($eventSettings->is_active) {
        EventLog::create([...]);
    }
}, 5); // ✅ Retry transaction 5 times on deadlock
```

**6. Use Prepared Statements (Automatic in Laravel):**

```php
// ✅ BEST PRACTICE: Laravel automatically uses prepared statements
// This is safe from SQL injection
$detections = DB::table('re_id_branch_detections')
    ->where('re_id', $reId)  // Automatically parameterized
    ->where('branch_id', $branchId)
    ->get();

// ❌ BAD: Raw queries without bindings
DB::select("SELECT * FROM re_id_branch_detections WHERE re_id = '$reId'");

// ✅ GOOD: Raw queries with bindings
DB::select("SELECT * FROM re_id_branch_detections WHERE re_id = ?", [$reId]);
```

### **2. Connection Pooling**

#### **Database Connection Configuration (PostgreSQL)**

```php
// config/database.php
'pgsql' => [
    'driver' => 'pgsql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),  // ✅ PostgreSQL default port
    'database' => env('DB_DATABASE', 'cctv_dashboard'),
    'username' => env('DB_USERNAME', 'postgres'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',  // ✅ PostgreSQL schema
    'sslmode' => env('DB_SSLMODE', 'prefer'),  // ✅ SSL connection

    // ✅ BEST PRACTICE: PDO options for performance
    'options' => [
        PDO::ATTR_EMULATE_PREPARES => false,  // Use native prepared statements
        PDO::ATTR_STRINGIFY_FETCHES => false,  // Return actual data types
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // Fetch as array
    ],

    // ✅ BEST PRACTICE: Connection settings
    'sticky' => true,  // Use write connection for reads after writes
    'read' => [
        'host' => [
            env('DB_READ_HOST_1', '127.0.0.1'),
            env('DB_READ_HOST_2', '127.0.0.1'),
        ],
    ],
    'write' => [
        'host' => [
            env('DB_WRITE_HOST', '127.0.0.1'),
        ],
    ],

    // ✅ PostgreSQL specific settings
    'application_name' => env('APP_NAME', 'CCTV_Dashboard'),
    'synchronous_commit' => 'on',  // Ensure data durability
],
```

**PostgreSQL Advantages:**

- ✅ **JSONB**: Binary JSON with indexing support (faster than JSON)
- ✅ **GIN Indexes**: Generalized Inverted Index for JSONB queries
- ✅ **INET Type**: Native IP address type
- ✅ **Partial Indexes**: Index only specific rows (WHERE clause)
- ✅ **Advanced Data Types**: Arrays, hstore, full-text search
- ✅ **Better Concurrency**: MVCC (Multi-Version Concurrency Control)
- ✅ **Table Partitioning**: Native support for large tables

**Note:** For high-performance applications, consider:

- **PgBouncer**: Connection pooling for PostgreSQL
- **Laravel Octane**: With Swoole or RoadRunner for persistent connections
- **Read Replicas**: For read-heavy workloads

#### **Queue Configuration for Background Jobs**

```php
// config/queue.php
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,  // ✅ Process immediately
    ],
],

// ✅ BEST PRACTICE: Use job classes for background processing
// app/Jobs/ProcessDetectionJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;  // ✅ Retry 3 times if failed
    public $timeout = 60;  // ✅ Timeout after 60 seconds

    public function __construct(
        public string $reId,
        public int $branchId,
        public string $deviceId,
        public int $detectedCount,
        public ?array $detectionData = null
    ) {}

    public function handle()
    {
        // Process detection in background
        ReIdBranchDetection::create([
            're_id' => $this->reId,
            'branch_id' => $this->branchId,
            'device_id' => $this->deviceId,
            'detected_count' => $this->detectedCount,
            'detection_timestamp' => now(),
            'detection_data' => $this->detectionData,
        ]);

        // Job completed successfully
    }

    public function failed(\Throwable $exception)
    {
        // ✅ BEST PRACTICE: Handle failed jobs
        Log::error('Detection processing failed', [
            're_id' => $this->reId,
            'error' => $exception->getMessage()
        ]);
    }
}

// Dispatch job
ProcessDetectionJob::dispatch($reId, $branchId, $deviceId, $detectedCount, $detectionData);
```

---

## 📦 Migration Order & Seeding Guide

### **Migration Order (Execute in this sequence)**

```bash
# 1. Core tables (no dependencies)
php artisan migrate --path=/database/migrations/2024_01_01_000001_create_company_groups_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000002_create_company_branches_table.php

# 2. Device and Person tables
php artisan migrate --path=/database/migrations/2024_01_01_000003_create_device_masters_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000004_create_re_id_masters_table.php

# 3. Detection table (depends on branches, devices, re_id)
php artisan migrate --path=/database/migrations/2024_01_01_000005_create_re_id_branch_detections_table.php

# 4. Event management tables
php artisan migrate --path=/database/migrations/2024_01_01_000006_create_branch_event_settings_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000007_create_event_logs_table.php

# 5. API security tables (depends on users - Laravel default)
php artisan migrate --path=/database/migrations/2024_01_01_000008_create_api_credentials_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000009_create_api_request_logs_table.php

# 6. CCTV and reporting tables
php artisan migrate --path=/database/migrations/2024_01_01_000010_create_cctv_streams_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000011_create_counting_reports_table.php

# 7. WhatsApp and Storage tables
php artisan migrate --path=/database/migrations/2024_01_01_000012_create_whatsapp_message_logs_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000013_create_storage_files_table.php

# 8. CCTV Layout Management tables
php artisan migrate --path=/database/migrations/2024_01_01_000014_create_cctv_layout_settings_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000015_create_cctv_position_settings_table.php

# 9. Supporting tables
php artisan migrate --path=/database/migrations/2024_01_01_000016_add_role_to_users_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000017_create_jobs_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000018_create_sessions_table.php
```

### **Seeding Order (Execute in this sequence)**

```bash
# 1. Seed users first (admin, operators)
php artisan db:seed --class=UserSeeder

# 2. Seed company structure
php artisan db:seed --class=CompanyGroupSeeder
php artisan db:seed --class=CompanyBranchSeeder

# 3. Seed devices
php artisan db:seed --class=DeviceMasterSeeder

# 4. Seed Re-ID master (persons)
php artisan db:seed --class=ReIdMasterSeeder

# 5. Seed configurations
php artisan db:seed --class=BranchEventSettingsSeeder

# 6. Seed sample detections and events
php artisan db:seed --class=ReIdBranchDetectionSeeder
php artisan db:seed --class=EventLogSeeder

# 7. Seed API credentials
php artisan db:seed --class=ApiCredentialSeeder

# 8. Seed CCTV streams
php artisan db:seed --class=CctvStreamSeeder

# 9. Seed CCTV Layout Management
php artisan db:seed --class=CctvLayoutSettingsSeeder
php artisan db:seed --class=CctvPositionSettingsSeeder

# Or run all at once
php artisan db:seed
```

### **Complete Setup Commands**

```bash
# Fresh installation
php artisan migrate:fresh --seed

# Rollback and re-migrate
php artisan migrate:rollback
php artisan migrate
php artisan db:seed

# Reset everything
php artisan migrate:fresh
php artisan db:seed
php artisan cache:clear
php artisan config:clear
```

---

## 🔗 Foreign Key Cascade Behavior

### **ON DELETE CASCADE vs ON DELETE SET NULL**

#### **Use ON DELETE CASCADE when:**

Child records should be **automatically deleted** when parent is deleted.

**Examples:**

```sql
-- Branch deleted → All its detections should be deleted
FOREIGN KEY (branch_id) REFERENCES company_branches(id) ON DELETE CASCADE

-- Device deleted → All its streams should be deleted
FOREIGN KEY (device_id) REFERENCES device_masters(device_id) ON DELETE CASCADE

-- Event deleted → All its related data should be deleted
FOREIGN KEY (event_log_id) REFERENCES event_logs(id) ON DELETE CASCADE
```

#### **Use ON DELETE SET NULL when:**

Child records should be **preserved** but reference should be removed.

**Examples:**

```sql
-- Person (re_id) deleted → Event logs should remain but without person reference
FOREIGN KEY (re_id) REFERENCES re_id_masters(re_id) ON DELETE SET NULL

-- User deleted → API credentials should remain but without creator reference
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
```

### **Complete Foreign Key Reference Table**

| Child Table                 | Parent Table         | Column            | Cascade Behavior | Reason                          |
| --------------------------- | -------------------- | ----------------- | ---------------- | ------------------------------- |
| **company_branches**        | company_groups       | group_id          | CASCADE          | Branch belongs to group         |
| **device_masters**          | company_branches     | branch_id         | CASCADE          | Device belongs to branch        |
| **re_id_branch_detections** | company_branches     | branch_id         | CASCADE          | Detection belongs to branch     |
| **re_id_branch_detections** | re_id_masters        | re_id             | CASCADE          | Detection needs re_id           |
| **re_id_branch_detections** | device_masters       | device_id         | CASCADE          | Detection needs device          |
| **branch_event_settings**   | company_branches     | branch_id         | CASCADE          | Settings belong to branch       |
| **branch_event_settings**   | device_masters       | device_id         | CASCADE          | Settings belong to device       |
| **event_logs**              | company_branches     | branch_id         | CASCADE          | Event belongs to branch         |
| **event_logs**              | device_masters       | device_id         | CASCADE          | Event needs device              |
| **event_logs**              | re_id_masters        | re_id             | **SET NULL**     | Keep event if person deleted    |
| **api_credentials**         | company_branches     | branch_id         | CASCADE          | Credential scoped to branch     |
| **api_credentials**         | device_masters       | device_id         | CASCADE          | Credential scoped to device     |
| **api_credentials**         | re_id_masters        | re_id             | CASCADE          | Credential scoped to person     |
| **api_credentials**         | users                | created_by        | **SET NULL**     | Keep credential if user deleted |
| **api_request_logs**        | api_credentials      | api_credential_id | CASCADE          | Log belongs to credential       |
| **cctv_streams**            | company_branches     | branch_id         | CASCADE          | Stream belongs to branch        |
| **cctv_streams**            | device_masters       | device_id         | CASCADE          | Stream belongs to device        |
| **counting_reports**        | company_branches     | branch_id         | CASCADE          | Report belongs to branch        |
| **cctv_layout_settings**    | users                | created_by        | CASCADE          | Layout created by user          |
| **cctv_position_settings**  | cctv_layout_settings | layout_id         | CASCADE          | Position belongs to layout      |
| **cctv_position_settings**  | company_branches     | branch_id         | CASCADE          | Position assigned to branch     |
| **cctv_position_settings**  | device_masters       | device_id         | CASCADE          | Position uses device            |

### **Migration Example**

```php
Schema::create('event_logs', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('branch_id');
    $table->string('device_id', 50);
    $table->string('re_id', 100)->nullable();

    // CASCADE - Delete event if branch/device deleted
    $table->foreign('branch_id')
          ->references('id')->on('company_branches')
          ->onDelete('cascade');

    $table->foreign('device_id')
          ->references('device_id')->on('device_masters')
          ->onDelete('cascade');

    // SET NULL - Keep event if person deleted
    $table->foreign('re_id')
          ->references('re_id')->on('re_id_masters')
          ->onDelete('set null');

    $table->timestamps();
});
```

---

## 🔐 Authentication & Authorization

### **User Roles**

```sql
-- Add role column to users table
ALTER TABLE users ADD COLUMN role ENUM('admin', 'operator', 'viewer') DEFAULT 'viewer';
```

#### **Role Definitions:**

| Role         | Description          | Access Level                                           |
| ------------ | -------------------- | ------------------------------------------------------ |
| **admin**    | System Administrator | Full access to all features, user management, settings |
| **operator** | Branch Operator      | Manage devices, view live streams, acknowledge events  |
| **viewer**   | Read-only User       | View dashboards, reports, and live streams only        |

#### **Role Permissions:**

**Admin:**

- ✅ Full CRUD on all modules
- ✅ User management
- ✅ System settings
- ✅ API credential management
- ✅ Branch/device configuration
- ✅ View all reports and analytics

**Operator:**

- ✅ View assigned branches
- ✅ Manage devices in assigned branches
- ✅ View live CCTV streams
- ✅ Acknowledge events/alerts
- ✅ View branch reports
- ❌ User management
- ❌ System settings
- ❌ API credentials

**Viewer:**

- ✅ View dashboards
- ✅ View reports
- ✅ View live streams (read-only)
- ❌ Any modifications
- ❌ Settings
- ❌ User management

### **User Seeder Example**

```php
// database/seeders/UserSeeder.php
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create Admin
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Operators
        User::create([
            'name' => 'Jakarta Operator',
            'email' => 'operator.jakarta@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
        ]);

        User::create([
            'name' => 'Bandung Operator',
            'email' => 'operator.bandung@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
        ]);

        // Create Viewers
        User::create([
            'name' => 'Dashboard Viewer',
            'email' => 'viewer@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'viewer',
        ]);
    }
}
```

### **Role Middleware**

```php
// app/Http/Middleware/CheckRole.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}

// Register in app/Http/Kernel.php
protected $routeMiddleware = [
    'role' => \App\Http\Middleware\CheckRole::class,
];

// Usage in routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});

Route::middleware(['auth', 'role:admin,operator'])->group(function () {
    Route::get('/devices', [DeviceController::class, 'index']);
});
```

### **API Authentication Methods**

#### **1. Laravel Sanctum (Recommended for SPA/Mobile)**

**Installation:**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

**Configuration:**

```php
// config/sanctum.php
'expiration' => 60 * 24 * 7, // 7 days

// app/Http/Kernel.php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

**Usage:**

```php
// Login and generate token
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('api-token', ['role:' . $user->role])->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user
    ]);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/detection/log', [DetectionController::class, 'log']);
});
```

**Client Usage:**

```javascript
// Store token
localStorage.setItem("api_token", response.token);

// Use in requests
fetch("/api/detection/log", {
  method: "POST",
  headers: {
    Authorization: `Bearer ${localStorage.getItem("api_token")}`,
    "Content-Type": "application/json",
  },
  body: JSON.stringify(data),
});
```

#### **2. API Key Authentication (For External Systems)**

**Middleware:** `ApiKeyAuth` (Enhanced with Rate Limiting & Security)

```php
// app/Http/Middleware/ApiKeyAuth.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiCredential;
use App\Helpers\ApiResponseHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');
        $apiSecret = $request->header('X-API-Secret');

        if (!$apiKey || !$apiSecret) {
            return ApiResponseHelper::unauthorized('API key and secret are required');
        }

        // Cache credentials for 5 minutes (performance)
        $credential = Cache::remember("api_credential:{$apiKey}", 300, function () use ($apiKey) {
            return ApiCredential::where('api_key', $apiKey)
                ->where('status', 'active')
                ->first();
        });

        if (!$credential) {
            Log::warning('Invalid API key attempt', ['api_key' => $apiKey, 'ip' => $request->ip()]);
            return ApiResponseHelper::error('Invalid API credentials', 'INVALID_CREDENTIALS', null, 401);
        }

        // Timing-safe secret comparison (prevents timing attacks)
        if (!hash_equals($credential->api_secret, $apiSecret)) {
            Log::warning('Invalid API secret attempt', ['api_key' => $apiKey, 'ip' => $request->ip()]);
            return ApiResponseHelper::error('Invalid API credentials', 'INVALID_CREDENTIALS', null, 401);
        }

        // Check expiration
        if ($credential->isExpired()) {
            return ApiResponseHelper::error('API credentials expired', 'EXPIRED_CREDENTIALS', null, 401);
        }

        // Rate limiting (per credential, hourly)
        $rateLimitKey = "api_rate_limit:{$credential->api_key}";
        $hourlyRequests = Cache::get($rateLimitKey, 0);

        if ($hourlyRequests >= $credential->rate_limit) {
            return ApiResponseHelper::error(
                'Rate limit exceeded. Try again later.',
                'RATE_LIMIT_EXCEEDED',
                [
                    'limit' => $credential->rate_limit,
                    'period' => 'hour',
                    'reset_at' => now()->startOfHour()->addHour()->toIso8601String(),
                ],
                429
            );
        }

        // Increment rate limit counter
        if ($hourlyRequests === 0) {
            Cache::put($rateLimitKey, 1, now()->endOfHour());
        } else {
            Cache::increment($rateLimitKey);
        }

        // Update last_used_at (async, after response)
        dispatch(function () use ($credential) {
            $credential->update(['last_used_at' => now()]);
        })->afterResponse();

        // Attach credential to request
        $request->merge(['api_credential' => $credential]);

        // Add rate limit headers to response
        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', $credential->rate_limit);
        $response->headers->set('X-RateLimit-Remaining', max(0, $credential->rate_limit - $hourlyRequests - 1));
        $response->headers->set('X-RateLimit-Reset', now()->startOfHour()->addHour()->timestamp);

        return $response;
    }
}
```

**Registration:**

```php
// bootstrap/app.php
$middleware->alias([
    'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'static.token' => \App\Http\Middleware\ValidateStaticToken::class,
    'admin' => \App\Http\Middleware\AdminOnly::class,
    'api.key' => \App\Http\Middleware\ApiKeyAuth::class,  // ✅ Registered
]);
```

**Usage:**

```php
// routes/api.php
Route::middleware('api.key')->group(function () {
    // Detection API routes
    Route::post('/detection/log', [DetectionController::class, 'store']);
    Route::get('/detection/status/{jobId}', [DetectionController::class, 'status']);
    Route::get('/detections', [DetectionController::class, 'index']);
    Route::get('/detection/summary', [DetectionController::class, 'summary']);

    // Person (Re-ID) API routes
    Route::get('/person/{reId}', [DetectionController::class, 'showPerson']);
    Route::get('/person/{reId}/detections', [DetectionController::class, 'personDetections']);

    // Branch API routes
    Route::get('/branch/{branchId}/detections', [DetectionController::class, 'branchDetections']);
});
```

**Client Usage:**

```bash
# Example API request
curl -X POST https://api.cctv.com/api/detection/log \
  -H "X-API-Key: cctv_live_abc123xyz789def456ghi789jkl012" \
  -H "X-API-Secret: secret_mno345pqr678stu901vwx234yz567ab" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "re_id": "person_001_abc123",
    "branch_id": 1,
    "device_id": "CAMERA_001",
    "detected_count": 1,
    "detection_data": {
      "confidence": 0.95
    }
  }'
```

**Security Features:**

- ✅ **Timing-safe comparison**: `hash_equals()` prevents timing attacks
- ✅ **Request logging**: Failed attempts logged with IP address
- ✅ **Credential caching**: 5-minute cache reduces DB queries
- ✅ **Rate limiting**: Per-credential hourly limits with Cache
- ✅ **Async updates**: `last_used_at` updated after response (non-blocking)
- ✅ **Rate limit headers**: Client can track remaining quota

#### **3. Rate Limiting (BEST PRACTICE)**

```php
// app/Providers/RouteServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

protected function configureRateLimiting()
{
    // ✅ BEST PRACTICE: Different rate limits for different user types
    RateLimiter::for('api', function (Request $request) {
        $apiCredential = $request->get('api_credential');

        if ($apiCredential) {
            return Limit::perHour($apiCredential->rate_limit)
                        ->by($apiCredential->api_key)
                        ->response(function () use ($apiCredential) {
                            return response()->json([
                                'message' => 'Rate limit exceeded',
                                'limit' => $apiCredential->rate_limit,
                                'retry_after' => 3600
                            ], 429);
                        });
        }

        return Limit::perMinute(60)->by($request->ip());
    });

    // ✅ BEST PRACTICE: Separate rate limit for authenticated users
    RateLimiter::for('api-auth', function (Request $request) {
        return $request->user()
            ? Limit::perMinute(100)->by($request->user()->id)
            : Limit::perMinute(10)->by($request->ip());
    });
}
```

#### **4. Input Validation (BEST PRACTICE)**

```php
// ✅ BEST PRACTICE: Use Form Requests for validation
// app/Http/Requests/StoreDetectionRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetectionRequest extends FormRequest
{
    public function authorize()
    {
        // Check if user has permission or valid API credential
        return $this->user() || $this->has('api_credential');
    }

    public function rules()
    {
        return [
            're_id' => 'required|string|max:100',
            'branch_id' => 'required|integer|exists:company_branches,id',
            'device_id' => 'required|string|max:50|exists:device_masters,device_id',
            'detected_count' => 'required|integer|min:0|max:1000',
            'detection_data' => 'nullable|array',
            'detection_data.confidence' => 'nullable|numeric|min:0|max:1',
            'detection_data.bounding_box' => 'nullable|array',
            'detection_data.bounding_box.x' => 'required_with:detection_data.bounding_box|integer|min:0',
            'detection_data.bounding_box.y' => 'required_with:detection_data.bounding_box|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            're_id.required' => 'Re-identification ID is required',
            'branch_id.exists' => 'Invalid branch ID',
            'device_id.exists' => 'Invalid device ID',
        ];
    }
}

// Usage in controller
public function store(StoreDetectionRequest $request)
{
    // Data is already validated
    $validated = $request->validated();

    // ✅ BEST PRACTICE: Use try-catch for error handling
    try {
        // Process detection
        ProcessDetectionJob::dispatch(
            $validated['re_id'],
            $validated['branch_id'],
            $validated['device_id'],
            $validated['detected_count'],
            $validated['detection_data'] ?? null
        );

        // ✅ BEST PRACTICE: Consistent API response format
        return response()->json([
            'success' => true,
            'message' => 'Detection logged successfully',
            'data' => [
                're_id' => $validated['re_id'],
                'branch_id' => $validated['branch_id'],
                'device_id' => $validated['device_id'],
            ]
        ], 201);  // ✅ BEST PRACTICE: Use proper HTTP status code

    } catch (\Exception $e) {
        // ✅ BEST PRACTICE: Log errors and return proper error response
        Log::error('Detection logging failed', [
            're_id' => $validated['re_id'],
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to log detection',
            'error' => $e->getMessage()
        ], 500);
    }
}
```

#### **5. Security Headers (BEST PRACTICE)**

```php
// ✅ BEST PRACTICE: Add security headers middleware
// app/Http/Middleware/SecurityHeaders.php
namespace App\Http\Middleware;

use Closure;

class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Content Security Policy
        $response->headers->set('Content-Security-Policy', "default-src 'self'");

        // Strict Transport Security (HSTS)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
```

---

## ✅ Best Practices Checklist

### **Database Design (PostgreSQL)**

- ✅ **Normalized Structure**: 3NF normalization to reduce redundancy
- ✅ **Proper Data Types**: VARCHAR for strings, BIGINT for IDs, BIGSERIAL for auto-increment
- ✅ **PostgreSQL Data Types**: JSONB for JSON, INET for IP, CHECK constraints for enums
- ✅ **Foreign Keys**: All relationships enforced with named constraints
- ✅ **Indexes**: All foreign keys and frequently queried columns indexed
- ✅ **Composite Indexes**: Multi-column indexes for common query patterns
- ✅ **GIN Indexes**: For JSONB and array columns (faster searches)
- ✅ **Partial Indexes**: For filtered queries (PostgreSQL specific)
- ✅ **UTF8**: Full Unicode support including emojis
- ✅ **MVCC**: Multi-Version Concurrency Control (PostgreSQL default)
- ✅ **Timestamp Tracking**: created_at and updated_at on all tables
- ✅ **Triggers**: Auto-update updated_at with triggers
- ✅ **Constraints**: CHECK constraints for data validation

### **Performance**

- ✅ **Database Caching**: Use PostgreSQL materialized views for complex queries
- ✅ **Eager Loading**: Prevent N+1 query problems
- ✅ **Query Builder**: Use for aggregations and complex queries
- ✅ **Chunking**: Process large datasets in batches
- ✅ **Database Transactions**: Wrap multiple operations for data consistency
- ✅ **Background Jobs**: Process heavy operations asynchronously
- ✅ **Read/Write Splitting**: Separate read and write database connections
- ✅ **Connection Pooling**: Reuse database connections with PgBouncer

### **Security**

- ✅ **API Key + Secret**: Dual credential authentication
- ✅ **Laravel Sanctum**: Token-based authentication for SPA/Mobile
- ✅ **Rate Limiting**: Prevent API abuse
- ✅ **Input Validation**: Form Requests with comprehensive rules
- ✅ **Prepared Statements**: Automatic SQL injection prevention
- ✅ **Encrypted Storage**: Sensitive data (passwords, API keys) encrypted
- ✅ **HTTPS Only**: Force HTTPS for all API endpoints
- ✅ **Security Headers**: XSS, clickjacking, MIME-sniffing protection
- ✅ **CORS Configuration**: Proper cross-origin resource sharing
- ✅ **Audit Trail**: Track all API requests and user actions

### **Code Quality**

- ✅ **Form Requests**: Separate validation logic
- ✅ **Service Classes**: Business logic in service layer
  - **ReIdMasterService**: Person tracking and detection management
    - `getBranchDetectionCounts()`: Branch detection statistics with MIN/MAX timestamps
    - `getPersonWithDetections()`: Person details with detection history
    - `getAllDetectionsForPerson()`: Cross-date person tracking
    - `getByDateRange()`: Date-filtered person queries
    - `getStatistics()`: Aggregated statistics
  - **WhatsAppSettingsService**: WhatsApp configuration management
    - `setAsDefault()`: Update default settings
    - `updateBranchEventSettings()`: Sync settings across branches
- ✅ **Job Classes**: Background processing
- ✅ **Resource Classes**: Consistent API responses
- ✅ **Middleware**: Reusable request filtering
- ✅ **Error Handling**: Proper exception handling
- ✅ **Logging**: Comprehensive error and activity logging
- ✅ **Type Hinting**: PHP 8+ type declarations

### **API Design**

- ✅ **RESTful Convention**: Standard HTTP methods and status codes
- ✅ **Consistent Responses**: Uniform JSON structure
- ✅ **Pagination**: For list endpoints
- ✅ **Filtering**: Query parameters for filtering
- ✅ **Versioning**: API version in URL (/api/v1/)
- ✅ **Documentation**: Clear API documentation
- ✅ **Error Messages**: Descriptive error responses

### **Scalability**

- ✅ **Horizontal Scaling**: Database read replicas
- ✅ **Queue Workers**: Multiple workers for background jobs
- ✅ **CDN**: Static assets delivery
- ✅ **Load Balancer**: Distribute traffic across servers
- ✅ **Microservices Ready**: Modular design for service separation
- ✅ **Database Partitioning**: PostgreSQL table partitioning for large tables

### **Monitoring & Maintenance**

- ✅ **Query Logging**: Log slow queries
- ✅ **Performance Monitoring**: Track response times
- ✅ **Error Tracking**: Centralized error logging
- ✅ **Database Metrics**: Track connections, queries per second
- ✅ **API Analytics**: Track endpoint usage
- ✅ **Health Checks**: Automated system health monitoring

---

## 🚨 Important Notes

### **Environment Variables**

```env
# Database PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cctv_dashboard
DB_USERNAME=postgres
DB_PASSWORD=
DB_SSLMODE=prefer

# Database Read/Write Split
DB_READ_HOST_1=127.0.0.1
DB_READ_HOST_2=127.0.0.1
DB_WRITE_HOST=127.0.0.1

# Database Query Logging (Performance Monitoring)
DB_LOG_QUERIES=false  # ✅ Enable only in development/staging

# Queue
QUEUE_CONNECTION=database

# Application
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://cctv.yourdomain.com
APP_DEBUG=false  # ✅ MUST be false in production
APP_KEY=base64:your-32-character-key-here  # ✅ REQUIRED for encryption

# Performance Monitoring
PERFORMANCE_MONITORING=true
PERFORMANCE_IN_RESPONSE=true   # Include in JSON response meta
PERFORMANCE_IN_HEADERS=true    # Include in HTTP headers
SLOW_QUERY_THRESHOLD=1000      # Log queries slower than 1000ms
HIGH_MEMORY_THRESHOLD=128      # Alert if memory usage > 128MB

# Encryption Settings
ENCRYPT_DEVICE_CREDENTIALS=true
ENCRYPT_STREAM_CREDENTIALS=true
ENCRYPTION_METHOD=AES-256-CBC

# WhatsApp Provider Settings
WHATSAPP_PROVIDER=waha
WHATSAPP_API_URL=http://localhost:3000
WHATSAPP_API_KEY=your_waha_api_key_here
WHATSAPP_SESSION_NAME=default
WHATSAPP_RETRY_ATTEMPTS=3
WHATSAPP_TIMEOUT=30

# Storage Settings
FILESYSTEM_DISK=local
STORAGE_MAX_FILE_SIZE=10240
STORAGE_ALLOWED_TYPES=jpg,jpeg,png,mp4,avi
STORAGE_AUTO_CLEANUP_DAYS=90

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Sanctum
SANCTUM_STATEFUL_DOMAINS=cctv.yourdomain.com
```

### **Required Packages**

```bash
# Core packages
composer require laravel/sanctum  # API authentication
composer require intervention/image  # Image processing

# PostgreSQL specific
# Laravel comes with PDO PostgreSQL support by default
# Ensure php-pgsql extension is installed: apt-get install php-pgsql

# Recommended packages
composer require spatie/laravel-query-builder  # Advanced query filtering
composer require spatie/laravel-activitylog    # Audit trail
composer require barryvdh/laravel-debugbar     # Development only
composer require doctrine/dbal                 # Schema manipulation for PostgreSQL
composer require guzzlehttp/guzzle  # HTTP client (included in Laravel)

# Optional (for advanced storage)
# composer require league/flysystem-aws-s3-v3  # S3 storage
```

### **Production Deployment Checklist**

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Generate strong `APP_KEY`: `php artisan key:generate`
- [ ] Configure encryption: `ENCRYPT_DEVICE_CREDENTIALS=true`
- [ ] Configure encryption: `ENCRYPT_STREAM_CREDENTIALS=true`
- [ ] Configure WhatsApp: `WHATSAPP_API_URL`, `WHATSAPP_API_KEY`
- [ ] Configure Storage: `FILESYSTEM_DISK`, `STORAGE_MAX_FILE_SIZE`
- [ ] **Performance Monitoring**: Set `DB_LOG_QUERIES=false` (production)
- [ ] **Performance Monitoring**: Set `PERFORMANCE_MONITORING=true`
- [ ] **Performance Monitoring**: Set `PERFORMANCE_IN_RESPONSE=true`
- [ ] **Performance Monitoring**: Set `PERFORMANCE_IN_HEADERS=true`
- [ ] **Performance Thresholds**: Configure `SLOW_QUERY_THRESHOLD` and `HIGH_MEMORY_THRESHOLD`
- [ ] Create storage link: `php artisan storage:link`
- [ ] Set up file permissions: `chmod -R 775 storage`
- [ ] Enable HTTPS/SSL
- [ ] Configure CORS properly
- [ ] Set up database backups (PostgreSQL pg_dump)
- [ ] Configure log rotation
- [ ] Set up monitoring (New Relic, DataDog)
- [ ] Optimize images and assets
- [ ] Set up CDN for static files
- [ ] Configure firewall rules
- [ ] Set up automated backups
- [ ] Test disaster recovery plan
- [ ] Verify encryption/decryption works
- [ ] Test WhatsApp delivery
- [ ] Set up cron for file cleanup: `StorageHelper::cleanupOldFiles()`
- [ ] Test file upload/download
- [ ] Monitor storage disk usage
- [ ] **Test Performance Metrics**: Verify query_count, memory_usage, execution_time in responses
- [ ] **Monitor Slow Queries**: Check Laravel logs for slow query warnings
- [ ] **Set up Supervisor**: Configure queue workers with proper priorities

---

## 🐘 PostgreSQL Specific Features & Optimizations

### **1. JSONB Queries (PostgreSQL Advantage)**

```sql
-- ✅ Query inside JSONB fields
SELECT * FROM re_id_masters
WHERE appearance_features->>'height' = 'medium';

-- ✅ JSONB containment
SELECT * FROM re_id_masters
WHERE appearance_features @> '{"clothing_colors": ["blue"]}';

-- ✅ JSONB array operations
SELECT * FROM branch_event_settings
WHERE whatsapp_numbers ? '+628123456789';

-- ✅ Update JSONB fields
UPDATE re_id_masters
SET appearance_features = appearance_features || '{"verified": true}'::jsonb
WHERE re_id = 'person_001_abc123';
```

### **2. Advanced PostgreSQL Features**

#### **Full-Text Search**

```sql
-- Add tsvector column for full-text search
ALTER TABLE re_id_masters ADD COLUMN search_vector tsvector;

-- Create GIN index for full-text search
CREATE INDEX idx_re_id_masters_search ON re_id_masters USING GIN (search_vector);

-- Update search vector
UPDATE re_id_masters
SET search_vector = to_tsvector('english', coalesce(person_name, '') || ' ' || coalesce(re_id, ''));

-- Full-text search query
SELECT * FROM re_id_masters
WHERE search_vector @@ to_tsquery('english', 'john');
```

#### **Table Partitioning**

```sql
-- ✅ Partition re_id_branch_detections by month
CREATE TABLE re_id_branch_detections_2024_01 PARTITION OF re_id_branch_detections
FOR VALUES FROM ('2024-01-01') TO ('2024-02-01');

CREATE TABLE re_id_branch_detections_2024_02 PARTITION OF re_id_branch_detections
FOR VALUES FROM ('2024-02-01') TO ('2024-03-01');

-- Auto-create partitions with pg_partman extension
CREATE EXTENSION pg_partman;
```

#### **Materialized Views for Reports**

```sql
-- ✅ Create materialized view for daily summaries
CREATE MATERIALIZED VIEW daily_branch_summary AS
SELECT
    cb.branch_name,
    DATE(rbd.detection_timestamp) as date,
    COUNT(DISTINCT rbd.re_id) as unique_persons,
    COUNT(*) as total_detections
FROM company_branches cb
LEFT JOIN re_id_branch_detections rbd ON cb.id = rbd.branch_id
GROUP BY cb.id, cb.branch_name, DATE(rbd.detection_timestamp);

-- Create index on materialized view
CREATE INDEX idx_daily_branch_summary_date ON daily_branch_summary(date);

-- Refresh materialized view (run daily via cron)
REFRESH MATERIALIZED VIEW CONCURRENTLY daily_branch_summary;
```

### **3. PostgreSQL Performance Tuning**

#### **postgresql.conf Optimization**

```ini
# Memory Settings
shared_buffers = 256MB  # 25% of RAM
effective_cache_size = 1GB  # 50-75% of RAM
work_mem = 16MB  # Per operation
maintenance_work_mem = 128MB  # For VACUUM, CREATE INDEX

# Checkpoint Settings
checkpoint_completion_target = 0.9
wal_buffers = 16MB
min_wal_size = 1GB
max_wal_size = 4GB

# Query Planner
random_page_cost = 1.1  # For SSD (default 4.0 for HDD)
effective_io_concurrency = 200  # For SSD

# Connection Settings
max_connections = 100
shared_preload_libraries = 'pg_stat_statements'  # Query analytics

# Logging
log_min_duration_statement = 1000  # Log queries > 1 second
log_line_prefix = '%t [%p]: [%l-1] user=%u,db=%d,app=%a,client=%h '
```

#### **Maintenance Commands**

```sql
-- ✅ VACUUM to reclaim storage
VACUUM ANALYZE re_id_branch_detections;

-- ✅ REINDEX for index optimization
REINDEX TABLE re_id_branch_detections;

-- ✅ Analyze for query planner statistics
ANALYZE re_id_branch_detections;

-- ✅ Check table bloat
SELECT schemaname, tablename,
       pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
```

### **4. Migration Example (PostgreSQL)**

```php
// database/migrations/2024_01_01_000001_create_company_groups_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCompanyGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('company_groups', function (Blueprint $table) {
            $table->id();  // BIGSERIAL PRIMARY KEY
            $table->string('province_code', 10)->unique();
            $table->string('province_name', 100);
            $table->string('group_name', 150);
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();  // created_at and updated_at

            // Add CHECK constraint
            $table->check("status IN ('active', 'inactive')");
        });

        // Create trigger function for updated_at
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_updated_at_column()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.updated_at = CURRENT_TIMESTAMP;
                RETURN NEW;
            END;
            $$ language \'plpgsql\';
        ');

        // Create trigger
        DB::unprepared('
            CREATE TRIGGER update_company_groups_updated_at
            BEFORE UPDATE ON company_groups
            FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
        ');

        // Create indexes
        Schema::table('company_groups', function (Blueprint $table) {
            $table->index('province_code');
            $table->index('status');
        });
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_company_groups_updated_at ON company_groups');
        Schema::dropIfExists('company_groups');
    }
}
```

---

_This comprehensive database plan follows industry best practices for PostgreSQL performance, security, scalability, and maintainability. All recommendations are based on Laravel framework standards and proven production patterns for PostgreSQL databases._
