#!/bin/bash

# Real-time Schedule Monitor
# This script monitors schedule activity in real-time

echo "🔍 CCTV Dashboard Schedule Monitor (Real-time)"
echo "=============================================="
echo "Press Ctrl+C to stop monitoring"
echo ""

# Function to display current status
show_status() {
    clear
    echo "🔍 CCTV Dashboard Schedule Monitor - $(date)"
    echo "=============================================="
    echo ""

    # Check if schedule is running
    echo "📅 Schedule Status:"
    if docker exec cctv_app_staging php artisan schedule:run 2>/dev/null | grep -q "No scheduled commands are ready to run"; then
        echo "✅ Scheduler is ACTIVE (no tasks ready)"
    else
        echo "🔄 Scheduler is RUNNING tasks"
    fi
    echo ""

    # Check recent activity
    echo "📊 Recent Activity (last 2 minutes):"
    journalctl -u cron.service --since "2 minutes ago" | grep "cctv_dashboard" | tail -3
    echo ""

    # Check queue status
    echo "⚡ Queue Status:"
    if docker exec cctv_app_staging php artisan horizon:status 2>/dev/null | grep -q "running"; then
        echo "✅ Horizon is RUNNING"
    else
        echo "❌ Horizon is NOT RUNNING"
    fi
    echo ""

    # Check reports count
    echo "📈 Reports Status:"
    total_reports=$(docker exec cctv_app_staging php artisan tinker --execute='echo \App\Models\CountingReport::count();' 2>/dev/null)
    echo "Total reports: $total_reports"
    echo ""

    echo "⏰ Next scheduled tasks:"
    docker exec cctv_app_staging php artisan schedule:list | head -3
    echo ""
    echo "🔄 Monitoring... (Press Ctrl+C to stop)"
}

# Main monitoring loop
while true; do
    show_status
    sleep 10
done
