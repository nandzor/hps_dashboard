# 📱 CCTV Dashboard - Application Plan & Workflow

## 🏗️ Application Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    CCTV Dashboard Application                    │
├─────────────────────────────────────────────────────────────────┤
│  Dashboard  │  Branches  │  Devices  │  Persons  │  Events  │   │
│             │            │           │  (Re-ID)  │          │   │
│  Live CCTV  │  Settings  │  Counting │  API      │  Reports │   │
│  View       │            │  Analytics│  Creds    │          │   │
└─────────────────────────────────────────────────────────────────┘
```

**Core Modules:**

- **Dashboard**: Overview statistics and live monitoring
- **Company Groups**: Province-level company group management (Admin only)
- **Branches**: Company branch management (city level)
- **Devices**: Camera/sensor/thermo device management (per branch)
- **Persons (Re-ID)**: Person re-identification tracking
- **Events**: Event logging and notification management
- **Live CCTV**: 4/6/8-window grid stream viewer
- **Counting**: Person detection analytics (Re-ID based)
- **API**: API credential and usage management
- **Reports**: Report generation and analytics
- **Settings**: System configuration

**Technology Stack:**

- **Backend**: Laravel 10+ (PHP 8.2+)
- **Frontend**: Laravel Blade Templates
- **JavaScript**: Alpine.js for interactivity
- **CSS**: Tailwind CSS
- **Database**: PostgreSQL 15+
- **Queue**: Database Queue (with Supervisor workers)
- **Real-time**: Laravel Echo + Pusher/WebSockets
- **Storage**: Local/S3 with registry tracking
- **WhatsApp**: WAHA/Twilio integration
- **Image Processing**: Intervention/Image
- **Architecture**: Service Layer Pattern for business logic separation

## 📋 Main Menu Structure

### **1. Dashboard** 📊

- **Overview Statistics**

  - Total branches
  - Total devices
  - Today's detections
  - Active events
  - System health status

- **Live Charts**

  - Detection trends (hourly/daily)
  - Branch performance comparison
  - Device activity heatmap
  - Event type distribution

- **Quick Actions**
  - View live CCTV streams
  - Generate quick reports
  - System alerts & notifications

### **2. Company Group Management** 🏢

- **Group Registry**

  - All company groups (province level)
  - Province code & name
  - Group name & description
  - Contact information
  - Status monitoring (active/inactive)
  - Associated branches count

- **Group Details**

  - Province information
  - Group contact details
  - Address & location
  - Associated branches list
  - Performance metrics
  - Group statistics

- **Group Settings**
  - Basic information
  - Contact details
  - Address management
  - Status control
  - Branch assignment

### **3. Branch Management** 🏢

- **Branch List**

  - View all branches
  - Search & filter by group/province
  - Branch status indicators
  - Quick actions (edit, view details)

- **Branch Details**

  - Basic information
  - Contact details
  - GPS coordinates
  - Associated devices
  - Event settings
  - Parent group information

- **Branch Settings**
  - General information
  - WhatsApp configuration
  - Notification preferences
  - API access settings

### **4. Device Management** 📹

- **Device Registry**

  - All devices across branches
  - Device type filtering (camera, sensor, thermo)
  - Status monitoring (active/inactive)
  - Performance metrics
  - Device belongs to specific branch

- **Device Details**

  - Device ID & name
  - Device type (camera/sensor/thermo)
  - Branch location
  - Technical specifications
  - Current status
  - Detection history
  - Stream configuration

- **Device Configuration**
  - Device settings
  - Detection parameters
  - Stream quality settings
  - Event triggers (per device per branch)

### **4.5 Person Tracking (Re-ID)** 🧑

- **Person Registry**

  - All detected persons (Re-ID)
  - Re-identification ID list
  - Person name (if identified)
  - Appearance features (JSON)
  - Detection statistics
  - First/last detected timestamps
  - Ordered by `last_detected_at DESC` (newest first)

- **Person Details**

  - Re-ID identifier
  - Person name (optional)
  - Appearance features (clothing colors, height, etc.)
  - Total detection count
  - Branches that detected this person
  - **Branch Detection Summary Table** (NEW)
    - Single-column card layout
    - Total Count with blue badge
    - First/Last detection times per branch
    - Clean visual hierarchy
  - Detection timeline (repositioned below Person Information)
  - Associated events

- **Person Tracking**
  - Track person across branches
  - View detection history
  - Appearance feature analysis
  - Cross-branch movement patterns
  - **Branch Detection Counts**: Aggregated statistics per branch

### **5. Live CCTV View** 📺

- **Dynamic Grid Layouts**

  - **4-Window Grid** (2x2): Standard quad view
  - **6-Window Grid** (2x3): Extended monitoring
  - **8-Window Grid** (2x4): Maximum surveillance
  - Admin-configurable layouts
  - Position-specific branch/device assignment

- **Layout Management (Admin Only)**

  - Create custom layouts
  - Set default layout
  - Configure position settings
  - Branch/device per position
  - Auto-switch functionality
  - Quality settings per position

- **Stream Management**

  - Position-based stream assignment
  - Stream health monitoring
  - Recording controls
  - Screenshot capture
  - Resolution & quality controls

- **Multi-Branch View**
  - Switch between branches per position
  - Compare multiple locations
  - Dynamic layout switching
  - Real-time stream status

### **6. CCTV Layout Management** 🎛️

- **Layout Configuration (Admin Only)**

  - Create/Edit/Delete layouts
  - Layout types: 4-window, 6-window, 8-window
  - Set default layout
  - Layout descriptions and metadata
  - User access control

- **Position Settings**

  - Branch assignment per position
  - Device assignment per position
  - Position naming
  - Enable/disable positions
  - Auto-switch configuration
  - Quality settings (low/medium/high)
  - Resolution settings

- **Layout Management**

  - Switch between layouts
  - Position-specific configurations
  - Real-time layout updates
  - Layout performance monitoring
  - Backup/restore layouts

- **Admin Controls**
  - Layout creation wizard
  - Position configuration interface
  - Layout testing and validation
  - User permission management

### **7. Event Management** 🚨

- **Event Logs**

  - Real-time event feed
  - Event type filtering
  - Branch/device filtering
  - Export capabilities

- **Event Settings**

  - Per-branch configuration
  - Notification rules
  - Image capture settings
  - WhatsApp integration

- **Notification Center**
  - WhatsApp delivery status
  - Failed notifications
  - Notification templates
  - Delivery reports

### **8. Counting & Analytics** 📈

- **Real-time Person Counting (Re-ID)**

  - Live person detection counts
  - Unique persons detected (by Re-ID)
  - Branch-wise breakdown
  - Device performance
  - Detection timeline
  - Cross-branch tracking

- **Detection Analytics**

  - Person tracking (Re-ID based)
  - Detection frequency per person
  - Branch counting logic (1 count per branch)
  - Actual detection count tracking
  - Appearance feature analysis
  - Movement patterns

- **Counting Reports**

  - Daily detection reports
  - Weekly person summaries
  - Monthly analytics
  - Unique person counts
  - Branch performance comparison
  - Custom date ranges

- **Branch Performance**
  - Individual branch stats
  - Unique persons detected per branch
  - Detection counts per branch
  - Device performance metrics
  - Comparative analysis
  - Performance rankings

### **9. API Management** 🔑 (Admin Only)

- **Credential Management**

  - Create API keys (web interface)
  - Global access only (all branches & devices)
  - Full permissions by default
  - Set expiration dates (optional)
  - Revoke/regenerate credentials
  - Test API with built-in interface

- **API Testing Interface**

  - Live API testing at `/api-credentials/{id}/test`
  - Select endpoints and send test requests
  - View response status, headers, and body
  - Track rate limit usage
  - Copy cURL commands
  - Syntax-highlighted JSON responses

- **API Documentation**

  - Complete endpoint documentation
  - Authentication guide (X-API-Key, X-API-Secret headers)
  - Rate limiting info (10,000/hour per credential)
  - Sample requests and responses
  - Error code reference

- **Usage Analytics**
  - Last used timestamp
  - Rate limit tracking
  - Performance metrics (via response headers)
  - Request logging (file-based daily logs)
  - Aggregated statistics (daily summaries)

**Features:**

- ✅ Simplified credential creation (3 fields only)
- ✅ Automatic key/secret generation
- ✅ Timing-safe authentication
- ✅ Built-in rate limiting (10,000 req/hour)
- ✅ Web-based testing interface
- ✅ One-time secret display (security)
- ✅ Credential caching for performance

### **10. Reports & Exports** 📄

- **Standard Reports**

  - Daily activity reports
  - Branch performance
  - Device utilization
  - Event summaries
  - Person tracking analytics (Re-ID)

- **Custom Reports**

  - Date range selection
  - Branch filtering
  - Device filtering
  - Person (Re-ID) filtering
  - Export formats (PDF, Excel, CSV)

- **Scheduled Reports**
  - Automated report generation
  - Email delivery
  - Report templates
  - Subscription management

### **11. Queue & Job Monitoring** 🔄

- **Queue Dashboard**

  - Pending jobs by queue (critical, notifications, detections, images, reports)
  - Failed jobs summary
  - Processing statistics
  - Queue health monitoring

- **Job Management**

  - Retry failed jobs
  - Clear old failed jobs
  - Monitor job performance
  - View job logs

- **Worker Status**
  - Active workers count
  - Worker performance metrics
  - Queue processing speed
  - Worker health checks

### **12. Storage Management** 📦

- **File Registry**

  - All uploaded files tracking
  - File size and type information
  - Storage disk usage statistics
  - Related table associations

- **Storage Operations**

  - View file metadata
  - Download files securely
  - Delete old files
  - Storage cleanup scheduling

- **Storage Analytics**
  - Total storage usage
  - Files by type breakdown
  - Files by disk breakdown
  - Growth trends

### **13. Settings** ⚙️

- **System Configuration**

  - General settings
  - Database configuration
  - Cache settings
  - Backup settings

- **Integration Settings**

  - WhatsApp providers
  - Email configuration
  - SMS providers
  - Third-party APIs

- **User Management**
  - User roles & permissions
  - Access control
  - Activity logs
  - Security settings

## 🔄 Module Workflows

### **1. Dashboard Workflow**

```
User Login → Dashboard Load
    │
    ├── Load Statistics (Async)
    │   ├── Total branches
    │   ├── Total devices
    │   ├── Today's detections
    │   └── Active events
    │
    ├── Load Charts (Async)
    │   ├── Detection trends
    │   ├── Branch comparison
    │   ├── Device heatmap
    │   └── Event distribution
    │
    ├── Load Live Feeds (WebSocket)
    │   ├── Real-time notifications
    │   ├── System alerts
    │   └── Status updates
    │
    └── Display Dashboard
        ├── Statistics cards
        ├── Interactive charts
        ├── Live feeds
        └── Quick actions
