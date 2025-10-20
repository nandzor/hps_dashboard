# 📊 CCTV Dashboard - Sequence Diagrams

**Technology Stack:**

- **Backend**: Laravel 10+ (PHP 8.2+)
- **Frontend**: Laravel Blade Templates
- **Interactivity**: Alpine.js
- **Real-time**: Laravel Echo + Pusher/WebSockets
- **API**: Laravel API Resources
- **Queue**: Laravel Queue (Database)

## 🔄 Key Workflow Sequence Diagrams

### **1. Company Group Management Workflow (Admin Only)**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Admin     │  │   Blade     │  │  Laravel    │  │  Database   │  │ WebSocket   │
│   User      │  │ Template    │  │ Controller  │  │             │  │   Server    │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Group Management Request     │                │                │
       │ (Create/Update/Delete Group)    │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. POST/PUT/DELETE /groups     │                │
       │                │ Form Data: {province_code,     │                │
       │                │  province_name, group_name,    │                │
       │                │  address, status}              │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 3. Validate Admin Access        │
       │                │                │ - Check admin role              │
       │                │                │ - Verify permissions            │
       │                │                │ - Log admin action              │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 4. Group CRUD Operations        │
       │                │                │ - Validate province_code unique │
       │                │                │ - Check for associated branches  │
       │                │                │ - Save to company_groups         │
       │                │                │ - Update timestamps             │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 5. Branch Association           │
       │                │                │ - View associated branches       │
       │                │                │ - Add/remove branches           │
       │                │                │ - Update branch-group relations  │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 6. Group Validation             │
       │                │                │ - Verify province code unique   │
       │                │                │ - Check branch associations     │
       │                │                │ - Validate contact information   │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 7. Group Response               │
       │                │                │ - Group ID returned             │
       │                │                │ - Associated branches count     │
       │                │                │ - Status confirmation           │
       │                │                │◄───────────────┤                │
       │                │                │                │                │
       │                │ 8. Return to Blade View         │                │
       │                │ - Redirect to groups.index      │                │
       │                │ - With success message          │                │
       │                │ - Flash session data            │                │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │                │ 9. Broadcast Group Change       │                │
       │                │ - WebSocket: group_updated      │                │
       │                │ - Data: {group_id, status}      │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │                │ 10. Notify All Clients│
       │                │                │                │ - Group change event │
       │                │                │                │ - Update UI components│
       │                │                │                ├─────────────────►│
       │                │                │                │                │
       │                │ 11. Render Blade View           │                │                │
       │                │ - Load groups.index.blade.php   │                │                │
       │                │ - Display success toast         │                │                │
       │                │ - Alpine.js updates list        │                │                │
       │                │◄─────────────────────────────────────────────────┤                │
```

### **2. Person Detection & Re-Identification Processing (Async Queue)**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Device    │  │  Laravel    │  │   Queue     │  │  Database   │  │  WhatsApp   │  │   Client    │
│  (Camera)   │  │ Controller  │  │   Worker    │  │             │  │  Provider   │  │ Dashboard   │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │                │
       │ 1. Person Detection                            │                │                │
       │ POST /api/v1/detection/log                     │                │                │
       │ {re_id, branch_id, device_id,  │                │                │                │
       │  detected_count, detection_data, image}        │                │                │
       ├───────────────►│                │                │                │                │
       │                │                │                │                │                │
       │                │ 2. Validate Request            │                │                │
       │                │ - Check API credentials        │                │                │
       │                │ - Validate payload             │                │                │
       │                │ - Upload image (if present)    │                │                │
       │                ├───────────────────────────────►│                │                │
       │                │                │                │                │                │
       │                │ 3. Dispatch ProcessDetectionJob│                │                │
       │                │ → Queue: detections            │                │                │
       │                │ - Store job in jobs table      │                │                │
       │                ├───────────────────────────────►│                │                │
       │                │                │                │                │                │
       │                │ 4. Return 202 Accepted (Immediate)              │                │
       │                │ {success: true, status: processing,             │                │
       │                │  job_id, message: "Detection queued"}           │                │
       │◄───────────────┤                │                │                │                │
       │                │                │                │                │                │
       │                │                │ 5. Worker Picks Job            │                │
       │                │                │ (ProcessDetectionJob)          │                │
       │                │                ├───────────────►│                │                │
       │                │                │                │                │                │
       │                │                │ 6. Start DB Transaction         │                │
       │                │                │ - Create/Update re_id_masters   │                │
       │                │                │ - Check status (active/inactive)│                │
       │                │                │ - Update appearance_features    │                │
       │                │                │ - Update first/last detected timestamps│                │
       │                │                │ - Increment total_actual_count   │                │
       │                │                │ - Update total_detection_branch_count│                │
       │                │                ├───────────────►│                │                │
       │                │                │                │                │                │
       │                │                │ 7. Log Detection                │                │
       │                │                │ - re_id_branch_detections       │                │
       │                │                │ - Save detection_timestamp      │                │
       │                │                │ - Save detection_data (JSONB)   │                │
       │                │                ├───────────────►│                │                │
       │                │                │                │                │                │
       │                │                │ 8. Create Event Log             │                │
       │                │                │ - event_logs table              │                │
       │                │                │ - Link to re_id, branch, device │                │
       │                │                ├───────────────►│                │                │
       │                │                │                │                │                │
       │                │                │ 9. Commit Transaction           │                │
       │                │                │ - All or nothing                │                │
       │                │                │◄───────────────┤                │                │
       │                │                │                │                │                │
       │                │                │ 10. Dispatch Child Jobs         │                │
       │                │                │ - SendWhatsAppNotificationJob   │                │
       │                │                │   → queue: notifications        │                │
       │                │                │ - ProcessDetectionImageJob      │                │
       │                │                │   → queue: images               │                │
       │                │                │ - UpdateDailyReportJob          │                │
       │                │                │   → queue: reports (delayed)    │                │
       │                │                ├───────────────►│                │                │
       │                │                │                │                │                │
       │                │                │ 11. Job Completed               │                │
       │                │                │ - Log success                   │                │
       │                │                │ - Remove from jobs table        │                │
       │                │                ├───────────────►│                │                │
       │                │                │                │                │                │
       │                │                │                │ 12. WhatsApp Worker Picks Job  │
       │                │                │                │ (SendWhatsAppNotificationJob)  │
       │                │                │                ├───────────────►│                │
       │                │                │                │                │                │
       │                │                │                │ 13. Get Settings & Send         │
       │                │                │                │ - branch_event_settings         │
       │                │                │                │ - Format message template       │
       │                │                │                │ - Send to each phone number     │
       │                │                │                ├───────────────────────────────►│
       │                │                │                │                │                │
                │                │                │                │ 14. Provider Response           │
                │                │                │                │ - Log to daily file (instant):  │
                │                │                │                │   storage/app/logs/whatsapp_messages/YYYY-MM-DD.log │
                │                │                │                │ - JSON Lines format             │
                │                │                │                │ - status: sent/failed           │
                │                │                │                │◄───────────────────────────────┤
                │                │                │                │                │                │
                │                │                │                │ 15. Update Event Log            │
                │                │                │                │ - notification_sent = true      │
                │                │                │                ├───────────────►│                │
       │                │                │                │                │                │
       │                │                │ 16. WebSocket Broadcast         │                │
       │                │                │ - Real-time dashboard update    │                │
       │                │                │ - Person tracking update        │                │
       │                │                │ - Branch detection summary update│                │
       │                │                ├─────────────────────────────────────────────────►│
       │                │                │                │                │                │
       │                │                │                │                │ 17. Update UI  │
       │                │                │                │                │ - Increment count│
       │                │                │                │                │ - Show notification│
       │                │                │                │                │ - Update branch summary│
       │                │                │                │                │◄───────────────┤
```

