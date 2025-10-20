# 📡 API Reference - CCTV Dashboard

## 🔗 Base URL

```
Production: https://your-domain.com/api
Development: http://localhost:8000/api
```

## 🔐 Authentication Methods

### 1. API Key Authentication (Primary - Recommended)

For external device/system integration. **All credentials have global access** to all branches and devices.

**Managed via:** `/api-credentials` (Admin only web interface)

**Headers:**

```
X-API-Key: your-api-key
X-API-Secret: your-api-secret
Accept: application/json
```

**Features:**

- 🌐 **Global Scope**: Access to all branches & devices (no restrictions)
- 🔑 **Full Permissions**: Read, write, and delete operations
- ⚡ **High Rate Limit**: 10,000 requests/hour per credential
- 🔒 **Security**: Timing-safe secret verification (`hash_equals`)
- 📊 **Rate Limit Headers**: Track remaining quota in response
- 🧪 **Test Interface**: Built-in API testing at `/api-credentials/{id}/test`
- ⚡ **Performance**: 5-minute credential caching, async updates

**Quick Start:**

1. Admin creates credential at `/api-credentials/create`
2. Enter name and optional expiration (only 3 fields!)
3. **Save the API secret** (shown only once!)
4. Test API at `/api-credentials/{id}/test`
5. Use in external applications with headers above

### 2. Sanctum Token Authentication

For user-based API access (web dashboard, mobile apps).

**Header:**

```
Authorization: Bearer user-generated-token
```

### 3. Static Token Authentication

For legacy system-to-system communication.

**Header:**

```
Authorization: Bearer your-static-token
```

---

## 📋 API Response Standards

All API responses follow a standardized format:

### Success Response (200, 201)

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data
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

### Error Response (400, 401, 403, 404, 422, 500)

```json
{
  "success": false,
  "message": "Error message",
  "error": {
    "code": "ERROR_CODE",
    "details": "Detailed error information",
    "field": "field_name"
  },
  "meta": {
    "timestamp": "2024-01-16T14:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 2,
    "memory_usage": "1.8 MB",
    "execution_time": "0.065s"
  }
}
```

### Paginated Response

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

## 📊 Performance Metrics in Response

All API responses include performance metrics in the `meta` section:

| Metric           | Type    | Description                         | Example    |
| ---------------- | ------- | ----------------------------------- | ---------- |
| `query_count`    | Integer | Number of database queries executed | `5`        |
| `memory_usage`   | String  | Memory used by the request          | `"2.5 MB"` |
| `execution_time` | String  | Total request execution time        | `"0.125s"` |

**Also available in HTTP Headers:**

```
X-Query-Count: 5
X-Memory-Usage: 2.5MB
X-Execution-Time: 0.125s
X-API-Version: 1.0
X-Request-ID: uuid-here
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 995
```

**Configuration:**

```env
# .env
DB_LOG_QUERIES=true              # Enable query logging
PERFORMANCE_MONITORING=true      # Enable performance tracking
PERFORMANCE_IN_RESPONSE=true     # Include in JSON response
PERFORMANCE_IN_HEADERS=true      # Include in HTTP headers
SLOW_QUERY_THRESHOLD=1000        # Alert for queries > 1000ms
HIGH_MEMORY_THRESHOLD=128        # Alert for memory > 128MB
```

**Benefits:**

- ✅ **Performance Monitoring**: Track API performance in real-time
- ✅ **Query Optimization**: Identify N+1 query problems
- ✅ **Memory Leaks**: Detect high memory usage
- ✅ **Slow Endpoints**: Find bottlenecks
- ✅ **Client-side Monitoring**: Clients can track API performance

---

## 🔒 HTTP Status Codes

| Code | Name                  | Usage                                      |
| ---- | --------------------- | ------------------------------------------ |
| 200  | OK                    | Successful GET, PUT, PATCH requests        |
| 201  | Created               | Successful POST request (resource created) |
| 202  | Accepted              | Async processing (queued)                  |
| 204  | No Content            | Successful DELETE request                  |
| 400  | Bad Request           | Invalid request format                     |
| 401  | Unauthorized          | Authentication required                    |
| 403  | Forbidden             | Insufficient permissions                   |
| 404  | Not Found             | Resource not found                         |
| 422  | Unprocessable Entity  | Validation failed                          |
| 429  | Too Many Requests     | Rate limit exceeded                        |
| 500  | Internal Server Error | Server error                               |
| 503  | Service Unavailable   | Service temporarily unavailable            |

---

## 📡 API Endpoints

### 🔐 Authentication Endpoints

#### Register User