```

### **2. Company Group Management Workflow**

```
Group Management Request → Group Processing
    │
    ├── Authentication & Authorization
    │   ├── Verify admin role
    │   ├── Check group permissions
    │   ├── Validate user access
    │   └── Log admin action
    │
    ├── Group CRUD Operations
    │   ├── Create Group:
    │   │   ├── Validate province_code (unique)
    │   │   ├── Validate province_name
    │   │   ├── Validate group_name
    │   │   ├── Set status (active/inactive)
    │   │   └── Save to company_groups
    │   │
    │   ├── Update Group:
    │   │   ├── Validate group exists
    │   │   ├── Update group information
    │   │   ├── Update contact details
    │   │   └── Update status
    │   │
    │   └── Delete Group:
    │       ├── Check for associated branches
    │       ├── Cascade delete branches (if confirmed)
    │       ├── Delete group record
    │       └── Log deletion
    │
    ├── Branch Association
    │   ├── View associated branches
    │   ├── Add branches to group
    │   ├── Remove branches from group
    │   └── Update branch-group relationships
    │
    ├── Group Validation
    │   ├── Verify province code uniqueness
    │   ├── Check branch associations
    │   ├── Validate contact information
    │   └── Test group functionality
    │
    └── Group Activation
        ├── Activate/deactivate group
        ├── Update associated branches status
        ├── Notify connected clients
        └── Log group changes
