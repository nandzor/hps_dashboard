#!/bin/bash

# Generate Multiple Reports Script
# This script generates reports for multiple days

DAYS=${1:-7}  # Default to 7 days if no argument provided

echo "🔄 Generating reports for last $DAYS days..."

for i in $(seq 1 $DAYS); do
    date=$(date -d "$i days ago" +%Y-%m-%d)
    echo "📅 Generating reports for: $date"

    docker exec cctv_app_staging php artisan reports:generate-counting --date=$date

    if [ $? -eq 0 ]; then
        echo "✅ Reports generated for $date"
    else
        echo "❌ Failed to generate reports for $date"
    fi
done

echo "🎉 Multiple reports generation completed!"