```http
POST /api/register
Content-Type: application/json
```

**Request:**

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**

```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "viewer",
      "created_at": "2024-01-16T08:00:00Z"
    },
    "token": "1|abcdef123456..."
  },
  "meta": {
    "timestamp": "2024-01-16T08:00:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 3,
    "memory_usage": "1.5 MB",
    "execution_time": "0.087s"
  }
}
```

#### Login User

```http
POST /api/login
Content-Type: application/json
```

**Request:**

```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:** Same as register

#### Logout User

```http
POST /api/logout
Authorization: Bearer user-token
```

**Response:**

```json
{
  "success": true,
  "message": "Logged out successfully",
  "meta": {
    "timestamp": "2024-01-16T08:00:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 1,
    "memory_usage": "1.2 MB",
    "execution_time": "0.035s"
  }
}
```

---

### 🧑 Person Detection & Re-ID Endpoints

#### Log Person Detection (Async)

```http
POST /api/v1/detection/log
X-API-Key: your-api-key
X-API-Secret: your-api-secret
Content-Type: multipart/form-data
```

**Request:**

```json
{
  "re_id": "person_001_abc123",        // Required - Person identifier
  "branch_id": 1,                      // Required - Branch ID
  "device_id": "CAM_JKT001_001",       // Required - Device ID
  "detected_count": 1,                 // Optional - Number of detections (default: 1)
  "detection_data": {                  // Optional - Detection metadata
    "confidence": 0.95,
    "location": "entrance",
    "appearance_features": ["blue_shirt", "black_pants"],
    "bounding_box": {
      "x": 120,
      "y": 150,
      "width": 80,
      "height": 200
    }
  },
  "image": "<file>" // Optional                   // Optional - Image file (JPEG/PNG) stored in whatsapp_detection_dd-mm-yyyy folder
}
```

**Field Requirements:**
- ✅ **Required**: `re_id`, `branch_id`, `device_id`
- ⚪ **Optional**: `detected_count`, `detection_data`, `image` ( Optional if provided, will be processed and sent via WhatsApp)

**Image Storage Structure:**
```
storage/app/
├── whatsapp_detection_08-10-2025/     // Today's folder
│   ├── 1696789123_abc123.jpg         // Original image
│   ├── 1696789123_abc123_thumb.jpg   // Thumbnail (320x240)
│   └── ...
├── whatsapp_detection_09-10-2025/     // Tomorrow's folder
│   └── ...
└── whatsapp_detection_10-10-2025/     // Day after tomorrow
    └── ...
