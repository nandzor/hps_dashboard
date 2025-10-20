#!/bin/bash

# Test Environments Script for CCTV Dashboard
# Usage: ./test-environments.sh [production|staging|both]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE} $1${NC}"
    echo -e "${BLUE}================================${NC}"
}

# Function to test environment
test_environment() {
    local env=$1
    local compose_file="docker-compose.$env.yml"

    print_header "Testing $env Environment"

    # Check if compose file exists
    if [ ! -f "$compose_file" ]; then
        print_error "Compose file $compose_file not found!"
        return 1
    fi

    # Check if containers are running
    print_status "Checking container status..."
    if ! docker compose -f "$compose_file" ps | grep -q "Up"; then
        print_error "Containers are not running for $env environment!"
        print_status "Start with: ./dockerize.sh $env"
        return 1
    fi

    # Test application endpoint
    print_status "Testing application endpoint..."
    local app_url="http://localhost:9001"
    local response=$(curl -s -o /dev/null -w "%{http_code}" "$app_url" || echo "000")

    if [ "$response" = "200" ] || [ "$response" = "302" ]; then
        print_status "✅ Application is accessible at $app_url"
    else
        print_error "❌ Application not accessible (HTTP $response)"
        return 1
    fi

    # Test database connection
    print_status "Testing database connection..."
    if docker compose -f "$compose_file" exec cctv_app php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; then
        print_status "✅ Database connection successful"
    else
        print_error "❌ Database connection failed"
        return 1
    fi

    # Test Redis connection
    print_status "Testing Redis connection..."
    if docker compose -f "$compose_file" exec cctv_app php artisan tinker --execute="Redis::ping();" > /dev/null 2>&1; then
        print_status "✅ Redis connection successful"
    else
        print_error "❌ Redis connection failed"
        return 1
    fi

    # Test Horizon status
    print_status "Testing Horizon status..."
    local horizon_status=$(docker compose -f "$compose_file" exec cctv_app php artisan horizon:status 2>/dev/null | grep -c "Horizon is running" || echo "0")

    if [ "$horizon_status" -gt 0 ]; then
        print_status "✅ Horizon is running"
    else
        print_warning "⚠️  Horizon is not running"
    fi

    # Test queue functionality
    print_status "Testing queue functionality..."
    if docker compose -f "$compose_file" exec cctv_app php artisan queue:test --count=3 > /dev/null 2>&1; then
        print_status "✅ Queue functionality working"
    else
        print_warning "⚠️  Queue functionality test failed"
    fi

    # Environment-specific tests
    if [ "$env" = "staging" ]; then
        # Test Vite server
        print_status "Testing Vite development server..."
        local vite_response=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:5173" || echo "000")

        if [ "$vite_response" = "200" ]; then
            print_status "✅ Vite server is accessible at http://localhost:5173"
        else
            print_warning "⚠️  Vite server not accessible (HTTP $vite_response)"
        fi

        # Test file sync
        print_status "Testing file sync..."
        echo "<!-- Test sync $(date) -->" >> resources/views/test-sync.blade.php
        sleep 2

        if docker compose -f "$compose_file" exec cctv_app test -f /app/resources/views/test-sync.blade.php; then
            print_status "✅ File sync working"
            docker compose -f "$compose_file" exec cctv_app rm -f /app/resources/views/test-sync.blade.php
        else
            print_warning "⚠️  File sync not working"
        fi
    fi

    # Performance tests
    print_status "Running performance tests..."

    # Test concurrent requests
    print_status "Testing concurrent requests..."
    local concurrent_requests=10
    local success_count=0

    for i in $(seq 1 $concurrent_requests); do
        if curl -s -o /dev/null -w "%{http_code}" "$app_url" | grep -q "200\|302"; then
            success_count=$((success_count + 1))
        fi
    done

    print_status "Concurrent requests: $success_count/$concurrent_requests successful"

    # Test queue processing
    print_status "Testing queue processing..."
    docker compose -f "$compose_file" exec cctv_app php artisan queue:test --count=5 > /dev/null 2>&1
    sleep 3

    local queue_size=$(docker compose -f "$compose_file" exec cctv_app php artisan tinker --execute="echo Redis::llen('queues:default');" 2>/dev/null || echo "0")

    if [ "$queue_size" -eq "0" ]; then
        print_status "✅ Queue processing working (all jobs processed)"
    else
        print_warning "⚠️  Queue processing slow ($queue_size jobs remaining)"
    fi

    # Resource usage
    print_status "Checking resource usage..."
    local memory_usage=$(docker stats --no-stream --format "table {{.MemUsage}}" cctv_app_$env 2>/dev/null | tail -n 1 || echo "N/A")
    local cpu_usage=$(docker stats --no-stream --format "table {{.CPUPerc}}" cctv_app_$env 2>/dev/null | tail -n 1 || echo "N/A")

    print_status "Memory usage: $memory_usage"
    print_status "CPU usage: $cpu_usage"

    print_status "✅ $env environment tests completed successfully!"
    return 0
}

# Main execution
if [ $# -eq 0 ]; then
    print_error "Please specify environment: production, staging, or both"
    echo "Usage: ./test-environments.sh [production|staging|both]"
    exit 1
fi

ENVIRONMENT=$1

print_header "CCTV Dashboard Environment Testing"

if [ "$ENVIRONMENT" = "both" ]; then
    print_status "Testing both production and staging environments..."

    # Test production
    if test_environment "production"; then
        print_status "✅ Production environment tests passed"
    else
        print_error "❌ Production environment tests failed"
    fi

    echo ""

    # Test staging
    if test_environment "staging"; then
        print_status "✅ Staging environment tests passed"
    else
        print_error "❌ Staging environment tests failed"
    fi

elif [ "$ENVIRONMENT" = "production" ] || [ "$ENVIRONMENT" = "staging" ]; then
    if test_environment "$ENVIRONMENT"; then
        print_status "✅ $ENVIRONMENT environment tests passed"
    else
        print_error "❌ $ENVIRONMENT environment tests failed"
        exit 1
    fi
else
    print_error "Invalid environment. Use 'production', 'staging', or 'both'"
    exit 1
fi

print_header "Testing Complete!"
print_status "All tests completed for $ENVIRONMENT environment(s)"