```

### **3. Person Detection Workflow (Re-ID) - Async Processing**

```
Device Detection → API Validation → Queue Job → Processing
    │
    ├── API Request Received
    │   ├── Validate API credentials
    │   ├── Check rate limits
    │   ├── Validate payload (re_id, branch_id, device_id)
    │   └── Upload image (if present)
    │
    ├── Dispatch to Queue (Return 202 Accepted)
    │   ├── Dispatch ProcessDetectionJob → queue: detections
    │   ├── Dispatch ProcessDetectionImageJob → queue: images (if image)
    │   ├── Return immediate response (job_id, status: processing)
    │   └── Client continues without waiting
    │
    ├── Background Processing (ProcessDetectionJob)
    │   ├── Database Transaction Start
    │   │   ├── Create/Update re_id_masters (daily record)
    │   │   ├── Check status (active/inactive)
    │   │   ├── Update appearance_features (JSONB)
    │   │   ├── Update timestamps (first/last detected)
    │   │   └── Increment total_actual_count
    │   │
    │   ├── Log Detection
    │   │   ├── Create re_id_branch_detections record
    │   │   ├── Save detection_timestamp
    │   │   ├── Save detection_data (JSONB)
    │   │   └── Update unique branch count
    │   │
    │   ├── Create Event Log
    │   │   ├── Save to event_logs
    │   │   ├── Link to re_id_masters
    │   │   ├── Set notification flags (false initially)
    │   │   └── Store image_path
    │   │
    │   └── Database Transaction Commit
    │       ├── All or nothing (rollback on error)
    │       ├── Retry up to 3 times on failure
    │       └── Log to failed_jobs if all retries fail
    │
    ├── Dispatch Child Jobs (Async Chain)
    │   ├── SendWhatsAppNotificationJob → queue: notifications
    │   │   ├── Exponential backoff (30s, 60s, 120s, 300s, 600s)
    │   │   ├── Up to 5 retry attempts
    │   │   └── Fire & forget delivery
    │   │
    │   ├── ProcessDetectionImageJob → queue: images
    │   │   ├── Resize & optimize image
    │   │   ├── Add watermark (timestamp + branch)
    │   │   ├── Create thumbnail (320x240)
    │   │   └── Save to storage_files registry
    │   │
    │   └── UpdateDailyReportJob → queue: reports (delayed 5 min)
    │       ├── Calculate statistics
    │       ├── Generate report_data (JSONB)
    │       └── Save to counting_reports
    │
    ├── WhatsApp Notification Processing (If Enabled)
    │   ├── Check branch_event_settings.whatsapp_enabled
    │   ├── Get whatsapp_numbers (JSONB array)
    │   ├── Format message with template variables
    │   ├── Send to each phone number
    │   ├── Log to storage/app/logs/whatsapp_messages/YYYY-MM-DD.log (instant file write)
    │   └── Update event_logs.notification_sent = true
    │
    └── Real-time Updates & Completion
        ├── Broadcast WebSocket events
        ├── Update dashboard counters
        ├── Trigger person tracking updates
        └── Complete with success status