```

**Image Processing:**
- ✅ **Auto-resize**: Images > 1920x1080 are resized maintaining aspect ratio
- ✅ **Watermark**: Timestamp and event ID added to image
- ✅ **Thumbnail**: 320x240 thumbnail created automatically
- ✅ **Quality**: Optimized to 85% quality for original, 75% for thumbnail

**Re-ID Uniqueness Logic:**
- ✅ **Daily Unique**: Same re_id can exist on different dates
- ✅ **New Person Each Day**: Each day creates a new person record
- ✅ **Unique Constraint**: (re_id, detection_date)
- ✅ **Historical Tracking**: Maintains detection history per day

**Response (202 Accepted - Async Processing):**

```json
{
  "success": true,
  "message": "Detection event received and queued successfully",
  "data": {
    "job_id": "4ffd24b1-fcaf-4b2c-98ac-7ecb4097c611",
    "status": "processing",
    "message": "Detection queued for processing",
    "re_id": "person_001_abc123",
    "branch_id": 1,
    "device_id": "CAM_JKT001_001"
  },
  "meta": {
    "timestamp": "2025-10-08T23:00:10+07:00",
    "version": "1.0",
    "request_id": "4b500cc9-1664-42c4-abb9-0e16ef7a7113",
    "query_count": 0,
    "memory_usage": "1.43 MB",
    "execution_time": "50.87ms"
  }
}
```

**Async Processing Chain:**
1. ✅ **ProcessDetectionJob** → Creates ReIdMaster + EventLog
2. ✅ **ProcessDetectionImageJob** → Resizes, watermarks, creates thumbnail
3. ✅ **SendWhatsAppNotificationJob** → Sends WhatsApp notification via WAHA
4. ✅ **UpdateDailyReportJob** → Updates daily statistics

**Note:** Returns `202 Accepted` because processing is asynchronous. Performance metrics show only validation time, not full processing time.

#### Get Person Info (Re-ID)

```http
GET /api/person/{re_id}
Authorization: Bearer api-key
```

**Query Parameters:**

- `date` (optional): Specific date (default: today)

**Response:**

```json
{
  "success": true,
  "message": "Person retrieved successfully",
  "data": {
    "re_id": "person_001_abc123",
    "detection_date": "2024-01-16",
    "detection_time": "2024-01-16T08:30:00Z",
    "person_name": "John Doe",
    "total_detection_branch_count": 2,
    "total_actual_count": 15,
    "first_detected_at": "2024-01-16T08:30:00Z",
    "last_detected_at": "2024-01-16T16:45:00Z",
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
    ],
    "branch_detection_counts": [
      {
        "branch_id": 1,
        "branch_name": "Jakarta Central Branch",
        "branch_code": "JKT001",
        "detection_count": 2,
        "total_detected_count": 2,
        "first_detection": "2024-01-16T08:30:00Z",
        "last_detection": "2024-01-16T16:45:00Z"
      },
      {
        "branch_id": 2,
        "branch_name": "Jakarta South Branch",
        "branch_code": "JKT002",
        "detection_count": 1,
        "total_detected_count": 1,
        "first_detection": "2024-01-16T10:15:00Z",
        "last_detection": "2024-01-16T10:15:00Z"
      }
    ]
  },
  "meta": {
    "timestamp": "2024-01-16T14:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 6,
    "memory_usage": "2.8 MB",
    "execution_time": "0.156s"
  }
}
```

**Branch Detection Counts Fields:**

- `detection_count`: Number of detection events per branch
- `total_detected_count`: Sum of all detected_count values per branch
- `first_detection`: Earliest detection timestamp per branch
- `last_detection`: Latest detection timestamp per branch

#### Get Branch Detections

```http
GET /api/branch/{branch_id}/detections
Authorization: Bearer api-key
```

**Query Parameters:**

- `per_page` (optional): Items per page (default: 15)
- `date` (optional): Filter by date

**Response:** Paginated detection records

---

### 📱 WhatsApp Integration (WAHA)

#### WhatsApp Configuration

**WAHA (WhatsApp HTTP API) Integration:**
- ✅ **WAHA Server**: External WhatsApp HTTP API server
- ✅ **Session Management**: Automatic session handling
- ✅ **Image Support**: Send images with messages
- ✅ **Phone Formatting**: Automatic Indonesia (+62) formatting
- ✅ **Error Handling**: Comprehensive error handling and retry logic

**Environment Configuration:**
```env
WAHA_URL=http://localhost:3000
WAHA_SESSION_ID=default
WHATSAPP_DEFAULT_COUNTRY_CODE=62
WHATSAPP_TIMEOUT=30
WHATSAPP_RETRY_ATTEMPTS=3
WHATSAPP_RETRY_DELAY=5
```

**Automatic WhatsApp Notifications:**
- ✅ **Trigger**: Detection events automatically trigger WhatsApp notifications
- ✅ **Template Support**: Customizable message templates with variables
- ✅ **Multi-recipient**: Send to multiple phone numbers
- ✅ **Image Attachment**: Include detection images
- ✅ **Queue Processing**: Async processing via SendWhatsAppNotificationJob

**Message Template Variables:**
```json
{
  "branch_name": "Jakarta Central",
  "device_name": "Main Entrance Camera",
  "device_id": "CAM_JKT001_001",
  "re_id": "person_001_abc123",
  "person_name": "John Doe",
  "detected_count": 1,
  "timestamp": "2025-10-08 23:00:10",
  "date": "2025-10-08",
  "time": "23:00:10"
}
```

**Example WhatsApp Message:**
```
🚨 Detection Alert
Branch: Jakarta Central
Device: Main Entrance Camera
Person: person_001_abc123
Time: 2025-10-08 23:00:10
Count: 1 detection(s)
```

#### WhatsApp Helper Functions

**Send Message:**
```php
WhatsAppHelper::sendMessage(
    '081234567890',           // Phone number
    'Detection alert message', // Message text
    'path/to/image.jpg',      // Optional image
    ['event_log_id' => 123]   // Optional metadata
);
```

**Check Session Status:**
```php
WhatsAppHelper::checkSessionStatus();
```

**Start Session:**
```php
WhatsAppHelper::startSession();
```

---

### 📹 Device Management Endpoints

#### Get Devices

```http
GET /api/devices
Authorization: Bearer api-key
```

**Query Parameters:**

- `branch_id` (optional): Filter by branch
- `device_type` (optional): camera, node_ai, mikrotik, cctv
- `status` (optional): active, inactive

**Response:**

```json
{
  "success": true,
  "message": "Devices retrieved successfully",
  "data": [
    {
      "id": 1,
      "device_id": "CAMERA_001",
      "device_name": "Main Entrance Camera",
      "device_type": "camera",
      "branch_id": 1,
      "url": "rtsp://192.168.1.100:554/stream1",
      "status": "active"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 25,
    "last_page": 2
  },
  "meta": {
    "timestamp": "2024-01-16T14:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 10,
    "memory_usage": "4.1 MB",
    "execution_time": "0.312s"
  }
}
```

---

### 🚨 Event Management Endpoints

#### Get Event Logs

```http
GET /api/events
Authorization: Bearer api-key
```

**Query Parameters:**

- `branch_id` (optional): Filter by branch
- `device_id` (optional): Filter by device
- `event_type` (optional): detection, alert, motion, manual
- `start_date` (optional): Start date
- `end_date` (optional): End date

**Response:** Paginated event logs

#### Configure Event Settings

```http
POST /api/event/settings
Authorization: Bearer api-key
Content-Type: application/json
```

**Request:**

```json
{
  "branch_id": 1,
  "device_id": "CAMERA_001",
  "is_active": true,
  "send_image": true,
  "send_message": true,
  "whatsapp_enabled": true,
  "whatsapp_numbers": ["+628123456789", "+628987654321"],
  "message_template": "Alert from {branch_name}: Person detected at {device_name}"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Event settings updated successfully",
  "data": {
    "id": 1,
    "branch_id": 1,
    "device_id": "CAMERA_001",
    "is_active": true,
    "whatsapp_enabled": true
  },
  "meta": {
    "timestamp": "2024-01-16T14:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 4,
    "memory_usage": "2.1 MB",
    "execution_time": "0.098s"
  }
}
```

---

### 📺 CCTV Stream Endpoints

#### Get Branch Streams

```http
GET /api/stream/branch/{branch_id}
Authorization: Bearer api-key
```

**Response:**

```json
{
  "success": true,
  "message": "Streams retrieved successfully",
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
  },
  "meta": {
    "timestamp": "2024-01-16T14:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 7,
    "memory_usage": "3.5 MB",
    "execution_time": "0.187s"
  }
}
```

---

### 🎛️ CCTV Layout Management Endpoints (Admin Only)

#### Get All Layouts

```http
GET /api/admin/cctv/layouts
Authorization: Bearer admin-token
```

**Response:**

```json
{
  "success": true,
  "message": "Layouts retrieved successfully",
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
          "branch_name": "Jakarta Central",
          "device_name": "Main Entrance Camera",
          "device_id": "CAMERA_001",
          "position_name": "Main Entrance",
          "is_enabled": true,
          "quality": "high"
        }
      ]
    }
  ],
  "meta": {
    "timestamp": "2024-01-16T14:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 12,
    "memory_usage": "4.8 MB",
    "execution_time": "0.298s"
  }
}
```

#### Create Layout

```http
POST /api/admin/cctv/layouts
Authorization: Bearer admin-token
Content-Type: application/json
```

**Request:**

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
      "quality": "high"
    }
  ]
}
```

