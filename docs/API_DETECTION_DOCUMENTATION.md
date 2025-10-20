# 📡 CCTV Dashboard - Detection API Documentation

**Complete API reference for Person Re-Identification (Re-ID) detection system**

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Base URL & Versioning](#base-url--versioning)
4. [Rate Limiting](#rate-limiting)
5. [Response Format](#response-format)
6. [Error Handling](#error-handling)
7. [Detection Endpoints](#detection-endpoints)
8. [Person Tracking Endpoints](#person-tracking-endpoints)
9. [Branch Detection Endpoints](#branch-detection-endpoints)
10. [CCTV Integration Endpoints](#cctv-integration-endpoints)
11. [Examples](#examples)
12. [SDK Examples](#sdk-examples)

---

## 🎯 Overview

The CCTV Dashboard Detection API provides comprehensive endpoints for:

- **Person Re-Identification (Re-ID)** tracking across multiple branches
- **Real-time detection logging** with async processing
- **Person tracking** and detection history
- **Branch-specific analytics** and statistics
- **CCTV stream management** and control
- **Global detection summaries** and trends

### Key Features

- ✅ **Async Processing** - Non-blocking detection logging (202 Accepted)
- ✅ **Rate Limiting** - 10,000 requests/hour per API credential
- ✅ **Global Access** - All branches and devices accessible
- ✅ **Image Upload** - Support for detection images (10MB max)
- ✅ **Comprehensive Filtering** - Date, branch, device, person filters
- ✅ **Real-time Statistics** - Live detection summaries
- ✅ **Job Status Tracking** - Monitor async processing status

---

## 🔐 Authentication

### API Key Authentication (Required)

All detection endpoints require API Key authentication via headers:

```http
X-API-Key: cctv_live_abc123xyz789def456ghi789jkl012
X-API-Secret: secret_mno345pqr678stu901vwx234yz567ab
Accept: application/json
```

### Creating API Credentials

1. **Admin Login** → Navigate to `/api-credentials`
2. **Create New Credential** → Fill 3 fields:
   - Credential name
   - Expiration date (optional)
   - Status (active/inactive)
3. **Save API Secret** (shown only once!)

### Rate Limiting Headers

Every response includes rate limit information:

```http
X-RateLimit-Limit: 10000
X-RateLimit-Remaining: 9847
X-RateLimit-Reset: 1728399600
```

---

## 🌐 Base URL & Versioning

### Base URLs

- **Development:** `http://localhost:8000/api/v1/`
- **Production:** `https://your-domain.com/api/v1/`

### Versioning

- **Current Version:** v1
- **Version Header:** Not required (URL-based versioning)
- **Backward Compatibility:** Maintained for v1

---

## ⚡ Rate Limiting

### Limits

- **Per Credential:** 10,000 requests/hour
- **Burst Limit:** 100 requests/minute
- **Window:** Rolling 1-hour window

### Headers

```http
X-RateLimit-Limit: 10000
X-RateLimit-Remaining: 9847
X-RateLimit-Reset: 1728399600
```

### Exceeded Limit Response

```json
{
  "success": false,
  "message": "Rate limit exceeded",
  "error": "RATE_LIMIT_EXCEEDED",
  "data": {
    "limit": 10000,
    "remaining": 0,
    "reset_at": "2024-12-31T23:59:59Z"
  }
}
```

---

## 📊 Response Format

### Success Response

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data here
  },
  "meta": {
    "timestamp": "2024-12-31T12:00:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "execution_time": "0.045s",
    "memory_usage": "2.5MB",
    "query_count": 3
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
    "total": 150,
    "last_page": 10,
    "from": 1,
    "to": 15
  },
  "meta": {
    "timestamp": "2024-12-31T12:00:00Z",
    "version": "1.0",
    "request_id": "uuid-here"
  }
}
```

---

## ❌ Error Handling

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "error": "ERROR_CODE",
  "data": {
    "field": "validation error message"
  },
  "meta": {
    "timestamp": "2024-12-31T12:00:00Z",
    "version": "1.0",
    "request_id": "uuid-here"
  }
}
```

### Common Error Codes

| Code                    | HTTP Status | Description                 |
| ----------------------- | ----------- | --------------------------- |
| `VALIDATION_ERROR`      | 422         | Request validation failed   |
| `AUTHENTICATION_FAILED` | 401         | Invalid API credentials     |
| `RATE_LIMIT_EXCEEDED`   | 429         | Rate limit exceeded         |
| `NOT_FOUND`             | 404         | Resource not found          |
| `SERVER_ERROR`          | 500         | Internal server error       |
| `JOB_FAILED`            | 500         | Async job processing failed |

---

## 🎯 Detection Endpoints

### 1. Log Detection (Async)

**POST** `/api/v1/detection/log`

Log a new person detection event. Returns immediately with job ID for async processing.

#### Request Body

```json
{
  "re_id": "person_001",
  "branch_id": 1,
  "device_id": "CAMERA_001",
  "detection_data": {
    "confidence": 0.95,
    "bounding_box": {
      "x": 100,
      "y": 150,
      "width": 200,
      "height": 300
    },
    "appearance_features": {
      "color": "blue",
      "style": "casual"
    }
  },
  "person_features": {
    "gender": "male",
    "age_range": "25-35",
    "clothing": ["shirt", "pants"]
  }
}
```

#### Parameters

| Field                                | Type    | Required | Description                                  |
| ------------------------------------ | ------- | -------- | -------------------------------------------- |
| `re_id`                              | string  | ✅       | Person re-identification ID (max: 100 chars) |
| `branch_id`                          | integer | ✅       | Branch ID (must exist)                       |
| `device_id`                          | string  | ✅       | Device ID (must exist)                       |
| `detection_data`                     | object  | ❌       | Detection metadata                           |
| `detection_data.confidence`          | float   | ❌       | Detection confidence (0-1)                   |
| `detection_data.bounding_box`        | object  | ❌       | Bounding box coordinates                     |
| `detection_data.appearance_features` | object  | ❌       | Appearance features                          |
| `person_features`                    | object  | ❌       | Person characteristics                       |
| `person_features.gender`             | string  | ❌       | Gender identification                        |
| `person_features.age_range`          | string  | ❌       | Age range estimate                           |
| `person_features.clothing`           | array   | ❌       | Clothing items array                         |
| `image`                              | file    | ❌       | Detection image (max: 10MB)                  |

#### Response (202 Accepted)

```json
{
  "success": true,
  "message": "Detection event received and queued successfully",
  "data": {
    "job_id": "550e8400-e29b-41d4-a716-446655440000",
    "status": "processing",
    "message": "Detection queued for processing",
    "re_id": "person_001",
    "branch_id": 1,
    "device_id": "CAMERA_001"
  },
  "meta": {
    "timestamp": "2024-12-31T12:00:00Z",
    "version": "1.0",
    "request_id": "uuid-here"
  }
}
```

### 2. Get All Detections

**GET** `/api/v1/detections`

Retrieve paginated list of all detections with filtering options.

#### Query Parameters

| Parameter   | Type    | Description                   |
| ----------- | ------- | ----------------------------- |
| `date_from` | date    | Filter from date (YYYY-MM-DD) |
| `date_to`   | date    | Filter to date (YYYY-MM-DD)   |
| `branch_id` | integer | Filter by branch ID           |
| `device_id` | string  | Filter by device ID           |
| `re_id`     | string  | Filter by person re-ID        |
| `per_page`  | integer | Items per page (default: 15)  |
| `page`      | integer | Page number (default: 1)      |

#### Example Request

```bash
GET /api/v1/detections?date_from=2024-12-01&branch_id=1&per_page=20
```

#### Response

```json
{
  "success": true,
  "message": "Detections retrieved successfully",
  "data": [
    {
      "id": 1,
      "re_id": "person_001",
      "branch_id": 1,
      "device_id": "CAMERA_001",
      "detected_count": 1,
      "detection_timestamp": "2024-12-31T12:00:00Z",
      "detection_data": {
        "confidence": 0.95
      },
      "branch": {
        "id": 1,
        "branch_name": "Jakarta Central",
        "city": "Jakarta"
      },
      "device": {
        "device_id": "CAMERA_001",
        "device_name": "Main Entrance Camera",
        "device_type": "camera"
      },
      "re_id_master": {
        "re_id": "person_001",
        "person_name": "John Doe",
        "status": "active"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8,
    "from": 1,
    "to": 20
  }
}
```

### 3. Get Detection Summary

**GET** `/api/v1/detection/summary`

Get global detection statistics and trends for a specific date.

#### Query Parameters

| Parameter | Type | Description                   |
| --------- | ---- | ----------------------------- |
| `date`    | date | Summary date (default: today) |

#### Response

```json
{
  "success": true,
  "message": "Detection summary retrieved successfully",
  "data": {
    "date": "2024-12-31",
    "summary": {
      "total_detections": 1250,
      "unique_persons": 45,
      "unique_branches": 8,
      "unique_devices": 12
    },
    "top_branches": [
      {
        "branch_id": 1,
        "branch_name": "Jakarta Central",
        "city": "Jakarta",
        "detection_count": 450
      }
    ],
    "top_persons": [
      {
        "re_id": "person_001",
        "detection_count": 25
      }
    ],
    "hourly_trend": [
      {
        "hour": 8,
        "count": 45,
        "unique_persons": 12
      }
    ]
  }
}
```

### 4. Get Job Status

**GET** `/api/v1/detection/status/{jobId}`

Check the status of an async detection job.

#### Response Examples

**Processing:**

```json
{
  "success": true,
  "message": "Job is still processing",
  "data": {
    "job_id": "550e8400-e29b-41d4-a716-446655440000",
    "status": "processing",
    "attempts": 1
  }
}
```

**Completed:**

```json
{
  "success": true,
  "message": "Job completed successfully",
  "data": {
    "job_id": "550e8400-e29b-41d4-a716-446655440000",
    "status": "completed"
  }
}
```

**Failed:**

```json
{
  "success": false,
  "message": "Job processing failed",
  "error": "JOB_FAILED",
  "data": {
    "error": "Exception details here"
  }
}
```

---

## 👤 Person Tracking Endpoints

### 1. Get Person Information

**GET** `/api/v1/person/{reId}`

Get detailed information about a specific person for a given date.

#### Query Parameters

| Parameter | Type | Description                  |
| --------- | ---- | ---------------------------- |
| `date`    | date | Person date (default: today) |

#### Response

```json
{
  "success": true,
  "message": "Person information retrieved successfully",
  "data": {
    "re_id": "person_001",
    "detection_date": "2024-12-31",
    "detection_time": "12:00:00",
    "person_name": "John Doe",
    "appearance_features": {
      "color": "blue",
      "style": "casual"
    },
    "total_detection_branch_count": 3,
    "total_actual_count": 25,
    "first_detected_at": "2024-12-31T08:00:00Z",
    "last_detected_at": "2024-12-31T18:00:00Z",
    "status": "active",
    "detected_branches": [
      {
        "branch_id": 1,
        "branch_name": "Jakarta Central",
        "city": "Jakarta",
        "detection_count": 15
      }
    ]
  }
}
```

### 2. Get Person Detection History

**GET** `/api/v1/person/{reId}/detections`

Get detection history for a specific person with filtering options.

#### Query Parameters

| Parameter   | Type    | Description                  |
| ----------- | ------- | ---------------------------- |
| `date_from` | date    | Filter from date             |
| `date_to`   | date    | Filter to date               |
| `branch_id` | integer | Filter by branch             |
| `per_page`  | integer | Items per page (default: 20) |

#### Response

```json
{
  "success": true,
  "message": "Detection history for re_id 'person_001' retrieved successfully",
  "data": [
    {
      "id": 1,
      "re_id": "person_001",
      "branch_id": 1,
      "device_id": "CAMERA_001",
      "detected_count": 1,
      "detection_timestamp": "2024-12-31T12:00:00Z",
      "branch": {
        "id": 1,
        "branch_name": "Jakarta Central",
        "city": "Jakarta"
      },
      "device": {
        "device_id": "CAMERA_001",
        "device_name": "Main Entrance Camera"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 25,
    "last_page": 2
  }
}
```

---

## 🏢 Branch Detection Endpoints

### Get Branch Detections

**GET** `/api/v1/branch/{branchId}/detections`

Get all detections for a specific branch with statistics.

#### Query Parameters

| Parameter   | Type    | Description                     |
| ----------- | ------- | ------------------------------- |
| `date`      | date    | Detection date (default: today) |
| `device_id` | string  | Filter by device                |
| `per_page`  | integer | Items per page (default: 20)    |

#### Response

```json
{
  "success": true,
  "message": "Branch detections retrieved successfully",
  "data": [
    {
      "id": 1,
      "re_id": "person_001",
      "branch_id": 1,
      "device_id": "CAMERA_001",
      "detected_count": 1,
      "detection_timestamp": "2024-12-31T12:00:00Z",
      "device": {
        "device_id": "CAMERA_001",
        "device_name": "Main Entrance Camera"
      },
      "re_id_master": {
        "re_id": "person_001",
        "person_name": "John Doe"
      }
    }
  ],
  "statistics": {
    "branch_id": 1,
    "branch_name": "Jakarta Central",
    "city": "Jakarta",
    "date": "2024-12-31",
    "total_detections": 150,
    "unique_persons": 25,
    "unique_devices": 3
  },
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8
  }
}
```

---

## 📹 CCTV Integration Endpoints

### 1. Get Stream URL

**GET** `/api/v1/cctv/streams/{deviceId}`

Get streaming URL for a specific CCTV device.

#### Response

```json
{
  "success": true,
  "message": "Stream URL retrieved successfully",
  "data": {
    "device_id": "CAMERA_001",
    "stream_url": "rtsp://192.168.1.100:554/stream1",
    "stream_name": "Main Entrance Stream",
    "status": "online",
    "resolution": "1920x1080",
    "fps": 30
  }
}
```

### 2. Get Branch Devices

**GET** `/api/v1/cctv/branches/{branchId}/devices`

Get all CCTV devices for a specific branch.

#### Response

```json
{
  "success": true,
  "message": "Branch devices retrieved successfully",
  "data": [
    {
      "device_id": "CAMERA_001",
      "device_name": "Main Entrance Camera",
      "device_type": "cctv",
      "url": "rtsp://192.168.1.100:554/stream1",
      "status": "active"
    }
  ]
}
```

### 3. Update Layout Position

**PUT** `/api/v1/cctv/layouts/{layoutId}/positions/{positionNumber}`

Update device assignment for a specific layout position.

#### Request Body

```json
{
  "device_id": "CAMERA_001",
  "quality": "high"
}
```

### 4. Capture Screenshot

**POST** `/api/v1/cctv/screenshots/{deviceId}`

Capture a screenshot from a CCTV device.

#### Response

```json
{
  "success": true,
  "message": "Screenshot captured successfully",
  "data": {
    "device_id": "CAMERA_001",
    "screenshot_url": "/storage/screenshots/screenshot_20241231_120000.jpg",
    "timestamp": "2024-12-31T12:00:00Z"
  }
}
```

### 5. Toggle Recording

**POST** `/api/v1/cctv/recordings/{deviceId}`

Start or stop recording for a CCTV device.

#### Request Body

```json
{
  "action": "start" // or "stop"
}
```

---

## 💻 Examples

### Complete Detection Workflow

#### 1. Log Detection

```bash
curl -X POST "http://localhost:8000/api/v1/detection/log" \
  -H "X-API-Key: cctv_live_abc123xyz789def456ghi789jkl012" \
  -H "X-API-Secret: secret_mno345pqr678stu901vwx234yz567ab" \
  -H "Content-Type: application/json" \
  -d '{
    "re_id": "person_001",
    "branch_id": 1,
    "device_id": "CAMERA_001",
    "detection_data": {
      "confidence": 0.95,
      "bounding_box": {
        "x": 100,
        "y": 150,
        "width": 200,
        "height": 300
      }
    },
    "person_features": {
      "gender": "male",
      "age_range": "25-35"
    }
  }'
```

#### 2. Check Job Status

```bash
curl "http://localhost:8000/api/v1/detection/status/550e8400-e29b-41d4-a716-446655440000" \
  -H "X-API-Key: cctv_live_abc123xyz789def456ghi789jkl012" \
  -H "X-API-Secret: secret_mno345pqr678stu901vwx234yz567ab"
```

#### 3. Get Detection Summary

```bash
curl "http://localhost:8000/api/v1/detection/summary?date=2024-12-31" \
  -H "X-API-Key: cctv_live_abc123xyz789def456ghi789jkl012" \
  -H "X-API-Secret: secret_mno345pqr678stu901vwx234yz567ab"
```

### Image Upload Example

```bash
curl -X POST "http://localhost:8000/api/v1/detection/log" \
  -H "X-API-Key: cctv_live_abc123xyz789def456ghi789jkl012" \
  -H "X-API-Secret: secret_mno345pqr678stu901vwx234yz567ab" \
  -F "re_id=person_001" \
  -F "branch_id=1" \
  -F "device_id=CAMERA_001" \
  -F "image=@detection_image.jpg"
```

---

## 🔧 SDK Examples

### JavaScript/Node.js

```javascript
const axios = require("axios");

const apiClient = axios.create({
  baseURL: "http://localhost:8000/api/v1",
  headers: {
    "X-API-Key": "cctv_live_abc123xyz789def456ghi789jkl012",
    "X-API-Secret": "secret_mno345pqr678stu901vwx234yz567ab",
    Accept: "application/json",
  },
});

// Log detection
async function logDetection(detectionData) {
  try {
    const response = await apiClient.post("/detection/log", detectionData);
    console.log("Detection logged:", response.data);
    return response.data.data.job_id;
  } catch (error) {
    console.error("Error logging detection:", error.response.data);
    throw error;
  }
}

// Get detection summary
async function getDetectionSummary(date = null) {
  try {
    const params = date ? { date } : {};
    const response = await apiClient.get("/detection/summary", { params });
    return response.data.data;
  } catch (error) {
    console.error("Error getting summary:", error.response.data);
    throw error;
  }
}

// Check job status
async function checkJobStatus(jobId) {
  try {
    const response = await apiClient.get(`/detection/status/${jobId}`);
    return response.data.data;
  } catch (error) {
    console.error("Error checking job status:", error.response.data);
    throw error;
  }
}
```

### Python

```python
import requests
import json

class CCTVDetectionAPI:
    def __init__(self, base_url, api_key, api_secret):
        self.base_url = base_url
        self.headers = {
            'X-API-Key': api_key,
            'X-API-Secret': api_secret,
            'Accept': 'application/json'
        }

    def log_detection(self, detection_data):
        """Log a new detection"""
        response = requests.post(
            f"{self.base_url}/detection/log",
            headers=self.headers,
            json=detection_data
        )
        response.raise_for_status()
        return response.json()

    def get_detection_summary(self, date=None):
        """Get detection summary"""
        params = {'date': date} if date else {}
        response = requests.get(
            f"{self.base_url}/detection/summary",
            headers=self.headers,
            params=params
        )
        response.raise_for_status()
        return response.json()

    def get_person_info(self, re_id, date=None):
        """Get person information"""
        params = {'date': date} if date else {}
        response = requests.get(
            f"{self.base_url}/person/{re_id}",
            headers=self.headers,
            params=params
        )
        response.raise_for_status()
        return response.json()

# Usage
api = CCTVDetectionAPI(
    'http://localhost:8000/api/v1',
    'cctv_live_abc123xyz789def456ghi789jkl012',
    'secret_mno345pqr678stu901vwx234yz567ab'
)

# Log detection
detection_data = {
    're_id': 'person_001',
    'branch_id': 1,
    'device_id': 'CAMERA_001',
    'detection_data': {
        'confidence': 0.95
    }
}

result = api.log_detection(detection_data)
print(f"Job ID: {result['data']['job_id']}")
```

### PHP/Laravel

```php
<?php

class CCTVDetectionAPI
{
    private $baseUrl;
    private $apiKey;
    private $apiSecret;

    public function __construct($baseUrl, $apiKey, $apiSecret)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    public function logDetection($data)
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
            'X-API-Secret' => $this->apiSecret,
            'Accept' => 'application/json'
        ])->post($this->baseUrl . '/detection/log', $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('API Error: ' . $response->body());
    }

    public function getDetectionSummary($date = null)
    {
        $params = $date ? ['date' => $date] : [];

        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
            'X-API-Secret' => $this->apiSecret,
            'Accept' => 'application/json'
        ])->get($this->baseUrl . '/detection/summary', $params);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception('API Error: ' . $response->body());
    }
}

// Usage
$api = new CCTVDetectionAPI(
    'http://localhost:8000/api/v1',
    'cctv_live_abc123xyz789def456ghi789jkl012',
    'secret_mno345pqr678stu901vwx234yz567ab'
);

$detectionData = [
    're_id' => 'person_001',
    'branch_id' => 1,
    'device_id' => 'CAMERA_001',
    'detection_data' => [
        'confidence' => 0.95
    ]
];

$result = $api->logDetection($detectionData);
echo "Job ID: " . $result['data']['job_id'];
```

---

## 📚 Additional Resources

### Related Documentation

- **[API_REFERENCE.md](API_REFERENCE.md)** - Complete API reference
- **[API_CREDENTIALS_INTEGRATION.md](API_CREDENTIALS_INTEGRATION.md)** - Credential management guide
- **[SETUP_GUIDE.md](../SETUP_GUIDE.md)** - Installation and setup

### Support

- **API Testing Interface:** `/api-credentials/{id}/test` (Admin only)
- **Rate Limit Monitoring:** Check response headers
- **Error Logging:** All errors logged with request details

### Changelog

- **v1.0** (December 2024) - Initial release
  - Complete detection API
  - Person tracking endpoints
  - Branch analytics
  - CCTV integration
  - Rate limiting and authentication

---

**Last Updated:** December 2024  
**Version:** 1.0  
**Status:** Production Ready ✅