**Notes:**

- ✅ **202 Accepted**: Immediate response without waiting for processing
- ✅ **Background Processing**: All heavy operations in queue workers
- ✅ **Job Chaining**: Parent job dispatches child jobs
- ✅ **Retry Mechanism**: Automatic retry with exponential backoff
- ✅ **Transaction Safety**: Database transactions with rollback
- ✅ **Real-time Updates**: WebSocket broadcast after completion
- ✅ **Branch Detection Summary**: Aggregated statistics per branch with MIN/MAX timestamps

### **3. CCTV Stream Request & Display**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   User      │  │   Blade     │  │  Laravel    │  │  Database   │  │  Stream     │
│ Browser     │  │ Template    │  │ Controller  │  │             │  │  Server     │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Select Stream                │                │                │
       │ (Position 1, Branch A)          │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. GET /api/stream/branch/1    │                │
       │                │ ?position=1                    │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 3. Query Streams                │
       │                │                │ - cctv_streams                  │
       │                │                │ - WHERE branch_id=1, position=1 │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 4. Stream Config                │
       │                │                │ {stream_url, credentials,       │
       │                │                │  resolution, fps}               │
       │                │                │◄───────────────┤                │
       │                │                │                │                │
       │                │                │ 5. Validate & Decrypt           │
       │                │                │ - Check user permissions        │
       │                │                │ - Decrypt stream credentials    │
       │                │                │ - Build authenticated URL       │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │ 6. Stream Response              │                │
       │                │ {stream_url, status, quality}   │                │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │                │ 7. Initialize Video Player      │                │
       │                │ - Alpine.js initializes player  │                │
       │                │ - Load stream URL               │                │
       │                │ - Set video element source      │                │
       │                ├─────────────────────────────────┤                │
       │                │                │                │                │
       │                │ 8. Connect to Stream            │                │
       │                │ - WebRTC/RTSP/HLS connection    │                │
       │                ├─────────────────────────────────────────────────►│
       │                │                │                │                │
       │                │ 9. Stream Data                  │                │
       │                │ - Video frames                  │                │
       │                │ - Audio (if available)          │                │
       │                │◄─────────────────────────────────────────────────┤
       │                │                │                │                │
       │                │ 10. Display Stream              │                │
       │                │ - Render video in grid          │                │
       │                │ - Show stream info              │                │
       │                ├─────────────────────────────────┤                │
       │                │                │                │                │
       │                │                │ 11. Health Check (Periodic)     │
       │                │                │ - Ping stream endpoint          │
       │                │                │ - Update stream status          │
       │                │                ├─────────────────────────────────►│
       │                │                │                │                │
       │                │ 12. Status Updates              │                │
       │                │ - Stream quality indicators     │                │
       │                │ - Connection status             │                │
       │                │◄───────────────┤                │                │