```

**Queue Configuration:**

- **critical**: 2 workers (system critical operations)
- **notifications**: 3 workers (WhatsApp, Email)
- **detections**: 5 workers (highest load - real-time detection)
- **images**: 2 workers (image processing)
- **reports**: 2 workers (report generation)
- **maintenance**: 2 workers (cleanup, optimization)

### **4. WhatsApp Notification Workflow - Async Queue Job**

```
Event Created → SendWhatsAppNotificationJob Dispatched
    │
    ├── Queue Job: SendWhatsAppNotificationJob
    │   ├── Queue: notifications
    │   ├── Tries: 5 (exponential backoff)
    │   ├── Timeout: 60 seconds
    │   └── Backoff: [30s, 60s, 120s, 300s, 600s]
    │
    ├── Job Processing
    │   ├── Get event_logs record
    │   ├── Get branch_event_settings
    │   ├── Check whatsapp_enabled (boolean ON/OFF)
    │   └── Get whatsapp_numbers (JSONB array)
    │
    ├── Message Preparation
    │   ├── Load message template
    │   ├── Replace template variables:
    │   │   ├── {branch_name}
    │   │   ├── {device_name}
    │   │   ├── {device_id}
    │   │   ├── {re_id}
    │   │   ├── {person_name}
    │   │   ├── {detected_count}
    │   │   ├── {timestamp}
    │   │   ├── {date}
    │   │   └── {time}
    │   └── Get image_path from event_logs
    │
    ├── Send via WhatsApp Helper
    │   ├── For each phone number:
    │   │   ├── Format phone number (62xxx@c.us)
    │   │   ├── Prepare payload (text + image base64)
    │   │   ├── Call WAHA/Twilio API
    │   │   ├── Log to daily file (instant write):
    │   │   │   → storage/app/logs/whatsapp_messages/YYYY-MM-DD.log
    │   │   │   → JSON Lines format (one JSON per line)
    │   │   │   ├── timestamp, event_log_id, phone_number
    │   │   │   ├── message_text, image_path
    │   │   │   ├── status (pending/sent/failed)
    │   │   │   ├── provider_response (execution_time_ms)
    │   │   │   └── error_message (if failed)
    │   │   └── No database INSERT (file-based logging)
    │   └── Fire & forget (no waiting for delivery)
    │
    ├── Update Event Log
    │   ├── notification_sent = true
    │   ├── image_sent = true/false
    │   └── message_sent = true
    │
    └── Job Completion
        ├── Log success/failure
        ├── Update job status
        ├── Retry if failed (up to 5 times)
        └── Move to failed_jobs if all retries fail