**Response:**

```json
{
  "success": true,
  "message": "Layout created successfully",
  "data": {
    "id": 2,
    "layout_name": "Custom 6-Window Layout",
    "layout_type": "6-window",
    "total_positions": 6
  },
  "meta": {
    "timestamp": "2024-01-16T14:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 8,
    "memory_usage": "3.2 MB",
    "execution_time": "0.178s"
  }
}
```

---

### 📈 Reporting Endpoints

#### Get Daily Report

```http
GET /api/reports/daily
Authorization: Bearer api-key
```

**Query Parameters:**

- `date` (optional): Report date (default: yesterday)
- `branch_id` (optional): Filter by branch

**Response:**

```json
{
  "success": true,
  "message": "Daily report retrieved successfully",
  "data": {
    "report_type": "daily",
    "report_date": "2024-01-16",
    "branch_id": 1,
    "total_devices": 5,
    "total_detections": 35,
    "total_events": 10,
    "unique_device_count": 3,
    "unique_person_count": 3,
    "report_data": {
      "top_persons": [
        {
          "re_id": "person_001_abc123",
          "count": 15
        }
      ],
      "hourly_breakdown": [...],
      "device_breakdown": [...],
      "peak_hour": 14,
      "peak_hour_count": 25
    }
  },
  "meta": {
    "timestamp": "2024-01-16T14:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 15,
    "memory_usage": "5.6 MB",
    "execution_time": "0.421s"
  }
}
```

---

### 🔑 API Credential Management (Admin Only - Web Interface)