```

### **4. API Credential Creation & Usage (Simplified Global Access)**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Admin     │  │   Blade     │  │  Laravel    │  │  Database   │  │  External   │
│   User      │  │ Template    │  │ Controller  │  │  + Cache    │  │   Client    │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Create API Key               │                │                │
       │ (Simplified: name + expiry only)│                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. POST /api-credentials        │                │
       │                │ Form: {credential_name,        │                │
       │                │  expires_at, status}           │                │
       │                │ (Only 3 fields!)               │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 3. Validate & Generate          │
       │                │                │ - Check admin middleware (routes)│
       │                │                │ - Generate unique API key (40)  │
       │                │                │ - Generate API secret (40)      │
       │                │                │ - Set defaults:                 │
       │                │                │   • branch_id = NULL (global)   │
       │                │                │   • device_id = NULL (global)   │
       │                │                │   • permissions = full          │
       │                │                │   • rate_limit = 10000/hour     │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 4. Save to Database             │
       │                │                │ - api_credentials table         │
       │                │                │ - Store api_secret (plain)      │
       │                │                │ - Set created_by                │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 5. Flash Session (Secret)       │
       │                │                │ - Store api_secret in session   │
       │                │                │ - Show only once!               │
       │                │                │◄───────────────┤                │
       │                │                │                │                │
       │                │ 6. Redirect to Show Page        │                │
       │                │ - Display credential details    │                │
       │                │ - Show secret (one-time alert)  │                │
       │                │ - Copy to clipboard button      │
       │                │ - "Test API" button visible     │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │ 7. Test API (Optional)          │                │                │
       │ GET /api-credentials/{id}/test  │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 8. Render Test Interface        │                │
       │                │ - Select endpoint dropdown      │                │
       │                │ - Enter API secret field        │                │
       │                │ - Send test request button      │                │
       │                │ - Live response display         │                │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │                │                │                │                │
       │                │                │ 9. External API Request (Live)  │
       │                │                │ GET /api/detections             │
       │                │                │ Headers:                        │
       │                │                │  X-API-Key: cctv_live_abc...    │
       │                │                │  X-API-Secret: secret_mno...    │
       │                │                ├─────────────────────────────────►│
       │                │                │                │                │
       │                │                │ 10. ApiKeyAuth Middleware       │
       │                │                │ - Check headers exist           │
       │                │                │ - Cache.remember credential (5m)│
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 11. Validate Credential         │
       │                │                │ - hash_equals(secret) timing-safe│
       │                │                │ - Check expiration              │
       │                │                │ - Check rate limit (Cache)      │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 12. Increment Rate Counter      │
       │                │                │ Cache::increment(rate_limit_key)│
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 13. Process Request             │
       │                │                │ - Query detections data         │
       │                │                │ - Generate response             │
       │                │                │ - Add rate limit headers        │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 14. API Response                │
       │                │                │ Headers:                        │
       │                │                │  X-RateLimit-Limit: 10000       │
       │                │                │  X-RateLimit-Remaining: 9847    │
       │                │                │  X-RateLimit-Reset: 1728399600  │
       │                │                │ Body: {success: true, data}     │
       │                │                │◄─────────────────────────────────┤
       │                │                │                │                │
       │                │                │ 15. Async Update last_used_at   │
       │                │                │ dispatch()->afterResponse()     │
       │                │                ├───────────────►│                │
```

**Key Changes:**

- ✅ **Simplified**: Only 3 form fields (name, expiry, status)
- ✅ **Auto-defaults**: Global access, full permissions, 10K rate limit
- ✅ **Test Interface**: Built-in API testing at `/api-credentials/{id}/test`
- ✅ **Security**: Timing-safe comparison, request logging
- ✅ **Performance**: Credential caching, async updates, rate limit headers

### **5. Report Generation Workflow (Async Queue)**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   User      │  │   Blade     │  │  Laravel    │  │   Queue     │  │  Database   │
│             │  │ Template    │  │ Controller  │  │   Worker    │  │ (PostgreSQL)│
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Request Report               │                │                │
       │ (Daily, Branch Jakarta)         │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. GET /reports/daily           │                │
       │                │ Query: {date: "2024-01-16",    │                │
       │                │  branch_id: 1}                 │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 3. Check Cached Report          │
       │                │                │ - counting_reports table        │
       │                │                │ - WHERE report_type, date,      │
       │                │                │   branch_id                     │
       │                │                ├───────────────────────────────►│
       │                │                │                │                │
       │                │                │ 4. Report Found                 │
       │                │                │ - Return cached report          │
       │                │                │ - Skip generation               │
       │                │                │◄───────────────────────────────┤
       │                │                │                │                │
       │                │ 5. Return Blade View (Cached)   │                │
       │                │ - Render reports.show.blade     │                │
       │                │ - Display cached data           │                │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │                │                │ 6. OR Dispatch UpdateDailyReportJob│
       │                │                │ → Queue: reports (if not cached)│
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │                │ 7. Worker Picks Job│
       │                │                │                │ (UpdateDailyReportJob)│
       │                │                │                ├───────────────►│
       │                │                │                │                │
       │                │                │                │ 8. Query Raw Data│
       │                │                │                │ - re_id_branch_detections│
       │                │                │                │ - event_logs    │
       │                │                │                │ - device_masters│
       │                │                │                │ - company_branches│
       │                │                │                ├───────────────►│
       │                │                │                │                │
       │                │                │                │ 9. Calculate Stats│
       │                │                │                │ - Total detections│
       │                │                │                │ - Unique persons│
       │                │                │                │ - Hourly breakdown│
       │                │                │                │ - Device breakdown│
       │                │                │                │◄───────────────┤
       │                │                │                │                │
       │                │                │                │ 10. Generate report_data (JSONB)│
       │                │                │                │ - top_persons    │
       │                │                │                │ - hourly_breakdown│
       │                │                │                │ - device_breakdown│
       │                │                │                │ - peak_hour     │
       │                │                │                ├───────────────►│
       │                │                │                │                │
       │                │                │                │ 11. Save Report │
       │                │                │                │ - UPSERT counting_reports│
       │                │                │                │ - Set generated_at│
       │                │                │                ├───────────────►│
       │                │                │                │                │
       │                │                │                │ 12. Job Completed│
       │                │                │                │ - Remove from jobs│
       │                │                │                │ - Log success   │
       │                │                │                ├───────────────►│
