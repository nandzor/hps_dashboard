#!/bin/bash

# Dockerize Script for CCTV Dashboard
# Usage: ./dockerize.sh [production|staging]

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

# Check if environment is provided
if [ $# -eq 0 ]; then
    print_error "Please specify environment: production or staging"
    echo "Usage: ./dockerize.sh [production|staging]"
    exit 1
fi

ENVIRONMENT=$1

# Validate environment
if [ "$ENVIRONMENT" != "production" ] && [ "$ENVIRONMENT" != "staging" ]; then
    print_error "Invalid environment. Use 'production' or 'staging'"
    exit 1
fi

print_header "Dockerizing CCTV Dashboard - $ENVIRONMENT"

# Stop existing containers
print_status "Stopping existing containers..."
docker compose down --remove-orphans

# Remove existing images if they exist
print_status "Removing existing images..."
docker image rm cctv_dashboard_app 2>/dev/null || true

# Create environment-specific docker-compose file
print_status "Creating docker-compose.$ENVIRONMENT.yml..."

if [ "$ENVIRONMENT" = "production" ]; then
    cat > docker-compose.$ENVIRONMENT.yml << 'EOF'
version: '3.8'

services:
  cctv_app:
    build:
      context: .
      dockerfile: ./docker/frankenphp/Dockerfile
      args:
        - ENVIRONMENT=production
    container_name: cctv_app_prod
    ports:
      - "9001:80"
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - redis_data:/data
    networks:
      - cctv_network
    restart: unless-stopped
    depends_on:
      postgresql:
        condition: service_healthy
      redis:
        condition: service_started
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=postgresql
      - DB_PORT=5432
      - DB_DATABASE=cctv_dashboard
      - DB_USERNAME=postgres
      - DB_PASSWORD=kambin
      - REDIS_HOST=redis
      - REDIS_PORT=6379
      - QUEUE_CONNECTION=redis
      - FRANKENPHP_WORKERS=32
      - HORIZON_WORKERS=16

  postgresql:
    image: postgres:17
    container_name: cctv_postgres_prod
    ports:
      - "5433:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - cctv_network
    restart: unless-stopped
    environment:
      - POSTGRES_DB=cctv_dashboard
      - POSTGRES_USER=postgres
      - POSTGRES_PASSWORD=kambin
      - POSTGRES_HOST_AUTH_METHOD=scram-sha-256
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postgres -d cctv_dashboard"]
      interval: 10s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    container_name: cctv_redis_prod
    ports:
      - "6380:6379"
    volumes:
      - redis_data:/data
    networks:
      - cctv_network
    restart: unless-stopped
    command: redis-server --appendonly yes

volumes:
  postgres_data:
  redis_data:

networks:
  cctv_network:
    driver: bridge
EOF

elif [ "$ENVIRONMENT" = "staging" ]; then
    cat > docker-compose.$ENVIRONMENT.yml << 'EOF'
version: '3.8'

services:
  cctv_app:
    build:
      context: .
      dockerfile: ./docker/frankenphp/Dockerfile
      args:
        - ENVIRONMENT=staging
    container_name: cctv_app_staging
    ports:
      - "9001:80"
    volumes:
      - .:/app
      - ./storage:/app/storage
      - ./bootstrap/cache:/app/bootstrap/cache
      - vite_node_modules:/app/node_modules
    networks:
      - cctv_network
    restart: unless-stopped
    depends_on:
      postgresql:
        condition: service_healthy
      redis:
        condition: service_started
    environment:
      - APP_ENV=staging
      - APP_DEBUG=true
      - DB_HOST=postgresql
      - DB_PORT=5432
      - REDIS_HOST=redis
      - REDIS_PORT=6379
      - QUEUE_CONNECTION=redis
      - FRANKENPHP_WORKERS=16
      - HORIZON_WORKERS=8

  postgresql:
    image: postgres:17
    container_name: cctv_postgres_staging
    ports:
      - "5433:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - cctv_network
    restart: unless-stopped
    environment:
      - POSTGRES_DB=cctv_dashboard
      - POSTGRES_USER=postgres
      - POSTGRES_PASSWORD=kambin
      - POSTGRES_HOST_AUTH_METHOD=scram-sha-256
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postgres -d cctv_dashboard"]
      interval: 10s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    container_name: cctv_redis_staging
    ports:
      - "6380:6379"
    volumes:
      - redis_data:/data
    networks:
      - cctv_network
    restart: unless-stopped
    command: redis-server --appendonly yes

  vite:
    image: node:20-alpine
    container_name: cctv_vite_staging
    ports:
      - "5173:5173"
    volumes:
      - .:/app
      - vite_node_modules:/app/node_modules
    networks:
      - cctv_network
    restart: unless-stopped
    depends_on:
      - cctv_app
    environment:
      - NODE_ENV=development
      - VITE_APP_URL=http://localhost:9001
      - VITE_DEV_SERVER_HOST=0.0.0.0
      - VITE_DEV_SERVER_PORT=5173
    working_dir: /app
    command: >
      sh -c "
        echo 'Installing dependencies...' &&
        npm ci &&
        echo 'Starting Vite development server...' &&
        npm run dev
      "

volumes:
  postgres_data:
  redis_data:
  vite_node_modules:

networks:
  cctv_network:
    driver: bridge
EOF
fi

# Create environment-specific Dockerfile
print_status "Creating Dockerfile.$ENVIRONMENT..."

if [ "$ENVIRONMENT" = "production" ]; then
    cat > docker/frankenphp/Dockerfile.$ENVIRONMENT << 'EOF'
FROM dunglas/frankenphp:1-php8.3

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql zip gd pcntl

# Install Redis extension via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Set working directory
WORKDIR /app

# Copy .env file first (for production environment)
COPY .env .env

# Copy application files
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install Node.js dependencies and build assets
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm ci \
    && npm run build \
    && rm -rf node_modules

# Create necessary directories and set permissions
RUN mkdir -p /var/log/caddy \
    && chmod 755 /app \
    && chmod 755 /var/log/caddy

# Configure FrankenPHP for production
ENV FRANKENPHP_CONFIG="worker:32 scheduler:2"
ENV QUEUE_CONNECTION=redis
ENV REDIS_HOST=redis
ENV REDIS_PORT=6379

# Expose port 80
EXPOSE 80

# Copy Caddyfile
COPY docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

# Start FrankenPHP
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
EOF

elif [ "$ENVIRONMENT" = "staging" ]; then
    cat > docker/frankenphp/Dockerfile.$ENVIRONMENT << 'EOF'
FROM dunglas/frankenphp:1-php8.3

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql zip gd pcntl

# Install Redis extension via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create necessary directories and set permissions
RUN mkdir -p /var/log/caddy \
    && chmod 755 /app \
    && chmod 755 /var/log/caddy

# Configure FrankenPHP for staging
ENV FRANKENPHP_CONFIG="worker:16 scheduler:2"
ENV QUEUE_CONNECTION=redis
ENV REDIS_HOST=redis
ENV REDIS_PORT=6379

# Expose port 80
EXPOSE 80

# Copy Caddyfile
COPY docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

# Start FrankenPHP
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
EOF
fi

# Create environment-specific setup script
print_status "Creating setup-db.$ENVIRONMENT.sh..."

if [ "$ENVIRONMENT" = "production" ]; then
    cat > docker/setup-db.$ENVIRONMENT.sh << 'EOF'
#!/bin/bash
# Database setup script for CCTV Dashboard - Production

echo "🚀 Starting database setup for PRODUCTION..."

# Wait for database to be ready
echo "⏳ Waiting for PostgreSQL to be ready..."
until php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; do
  echo "PostgreSQL is unavailable - sleeping"
  sleep 2
done

echo "✅ PostgreSQL is ready!"

# Create database if not exists
echo "📊 Creating database if not exists..."
php artisan db:create || echo "Database might already exist"

# Test database connection
echo "🔗 Testing database connection..."
php artisan tinker --execute="DB::connection()->getPdo();" || exit 1

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Check migration status
echo "📋 Checking migration status..."
php artisan migrate:status

# Run seeders
echo "🌱 Running database seeders..."
php artisan db:seed --force

# Clear cache
echo "🧹 Clearing application cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Setup queue processing with Horizon
echo "⚙️ Setting up Laravel Horizon for PRODUCTION..."
echo "✅ FrankenPHP: 32 workers for web requests"
echo "✅ Horizon: 16 workers for queue processing"

# Start Horizon in background
echo "🚀 Starting Laravel Horizon..."
php artisan horizon &
HORIZON_PID=$!

echo "✅ Database setup completed!"
echo "🚀 Starting FrankenPHP with Horizon queue processing..."

# Start FrankenPHP
exec frankenphp run --config /etc/caddy/Caddyfile
EOF

elif [ "$ENVIRONMENT" = "staging" ]; then
    cat > docker/setup-db.$ENVIRONMENT.sh << 'EOF'
#!/bin/bash
# Database setup script for CCTV Dashboard - Staging

echo "🚀 Starting database setup for STAGING..."

# Wait for database to be ready
echo "⏳ Waiting for PostgreSQL to be ready..."
until php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; do
  echo "PostgreSQL is unavailable - sleeping"
  sleep 2
done

echo "✅ PostgreSQL is ready!"

# Create database if not exists
echo "📊 Creating database if not exists..."
php artisan db:create || echo "Database might already exist"

# Test database connection
echo "🔗 Testing database connection..."
php artisan tinker --execute="DB::connection()->getPdo();" || exit 1

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Check migration status
echo "📋 Checking migration status..."
php artisan migrate:status

# Run seeders
echo "🌱 Running database seeders..."
php artisan db:seed --force

# Clear cache
echo "🧹 Clearing application cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Setup queue processing with Horizon
echo "⚙️ Setting up Laravel Horizon for STAGING..."
echo "✅ FrankenPHP: 16 workers for web requests"
echo "✅ Horizon: 8 workers for queue processing"
echo "✅ Vite: Development server for hot reload"

# Start Horizon in background
echo "🚀 Starting Laravel Horizon..."
php artisan horizon &
HORIZON_PID=$!

echo "✅ Database setup completed!"
echo "🚀 Starting FrankenPHP with Horizon queue processing..."

# Start FrankenPHP
exec frankenphp run --config /etc/caddy/Caddyfile
EOF
fi

# Make setup script executable
chmod +x docker/setup-db.$ENVIRONMENT.sh

# Update docker-compose to use environment-specific files
if [ "$ENVIRONMENT" = "production" ]; then
    # Update docker-compose to use production Dockerfile and setup script
    sed -i 's|dockerfile: ./docker/frankenphp/Dockerfile|dockerfile: ./docker/frankenphp/Dockerfile.production|g' docker-compose.$ENVIRONMENT.yml
    sed -i 's|setup-db.sh|setup-db.production.sh|g' docker-compose.$ENVIRONMENT.yml
elif [ "$ENVIRONMENT" = "staging" ]; then
    # Update docker-compose to use staging Dockerfile and setup script
    sed -i 's|dockerfile: ./docker/frankenphp/Dockerfile|dockerfile: ./docker/frankenphp/Dockerfile.staging|g' docker-compose.$ENVIRONMENT.yml
    sed -i 's|setup-db.sh|setup-db.staging.sh|g' docker-compose.$ENVIRONMENT.yml
fi

# Build and start containers
    print_status "Building and starting containers for $ENVIRONMENT..."
    docker compose -f docker-compose.$ENVIRONMENT.yml up --build -d

    # Fix Vite URL for staging environment
    if [ "$ENVIRONMENT" = "staging" ]; then
        print_status "Fixing Vite URL for staging environment..."
        sleep 10  # Wait for containers to be ready
        docker compose -f docker-compose.staging.yml exec cctv_app bash -c "echo 'http://localhost:5173' > public/hot" 2>/dev/null || true
        print_status "✅ Vite URL fixed for staging environment"
    fi

# Wait for services to be ready
print_status "Waiting for services to be ready..."
sleep 10

# Check if services are running
print_status "Checking service status..."
docker compose -f docker-compose.$ENVIRONMENT.yml ps

print_header "Dockerization Complete!"

if [ "$ENVIRONMENT" = "production" ]; then
    print_status "Production Environment:"
    echo "  🌐 Application: http://localhost:9001"
    echo "  🗄️  Database: localhost:5433"
    echo "  🔴 Redis: localhost:6380"
    echo "  ⚡ FrankenPHP: 32 workers"
    echo "  🔄 Horizon: 16 queue workers"
    echo "  📦 All files inside container"
    echo "  🔒 Environment variables inside container"
elif [ "$ENVIRONMENT" = "staging" ]; then
    print_status "Staging Environment:"
    echo "  🌐 Application: http://localhost:9001"
    echo "  🗄️  Database: localhost:5433"
    echo "  🔴 Redis: localhost:6380"
    echo "  ⚡ FrankenPHP: 16 workers"
    echo "  🔄 Horizon: 8 queue workers"
    echo "  🚀 Vite: http://localhost:5173"
    echo "  📁 Files synced with host"
    echo "  🔧 Environment variables from host .env"
fi

print_status "Use 'docker compose -f docker-compose.$ENVIRONMENT.yml logs -f' to view logs"
print_status "Use 'docker compose -f docker-compose.$ENVIRONMENT.yml down' to stop"
