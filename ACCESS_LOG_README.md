# 📊 Access Log System - CCTV Dashboard

## Overview

Sistem access log harian untuk API CCTV Dashboard yang mencatat semua request API dengan detail lengkap termasuk performance metrics.

## 🚀 Features

- ✅ **Daily Log Rotation** - Log otomatis dipisah per hari
- ✅ **JSON Format** - Format JSON Lines untuk mudah parsing
- ✅ **Performance Metrics** - Response time, memory usage, query count
- ✅ **Request Details** - Method, endpoint, payload, headers
- ✅ **User Information** - User ID, name, email dari authentication
- ✅ **Request Tracking** - Unique request ID untuk setiap request
- ✅ **Data Encryption** - API credential ID dan key ter-encrypt
- ✅ **Query Monitoring** - Query count otomatis tercatat
- ✅ **Security** - Sensitive data di-redact otomatis
- ✅ **Auto Directory Creation** - Direktori log dibuat otomatis

## 📁 Log Structure

```
storage/app/logs/api_requests/
├── 2025-10-10.log
├── 2025-10-11.log
└── 2025-10-12.log
```

## 📝 Log Format

Setiap baris dalam file log berformat JSON:

```json
{
  "request_id": "7dcebf80-0a9b-4da0-a5ca-7441eacd1e0a",
  "timestamp": "2025-10-10T18:10:58+07:00",
  "api_credential_id": "eyJpdiI6Ik1...",
  "api_key": "eyJpdiI6Ik1...",
  "user_id": 456,
  "user_name": "John Doe",
  "user_email": "john.doe@example.com",
  "endpoint": "api/v1/detections",
  "method": "GET",
  "request_payload": {...},
  "response_status": 200,
  "response_time_ms": 45,
  "query_count": 3,
  "memory_usage_mb": 2.5,
  "ip_address": "192.168.1.100",
  "user_agent": "curl/8.5.0"
}
```

## 🔧 Configuration

### Environment Variables

```env
# Logging settings
LOG_DAILY_DAYS=30
LOG_ACCESS_DAYS=90
LOG_LEVEL=info
```

### Log Rotation

Log otomatis di-rotate setiap hari dengan konfigurasi:

- **Retention**: 90 hari
- **Compression**: Otomatis setelah 1 hari
- **Format**: `YYYY-MM-DD.log`

## 🛠️ Setup

### 1. Middleware Registration

Middleware sudah terdaftar di `bootstrap/app.php`:

```php
$middleware->api(append: [
    \App\Http\Middleware\RequestResponseInterceptor::class,
]);
```

### 2. Log Rotation Setup

Jalankan script setup:

```bash
./setup-log-rotation.sh
```

### 3. Manual Log Rotation Test

```bash
# Test logrotate configuration
sudo logrotate -d /etc/logrotate.d/cctv-dashboard

# Force rotation (test)
sudo logrotate -f /etc/logrotate.d/cctv-dashboard
```

## 📊 Monitoring

### View Current Logs

```bash
# View today's logs
tail -f storage/app/logs/api_requests/$(date +%Y-%m-%d).log

# Count requests today
wc -l storage/app/logs/api_requests/$(date +%Y-%m-%d).log

# Search for specific endpoint
grep "api/v1/detections" storage/app/logs/api_requests/$(date +%Y-%m-%d).log
```

### Parse Logs with jq

```bash
# Get all 200 responses
cat storage/app/logs/api_requests/2025-10-10.log | jq 'select(.response_status == 200)'

# Get slow requests (>1000ms)
cat storage/app/logs/api_requests/2025-10-10.log | jq 'select(.response_time_ms > 1000)'

# Get requests by IP
cat storage/app/logs/api_requests/2025-10-10.log | jq 'select(.ip_address == "192.168.1.100")'

# Get requests by user
cat storage/app/logs/api_requests/2025-10-10.log | jq 'select(.user_name != null)'

# Get requests by specific user
cat storage/app/logs/api_requests/2025-10-10.log | jq 'select(.user_name == "John Doe")'

# Get requests by request ID
cat storage/app/logs/api_requests/2025-10-10.log | jq 'select(.request_id == "7dcebf80-0a9b-4da0-a5ca-7441eacd1e0a")'

# Get all request IDs
cat storage/app/logs/api_requests/2025-10-10.log | jq '.request_id'
```

## 🔒 Security Features

### Sensitive Data Redaction

Field berikut otomatis di-redact:

- `password`
- `api_secret`
- `token`
- `credit_card`
- `stream_password`

### Data Encryption

API credential ID dan API key di-encrypt menggunakan Laravel Crypt:

```
"api_credential_id": "eyJpdiI6Ik1...",
"api_key": "eyJpdiI6Ik1..."
```

### Request ID Tracking

Setiap request memiliki unique identifier:

```
"request_id": "7dcebf80-0a9b-4da0-a5ca-7441eacd1e0a"
```

## 📈 Performance Metrics

### Tracked Metrics

- **Response Time**: Waktu eksekusi dalam milliseconds
- **Memory Usage**: Penggunaan memory dalam MB
- **Query Count**: Jumlah database queries
- **Request Size**: Ukuran request payload

### Performance Alerts

Sistem otomatis alert jika:

- Response time > 1000ms
- Memory usage > 128MB
- Query count > 20

## 🚨 Troubleshooting

### Log Tidak Terbuat

1. Periksa permissions:

```bash
ls -la storage/app/logs/
chmod -R 755 storage/app/logs/
```

2. Periksa Laravel log:

```bash
tail -f storage/logs/laravel.log
```

3. Test middleware:

```bash
curl -X GET "http://localhost:9001/api/static/info"
```

### Log Rotation Tidak Berjalan

1. Periksa cron job:

```bash
sudo crontab -l | grep logrotate
```

2. Test manual:

```bash
sudo logrotate -f /etc/logrotate.d/cctv-dashboard
```

## 📋 Maintenance

### Daily Tasks

- Monitor log file sizes
- Check for errors in Laravel log
- Verify log rotation is working

### Weekly Tasks

- Review performance metrics
- Check for unusual patterns
- Clean up old compressed logs

### Monthly Tasks

- Analyze access patterns
- Review security logs
- Update log retention policies

---

**Created**: October 10, 2025  
**Version**: 1.0  
**Status**: ✅ Active