API Credentials are managed through web interface at `/api-credentials` (admin only).

#### Create API Credential (Web Form)

**Access:** `/api-credentials/create` (Admin role required)

**Form Fields:**

```json
{
  "credential_name": "Mobile App API Key",
  "expires_at": "2025-12-31", // Optional
  "status": "active"
}
```

**Auto-Generated:**

- `api_key`: Unique 40-character key (e.g., `cctv_live_abc123xyz789...`)
- `api_secret`: Secure 40-character secret (shown once!)
- `branch_id`: `null` (Global access)
- `device_id`: `null` (Global access)
- `permissions`: `{"read": true, "write": true, "delete": true}` (Full access)
- `rate_limit`: `10000` (10,000 requests/hour)

**Response After Creation:**

- Redirects to credential details page
- Shows `api_secret` **once** in session (must be saved!)
- Provides "Test API" interface
- Displays credential information

#### Test API Credential

**Access:** `/api-credentials/{id}/test` (Admin only)

**Features:**

- 🧪 Live API testing with web interface
- 📊 Response display (status, headers, body)
- ⏱️ Response time measurement
- 🔢 Rate limit tracking
- 📋 Copy cURL commands
- 🎨 Syntax-highlighted JSON responses

#### API Credential Properties

All credentials have:

- 🌐 **Global Scope**: Access to all branches and devices
- 🔑 **Full Permissions**: Read, write, and delete operations
- ⚡ **High Rate Limit**: 10,000 requests per hour
- 🔒 **Secure**: Timing-safe secret comparison
- 📊 **Monitored**: Last used timestamp and usage tracking

---

## 🚨 Error Codes Reference

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
| `TRACKING_DISABLED`    | 403         | Person tracking disabled    |
|| `WAHA_SESSION_ERROR`   | 503         | WAHA session not available  |
|| `WAHA_UPLOAD_FAILED`   | 503         | WAHA image upload failed    |
|| `IMAGE_PROCESSING_FAILED` | 500       | Image resize/watermark failed |
|| `RE_ID_CONSTRAINT_ERROR` | 400        | Re-ID uniqueness violation  |

---

## 📈 Rate Limiting

| Endpoint Type            | Rate Limit              | Window   | Implementation        |
| ------------------------ | ----------------------- | -------- | --------------------- |
| **Authentication**       | 5 requests              | 1 minute | Sanctum throttle      |
| **Detection Logging**    | Per credential (10,000) | 1 hour   | ApiKeyAuth middleware |
| **User Management**      | 60 requests             | 1 minute | Sanctum throttle      |
| **Static Token**         | 100 requests            | 1 minute | ValidateStaticToken   |
| **API Credential Based** | 10,000 requests         | 1 hour   | Cache-based tracking  |

**Rate Limit Headers:**

All API responses protected by `api.key` middleware include:

```
X-RateLimit-Limit: 10000
X-RateLimit-Remaining: 9847
X-RateLimit-Reset: 1728399600
```

**Rate Limit Features:**

- ✅ Per-credential tracking using Laravel Cache
- ✅ Hourly reset (automatic at start of each hour)
- ✅ 429 status with retry-after information
- ✅ Headers show remaining quota
- ✅ Credential caching (5 minutes) for performance

**Performance Headers:**

```
X-Query-Count: 5
X-Memory-Usage: 2.5MB
X-Execution-Time: 0.125s
```

---

## 🧪 Testing Examples

### cURL Examples

#### Person Detection

```bash
# Minimal request (only required fields)
curl -X POST http://localhost:8000/api/v1/detection/log \
  -H "X-API-Key: cctv_test_dev_key" \
  -H "X-API-Secret: secret_test_dev_2024" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "re_id": "test_person_001",
    "branch_id": 1,
    "device_id": "CAM_JKT001_001"
  }'

# Full request (with optional fields)
curl -X POST http://localhost:8000/api/v1/detection/log \
  -H "X-API-Key: cctv_test_dev_key" \
  -H "X-API-Secret: secret_test_dev_2024" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "re_id": "test_person_001",
    "branch_id": 1,
    "device_id": "CAM_JKT001_001",
    "detected_count": 1,      // Optional feature comming soon
    "detection_data": {       // Optional feature comming soon
      "confidence": 0.95, 
      "location": "entrance", 
      "appearance_features": ["blue_shirt", "black_pants"]
    }
  }'
```

