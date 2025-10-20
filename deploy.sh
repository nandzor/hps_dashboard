#!/bin/bash

# CCTV Dashboard Deployment Script
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production

ENVIRONMENT=${1:-staging}

echo "=========================================="
echo "CCTV Dashboard - Deployment Script"
echo "Environment: $ENVIRONMENT"
echo "=========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}Error: .env file not found!${NC}"
    echo "Please copy .env.example to .env and configure it first"
    exit 1
fi

# Put application in maintenance mode
echo -e "${YELLOW}→ Putting application in maintenance mode...${NC}"
php artisan down || true

# Pull latest code (if using git)
if [ -d .git ]; then
    echo -e "${YELLOW}→ Pulling latest code from git...${NC}"
    git pull origin main || git pull origin master
fi

# Install/update composer dependencies
echo -e "${YELLOW}→ Installing composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# Install/update npm dependencies
echo -e "${YELLOW}→ Installing npm dependencies...${NC}"
npm install --production

# Clear all caches
echo -e "${YELLOW}→ Clearing caches...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations (if needed)
echo -e "${YELLOW}→ Running migrations...${NC}"
php artisan migrate --force

# Optimize for production
echo -e "${YELLOW}→ Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Build frontend assets
echo -e "${YELLOW}→ Building frontend assets...${NC}"
npm run build

# Create storage link (if not exists)
if [ ! -L public/storage ]; then
    echo -e "${YELLOW}→ Creating storage link...${NC}"
    php artisan storage:link
fi

# Set correct permissions
echo -e "${YELLOW}→ Setting permissions...${NC}"
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Restart queue workers (if using supervisor)
if command -v supervisorctl &> /dev/null; then
    echo -e "${YELLOW}→ Restarting queue workers...${NC}"
    sudo supervisorctl restart all
fi

# Bring application back online
echo -e "${YELLOW}→ Bringing application back online...${NC}"
php artisan up

echo ""
echo "=========================================="
echo -e "${GREEN}✓ Deployment Complete!${NC}"
echo "=========================================="
echo ""
echo "Post-deployment checklist:"
echo "  [ ] Test website is accessible"
echo "  [ ] Test login functionality"
echo "  [ ] Check queue workers are running"
echo "  [ ] Verify database migrations"
echo "  [ ] Check error logs: storage/logs/laravel.log"
echo "  [ ] Test API endpoints"
echo ""

if [ "$ENVIRONMENT" = "production" ]; then
    echo -e "${RED}⚠️  PRODUCTION DEPLOYMENT${NC}"
    echo "  [ ] Verify APP_DEBUG=false in .env"
    echo "  [ ] Verify APP_ENV=production in .env"
    echo "  [ ] Check SSL certificate is valid"
    echo "  [ ] Backup database before deployment"
    echo ""
fi

