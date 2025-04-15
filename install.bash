#!/bin/bash
set -e  # Exit on error
clear

# Set colors for better readability
GREEN="\033[0;32m"
CYAN="\033[0;36m"
YELLOW="\033[1;33m"
RED="\033[0;31m"
PURPLE="\033[0;35m"
NC="\033[0m" # No Color
BOLD="\033[1m"

# Function to handle errors
handle_error() {
    echo -e "\n${RED}┃ ERROR: ${1}${NC}"
    echo -e "${YELLOW}┃ See error details above for troubleshooting.${NC}"
    exit 1
}

# Check if mythicaldash is already installed
INSTALL_FLAG="/opt/mythicaldash/.installed"

if [ -f "$INSTALL_FLAG" ]; then
    echo -e "\r\x1b[31;1m┃\x1b[0;31m mythicaldash is already installed. Exiting...\x1b[0m"
    exit 0
fi

# Display banner
echo -e "\n\x1b[35;1m
 ███▄ ▄███▓▓██   ██▓▄▄▄█████▓ ██░ ██  ██▓ ▄████▄   ▄▄▄       ██▓    
▓██▒▀█▀ ██▒ ▒██  ██▒▓  ██▒ ▓▒▓██░ ██▒▓██▒▒██▀ ▀█  ▒████▄    ▓██▒    
▓██    ▓██░  ▒██ ██░▒ ▓██░ ▒░▒██▀▀██░▒██▒▒▓█    ▄ ▒██  ▀█▄  ▒██░    
▒██    ▒██   ░ ▐██▓░░ ▓██▓ ░ ░▓█ ░██ ░██░▒▓▓▄ ▄██▒░██▄▄▄▄██ ▒██░    
▒██▒   ░██▒  ░ ██▒▓░  ▒██▒ ░ ░▓█▒░██▓░██░▒ ▓███▀ ░ ▓█   ▓██▒░██████▒
░ ▒░   ░  ░   ██▒▒▒   ▒ ░░    ▒ ░░▒░▒░▓  ░ ░▒ ▒  ░ ▒▒   ▓▒█░░ ▒░▓  ░
░  ░      ░ ▓██ ░▒░     ░     ▒ ░▒░ ░ ▒ ░  ░  ▒     ▒   ▒▒ ░░ ░ ▒  ░
░      ░    ▒ ▒ ░░    ░       ░  ░░ ░ ▒ ░░          ░   ▒     ░ ░   
    ░    ░ ░               ░  ░  ░ ░  ░ ░            ░  ░    ░  ░
         ░ ░                          ░                          
         