**With Image Upload:**
```bash
curl -X POST http://localhost:8000/api/v1/detection/log \
  -H "X-API-Key: cctv_test_dev_key" \
  -H "X-API-Secret: secret_test_dev_2024" \
  -H "Accept: application/json" \
  -F "re_id=test_person_001" \
  -F "branch_id=1" \
  -F "device_id=CAM_JKT001_001" \
  -F "detected_count=1" \     // Optional
  -F "detection_data[confidence]=0.95" \      // Optional
  -F "detection_data[location]=entrance" \    // Optional
  -F "image=@/path/to/image.jpg"
```

#### Get Person Info

```bash
curl -X GET "https://api.cctv.com/api/person/person_001_abc123?date=2024-01-16" \
  -H "X-API-Key: your-api-key" \
  -H "X-API-Secret: your-api-secret"
```

### JavaScript (Fetch API)

```javascript
// Detection Logging
const logDetection = async (data) => {
  const response = await fetch("/api/v1/detection/log", {
    method: "POST",
    headers: {
      "X-API-Key": "your-api-key",
      "X-API-Secret": "your-api-secret",
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  });

  return await response.json();
};

// Example usage
const detectionData = {
  re_id: "person_001_abc123",        // Required
  branch_id: 1,                       // Required
  device_id: "CAMERA_001",           // Required
  detected_count: 1,                  // Optional (default: 1)
  detection_data: {                   // Optional
    confidence: 0.95,
    bounding_box: {x: 120, y: 150, width: 80, height: 200}
  }
};

const result = await logDetection(detectionData);

// Get Person Info
const getPersonInfo = async (reId) => {
  const response = await fetch(`/api/person/${reId}`, {
    headers: {
      "X-API-Key": "your-api-key",
      "X-API-Secret": "your-api-secret",
    },
  });

  return await response.json();
};
```

### Python Example

```python
import requests

# Detection Logging
def log_detection(data):
    headers = {
        'X-API-Key': 'your-api-key',
        'X-API-Secret': 'your-api-secret',
        'Content-Type': 'application/json'
    }

    response = requests.post(
        'https://api.cctv.com/api/detection/log',
        headers=headers,
        json=data
    )

    return response.json()

# Usage
detection_data = {
    're_id': 'person_001_abc123',        # Required
    'branch_id': 1,                       # Required
    'device_id': 'CAMERA_001',           # Required
    'detected_count': 1,                  # Optional (default: 1)
    'detection_data': {                   # Optional
        'confidence': 0.95,
        'bounding_box': {'x': 120, 'y': 150, 'width': 80, 'height': 200}
    }
}

result = log_detection(detection_data)
print(result)
```

---

## 🔄 Async Processing (Queue Jobs)

Many endpoints use async processing for better performance:

- ✅ **Detection Logging**: Returns `202 Accepted` immediately
- ✅ **WhatsApp Notifications**: Queued in background
- ✅ **Image Processing**: Async resizing/optimization
- ✅ **Report Generation**: Queued for large datasets

**Job Status Tracking:**

```http
GET /api/jobs/{job_id}/status
Authorization: Bearer api-key
```

**Response:**

```json
{
  "success": true,
  "data": {
    "job_id": "uuid-here",
    "status": "completed",
    "progress": 100,
    "result": {
      "re_id": "person_001_abc123",
      "detection_id": 123
    }
  }
}
```

---

## 📊 Performance Monitoring & Request Logging

### Automatic Request/Response Logging (File-based)

All API requests are **automatically logged** via middleware to **daily log files**:

```
Request → RequestResponseInterceptor
    ↓
Calculate Metrics (query_count, memory_usage, execution_time)
    ↓
Write to Daily Log File (instant, no queue delay)
    ↓
Return Response with Performance Metrics
    ↓
Daily Aggregation Job (01:30) → Parse Logs → Save to api_usage_summary
```

**Logged to:** `storage/app/logs/api_requests/YYYY-MM-DD.log` (JSON Lines format)  
**Format:** One JSON object per line (instant write, no database INSERT)  
**Summary Table:** `api_usage_summary` (daily aggregated statistics)  
**Includes:** All request/response data + performance metrics

### Response Metrics

Every API response includes performance metrics in the `meta` section and HTTP headers:

**Meta Section (JSON):**

```json
{
  "meta": {
    "query_count": 5, // Number of DB queries
    "memory_usage": "2.5 MB", // Memory consumed
    "execution_time": "0.125s" // Total processing time
  }
}
```

**HTTP Headers:**

```
X-Query-Count: 5
X-Memory-Usage: 2.5MB
X-Execution-Time: 0.125s
X-API-Version: 1.0
X-Request-ID: uuid-here
```

### Metrics Interpretation