```

**Retry Mechanism:**

- **Scheduled Retry**: `RetryFailedWhatsAppMessagesJob` runs every 30 minutes
- **Max Retries**: 3 attempts (configurable via `WHATSAPP_RETRY_ATTEMPTS`)
- **Retry Window**: Last 24 hours only

### **5. API Request Workflow**

```
API Request → Processing & Response
    │
    ├── Authentication
    │   ├── Validate API key
    │   ├── Check expiration
    │   ├── Verify permissions
    │   └── Check rate limits
    │
    ├── Authorization
    │   ├── Check scope (branch/device)
    │   ├── Validate permissions
    │   ├── Check resource access
    │   └── Log request
    │
    ├── Process Request
    │   ├── Validate payload
    │   ├── Execute business logic
    │   ├── Update database
    │   └── Prepare response
    │
    ├── Log Response (File-based)
    │   ├── Write to storage/app/logs/api_requests/YYYY-MM-DD.log
    │   ├── Record: response_time_ms, query_count, memory_usage_mb
    │   ├── Log status code, endpoint, method
    │   ├── Sanitize sensitive fields (password, token, api_secret)
    │   └── Instant file append (no queue delay)
    │
    └── Return Response
        ├── JSON response
        ├── Status code
        ├── Error messages (if any)
        └── Rate limit headers
```

### **6. CCTV Layout Management Workflow (Admin Only)**

```
Admin Layout Request → Layout Configuration
    │
    ├── Authentication & Authorization
    │   ├── Verify admin role
    │   ├── Check layout permissions
    │   ├── Validate user access
    │   └── Log admin action
    │
    ├── Layout Creation/Update
    │   ├── Validate layout type (4/6/8-window)
    │   ├── Check position count
    │   ├── Validate branch/device assignments
    │   └── Save to cctv_layout_settings
    │
    ├── Position Configuration
    │   ├── Configure each position (1-8)
    │   ├── Assign branch per position
    │   ├── Assign device per position
    │   ├── Set position name & quality
    │   ├── Configure auto-switch settings
    │   └── Save to cctv_position_settings
    │
    ├── Layout Validation
    │   ├── Verify all positions configured
    │   ├── Check device availability
    │   ├── Validate stream URLs
    │   └── Test layout functionality
    │
    ├── Set Default Layout (Optional)
    │   ├── Unset previous default
    │   ├── Set new default layout
    │   ├── Update user preferences
    │   └── Broadcast layout change
    │
    └── Layout Activation
        ├── Activate layout
        ├── Update frontend configuration
        ├── Notify connected clients
        └── Log layout change
```

### **7. CCTV Stream Management Workflow**

```
Stream Request → Stream Delivery
    │
    ├── Stream Validation
    │   ├── Check stream exists
    │   ├── Verify stream status
    │   ├── Validate user access
    │   └── Check branch permissions
    │
    ├── Stream Preparation
    │   ├── Get stream configuration
    │   ├── Decrypt credentials
    │   ├── Build stream URL
    │   └── Set quality parameters
    │
    ├── Stream Delivery
    │   ├── Establish connection
    │   ├── Stream to client
    │   ├── Monitor stream health
    │   └── Handle disconnections
    │
    ├── Health Monitoring
    │   ├── Ping stream endpoint
    │   ├── Check stream quality
    │   ├── Update status
    │   └── Log performance
    │
    └── Cleanup
        ├── Close connections
        ├── Update last_checked_at
        ├── Log session data
        └── Release resources
