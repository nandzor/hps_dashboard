#!/bin/bash

# Create Environment Files Script for CCTV Dashboard
# Usage: ./create-env-files.sh [production|staging|both]

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

# Function to generate APP_KEY
generate_app_key() {
    openssl rand -base64 32
}

# Function to create production .env
create_production_env() {
    print_header "Creating Production .env File"

    local app_key=$(generate_app_key)

    cat > .env.production << EOF
APP_NAME="CCTV Dashboard Pro"
APP_ENV=production
APP_KEY=base64:$app_key
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
MAIL_FROM_NAME="\${APP_NAME}"

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

VITE_APP_NAME="\${APP_NAME}"
VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="\${PUSHER_HOST}"
VITE_PUSHER_PORT="\${PUSHER_PORT}"
VITE_PUSHER_SCHEME="\${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"

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

# Security settings
BCRYPT_ROUNDS=12
JWT_SECRET=your-jwt-secret-here
JWT_ALGO=HS256
JWT_TTL=60

# Performance settings
CACHE_TTL=3600
SESSION_TIMEOUT=7200
QUEUE_TIMEOUT=300
EOF

    print_status "✅ Production .env file created"
}

# Function to create staging .env
create_staging_env() {
    print_header "Creating Staging .env File"

    local app_key=$(generate_app_key)

    cat > .env.staging << EOF
APP_NAME="CCTV Dashboard Pro"
APP_ENV=staging
APP_KEY=base64:$app_key
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
MAIL_FROM_NAME="\${APP_NAME}"

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

VITE_APP_NAME="\${APP_NAME}"
VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="\${PUSHER_HOST}"
VITE_PUSHER_PORT="\${PUSHER_PORT}"
VITE_PUSHER_SCHEME="\${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"

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

# Security settings
BCRYPT_ROUNDS=10
JWT_SECRET=your-jwt-secret-here
JWT_ALGO=HS256
JWT_TTL=60

# Performance settings
CACHE_TTL=1800
SESSION_TIMEOUT=3600
QUEUE_TIMEOUT=300
EOF

    print_status "✅ Staging .env file created"
}

# Main execution
if [ $# -eq 0 ]; then
    print_error "Please specify environment: production, staging, or both"
    echo "Usage: ./create-env-files.sh [production|staging|both]"
    exit 1
fi

ENVIRONMENT=$1

print_header "Creating Environment Files for CCTV Dashboard"

case $ENVIRONMENT in
    "production")
        create_production_env
        ;;
    "staging")
        create_staging_env
        ;;
    "both")
        create_production_env
        echo ""
        create_staging_env
        ;;
    *)
        print_error "Invalid environment. Use 'production', 'staging', or 'both'"
        exit 1
        ;;
esac

print_header "Environment Files Created Successfully!"

print_status "Files created:"
if [ "$ENVIRONMENT" = "production" ] || [ "$ENVIRONMENT" = "both" ]; then
    echo "  📄 .env.production"
fi
if [ "$ENVIRONMENT" = "staging" ] || [ "$ENVIRONMENT" = "both" ]; then
    echo "  📄 .env.staging"
fi

print_status "Next steps:"
echo "  1. Review and customize the .env files if needed"
echo "  2. Run './dockerize.sh $ENVIRONMENT' to build and start containers"
echo "  3. Use './manage-docker.sh status $ENVIRONMENT' to check status"
echo "  4. Use './test-environments.sh $ENVIRONMENT' to run tests"