| Metric             | Good    | Warning  | Critical | Action                              |
| ------------------ | ------- | -------- | -------- | ----------------------------------- |
| **Query Count**    | < 10    | 10-20    | > 20     | Optimize queries, use eager loading |
| **Memory Usage**   | < 10 MB | 10-50 MB | > 50 MB  | Check for memory leaks, optimize    |
| **Execution Time** | < 0.2s  | 0.2-1s   | > 1s     | Optimize queries, use caching       |

### Performance Best Practices

1. **Monitor Query Count**: High query count indicates N+1 problem
2. **Track Memory Usage**: High memory usage may cause performance issues
3. **Optimize Slow Endpoints**: Use execution_time to identify bottlenecks
4. **Enable in Development**: Use `DB_LOG_QUERIES=true` to debug
5. **Disable in Production**: Set `DB_LOG_QUERIES=false` for better performance (unless needed)

### Logging Features

| Feature                      | Description                                  | Benefit                            |
| ---------------------------- | -------------------------------------------- | ---------------------------------- |
| **Auto Logging**             | All API requests logged via middleware       | No manual code needed              |
| **File-based Logs**          | Daily log files (JSON Lines format)          | Prevents database bloat            |
| **Instant Write**            | No queue delay, writes immediately to file   | Fast, non-blocking                 |
| **Daily Aggregation**        | Scheduled jobs process logs → summary tables | Database has clean aggregated data |
| **Performance Tracking**     | query_count, memory_usage, execution_time    | Identify bottlenecks               |
| **Sensitive Data Filtering** | Sanitize passwords, tokens, secrets          | Security compliance                |
| **Automatic Alerts**         | Log warnings for slow/high-memory requests   | Proactive monitoring               |
| **Flexible Service**         | LoggingService for API, WhatsApp, Storage    | Reusable across modules            |
| **Scalable**                 | File-based logs scale better than DB inserts | High-volume ready                  |

### Example Response with Metrics

```bash
curl -v https://api.cctv.com/api/person/person_001_abc123 \
  -H "X-API-Key: your-key"

# Response Headers:
< X-Query-Count: 6
< X-Memory-Usage: 2.8MB
< X-Execution-Time: 0.156s
< X-API-Version: 1.0
< X-Request-ID: abc-123-def-456

# Response Body:
{
  "success": true,
  "data": {...},
  "meta": {
    "timestamp": "2024-01-16T14:30:00Z",
    "version": "1.0",
    "request_id": "abc-123-def-456",
    "query_count": 6,
    "memory_usage": "2.8 MB",
    "execution_time": "0.156s"
  }
}

# Automatically logged to file (instant):
# storage/app/logs/api_requests/2024-01-16.log (JSON Lines)
{"timestamp":"2024-01-16T14:30:00Z","api_credential_id":2,"endpoint":"api/person/person_001_abc123","method":"GET","response_status":200,"response_time_ms":156,"query_count":6,"memory_usage_mb":2.8,"ip_address":"192.168.1.100"}

# Daily aggregation (01:30 next day) → api_usage_summary table:
{
  "api_credential_id": 2,
  "summary_date": "2024-01-16",
  "endpoint": "api/person/person_001_abc123",
  "method": "GET",
  "total_requests": 234,
  "success_requests": 232,
  "error_requests": 2,
  "avg_response_time_ms": 156,
  "max_response_time_ms": 345,
  "avg_query_count": 6,
  "max_query_count": 12
}
```

---

## 📝 Changelog

### Version 1.3.0 (Latest)

**New Features:**
- ✅ **Branch Detection Summary Table**: Added branch detection counts table in `/re-id-masters/` detail page
- ✅ **Single Column Layout**: Branch detection summary uses clean single-column card layout
- ✅ **Detection History Repositioning**: Moved Detection History below Person Information
- ✅ **Count Column Removal**: Removed redundant Count column from Detection History table
- ✅ **Badge Integration**: Total Count displayed with blue badge for better visual hierarchy
- ✅ **Service Layer Enhancement**: Added `getBranchDetectionCounts()` method to ReIdMasterService
- ✅ **UI Component Consistency**: Maintained reusable Blade components throughout

**API Changes:**
- ✅ **Branch Detection Data**: Enhanced person detail API with branch detection counts
- ✅ **Aggregated Statistics**: MIN/MAX detection timestamps per branch
- ✅ **Performance Optimization**: Efficient database queries with proper indexing

**Database Changes:**
- ✅ **Query Optimization**: Optimized branch detection count queries with JOIN operations
- ✅ **Data Aggregation**: MIN/MAX timestamps and COUNT/SUM operations per branch
- ✅ **Index Usage**: Proper indexing for branch detection count queries