```

### **8. Report Generation Workflow - Async Queue Job**

```
Report Request → UpdateDailyReportJob Dispatched
    │
    ├── Queue Job: UpdateDailyReportJob
    │   ├── Queue: reports
    │   ├── Tries: 3
    │   ├── Timeout: 300 seconds (5 minutes)
    │   └── Backoff: [30s, 60s, 120s]
    │
    ├── Check Existing Report
    │   ├── Query counting_reports
    │   ├── WHERE report_type, report_date, branch_id
    │   ├── Return cached if exists and fresh
    │   └── Continue if expired or not exists
    │
    ├── Calculate Statistics (PostgreSQL Queries)
    │   ├── total_devices: DISTINCT device_id count
    │   ├── total_detections: Total re_id_branch_detections count
    │   ├── total_events: Total event_logs count
    │   ├── unique_devices: Active device_masters count
    │   └── unique_persons: DISTINCT re_id count
    │
    ├── Generate Report Data (JSONB)
    │   ├── Top persons detected (top 10)
    │   ├── Hourly breakdown (EXTRACT HOUR)
    │   ├── Device breakdown (JOIN device_masters)
    │   ├── Peak hour calculation
    │   └── Additional analytics
    │
    ├── Save/Update Report
    │   ├── UPSERT to counting_reports
    │   ├── Save report_data as JSONB
    │   ├── Set generated_at timestamp
    │   └── Database transaction commit
    │
    └── Job Completion
        ├── Log report generation
        ├── Broadcast WebSocket update
        ├── Update report cache
        └── Return success status