```

**Notes:**

- ✅ **Cache-First**: Always check counting_reports table first
- ✅ **Async Generation**: Report generation in background queue
- ✅ **JSONB Storage**: report_data stored as JSONB for flexibility
- ✅ **Scheduled Jobs**: Daily at 01:00 for all branches (yesterday's data)
- ✅ **Materialized Views**: For complex queries (PostgreSQL)

### **6. CCTV Layout Management Workflow (Admin Only)**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Admin     │  │   Blade     │  │  Laravel    │  │  Database   │  │ WebSocket   │
│   User      │  │ Template    │  │ Controller  │  │             │  │   Server    │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Create Layout Request        │                │                │
       │ (4-window, positions config)    │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. POST /layouts                │                │
       │                │ Form: {layout_name, layout_type,│                │
       │                │  positions: [{branch_id,       │                │
       │                │   device_id, position_name}]}  │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 3. Validate Admin Access        │
       │                │                │ - Check admin role              │
       │                │                │ - Verify permissions            │
       │                │                │ - Log admin action              │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 4. Create Layout                │
       │                │                │ - Save to cctv_layout_settings  │
       │                │                │ - Validate layout type         │
       │                │                │ - Set created_by               │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 5. Configure Positions         │
       │                │                │ - Save to cctv_position_settings│
       │                │                │ - Validate branch/device        │
       │                │                │ - Set position settings        │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 6. Layout Created               │
       │                │                │ - Layout ID returned            │
       │                │                │ - Position count confirmed      │
       │                │                │◄───────────────┤                │
       │                │                │                │                │
       │                │ 7. Redirect to Blade View       │                │
       │                │ - Redirect to layouts.index     │                │
       │                │ - With success flash message    │                │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │                │ 8. Set Default Layout (Optional)│                │
       │                │ POST /layouts/{id}/set-default  │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 9. Update Default              │
       │                │                │ - Unset previous default        │
       │                │                │ - Set new default layout        │
       │                │                │ - Update user preferences       │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 10. Broadcast Layout Change     │
       │                │                │ - WebSocket: layout_updated      │
       │                │                │ - Data: {layout_id, is_default} │
       │                │                ├───────────────────────────────►│
       │                │                │                │                │
       │                │                │                │ 11. Notify All Clients│
       │                │                │                │ - Layout change event │
       │                │                │                │ - Update UI components│
       │                │                │                ├─────────────────►│
       │                │                │                │                │
       │                │ 12. Render Blade View           │                │                │
       │                │ - Load layouts.index.blade      │                │                │
       │                │ - Display success toast         │                │                │
       │                │ - Alpine.js updates UI          │                │                │
       │                │◄─────────────────────────────────────────────────┤                │
```

### **7. Queue Job Processing Workflow**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│  API        │  │   jobs      │  │   Queue     │  │ failed_jobs │  │   Admin     │
│ Controller  │  │   Table     │  │   Worker    │  │   Table     │  │ Dashboard   │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Dispatch Job                 │                │                │
       │ ProcessDetectionJob::dispatch() │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. Insert Job Record           │                │
       │                │ - queue: detections            │                │
       │                │ - payload: serialized data     │                │
       │                │ - available_at: now()          │                │
       │                ├───────────────►│                │                │
       │                │                │                │                │
       │                │ 3. Return 202 Accepted         │                │
       │                │ {job_id, status: processing}   │                │
       │◄───────────────┤                │                │                │
       │                │                │                │                │
       │                │                │ 4. Worker Picks Job (FIFO)     │
       │                │                │ - ORDER BY id ASC              │
       │                │                │ - LIMIT 1                       │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 5. Execute Job Handle()        │
       │                │                │ - Try/catch block              │
       │                │                │ - Database transaction         │
       │                │                │ - Business logic               │
       │                │                │◄───────────────┤                │
       │                │                │                │                │
       │                │                │ 6A. Success Path               │
       │                │                │ - Delete from jobs table       │
       │                │                │ - Log completion               │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 6B. Failure Path (Retry)       │
       │                │                │ - Increment attempts           │
       │                │                │ - Calculate backoff delay      │
       │                │                │ - Update available_at          │
       │                │                │ - available_at = now() + backoff│
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 7. Max Retries Reached         │
       │                │                │ - Call failed() method         │
       │                │                │ - Delete from jobs table       │
       │                │                │ - Insert to failed_jobs        │
       │                │                ├───────────────────────────────►│
       │                │                │                │                │
       │                │                │                │ 8. Admin View Failed Jobs│
       │                │                │                │ GET /admin/queue/failed  │
       │                │                │                ├─────────────────►│
       │                │                │                │                │
       │                │                │ 9. Retry Failed Job            │
       │                │                │ POST /admin/queue/retry/{id}   │
       │                │                │◄─────────────────────────────────┤
       │                │                │                │                │
       │                │                │ 10. Re-dispatch Job            │
       │                │                │ - Delete from failed_jobs      │
       │                │                │ - Insert to jobs table         │
       │                │                │ - Reset attempts = 0           │
       │                │                ├───────────────►│                │
