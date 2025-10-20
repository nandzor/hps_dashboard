#!/bin/bash

# Docker Management Script for CCTV Dashboard
# Usage: ./manage-docker.sh [start|stop|restart|clean|logs|status] [production|staging]

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

# Function to show usage
show_usage() {
    echo "Usage: ./manage-docker.sh [command] [environment]"
    echo ""
    echo "Commands:"
    echo "  start     - Start containers"
    echo "  stop      - Stop containers"
    echo "  restart   - Restart containers"
    echo "  clean     - Clean up containers, images, and volumes"
    echo "  logs      - Show logs"
    echo "  status    - Show container status"
    echo "  monitor   - Monitor system resources"
    echo ""
    echo "Environments:"
    echo "  production - Production environment"
    echo "  staging    - Staging environment"
    echo "  all        - All environments"
    echo ""
    echo "Examples:"
    echo "  ./manage-docker.sh start production"
    echo "  ./manage-docker.sh logs staging"
    echo "  ./manage-docker.sh clean all"
}

# Function to get compose file
get_compose_file() {
    local env=$1
    echo "docker-compose.$env.yml"
}

# Function to start environment
start_environment() {
    local env=$1
    local compose_file=$(get_compose_file $env)

    print_header "Starting $env Environment"

    if [ ! -f "$compose_file" ]; then
        print_error "Compose file $compose_file not found!"
        print_status "Run './dockerize.sh $env' first"
        return 1
    fi

    print_status "Starting containers..."
    docker compose -f "$compose_file" up -d

    print_status "Waiting for services to be ready..."
    sleep 10

    print_status "Checking service status..."
    docker compose -f "$compose_file" ps

    print_status "✅ $env environment started successfully!"
}

# Function to stop environment
stop_environment() {
    local env=$1
    local compose_file=$(get_compose_file $env)

    print_header "Stopping $env Environment"

    if [ ! -f "$compose_file" ]; then
        print_error "Compose file $compose_file not found!"
        return 1
    fi

    print_status "Stopping containers..."
    docker compose -f "$compose_file" down

    print_status "✅ $env environment stopped successfully!"
}

# Function to restart environment
restart_environment() {
    local env=$1

    print_header "Restarting $env Environment"

    stop_environment $env
    sleep 5
    start_environment $env
}

# Function to clean environment
clean_environment() {
    local env=$1
    local compose_file=$(get_compose_file $env)

    print_header "Cleaning $env Environment"

    if [ -f "$compose_file" ]; then
        print_status "Stopping and removing containers..."
        docker compose -f "$compose_file" down --volumes --remove-orphans
    fi

    print_status "Removing images..."
    docker image rm cctv_dashboard_app 2>/dev/null || true

    print_status "Removing unused volumes..."
    docker volume prune -f

    print_status "Removing unused networks..."
    docker network prune -f

    print_status "✅ $env environment cleaned successfully!"
}

# Function to show logs
show_logs() {
    local env=$1
    local compose_file=$(get_compose_file $env)

    print_header "Showing Logs for $env Environment"

    if [ ! -f "$compose_file" ]; then
        print_error "Compose file $compose_file not found!"
        return 1
    fi

    print_status "Showing logs (Ctrl+C to exit)..."
    docker compose -f "$compose_file" logs -f
}

# Function to show status
show_status() {
    local env=$1
    local compose_file=$(get_compose_file $env)

    print_header "Status for $env Environment"

    if [ ! -f "$compose_file" ]; then
        print_error "Compose file $compose_file not found!"
        return 1
    fi

    print_status "Container status:"
    docker compose -f "$compose_file" ps

    echo ""
    print_status "Resource usage:"
    docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}\t{{.BlockIO}}" $(docker compose -f "$compose_file" ps -q) 2>/dev/null || echo "No containers running"

    echo ""
    print_status "Disk usage:"
    docker system df
}

# Function to monitor system
monitor_system() {
    local env=$1
    local compose_file=$(get_compose_file $env)

    print_header "Monitoring $env Environment"

    if [ ! -f "$compose_file" ]; then
        print_error "Compose file $compose_file not found!"
        return 1
    fi

    print_status "Starting system monitoring (Ctrl+C to exit)..."
    print_status "Press 'q' to quit monitoring"

    while true; do
        clear
        print_header "System Monitor - $env Environment"

        echo ""
        print_status "Container Status:"
        docker compose -f "$compose_file" ps

        echo ""
        print_status "Resource Usage:"
        docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}\t{{.BlockIO}}" $(docker compose -f "$compose_file" ps -q) 2>/dev/null || echo "No containers running"

        echo ""
        print_status "Recent Logs:"
        docker compose -f "$compose_file" logs --tail=5

        echo ""
        print_status "Press Ctrl+C to exit monitoring"

        sleep 5
    done
}

# Main execution
if [ $# -lt 1 ]; then
    show_usage
    exit 1
fi

COMMAND=$1
ENVIRONMENT=${2:-"staging"}

case $COMMAND in
    "start")
        if [ "$ENVIRONMENT" = "all" ]; then
            start_environment "production"
            start_environment "staging"
        else
            start_environment $ENVIRONMENT
        fi
        ;;
    "stop")
        if [ "$ENVIRONMENT" = "all" ]; then
            stop_environment "production"
            stop_environment "staging"
        else
            stop_environment $ENVIRONMENT
        fi
        ;;
    "restart")
        if [ "$ENVIRONMENT" = "all" ]; then
            restart_environment "production"
            restart_environment "staging"
        else
            restart_environment $ENVIRONMENT
        fi
        ;;
    "clean")
        if [ "$ENVIRONMENT" = "all" ]; then
            clean_environment "production"
            clean_environment "staging"
        else
            clean_environment $ENVIRONMENT
        fi
        ;;
    "logs")
        if [ "$ENVIRONMENT" = "all" ]; then
            print_error "Cannot show logs for all environments at once"
            print_status "Use: ./manage-docker.sh logs production"
            print_status "Or: ./manage-docker.sh logs staging"
        else
            show_logs $ENVIRONMENT
        fi
        ;;
    "status")
        if [ "$ENVIRONMENT" = "all" ]; then
            show_status "production"
            echo ""
            show_status "staging"
        else
            show_status $ENVIRONMENT
        fi
        ;;
    "monitor")
        if [ "$ENVIRONMENT" = "all" ]; then
            print_error "Cannot monitor all environments at once"
            print_status "Use: ./manage-docker.sh monitor production"
            print_status "Or: ./manage-docker.sh monitor staging"
        else
            monitor_system $ENVIRONMENT
        fi
        ;;
    *)
        print_error "Invalid command: $COMMAND"
        show_usage
        exit 1
        ;;
esac
