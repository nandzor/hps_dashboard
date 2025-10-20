# 📊 CCTV Dashboard Reports Scheduler

## Overview
This document describes the automated report generation system for CCTV Dashboard, including daily and monthly reports.

## 🕐 Scheduled Tasks

### Daily Reports (Legacy)
- **Schedule**: Every day at 01:00 AM
- **Job**: `UpdateDailyReportJob`
- **Queue**: `reports`
- **Function**: Generates daily counting reports from detection data (legacy)

### Daily Reports (New - Every 5 Minutes)
- **Schedule**: Every 5 minutes
- **Job**: `UpdateDailyReportJob`
- **Queue**: `reports`
- **Function**: Updates daily reports for all active branches
- **Benefit**: More frequent updates, better performance

### Monthly Reports  
- **Schedule**: 1st of each month at 02:00 AM
- **Job**: `UpdateMonthlyReportJob`
- **Queue**: `reports`
- **Function**: Aggregates daily reports into monthly summaries

### Counting Reports
- **Schedule**: Every day at 01:15 AM
- **Command**: `reports:generate-counting --days=1`
- **Function**: Generates counting reports from detection data

## 🚀 Manual Commands

### Generate Daily Reports
```bash
# Generate for specific date
docker exec cctv_app_staging php artisan reports:generate-counting --date=2025-10-09

# Generate for yesterday
docker exec cctv_app_staging php artisan reports:generate-counting --date=$(date -d "yesterday" +%Y-%m-%d)
```

### Generate Monthly Reports
```bash
# Generate for specific month
docker exec cctv_app_staging php artisan reports:generate-monthly --month=2025-10

# Generate for current month
docker exec cctv_app_staging php artisan reports:generate-monthly --month=$(date +%Y-%m)
```

### Generate Multiple Daily Reports
```bash
# Generate reports for last 7 days (default)
./generate-multiple-reports.sh

# Generate reports for specific number of days
./generate-multiple-reports.sh 14
```

## 🔧 Setup & Configuration

### 1. Crontab Setup
```bash
# Run the setup script
./setup-crontab.sh

# Or manually add to crontab
crontab -e
```

Add this line to crontab:
```cron
* * * * * cd /home/nandzo/app/cctv_dashboard && docker exec cctv_app_staging php artisan schedule:run >> /dev/null 2>&1
```

### 2. Horizon Queue Worker
Make sure Horizon is running to process the jobs:
```bash
docker exec cctv_app_staging php artisan horizon
```

### 3. Verify Scheduler
```bash
# Check scheduled tasks
docker exec cctv_app_staging php artisan schedule:list

# Test scheduler
docker exec cctv_app_staging php artisan schedule:run
```

## 📋 Report Types

### Daily Reports
- **Data Source**: `re_id_branch_detections` table
- **Metrics**: Total detections, unique persons, active devices, events
- **Scope**: Per branch + overall
- **Storage**: `counting_reports` table with `report_type = 'daily'`

### Monthly Reports
- **Data Source**: Daily reports aggregation
- **Metrics**: Monthly totals, averages, trends
- **Scope**: Per branch + overall
- **Storage**: `counting_reports` table with `report_type = 'monthly'`

## 🐛 Troubleshooting

### Check Report Data
```bash
# Check daily reports
docker exec cctv_app_staging php artisan tinker --execute="
echo 'Daily Reports: ' . \App\Models\CountingReport::where('report_type', 'daily')->count();
echo 'Monthly Reports: ' . \App\Models\CountingReport::where('report_type', 'monthly')->count();
"
```

### Check Queue Status
```bash
# Check failed jobs
docker exec cctv_app_staging php artisan queue:failed

# Retry failed jobs
docker exec cctv_app_staging php artisan queue:retry all
```

### Check Logs
```bash
# Check Laravel logs
docker exec cctv_app_staging tail -f storage/logs/laravel.log

# Check Horizon logs
docker exec cctv_app_staging tail -f storage/logs/horizon.log
```

## 📊 Report Data Structure

### Daily Report Fields
- `report_type`: 'daily'
- `report_date`: Date of the report
- `branch_id`: Branch ID (null for overall)
- `total_devices`: Number of active devices
- `total_detections`: Total detection count
- `total_events`: Total event count
- `unique_person_count`: Number of unique persons detected
- `report_data`: JSON with additional metrics

### Monthly Report Fields
- `report_type`: 'monthly'
- `report_date`: First day of the month
- `branch_id`: Branch ID (null for overall)
- `total_devices`: Maximum devices in the month
- `total_detections`: Sum of daily detections
- `total_events`: Sum of daily events
- `unique_person_count`: Maximum unique persons in the month
- `report_data`: JSON with monthly summary and daily breakdown

## 🔄 Automation Flow

1. **Every 5 minutes**: `UpdateDailyReportJob` runs for all active branches
2. **Daily at 01:00**: Legacy `UpdateDailyReportJob` runs (backup)
3. **Daily at 01:15**: `reports:generate-counting` runs
4. **Monthly on 1st at 02:00**: `UpdateMonthlyReportJob` runs
5. **Continuous**: Crontab runs `schedule:run` every minute
6. **Queue Processing**: Horizon processes jobs in `reports` queue

## 🚀 Performance Improvements

### Before (Detection Event Based)
- ❌ **Every detection event** triggered `UpdateDailyReportJob`
- ❌ **High queue load** during peak detection times
- ❌ **Inefficient** - reports updated per detection
- ❌ **Queue congestion** with many small jobs

### After (Scheduler Based)
- ✅ **Every 5 minutes** - consistent update frequency
- ✅ **Batch processing** - all branches updated together
- ✅ **Better performance** - no jobs from detection events
- ✅ **Cleaner queues** - reports queue only for scheduled jobs
- ✅ **More efficient** - fewer, larger jobs instead of many small ones

## 📈 Performance Notes

- Reports are generated asynchronously using Laravel queues
- Jobs are tagged for easy identification and monitoring
- Failed jobs are automatically retried (3 attempts)
- Timeout is set to 5 minutes per job
- Reports use `updateOrCreate` to avoid duplicates

## 🔧 Recent Changes (v2.0)

### Migration from Detection-Based to Scheduler-Based Reports
- **Date**: October 10, 2025
- **Change**: Moved `UpdateDailyReportJob` from detection events to scheduler
- **Impact**: 
  - ✅ **Better Performance**: No more jobs triggered by every detection
  - ✅ **Cleaner Queues**: Reports queue only processes scheduled jobs
  - ✅ **More Efficient**: Batch processing every 5 minutes
  - ✅ **Reduced Load**: Less stress on queue system during peak times

### Detection Event Changes
- **Before**: Every detection → `UpdateDailyReportJob` dispatched
- **After**: Detection events only dispatch:
  - `SendWhatsAppNotificationJob` (notifications queue)
  - `ProcessDetectionImageJob` (images queue)
  - ❌ **No more** `UpdateDailyReportJob` from detection events

### Scheduler Changes
- **Added**: `update_daily_reports_scheduled` - runs every 5 minutes
- **Scope**: All active branches updated in batch
- **Queue**: `reports` queue (same as before)
- **Benefit**: More frequent updates, better performance