```

**Queue Monitoring:**

- ✅ **Pending Jobs**: View all jobs in queue
- ✅ **Failed Jobs**: View and retry failed jobs
- ✅ **Job Logs**: Track job execution history
- ✅ **Worker Status**: Monitor worker health
- ✅ **Queue Size**: Alert if exceeds threshold (10,000 jobs)

---

### **8. Storage & File Management Workflow**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   User/     │  │  Laravel    │  │   Queue     │  │  Database   │  │   Storage   │
│   Device    │  │ Controller  │  │   Worker    │  │             │  │    Disk     │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Upload File (Detection Image)                │                │
       │ POST /api/detection/log         │                │                │
       │ - multipart/form-data           │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. Validate File               │                │
       │                │ - Check file type              │                │
       │                │ - Check file size (<10MB)      │                │
       │                │ - Generate unique filename     │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │ 3. Store File Temporarily      │                │
       │                │ - events/2024/01/16/           │                │
       │                │ - filename: {timestamp}_{uuid}.jpg│             │
       │                ├─────────────────────────────────────────────────►│
       │                │                │                │                │
       │                │ 4. Dispatch ProcessDetectionImageJob            │
       │                │ → Queue: images                │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │ 5. Worker Picks Job            │
       │                │                │ (ProcessDetectionImageJob)     │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 6. Image Processing            │
       │                │                │ - Load image (Intervention)    │
       │                │                │ - Resize if >1920x1080         │
       │                │                │ - Add watermark (timestamp)    │
       │                │                │ - Create thumbnail (320x240)   │
       │                │                │ - Optimize quality (85%)       │
       │                │                ├─────────────────────────────────►│
       │                │                │                │                │
       │                │                │ 7. Save to storage_files       │
       │                │                │ - file_path (unique)           │
       │                │                │ - file_type (MIME)             │
       │                │                │ - file_size (bytes)            │
       │                │                │ - metadata (JSONB: dimensions) │
       │                │                │ - related_table: event_logs    │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 8. Job Completed               │
       │                │                │ - Log success                  │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 9. Scheduled Cleanup (Daily 02:00)│
       │                │                │ CleanupOldFilesJob             │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 10. Query Old Files            │
       │                │                │ - WHERE created_at < 90 days   │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 11. Delete Files               │
       │                │                │ - Delete from storage_files    │
       │                │                │ - Delete physical files        │
       │                │                ├─────────────────────────────────►│
       │                │                │                │                │
       │                │                │ 12. Cleanup Complete           │
       │                │                │ - Log deleted count            │
       │                │                ├───────────────►│                │
```

**File Access (Secure):**

```
GET /storage/file?path={encrypted_path}
- Decrypt file path
- Verify user authorization
- Check file exists in storage_files
- Stream file with proper Content-Type
- Log file access
```

---

### **9. Real-time Dashboard Updates**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Device    │  │   API       │  │  Database   │  │ WebSocket   │  │   Client    │
│  (Camera)   │  │   Server    │  │             │  │   Server    │  │ Dashboard   │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Detection Event              │                │                │
       │ (New detection: count=5)        │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. Process & Save              │                │
       │                │ - Update detection logs        │                │
       │                │ - Update device totals         │                │
       │                │ - Create event log             │                │
       │                ├───────────────►│                │                │
       │                │                │                │                │
       │                │ 3. Database Updated            │                │
       │                │ - All tables updated           │                │
       │                │ - Statistics recalculated      │                │
       │                │◄───────────────┤                │                │
       │                │                │                │                │
       │                │ 4. Broadcast Update            │                │
       │                │ - WebSocket channel: dashboard │                │
       │                │ - Event: detection_update      │                │
       │                │ - Data: {device_id, count,     │                │
       │                │   branch_id, timestamp}        │                │
       │                ├───────────────────────────────►│                │
       │                │                │                │                │
       │                │                │                │ 5. Broadcast to Clients│
       │                │                │                │ - All connected clients│
       │                │                │                │ - Dashboard subscribers│
       │                │                │                ├─────────────────►│
       │                │                │                │                │
       │                │                │                │                │ 6. Update UI
       │                │                │                │                │ - Update statistics│
       │                │                │                │                │ - Refresh charts  │
       │                │                │                │                │ - Show notification│
       │                │                │                │                │◄─────────────────┤
       │                │                │                │                │
       │                │                │                │                │
       │                │ 7. WhatsApp Notification       │                │                │
       │                │ - Send to configured numbers   │                │                │
       │                │ - Track delivery status        │                │                │
       │                ├─────────────────────────────────────────────────┤                │
       │                │                │                │                │
       │                │ 8. Notification Status Update  │                │                │
       │                │ - WhatsApp delivered/read      │                │                │
       │                │ - Update notification status   │                │                │
       │                ├─────────────────────────────────────────────────┤                │
       │                │                │                │                │
       │                │ 9. Broadcast Notification Update                │                │
       │                │ - WebSocket: notification_update                │                │
       │                │ - Data: {event_id, status}     │                │                │
       │                ├───────────────────────────────►│                │                │
       │                │                │                │                │
       │                │                │                │ 10. Update Notification UI│
       │                │                │                │ - Show delivery status │
       │                │                │                │ - Update notification list│
       │                │                │                ├─────────────────►│