\x1b[0m"

mkdir -p /opt/mythicaldash

echo -e "
\x1b[35;1m┃  Welcome to mythicaldash
\x1b[35;1m┃\x1b[0m
\x1b[35;1m┃\x1b[0m Thanks for downloading mythicaldash! We're
\x1b[35;1m┃\x1b[0m are so excited to have you here. If you have
\x1b[35;1m┃\x1b[0m any questions or need help, feel free to reach
\x1b[35;1m┃\x1b[0m us at any of the following:
\x1b[35;1m┃\x1b[0m
\x1b[35;1m┃ ☻ \x1b[0msupport@mythical.systems
\x1b[35;1m┃ ☻ \x1b[0mhttps://github.com/mythicalltd/mythicaldash/issues
\x1b[35;1m┃ ☻ \x1b[0mhttps://discord.mythical.systems
"

echo -e "${PURPLE}┃${NC} ${BOLD}Step 1:${NC} Installing dependencies..."
# Install the dependencies with output displayed
apt update || handle_error "Failed to update package lists"
apt install sudo wget curl git zip unzip -y || handle_error "Failed to install dependencies"
echo -e "${GREEN}┃${NC} Dependencies installed successfully!"

echo -e "${PURPLE}┃${NC} ${BOLD}Step 2:${NC} Installing Docker..."
# Install Docker if not installed
if ! [ -x "$(command -v docker)" ]; then
    curl -sSL https://get.docker.com/ | CHANNEL=stable bash || handle_error "Failed to install Docker"
    sudo systemctl enable --now docker || handle_error "Failed to start Docker service"
    echo -e "${GREEN}┃${NC} Docker installed successfully!"
else
    echo -e "${GREEN}┃${NC} Docker is already installed!"
fi

echo -e "${PURPLE}┃${NC} ${BOLD}Step 3:${NC} Installing Docker Compose..."
# Install Docker Compose if not installed
if ! [ -x "$(command -v docker-compose)" ]; then
    apt install docker-compose -y || handle_error "Failed to install Docker Compose"
    echo -e "${GREEN}┃${NC} Docker Compose installed successfully!"
else
    echo -e "${GREEN}┃${NC} Docker Compose is already installed!"
fi

clear
echo -e "\n
\x1b[35;1m┃  Software agreements
\x1b[35;1m┃\x1b[35
\x1b[35;1m┃\x1b[0m By using mythicaldash you (the owner and
\x1b[35;1m┃\x1b[0m ALL your clients) agree to our software
\x1b[35;1m┃\x1b[0m agreements listed on our homepage at: 
\x1b[35;1m┃\x1b[35
\x1b[35;1m┃\x1b[0m https://www.mythical.systems/eula
"
echo -e "${YELLOW}┃${NC} Type 'AGREE' to continue and agree to our software agreements."
echo -e "${YELLOW}┃${NC} Type 'DISAGREE' to exit the installation."
read -p " " AGREEMENT
if [ "$AGREEMENT" != "AGREE" ]; then
    echo -e "\x1b[31;1m┃\x1b[0;31m You must agree to our software agreements to continue.\x1b[0m"
    exit 1
fi

echo -e "${PURPLE}┃${NC} ${BOLD}Step 4:${NC} Downloading MythicalDash files..."
cd /opt/mythicaldash
curl -Lo MythicalDash.zip https://github.com/MythicalLTD/MythicalDash-Nightly/releases/latest/download/MythicalDash.zip || handle_error "Failed to download MythicalDash files"
echo -e "${GREEN}┃${NC} Download completed."

echo -e "${PURPLE}┃${NC} ${BOLD}Step 5:${NC} Extracting files..."
unzip -o MythicalDash.zip || handle_error "Failed to extract files"
echo -e "${GREEN}┃${NC} Extraction completed."

echo -e "${PURPLE}┃${NC} ${BOLD}Step 6:${NC} Creating required directories..."
# Create necessary directories
mkdir -p ./backend/storage/caches
mkdir -p ./backend/storage/logs
mkdir -p ./backend/public/attachments
echo -e "${GREEN}┃${NC} Directories created."

echo -e "${PURPLE}┃${NC} ${BOLD}Step 7:${NC} Preparing Docker environment..."
# Use the docker.env file instead of .env
rm -rf ./backend/storage/.env
cp ./backend/storage/.docker.env ./backend/storage/.env || handle_error "Failed to set up environment file"
echo -e "${GREEN}┃${NC} Docker environment prepared."

echo -e "${PURPLE}┃${NC} ${BOLD}Step 8:${NC} Building Docker containers (this may take 5-10 minutes)..."
echo -e "${YELLOW}┃${NC} Please be patient while the containers are being built..."
# Start the build process with visible output
docker-compose --env-file ./backend/storage/.env up -d --build || handle_error "Docker build failed - see error output above"
echo -e "${GREEN}┃${NC} Docker containers built and started successfully!"

echo -e "${PURPLE}┃${NC} ${BOLD}Step 9:${NC} Setting correct permissions..."
# Set permissions with visible output
chown -R www-data:www-data ./
chmod -R 775 ./backend/storage
chmod -R 775 ./backend/public/attachments
echo -e "${GREEN}┃${NC} Permissions set successfully!"



# Verify container is running before proceeding
if [ "$(docker ps -q -f name=mythicaldash_backend)" ]; then
    echo -e "${PURPLE}┃${NC} ${BOLD}Step 10:${NC} Updating internal packages..."
    # Update dependencies with visible output
    docker exec mythicaldash_backend bash -c "COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader" || handle_error "Failed to update internal packages"
    echo -e "${GREEN}┃${NC} Internal packages updated successfully!"

    # Check if the installation has already been completed
    INSTALL_FLAG=".installed"

    if [ ! -f "$INSTALL_FLAG" ]; then
        # Run the installation steps
        touch "$INSTALL_FLAG"
        echo -e "${PURPLE}┃${NC} ${BOLD}Step 11:${NC} Generating encryption keys..."
        docker exec mythicaldash_backend bash -c "php mythicaldash keyRegen -force" || handle_error "Failed to generate encryption keys"
        echo -e "${GREEN}┃${NC} Encryption keys generated successfully!"
    else
        echo -e "${YELLOW}┃${NC} MythicalDash already installed!"
    fi
else
    handle_error "Backend container isn't running. Check Docker logs for more information."
fi

echo -e "${PURPLE}┃${NC} ${BOLD}Step 12:${NC} Waiting for database to be ready..."
# Wait for the database container to be ready with a progress indicator
MAX_RETRIES=30
RETRY_COUNT=0

while [ "$(docker inspect -f '{{.State.Health.Status}}' mythicaldash_database 2>/dev/null)" != "healthy" ]; do
    echo -e "${YELLOW}┃${NC} Waiting for MySQL database to be ready... (this may take a minute)"
    sleep 5
    RETRY_COUNT=$((RETRY_COUNT+1))
    
    if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
        handle_error "Database container failed to become healthy after $MAX_RETRIES retries"
    fi
done
echo -e "${GREEN}┃${NC} MySQL database is ready!"

echo -e "${PURPLE}┃${NC} ${BOLD}Step 13:${NC} Waiting for Redis to be ready..."
# Wait for Redis to be ready
RETRY_COUNT=0

while [ "$(docker inspect -f '{{.State.Health.Status}}' mythicaldash_redis 2>/dev/null)" != "healthy" ]; do
    echo -e "${YELLOW}┃${NC} Waiting for Redis to be ready... (this may take a minute)"
    sleep 5
    RETRY_COUNT=$((RETRY_COUNT+1))
    
    if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
        handle_error "Redis container failed to become healthy after $MAX_RETRIES retries"
    fi
done
echo -e "${GREEN}┃${NC} Redis is ready!"

echo -e "${PURPLE}┃${NC} ${BOLD}Step 14:${NC} Running database migrations..."
# Run migrations with visible output
docker exec mythicaldash_backend bash -c "php mythicaldash migrate" || handle_error "Failed to run database migrations"
echo -e "${GREEN}┃${NC} Database migrations completed successfully!"

echo -e "${PURPLE}┃${NC} ${BOLD}Step 15:${NC} Cleaning up installation files..."
# Clean up installation files
rm -rf /opt/mythicaldash/MythicalDash.zip
echo -e "${GREEN}┃${NC} Cleanup completed!"

# Get server IP address
SERVER_IP=$(hostname -I | awk '{print $1}')

# Installation completed message
echo -e "\n${GREEN}┃ MythicalDash installation completed successfully! ${NC}"
echo -e "${GREEN}┃${NC}"
echo -e "${GREEN}┃ You can now start using MythicalDash. ${NC}"
echo -e "${GREEN}┃${NC}"
echo -e "${GREEN}┃ For more information, visit: ${NC}"
echo -e "${GREEN}┃${NC} https://mythicaldash-v3.mythical.systems"
echo -e "${GREEN}┃${NC}"
echo -e "${GREEN}┃ Thank you for choosing MythicalDash! ${NC}"
echo -e "${GREEN}┃${NC}"
echo -e "${CYAN}┃ You can access it at: ${GREEN}http://${SERVER_IP}:9271 ${NC}"
echo -e "${CYAN}┃ Working directory: ${GREEN}/opt/mythicaldash ${NC}"
echo -e "${CYAN}┃ Channel: ${GREEN}develop (NO SUPPORT) ${NC}"
echo -e "${CYAN}┃ License: ${GREEN}free (NO SUPPORT) ${NC}"
echo -e "${GREEN}┃${NC}"
echo -e "${GREEN}┃ Make sure you read our docs on how to use a domain and SSL. ${NC}"
echo -e "${GREEN}┃ We recommend you use Cloudflare Tunnels for this installation.${NC}"
echo -e "${NC}"