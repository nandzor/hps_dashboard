#!/bin/bash

# Docker Help Script for CCTV Dashboard
# Usage: ./docker-help.sh

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Function to print colored output
print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE} $1${NC}"
    echo -e "${BLUE}================================${NC}"
}

print_section() {
    echo -e "${GREEN}📋 $1${NC}"
}

print_command() {
    echo -e "${YELLOW}  $1${NC}"
}

print_description() {
    echo -e "${CYAN}    $1${NC}"
}

print_example() {
    echo -e "${PURPLE}    Example: $1${NC}"
}

print_warning() {
    echo -e "${RED}⚠️  $1${NC}"
}

print_header "🐳 CCTV Dashboard Docker Management"

echo ""
print_section "Available Scripts:"
echo ""

print_command "dockerize.sh"
print_description "Main script to build and start containers"
print_example "./dockerize.sh production"
print_example "./dockerize.sh staging"

echo ""
print_command "manage-docker.sh"
print_description "Container management (start, stop, restart, clean, logs, status, monitor)"
print_example "./manage-docker.sh start production"
print_example "./manage-docker.sh logs staging"
print_example "./manage-docker.sh clean all"

echo ""
print_command "test-environments.sh"
print_description "Test both environments for functionality"
print_example "./test-environments.sh production"
print_example "./test-environments.sh staging"
print_example "./test-environments.sh both"

echo ""
print_command "create-env-files.sh"
print_description "Generate .env files for different environments"
print_example "./create-env-files.sh production"
print_example "./create-env-files.sh staging"
print_example "./create-env-files.sh both"

echo ""
print_command "update-config.sh"
print_description "Update configuration files for specific environment"
print_example "./update-config.sh production"
print_example "./update-config.sh staging"

echo ""
print_section "Environment Comparison:"
echo ""

echo -e "${YELLOW}Production Environment:${NC}"
echo "  🚀 FrankenPHP: 32 workers"
echo "  🔄 Horizon: 16 queue workers"
echo "  📦 Files: Inside container"
echo "  🔒 Environment: Inside container"
echo "  ❌ Vite: No (pre-built assets)"
echo "  🌐 URL: http://localhost:9001"

echo ""
echo -e "${YELLOW}Staging Environment:${NC}"
echo "  🚀 FrankenPHP: 16 workers"
echo "  🔄 Horizon: 8 queue workers"
echo "  📁 Files: Sync with host"
echo "  🔧 Environment: From host .env"
echo "  ✅ Vite: Yes (hot reload)"
echo "  🌐 URL: http://localhost:9001"
echo "  🚀 Vite: http://localhost:5173"

echo ""
print_section "Quick Start Guide:"
echo ""

echo -e "${GREEN}1. Create Environment Files:${NC}"
print_command "./create-env-files.sh both"
print_description "Generate .env files for both environments"

echo ""
echo -e "${GREEN}2. Build and Start:${NC}"
print_command "./dockerize.sh production"
print_description "Build and start production environment"
print_command "./dockerize.sh staging"
print_description "Build and start staging environment"

echo ""
echo -e "${GREEN}3. Test Environments:${NC}"
print_command "./test-environments.sh both"
print_description "Test both environments for functionality"

echo ""
echo -e "${GREEN}4. Monitor:${NC}"
print_command "./manage-docker.sh status production"
print_description "Check production status"
print_command "./manage-docker.sh monitor staging"
print_description "Monitor staging in real-time"

echo ""
print_section "Common Commands:"
echo ""

echo -e "${YELLOW}Container Management:${NC}"
print_command "./manage-docker.sh start [production|staging]"
print_command "./manage-docker.sh stop [production|staging]"
print_command "./manage-docker.sh restart [production|staging]"
print_command "./manage-docker.sh clean [production|staging|all]"