```

## 📊 Sequence Diagram Summary

### **Key Interactions:**

1. **Company Group Management Flow (Admin Only)**

   - Admin → Blade Template → Laravel Controller → Database → WebSocket → All Clients
   - Group creation, update, deletion with CRUD operations
   - Branch association management
   - Real-time group updates and notifications

2. **Person Detection Flow (Re-ID Based) - Async Queue**

   - Device → API → Queue (ProcessDetectionJob) → Database → Child Jobs → Client
   - **202 Accepted** response (immediate, non-blocking)
   - Background processing with database transaction
   - Job chaining: Detection → WhatsApp → Image → Report
   - Retry mechanism: 3 attempts with exponential backoff (10s, 30s, 60s)
   - Real-time person re-identification and tracking
   - Fire & forget WhatsApp notifications

3. **CCTV Stream Flow**

   - Client → Blade Template → Laravel Controller → Database → Stream Server → Display
   - Device-based stream management
   - Authentication, validation, and health monitoring

4. **API Management Flow**

   - Admin → Blade Template → Laravel Controller → Database → External Client
   - Complete credential lifecycle management
   - Device and Re-ID scoping support

5. **Report Generation Flow - Async Queue**

   - User → Blade → Controller → Queue (UpdateDailyReportJob) → Database → Response
   - Cache-first approach (counting_reports table)
   - Async generation for large datasets via queue worker
   - JSONB storage for flexible report_data
   - Scheduled generation daily at 01:00 via cron

6. **CCTV Layout Management Flow (Admin Only)**

   - Admin → Blade Template → Laravel Controller → Database → WebSocket → All Clients
   - Layout creation and position configuration
   - Real-time layout updates and notifications
   - Admin-controlled layout switching

7. **Queue Job Processing Flow**

   - API → jobs table → Queue Worker → Database → failed_jobs (if all retries fail)
   - FIFO processing with priority queues
   - Automatic retry with exponential backoff
   - Failed job tracking and manual retry
   - Admin dashboard for queue monitoring

8. **Storage & File Management Flow**

   - Upload → Validate → Store → Queue (ProcessDetectionImageJob) → Process → storage_files
   - Image optimization: resize, watermark, thumbnail
   - File registry tracking in storage_files table
   - Scheduled cleanup: CleanupOldFilesJob (daily at 02:00)
   - Secure file access with encrypted paths

9. **Real-time Updates Flow**

   - Device → API → Queue Worker → Database → WebSocket → All Clients
   - Live dashboard updates and notifications
   - Person tracking updates
   - Detection count updates
   - Layout change notifications
   - Group change notifications
   - Queue status updates

10. **Daily Log Aggregation Flow**

- Scheduler → Queue (AggregateApiUsageJob, AggregateWhatsAppDeliveryJob)
- Read daily log files (JSON Lines format)
- Parse and aggregate metrics
- Save to summary tables (api_usage_summary, whatsapp_delivery_summary)
- Log completion status

### **Performance Optimizations:**

- **Queue System**: 6 priority queues with 16 total workers
  - **critical**: 2 workers
  - **notifications**: 3 workers
  - **detections**: 5 workers (highest load)
  - **images**: 2 workers
  - **reports**: 2 workers (includes daily aggregation jobs)
  - **maintenance**: 2 workers
- **API Credential Caching**: 5-minute cache for credentials reduces DB queries
- **Rate Limiting**: Cache-based per-credential hourly limits
- **Async Updates**: `last_used_at` updated after response (non-blocking)
- **Async Processing**:
  - Detection processing (202 Accepted)
  - WhatsApp notifications (exponential backoff: 30s, 60s, 120s, 300s, 600s)
  - Image processing (resize, watermark, thumbnail)
  - Report generation (scheduled & on-demand)
  - **Daily log aggregation** (AggregateApiUsageJob, AggregateWhatsAppDeliveryJob)
- **File-based Logging**:
  - **API requests** → `storage/app/logs/api_requests/YYYY-MM-DD.log` (instant write)
  - **WhatsApp messages** → `storage/app/logs/whatsapp_messages/YYYY-MM-DD.log` (instant write)
  - **Daily aggregation** → Parse log files → Save to summary tables (01:30 daily)
  - **Prevents database bloat** for high-volume logs
- **Database Caching**: PostgreSQL materialized views for complex queries
- **Job Retry Mechanism**: Automatic retry with exponential backoff
- **WebSocket**: Real-time updates without polling (Laravel Echo)
- **Database Transactions**: ACID compliance with deadlock retry (5 attempts)
- **Read/Write Splitting**: Separate read and write connections
- **Composite Indexes**: Optimized multi-column indexes (GIN, B-tree, partial)
- **Eager Loading**: Prevent N+1 query problems
- **Rate Limiting**: Per-credential and per-IP throttling
- **Connection Pooling**: PgBouncer for PostgreSQL
- **File Storage**: Centralized registry with auto-cleanup (90 days)
- **Supervisor**: Auto-restart workers on failure

### **Error Handling:**

- **Validation**: Form Requests with comprehensive rules (StoreDetectionRequest)
- **Queue Retry**: Automatic retry with exponential backoff
  - Detection jobs: 3 attempts (10s, 30s, 60s)
  - WhatsApp jobs: 5 attempts (30s, 60s, 120s, 300s, 600s)
  - Image jobs: 3 attempts (10s, 30s, 60s)
  - Report jobs: 3 attempts (30s, 60s, 120s)
- **Failed Jobs Table**: Permanent storage after all retries exhausted
- **Monitoring**: Health checks, status tracking, performance metrics
  - **RequestResponseInterceptor**: Logs all API requests to daily files
  - **PerformanceMonitoringMiddleware**: Alerts for slow queries (> 1000ms) and high memory (> 128MB)
- **Logging**: Comprehensive audit trails
  - **Laravel logs**: Application errors, slow queries, high memory warnings
  - **File-based logs**: API requests, WhatsApp messages (JSON Lines daily files)
  - **Database summaries**: api_usage_summary, whatsapp_delivery_summary (aggregated)
- **Transactions**: Automatic rollback on errors (DB::transaction with 5 retries)
- **Exception Handler**: Global API error handling (ApiResponseHelper)
- **Scheduled Jobs**:
  - **AggregateApiUsageJob**: Daily at 01:30 (parse API logs → summary table)
  - **AggregateWhatsAppDeliveryJob**: Daily at 01:30 (parse WhatsApp logs → summary table)
  - **CleanupOldFilesJob**: Daily at 02:00 (delete files > 90 days)
  - **Log Cleanup**: Auto-delete old log files (configurable retention)

---

### **10. Daily Log Aggregation Workflow**

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│  Scheduler  │  │  Queue      │  │  Log Files  │  │  Database   │  │  Laravel    │
│  (Cron)     │  │  Worker     │  │  (JSON)     │  │ (Summary)   │  │   Logs      │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
       │                │                │                │                │
       │ 1. Daily Cron (01:30)           │                │                │
       │ Dispatch AggregateApiUsageJob   │                │                │
       ├───────────────►│                │                │                │
       │                │                │                │                │
       │                │ 2. Worker Picks Job            │                │
       │                │ (Queue: reports)               │                │
       │                ├───────────────►│                │                │
       │                │                │                │                │
       │                │                │ 3. Read Log File                │
       │                │                │ logs/api_requests/2024-01-16.log│
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │                │ 4. Parse JSON Lines             │
       │                │                │ - One JSON per line             │
       │                │                │ - Extract: response_time,       │
       │                │                │   query_count, memory_usage     │
       │                │                │◄───────────────┤                │
       │                │                │                │                │
       │                │ 5. Aggregate Data               │                │
       │                │ - Group by credential+endpoint+method           │
       │                │ - Calculate avg/max/min metrics │                │
       │                │ - Count success vs errors       │                │
       │                ├─────────────────────────────────┤                │
       │                │                │                │                │
       │                │                │ 6. Save to api_usage_summary    │
       │                │                │ - UPSERT per endpoint           │
       │                │                ├───────────────►│                │
       │                │                │                │                │
       │                │ 7. Log Completion               │                │
       │                │ - Processed: 15,234 entries     │                │
       │                │ - Summary rows: 45              │                │
       │                ├─────────────────────────────────────────────────►│
```