### Version 1.2.0

**New Features:**
- ✅ **Person Tracking Table Ordering**: Person Tracking table now ordered by `last_detected_at DESC` (newest first)
- ✅ **Re-ID Daily Uniqueness**: Same re_id can exist on different dates
- ✅ **WhatsApp Integration**: WAHA (WhatsApp HTTP API) integration
- ✅ **Image Processing**: Automatic resize, watermark, thumbnail generation
- ✅ **Daily Storage Folders**: Images stored in `whatsapp_detection_dd-mm-yyyy` folders
- ✅ **Enhanced Detection Data**: Support for appearance_features, location, bounding_box
- ✅ **Queue Job Chain**: ProcessDetectionJob → ProcessDetectionImageJob → SendWhatsAppNotificationJob
- ✅ **Migration Files**: Database constraint fixes for re_id uniqueness
- ✅ **UI Component Refactoring**: Reusable Blade components for buttons, cards, badges, stat-cards
- ✅ **Service Layer Architecture**: Business logic moved from controllers to dedicated service classes

**API Changes:**
- ✅ **Endpoint**: Updated to `/api/v1/detection/log`
- ✅ **Authentication**: X-API-Key + X-API-Secret headers
- ✅ **Image Upload**: Optional image field with multipart/form-data support
- ✅ **Response Format**: Enhanced with job_id and processing status

**Database Changes:**
- ✅ **Unique Constraint**: Changed from `re_id` to `(re_id, detection_date)`
- ✅ **Foreign Keys**: Removed foreign key constraints on re_id alone
- ✅ **Migration Files**: Added constraint fix migrations

### Version 1.0.0

- Initial API release
- Person Re-ID detection endpoints
- Event management with WhatsApp integration
- CCTV layout management (Admin only)
- API credential management
- Queue-based async processing
- Standardized response format with performance metrics
- Comprehensive error handling
- Rate limiting per endpoint
- File storage with secure access
- PostgreSQL database with JSONB support
- Performance monitoring (query_count, memory_usage, execution_time)
- **File-based daily logs** for API requests and WhatsApp messages
- **Automatic request/response logging** via middleware (instant file write)
- **Daily aggregation jobs** process log files → database summaries
- **Scalable architecture** prevents database bloat for high-volume operations

---

## 🏗️ Logging Architecture (File-based)

### Middleware Stack

```
API Request
    ↓
1. RequestResponseInterceptor
   → Start timer, enable query log
   → Process request
   → Calculate metrics (query_count, memory_usage, execution_time)
   → Write to daily log file (instant, no queue)
   → Add performance headers
    ↓
2. ApiResponseMiddleware
   → Add standard headers (X-API-Version, X-Request-ID, X-RateLimit-*)
    ↓
3. PerformanceMonitoringMiddleware
   → Alert if slow (> 1000ms)
   → Alert if high memory (> 128MB)
   → Log slow queries (> 100ms each)
    ↓
Response (with performance metrics in meta + headers)

Daily Aggregation (Scheduled at 01:30):
    ↓
AggregateApiUsageJob
   → Read: storage/app/logs/api_requests/YYYY-MM-DD.log
   → Parse: JSON Lines format
   → Aggregate: By credential + endpoint + method
   → Save: To api_usage_summary table (avg/max/min metrics)
    ↓
AggregateWhatsAppDeliveryJob
   → Read: storage/app/logs/whatsapp_messages/YYYY-MM-DD.log
   → Parse: JSON Lines format
   → Aggregate: By branch + device
   → Save: To whatsapp_delivery_summary table
```

### Logging Services

| Service                                | Purpose                   | Storage                                       | Async | Performance Tracked                          |
| -------------------------------------- | ------------------------- | --------------------------------------------- | ----- | -------------------------------------------- |
| **RequestResponseInterceptor**         | Auto-log ALL API requests | Daily file → api_usage_summary (aggregated)   | ❌ No | ✅ query_count, memory_usage, execution_time |
| **LoggingService::logWhatsAppMessage** | Log WhatsApp messages     | Daily file → whatsapp_delivery_summary        | ❌ No | ✅ execution_time in provider_response       |
| **LoggingService::logStorageFile**     | Log file uploads          | storage_files (database)                      | ❌ No | ✅ file_size, metadata                       |
| **LoggingService::getApiUsageStats**   | Get API statistics        | api_usage_summary (from aggregated summaries) | ❌ No | ✅ Analyze avg/max/min response times        |

---

_For complete database schema, queue jobs, and best practices, refer to `database_plan_en.md`._