echo ""
echo -e "${YELLOW}Monitoring:${NC}"
print_command "./manage-docker.sh status [production|staging|all]"
print_command "./manage-docker.sh logs [production|staging]"
print_command "./manage-docker.sh monitor [production|staging]"

echo ""
echo -e "${YELLOW}Testing:${NC}"
print_command "./test-environments.sh [production|staging|both]"

echo ""
print_section "Troubleshooting:"
echo ""

echo -e "${RED}Port Conflicts:${NC}"
print_command "netstat -tulpn | grep :9001"
print_command "netstat -tulpn | grep :5433"
print_command "netstat -tulpn | grep :6380"
print_command "netstat -tulpn | grep :5173"

echo ""
echo -e "${RED}Container Issues:${NC}"
print_command "./manage-docker.sh logs production"
print_command "./manage-docker.sh status production"
print_command "./manage-docker.sh clean production"

echo ""
echo -e "${RED}Database Issues:${NC}"
print_command "docker compose -f docker-compose.production.yml exec cctv_app php artisan tinker --execute=\"DB::connection()->getPdo();\""
print_command "docker compose -f docker-compose.production.yml exec cctv_app php artisan tinker --execute=\"Redis::ping();\""

echo ""
print_section "File Structure:"
echo ""

echo -e "${CYAN}Scripts:${NC}"
echo "  📄 dockerize.sh - Main dockerize script"
echo "  📄 manage-docker.sh - Container management"
echo "  📄 test-environments.sh - Environment testing"
echo "  📄 create-env-files.sh - Generate .env files"
echo "  📄 update-config.sh - Update configurations"
echo "  📄 docker-help.sh - This help script"

echo ""
echo -e "${CYAN}Configuration Files:${NC}"
echo "  📄 docker-compose.production.yml - Production compose"
echo "  📄 docker-compose.staging.yml - Staging compose"
echo "  📄 .env.production - Production environment"
echo "  📄 .env.staging - Staging environment"
echo "  📄 config/horizon.production.php - Production Horizon config"

echo ""
echo -e "${CYAN}Docker Files:${NC}"
echo "  📄 docker/frankenphp/Dockerfile.production - Production Dockerfile"
echo "  📄 docker/frankenphp/Dockerfile.staging - Staging Dockerfile"
echo "  📄 docker/setup-db.production.sh - Production setup"
echo "  📄 docker/setup-db.staging.sh - Staging setup"

echo ""
print_section "Performance Comparison:"
echo ""

echo -e "${GREEN}Production:${NC}"
echo "  🚀 Web Workers: 32"
echo "  🔄 Queue Workers: 16"
echo "  💾 Memory: ~1.5GB"
echo "  ⚡ CPU: ~20%"
echo "  📊 Concurrent: 32 requests"
echo "  🔄 Queue: 16 jobs/sec"

echo ""
echo -e "${GREEN}Staging:${NC}"
echo "  🚀 Web Workers: 16"
echo "  🔄 Queue Workers: 8"
echo "  💾 Memory: ~800MB"
echo "  ⚡ CPU: ~10%"
echo "  📊 Concurrent: 16 requests"
echo "  🔄 Queue: 8 jobs/sec"

echo ""
print_section "Next Steps:"
echo ""

echo -e "${GREEN}1. Choose Environment:${NC}"
echo "  🏭 Production: For live deployment"
echo "  🧪 Staging: For development and testing"

echo ""
echo -e "${GREEN}2. Start Development:${NC}"
print_command "./create-env-files.sh staging"
print_command "./dockerize.sh staging"
print_command "./test-environments.sh staging"

echo ""
echo -e "${GREEN}3. Deploy to Production:${NC}"
print_command "./create-env-files.sh production"
print_command "./dockerize.sh production"
print_command "./test-environments.sh production"

echo ""
print_warning "Remember to customize .env files before deployment!"
print_warning "Review security settings for production environment!"

echo ""
print_header "Happy Dockerizing! 🐳"