**Benefits:**

- ✅ **Scalable**: File-based logs prevent database bloat
- ✅ **Fast**: Instant file append vs slow database INSERT
- ✅ **Efficient**: Daily aggregation instead of real-time summarization
- ✅ **Clean**: Database only stores aggregated summaries
- ✅ **Flexible**: Raw logs available for detailed analysis

---

### **Frontend Architecture (Laravel Blade + Alpine.js):**

#### **Blade Components:**

```php
// resources/views/components/button.blade.php
@props(['variant' => 'primary', 'type' => 'button'])

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => "btn btn-{$variant}"]) }}>
    {{ $slot }}
</button>
```

#### **Service Layer Architecture:**

```php
// app/Services/ReIdMasterService.php
class ReIdMasterService extends BaseService
{
    public function getBranchDetectionCounts(string $reId, string $date)
    {
        return DB::table('re_id_branch_detections as rbd')
            ->join('company_branches as cb', 'rbd.branch_id', '=', 'cb.id')
            ->where('rbd.re_id', $reId)
            ->whereDate('rbd.detection_timestamp', $date)
            ->select(
                'cb.id as branch_id',
                'cb.branch_name',
                'cb.branch_code',
                DB::raw('COUNT(rbd.id) as detection_count'),
                DB::raw('SUM(rbd.detected_count) as total_detected_count'),
                DB::raw('MIN(rbd.detection_timestamp) as first_detection'),
                DB::raw('MAX(rbd.detection_timestamp) as last_detection')
            )
            ->groupBy('cb.id', 'cb.branch_name', 'cb.branch_code')
            ->orderBy('total_detected_count', 'desc')
            ->get();
    }
}
```

#### **UI Component Refactoring:**

```html
<!-- Before: Manual HTML -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Person Information</h3>
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm font-medium text-blue-600">Total Detections</div>
            <div class="text-2xl font-bold text-blue-900">15</div>
        </div>
    </div>
</div>

<!-- After: Reusable Components -->
<x-card title="Person Information">
    <div class="grid grid-cols-2 gap-4">
        <x-stat-card title="Total Detections" value="15" color="blue" />
        <x-stat-card title="Branches" value="2" color="green" />
    </div>
</x-card>

<!-- Branch Detection Summary -->
<x-card title="Branch Detection Summary">
    @foreach($branchDetectionCounts as $branch)
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div class="flex-1">
                <div class="font-medium text-gray-900">{{ $branch->branch_name }}</div>
                <div class="text-sm text-gray-500">{{ $branch->branch_code }}</div>
            </div>
            <div class="flex items-center space-x-4 text-sm">
                <div class="text-center">
                    <div class="text-xs text-gray-500">Total Count</div>
                    <x-badge color="blue" size="sm">{{ $branch->total_detected_count }}</x-badge>
                </div>
            </div>
        </div>
    @endforeach
</x-card>
```

