#!/bin/bash

# Update Configuration Script for CCTV Dashboard
# Usage: ./update-config.sh [production|staging]

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

# Check if environment is provided
if [ $# -eq 0 ]; then
    print_error "Please specify environment: production or staging"
    echo "Usage: ./update-config.sh [production|staging]"
    exit 1
fi

ENVIRONMENT=$1

# Validate environment
if [ "$ENVIRONMENT" != "production" ] && [ "$ENVIRONMENT" != "staging" ]; then
    print_error "Invalid environment. Use 'production' or 'staging'"
    exit 1
fi

print_status "Updating configuration for $ENVIRONMENT..."

# Update Horizon configuration
if [ "$ENVIRONMENT" = "production" ]; then
    print_status "Updating Horizon configuration for production..."
    cp config/horizon.production.php config/horizon.php

    # Update .env for production
    print_status "Creating production .env..."
    cat > .env.production << 'EOF'
APP_NAME="CCTV Dashboard Pro"
APP_ENV=production
APP_KEY=base64:your-production-key-here
APP_DEBUG=false
APP_URL=http://localhost:9001

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=postgresql
DB_PORT=5432
DB_DATABASE=cctv_dashboard
DB_USERNAME=cctv_user
DB_PASSWORD=cctv_password_2024

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Production specific settings
HORIZON_PREFIX=horizon:
HORIZON_BALANCE=auto
HORIZON_MAX_PROCESSES=16
HORIZON_MEMORY_LIMIT=128
HORIZON_TRIES=3
HORIZON_TIMEOUT=60

# FrankenPHP settings
FRANKENPHP_WORKERS=32
FRANKENPHP_SCHEDULERS=2
EOF

elif [ "$ENVIRONMENT" = "staging" ]; then
    print_status "Updating Horizon configuration for staging..."
    # Keep existing horizon.php for staging (8 workers)

    # Update .env for staging
    print_status "Creating staging .env..."
    cat > .env.staging << 'EOF'
APP_NAME="CCTV Dashboard Pro"
APP_ENV=staging
APP_KEY=base64:your-staging-key-here
APP_DEBUG=true
APP_URL=http://localhost:9001

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=postgresql
DB_PORT=5432
DB_DATABASE=cctv_dashboard
DB_USERNAME=cctv_user
DB_PASSWORD=cctv_password_2024

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Staging specific settings
HORIZON_PREFIX=horizon:
HORIZON_BALANCE=auto
HORIZON_MAX_PROCESSES=8
HORIZON_MEMORY_LIMIT=128
HORIZON_TRIES=3
HORIZON_TIMEOUT=60

# FrankenPHP settings
FRANKENPHP_WORKERS=16
FRANKENPHP_SCHEDULERS=2

# Vite settings
VITE_APP_URL=http://localhost:9001
VITE_DEV_SERVER_URL=http://localhost:5173
EOF
fi

print_status "Configuration updated for $ENVIRONMENT!"
print_status "Use './dockerize.sh $ENVIRONMENT' to build and start containers"