```

**Scheduled Report Generation:**

- **Daily at 01:00**: Generate reports for all active branches (yesterday's data)
- **Queue**: `reports` queue with delay
- **Retry**: 3 attempts with exponential backoff

## 🎯 User Roles & Access Control

### **1. Admin** (System Administrator)

**Full Access:**

- ✅ Full CRUD on all modules
- ✅ User management
- ✅ System settings
- ✅ **API credential management** (create/edit/delete/test credentials)
- ✅ Company Group Management (create/edit/delete groups)
- ✅ Branch/device configuration
- ✅ View all reports and analytics
- ✅ Re-ID (person) management
- ✅ Event configuration (all branches)
- ✅ CCTV Layout Management (create/edit/delete layouts)
- ✅ Queue & Job Monitoring (view jobs, retry failed)
- ✅ Storage Management (view files, manage storage)
- ✅ WhatsApp Message Logs (view delivery status)

**Access Scope:**

- All company groups
- All branches
- All devices
- All persons (Re-ID)
- System configuration
- **API credentials management** (web interface at `/api-credentials`)

**Admin-Only Features:**

- 🔑 Create/manage API credentials
- 🧪 Test API endpoints with web interface
- 🌐 All credentials have global access
- 🔒 View API usage statistics
- ⚡ Monitor rate limits and performance

### **2. Operator** (Branch Operator)

**Limited Access:**

- ✅ View assigned branches
- ✅ Manage devices in assigned branches
- ✅ View live CCTV streams
- ✅ Acknowledge events/alerts
- ✅ View branch reports
- ✅ View person detections (Re-ID)
- ✅ Use configured CCTV layouts (view only)
- ❌ User management
- ❌ System settings
- ❌ API credentials
- ❌ Company Group Management
- ❌ CCTV Layout Management

**Access Scope:**

- Assigned branches only
- Devices in assigned branches
- Events in assigned branches

### **3. Viewer** (Read-only User)

**Read-only Access:**

- ✅ View dashboards
- ✅ View reports
- ✅ View live streams (read-only)
- ✅ View person tracking (Re-ID)
- ✅ Use configured CCTV layouts (view only)
- ❌ Any modifications
- ❌ Settings
- ❌ User management
- ❌ Event configuration
- ❌ Company Group Management
- ❌ CCTV Layout Management

**Access Scope:**

- Dashboard view only
- Report viewing
- Stream viewing (no control)

## 📱 Mobile Responsiveness

### **Dashboard Mobile View**

- Stacked statistics cards
- Swipeable charts
- Collapsible sections
- Touch-friendly controls

### **CCTV Mobile View**

- Single stream view
- Swipe to switch streams
- Pinch to zoom
- Landscape mode support

### **Navigation Mobile**

- Hamburger menu
- Bottom navigation
- Quick actions
- Search functionality

## 🔔 Real-time Features

### **WebSocket Events**

- Live detection updates
- Stream status changes
- Notification deliveries
- System alerts

### **Push Notifications**

- Browser notifications
- Mobile app notifications
- Email notifications
- SMS alerts (future)

### **Live Updates**

- Dashboard statistics
- Chart data
- Event feeds
- Stream health

## 📊 Performance Considerations

### **Database Optimization (PostgreSQL)**

- **Indexed Queries**: GIN (JSONB), B-tree, Partial indexes
- **Materialized Views**: For complex queries (daily_branch_summary)
- **Table Partitioning**: For large tables (re_id_branch_detections by month)
- **Read Replicas**: For read-heavy workloads
- **Connection Pooling**: PgBouncer for PostgreSQL
- **Query Optimization**: Eager loading, chunking, composite indexes

### **Queue & Background Processing**

- **Queue System**: Database Queue with 6 priority levels
- **Worker Processes**: 16 total workers across queues
- **Supervisor**: Auto-restart, graceful shutdown
- **Async Operations**:
  - Detection processing (202 Accepted response)
  - WhatsApp notifications (fire & forget)
  - Image processing (resize, watermark, thumbnail)
  - Report generation (scheduled & on-demand)
  - File cleanup (scheduled daily)

### **Frontend Optimization**

- **Blade Components**: Reusable UI components
  - ✅ **x-button**: Consistent button styling with variants
  - ✅ **x-card**: Standardized card layouts
  - ✅ **x-badge**: Status indicators with color variants
  - ✅ **x-stat-card**: Statistics display cards
  - ✅ **x-action-dropdown**: Interactive dropdown menus
  - ✅ **x-table**: Responsive table components
  - ✅ **x-select**: Reusable select components (branch, device, status)
- **Alpine.js**: Lightweight interactivity (no heavy JS framework)
- **Lazy Loading**: Images and content
- **Livewire (Optional)**: For reactive components
- **Vite**: Asset bundling and HMR
- **CDN**: Static assets delivery
- **Turbo/Inertia (Optional)**: SPA-like experience
- **DRY Principles**: Consistent use of reusable components across all pages

### **API Response Standardization**

- **Consistent Format**: All responses use ApiResponseHelper
- **Status Codes**: Proper HTTP status codes (200, 201, 202, 400, 401, 403, 404, 422, 429, 500)
- **Error Codes**: Standardized error codes for client handling
- **Meta Information**: Timestamp, version, request_id in all responses
- **Pagination**: Standard pagination format

### **Storage & File Management**

- **File Registry**: Centralized tracking in storage_files table
- **Secure Access**: Encrypted file paths with auth middleware
- **Auto Cleanup**: Scheduled job (90 days retention)
- **Metadata Tracking**: File size, type, dimensions, related records
- **Multi-disk Support**: Local, S3, Public disks

---

## 🏗️ Service Layer Architecture

### **Service Classes**

**Purpose**: Separate business logic from controllers for better maintainability and testability.

**Key Services:**

- ✅ **ReIdMasterService**: Person tracking and detection management
  - `getBranchDetectionCounts()`: Branch detection statistics
  - `getPersonWithDetections()`: Person details with detection history
  - `getAllDetectionsForPerson()`: Cross-date person tracking
  - `getByDateRange()`: Date-filtered person queries
  - `getStatistics()`: Aggregated statistics
- ✅ **WhatsAppSettingsService**: WhatsApp configuration management
  - `setAsDefault()`: Update default settings
  - `updateBranchEventSettings()`: Sync settings across branches
  - `getActive()`: Get active WhatsApp settings
- ✅ **BaseService**: Common service functionality
  - Pagination, filtering, search
  - CRUD operations
  - Query optimization

**Benefits:**

- ✅ **Separation of Concerns**: Controllers handle HTTP, services handle business logic
- ✅ **Reusability**: Services can be used across multiple controllers
- ✅ **Testability**: Business logic can be unit tested independently
- ✅ **Maintainability**: Changes to business rules centralized in services
- ✅ **Performance**: Optimized queries and caching in service layer

## 📋 Database Tables Summary

**Total: 17 Tables** (Optimized with File-based Logs)

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
| **Queue**    | 2      | Jobs + failed_jobs (Laravel default)               |

**Notes:**

- ✅ **Raw Logs**: API requests and WhatsApp messages stored in **daily log files** (JSON Lines format)
- ✅ **Database**: Only stores **aggregated summaries** (api_usage_summary, whatsapp_delivery_summary)
- ✅ **Scalability**: File-based logs prevent database bloat for high-volume operations

---

_This application plan provides a comprehensive overview of the CCTV Dashboard system with detailed workflows, queue processing, and user experience considerations._