#### **Alpine.js Integration:**

```html
<!-- resources/views/groups/index.blade.php -->
<div
  x-data="{ 
    showModal: false, 
    selectedGroup: null,
    deleteGroup(id) {
        this.selectedGroup = id;
        this.showModal = true;
    }
}"
>
  <!-- Group list with Alpine.js interactivity -->
  <button @click="deleteGroup({{ $group->id }})">Delete</button>

  <!-- Modal component -->
  <x-modal x-show="showModal" @close="showModal = false">
    <!-- Modal content -->
  </x-modal>
</div>
```

#### **Real-time Updates with Laravel Echo:**

```javascript
// resources/js/app.js
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;
window.Echo = new Echo({
  broadcaster: "pusher",
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
});

// Listen for real-time updates
Echo.channel("dashboard").listen("GroupUpdated", (e) => {
  // Alpine.js updates UI
  Alpine.store("groups").refresh();
});

// Listen for branch detection updates
Echo.channel("dashboard").listen("BranchDetectionUpdated", (e) => {
  // Update branch detection summary
  Alpine.store("branchDetection").refresh();
});
```

#### **Chart.js Integration:**

```html
<!-- resources/views/dashboard/index.blade.php -->
<canvas id="detectionChart" x-data="chartComponent()"></canvas>

<script>
  function chartComponent() {
      return {
          chart: null,
          init() {
              const ctx = document.getElementById('detectionChart');
              this.chart = new Chart(ctx, {
                  type: 'line',
                  data: @json($chartData)
              });
          }
      }
  }
</script>
```

#### **Key Features:**

- ✅ **Reusable Components**: x-button, x-card, x-badge, x-stat-card, x-action-dropdown
- ✅ **Service Layer**: Business logic separated from controllers
- ✅ **Branch Detection Summary**: Aggregated statistics per branch
- ✅ **Real-time Updates**: WebSocket integration with Alpine.js
- ✅ **DRY Principles**: Consistent use of reusable components
- ✅ **Performance**: Optimized queries and caching

---

_These sequence diagrams provide a detailed view of how each major workflow operates within the CCTV Dashboard system using Laravel Blade Templates and Alpine.js, ensuring proper understanding of data flow and system interactions._

## 🆕 Latest Updates (Version 1.3.0)

### **Branch Detection Summary Table**
- ✅ **Single Column Layout**: Clean card-based design
- ✅ **Badge Integration**: Total Count displayed with blue badge
- ✅ **MIN/MAX Timestamps**: First and Last detection times per branch
- ✅ **Service Layer**: `ReIdMasterService::getBranchDetectionCounts()` method
- ✅ **Real-time Updates**: WebSocket integration for live updates

### **UI Component Refactoring**
- ✅ **Reusable Components**: x-button, x-card, x-badge, x-stat-card
- ✅ **DRY Principles**: Consistent use across all pages
- ✅ **Service Layer**: Business logic separated from controllers
- ✅ **Performance**: Optimized queries and caching

### **Person Tracking Enhancements**
- ✅ **Ordering**: Person Tracking table ordered by `last_detected_at DESC`
- ✅ **Detection History**: Repositioned below Person Information
- ✅ **Count Column Removal**: Streamlined table layout
- ✅ **Branch Detection Counts**: Aggregated statistics per branch

### **Service Layer Architecture**
- ✅ **ReIdMasterService**: Person tracking and detection management
- ✅ **WhatsAppSettingsService**: WhatsApp configuration management
- ✅ **BaseService**: Common service functionality
- ✅ **Separation of Concerns**: Controllers handle HTTP, services handle business logic
- ✅ **Reusability**: Services can be used across multiple controllers
- ✅ **Testability**: Business logic can be unit tested independently

### **Database Optimizations**
- ✅ **Query Optimization**: Efficient branch detection count queries with JOIN operations
- ✅ **Data Aggregation**: MIN/MAX timestamps and COUNT/SUM operations per branch
- ✅ **Index Usage**: Proper indexing for branch detection count queries
- ✅ **Performance**: Optimized queries and caching in service layer

### **UI/UX Improvements**
- ✅ **Single Column Layout**: Clean card-based design for branch detection summary
- ✅ **Badge Integration**: Total Count displayed with blue badge for better visual hierarchy
- ✅ **Detection History Repositioning**: Moved below Person Information for better flow
- ✅ **Count Column Removal**: Streamlined table layout for better readability
- ✅ **Consistent Components**: Reusable Blade components across all pages

### **API Enhancements**
- ✅ **Branch Detection Data**: Enhanced person detail API with branch detection counts
- ✅ **Aggregated Statistics**: MIN/MAX detection timestamps per branch
- ✅ **Performance Optimization**: Efficient database queries with proper indexing
- ✅ **Response Format**: Consistent JSON structure with meta information

### **Version 1.3.0 Summary**
- ✅ **Branch Detection Summary Table**: Added to `/re-id-masters/` detail page
- ✅ **Service Layer Enhancement**: Added `getBranchDetectionCounts()` method
- ✅ **UI Component Consistency**: Maintained reusable Blade components
- ✅ **Database Query Optimization**: Efficient JOIN operations and aggregation
- ✅ **Real-time Updates**: WebSocket integration for live updates
- ✅ **Performance Improvements**: Optimized queries and caching
