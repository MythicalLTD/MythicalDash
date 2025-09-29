#!/bin/bash
# MythicalDash Installation Script
# Version 2.0.1
# Supports Ubuntu, Ubuntu Server, and Debian
echo "Script By _webdevkin && MythicalLTD By Cassian"
# Function to check if a package is installed and install it if not
install_packages() {
    packages_to_install=()
    for pkg in "$@"; do
        if dpkg -s "$pkg" >/dev/null 2>&1;
        then
            echo "$pkg is already installed. Skipping..."
        else
            packages_to_install+=("$pkg")
        fi
    done

    if [ ${#packages_to_install[@]} -gt 0 ]; then
        echo "Installing packages: ${packages_to_install[@]}"
        sudo apt-get -qq install -y "${packages_to_install[@]}"
    fi
}

# Function to uninstall Docker-based installation
uninstall_docker() {
    echo "Uninstalling MythicalDash (Docker)..."
    uninstall_cloudflare_tunnel
    # Stop and remove Docker containers
    if [ -f /var/www/mythicaldash-v3/docker-compose.yml ]; then
        echo "Stopping and removing Docker containers..."
        (cd /var/www/mythicaldash-v3 && sudo docker compose down -v)
    fi

    # Remove MythicalDash files
    echo "Removing MythicalDash files..."
    sudo rm -rf /var/www/mythicaldash-v3

    echo "Docker-based uninstallation complete."
}

# Function to uninstall without-Docker installation
uninstall_no_docker() {
    echo "Uninstalling MythicalDash (without-Docker)..."
    uninstall_cloudflare_tunnel
    # Remove MariaDB database and user
    echo "Removing MariaDB database and user..."
    sudo mysql -e "DROP DATABASE IF EXISTS mythicaldash_remastered;"
    sudo mysql -e "DROP USER IF EXISTS 'mythicaldash_remastered'@'127.0.0.1';"

    # Stop services
    echo "Stopping services..."
    sudo systemctl stop redis-server
    sudo systemctl stop mariadb

    # Remove MythicalDash files
    echo "Removing MythicalDash files..."
    sudo rm -rf /var/www/mythicaldash-v3

    # Remove cron jobs
    (crontab -l | grep -v -e '/var/www/mythicaldash-v3/') | crontab -
    
    echo "Without-Docker based uninstallation complete."
}

uninstall_cloudflare_tunnel() {
    echo "Uninstalling Cloudflare Tunnel..."
    if [ -f /var/www/mythicaldash-v3/.cf_creds ]; then
        . /var/www/mythicaldash-v3/.cf_creds

        if [ -n "$TUNNEL_ID" ] && [ -n "$ACCOUNT_ID" ] && [ -n "$ZONE_ID" ] && [ -n "$CF_HOSTNAME" ]; then
            echo "Deleting DNS record for $CF_HOSTNAME..."
            # Get DNS record ID
            DNS_RECORD_ID=$(curl -s -X GET "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/dns_records?type=CNAME&name=$CF_HOSTNAME" \
                 -H "X-Auth-Email: $CF_EMAIL" \
                 -H "X-Auth-Key: $CF_API_KEY" \
                 -H "Content-Type: application/json" | jq -r '.result[0].id')

            if [ -n "$DNS_RECORD_ID" ] && [ "$DNS_RECORD_ID" != "null" ]; then
                curl -s -X DELETE "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/dns_records/$DNS_RECORD_ID" \
                     -H "X-Auth-Email: $CF_EMAIL" \
                     -H "X-Auth-Key: $CF_API_KEY" \
                     -H "Content-Type: application/json" > /dev/null
                echo "DNS record deleted."
            else
                echo "Could not find DNS record for $CF_HOSTNAME or already deleted."
            fi

            echo "Deleting Cloudflare Tunnel..."
            curl -s -X DELETE "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel/$TUNNEL_ID" \
                 -H "X-Auth-Email: $CF_EMAIL" \
                 -H "X-Auth-Key: $CF_API_KEY" \
                 -H "Content-Type: application/json" > /dev/null
            echo "Cloudflare Tunnel deleted."
        else
            echo "Cloudflare Tunnel credentials not found or incomplete. Skipping tunnel deletion."
        fi
        sudo rm /var/www/mythicaldash-v3/.cf_creds
    else
        echo "Cloudflare Tunnel credentials file not found. Skipping tunnel deletion."
    fi
}

setup_cloudflare_tunnel_full_auto() {
    echo "Starting full-automatic Cloudflare Tunnel setup..."
    install_packages jq

    ACCOUNTS_DATA=$(curl -s -X GET "https://api.cloudflare.com/client/v4/accounts" \
         -H "X-Auth-Email: $CF_EMAIL" \
         -H "X-Auth-Key: $CF_API_KEY" \
         -H "Content-Type: application/json")

    ACCOUNT_COUNT=$(echo $ACCOUNTS_DATA | jq -r '.result | length')

    if [ "$ACCOUNT_COUNT" == "0" ]; then
        echo "Error: No Cloudflare accounts found. Please check your email and API key."
        return 1
    elif [ "$ACCOUNT_COUNT" -gt "1" ]; then
        echo "Multiple Cloudflare accounts found. Please choose one:"
        echo $ACCOUNTS_DATA | jq -r '.result[] | "\(.id) \(.name)"' | nl
        read -p "Enter the number of the account you want to use: " ACCOUNT_CHOICE
        ACCOUNT_ID=$(echo $ACCOUNTS_DATA | jq -r ".result[$((ACCOUNT_CHOICE-1))].id")
    else
        ACCOUNT_ID=$(echo $ACCOUNTS_DATA | jq -r '.result[0].id')
    fi

    if [ "$ACCOUNT_ID" == "null" ] || [ -z "$ACCOUNT_ID" ]; then
        echo "Error: Could not get Cloudflare Account ID. Please check your email and API key."
        return 1
    fi

    TUNNEL_ID=$(curl -s -X GET "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel?name=Mythical-Dash" \
         -H "X-Auth-Email: $CF_EMAIL" \
         -H "X-Auth-Key: $CF_API_KEY" \
         -H "Content-Type: application/json" | jq -r '.result[0].id')

    if [ "$TUNNEL_ID" == "null" ] || [ -z "$TUNNEL_ID" ]; then
        echo "Creating Cloudflare Tunnel 'Mythical-Dash'..."
        TUNNEL_CREATE_DATA=$(curl -s -X POST "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel" \
             -H "X-Auth-Email: $CF_EMAIL" \
             -H "X-Auth-Key: $CF_API_KEY" \
             -H "Content-Type: application/json" \
             --data '{"name":"Mythical-Dash"}')
        TUNNEL_ID=$(echo $TUNNEL_CREATE_DATA | jq -r '.result.id')
        if [ "$TUNNEL_ID" == "null" ] || [ -z "$TUNNEL_ID" ]; then
            echo "Error: Could not create Cloudflare Tunnel."
            echo "API Response: $TUNNEL_CREATE_DATA"
            return 1
        fi
    fi

    echo "Using Tunnel ID: $TUNNEL_ID"

    CF_TUNNEL_TOKEN=$(curl -s -X GET "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel/$TUNNEL_ID/token" \
         -H "X-Auth-Email: $CF_EMAIL" \
         -H "X-Auth-Key: $CF_API_KEY" \
         -H "Content-Type: application/json" | jq -r '.result')

    if [ "$CF_TUNNEL_TOKEN" == "null" ] || [ -z "$CF_TUNNEL_TOKEN" ]; then
        echo "Error: Could not get Cloudflare Tunnel token. This might be due to API limitations."
        echo "Please try the semi-automatic mode."
        return 1
    fi

    ZONE_NAME=$(echo $CF_HOSTNAME | awk -F. '{print $(NF-1)"."$NF}')
    ZONE_ID=$(curl -s -X GET "https://api.cloudflare.com/client/v4/zones?name=$ZONE_NAME" \
         -H "X-Auth-Email: $CF_EMAIL" \
         -H "X-Auth-Key: $CF_API_KEY" \
         -H "Content-Type: application/json" | jq -r '.result[0].id')

    if [ "$ZONE_ID" == "null" ] || [ -z "$ZONE_ID" ]; then
        echo "Error: Could not get Cloudflare Zone ID for domain '$ZONE_NAME'."
        return 1
    fi

    echo "Configuring DNS and ingress rules..."
    curl -s -X POST "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/dns_records" \
         -H "X-Auth-Email: $CF_EMAIL" \
         -H "X-Auth-Key: $CF_API_KEY" \
         -H "Content-Type: application/json" \
         --data '{"type":"CNAME","name":"'$CF_HOSTNAME'","content":"'$TUNNEL_ID'.cfargotunnel.com","proxied":true}' > /dev/null

    curl -s -X PUT "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel/$TUNNEL_ID/configurations" \
         -H "X-Auth-Email: $CF_EMAIL" \
         -H "X-Auth-Key: $CF_API_KEY" \
         -H "Content-Type: application/json" \
         --data '{"config":{"ingress":[{"hostname":"'$CF_HOSTNAME'","service":"http://localhost:4830"},{"service":"http_status:404"}]}}' > /dev/null

    echo "Full-automatic Cloudflare Tunnel setup complete."

    # Save Cloudflare credentials for uninstallation
    echo "CF_EMAIL=\"$CF_EMAIL\"" > /var/www/mythicaldash-v3/.cf_creds
    echo "CF_API_KEY=\"$CF_API_KEY\"" >> /var/www/mythicaldash-v3/.cf_creds
    echo "ACCOUNT_ID=\"$ACCOUNT_ID\"" >> /var/www/mythicaldash-v3/.cf_creds
    echo "TUNNEL_ID=\"$TUNNEL_ID\"" >> /var/www/mythicaldash-v3/.cf_creds
    echo "ZONE_ID=\"$ZONE_ID\"" >> /var/www/mythicaldash-v3/.cf_creds
    echo "CF_HOSTNAME=\"$CF_HOSTNAME\"" >> /var/www/mythicaldash-v3/.cf_creds
    sudo chmod 600 /var/www/mythicaldash-v3/.cf_creds
}

setup_cloudflare_tunnel_client() {
    if [ -n "$CF_TUNNEL_TOKEN" ]; then
        echo "Setting up Cloudflare Tunnel..."
        if [ "$INST_TYPE" == "0" ]; then # Only use Docker for Docker installation
            if command -v docker &> /dev/null
            then
                echo "Docker is already installed."
            else
                echo "Docker not found, installing Docker..."
                # Quick install using recommended method
                curl -sSL https://get.docker.com/ | CHANNEL=stable bash

                # Start and enable Docker on boot
                sudo systemctl enable --now docker

                # Add your user to docker group (optional, allows running docker without sudo)
                sudo usermod -aG docker $USER
                echo "Docker installation complete. Please log out and log back in for group changes to take effect."
            fi
            docker run -d --network host --restart always cloudflare/cloudflared:latest tunnel --no-autoupdate run --token "$CF_TUNNEL_TOKEN"
        else # Install cloudflared directly for non-Docker installation
            echo "Installing cloudflared..."
            curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
            sudo dpkg -i cloudflared.deb
            sudo cloudflared service install "$CF_TUNNEL_TOKEN"
            sudo systemctl enable --now cloudflared
            rm cloudflared.deb
            echo "cloudflared installed and running."
        fi
        echo "Cloudflare Tunnel setup complete."
        if [ "$CF_TUNNEL_MODE" == "2" ]; then
            echo -e "\033[0;33mYou have chosen Semi-Automatic Cloudflare Tunnel setup.\033[0m"
            echo -e "\033[0;33mPlease manually create a DNS record for your hostname pointing to the tunnel in your Cloudflare dashboard.\033[0m"
            echo -e "\033[0;33mThe ingress rule should point to http://localhost:4830.\033[0m"
            echo -e "\033[0;33mMore information: https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/get-started/create-remote-tunnel-api/\033[0m"
        fi
    else
        echo "Skipping Cloudflare Tunnel setup as no token was provided or generated."
    fi
}

if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
    if [ "$OS" = "ubuntu" ] || [ "$OS" = "ubuntu-server" ] || [ "$OS" = "debian" ]; then
        echo "Supported OS: $OS"
        
        # Ask all questions upfront
        INST_TYPE=""
        while [[ ! "$INST_TYPE" =~ ^[0-3]$ ]]; do
            echo "Please choose an option:"
            echo "0: Install with Docker"
            echo "1: Install without Docker"
            echo "2: Uninstall Docker installation"
            echo "3: Uninstall without-Docker installation"
            read -p "Option (0/1/2/3): " INST_TYPE
            if [[ ! "$INST_TYPE" =~ ^[0-3]$ ]]; then
                echo "Invalid input. Please enter a number between 0 and 3."
            fi
        done

        reinstall="n"
PTERO_CONFIGURE=""
PTERO_URL=""
PTERO_API_KEY=""
ADMIN_EMAIL=""
ADMIN_USERNAME=""
ADMIN_FIRST_NAME=""
ADMIN_LAST_NAME=""
ADMIN_PASSWORD=""
confirm="n"
CF_TUNNEL_SETUP=""
CF_TUNNEL_TOKEN=""
CF_TUNNEL_MODE=""
CF_API_KEY=""
CF_EMAIL=""
CF_HOSTNAME=""

        if [[ "$INST_TYPE" == "0" || "$INST_TYPE" == "1" ]]; then
            if [ -f /var/www/mythicaldash-v3/.installed ]; then
                read -p "MythicalDash appears to be already installed. Do you want to reinstall? (y/n): " reinstall
                if [ "$reinstall" != "y" ]; then
                    echo "Exiting installation."
                    exit 0
                fi
            fi

            while [[ ! "$CF_TUNNEL_SETUP" =~ ^[ynYN]$ ]]; do
                read -p "Do you want to set up Cloudflare Tunnel? (y/n): " CF_TUNNEL_SETUP
                if [[ ! "$CF_TUNNEL_SETUP" =~ ^[ynYN]$ ]]; then
                    echo "Invalid input. Please enter 'y' or 'n'."
                fi
            done

            if [[ "$CF_TUNNEL_SETUP" =~ ^[yY]$ ]]; then
                echo "Choose a Cloudflare Tunnel setup mode:"
                echo "1: Full Automatic (needs Cloudflare API key, creates tunnel and DNS records)"
                echo "2: Semi-Automatic (you provide a tunnel token)"
                while [[ ! "$CF_TUNNEL_MODE" =~ ^[12]$ ]]; do
                    read -p "Mode (1/2): " CF_TUNNEL_MODE
                    if [[ ! "$CF_TUNNEL_MODE" =~ ^[12]$ ]]; then
                        echo "Invalid input. Please enter 1 or 2."
                    fi
                done

                if [ "$CF_TUNNEL_MODE" == "1" ]; then
                    echo "Entering Full Automatic setup for Cloudflare Tunnel."
                    while [ -z "$CF_EMAIL" ]; do
                        read -p "Enter your Cloudflare email: " CF_EMAIL
                    done
                    while [ -z "$CF_API_KEY" ]; do
                        read -p "Enter your Cloudflare Global API Key: " CF_API_KEY
                    done
                    while [ -z "$CF_HOSTNAME" ]; do
                        read -p "Enter the hostname for MythicalDash (e.g., dash.example.com): " CF_HOSTNAME
                    done
                else
                    echo "Entering Semi-Automatic setup for Cloudflare Tunnel."
                    while [ -z "$CF_TUNNEL_TOKEN" ]; do
                        read -p "Enter your Cloudflare Tunnel token: " CF_TUNNEL_TOKEN
                    done
                fi
            else
                echo -e "\033[0;33mYou have chosen not to use Cloudflare Tunnel. You will need to set up a web server like Nginx manually to access MythicalDash. More information can be found here: https://docs.mythical.systems/docs/mythicaldash-v3-remastered/create_ssl\033[0m"
            fi

            
            while [[ ! "$PTERO_CONFIGURE" =~ ^[ynYN]$ ]]; do
                read -p "Do you want to configure the Pterodactyl panel settings now? (y/n): " PTERO_CONFIGURE
                if [[ ! "$PTERO_CONFIGURE" =~ ^[ynYN]$ ]]; then
                    echo "Invalid input. Please enter 'y' or 'n'."
                fi
            done

            if [[ "$PTERO_CONFIGURE" =~ ^[yY]$ ]]; then
                while [ -z "$PTERO_URL" ]; do
                    read -p "Enter your Pterodactyl panel URL (e.g., panel.example.com): " PTERO_URL
                    if [ -z "$PTERO_URL" ]; then
                        echo "Pterodactyl panel URL cannot be empty."
                    fi
                done
                while [ -z "$PTERO_API_KEY" ]; do
                    read -p "Enter your Pterodactyl panel API key: " PTERO_API_KEY
                    if [ -z "$PTERO_API_KEY" ]; then
                        echo "Pterodactyl panel API key cannot be empty."
                    fi
                done
            fi

            if [ "$INST_TYPE" == "1" ]; then
                echo "Let's create an admin user."
                while [ -z "$ADMIN_EMAIL" ]; do
                    read -p "Enter admin email : " ADMIN_EMAIL
                done

                while [ -z "$ADMIN_USERNAME" ]; do
                    read -p "Enter admin username : " ADMIN_USERNAME
                done
                while [ -z "$ADMIN_FIRST_NAME" ]; do
                    read -p "Enter admin first name : " ADMIN_FIRST_NAME
                done
                while [ -z "$ADMIN_LAST_NAME" ]; do
                    read -p "Enter admin last name : " ADMIN_LAST_NAME
                done
                while [ -z "$ADMIN_PASSWORD" ]; do
                    read -s -p "Enter admin password : " ADMIN_PASSWORD
                    echo
                done
            fi
        elif [[ "$INST_TYPE" == "2" ]]; then
            read -p "Are you sure you want to uninstall the Docker-based installation? (y/n): " confirm
        elif [[ "$INST_TYPE" == "3" ]]; then
            read -p "Are you sure you want to uninstall the without-Docker installation? (y/n): " confirm
        fi

        case $INST_TYPE in
            0)
                install_packages curl unzip jq
                if [ "$reinstall" = "y" ]; then
                    echo "Proceeding with reinstallation..."
                    sudo rm /var/www/mythicaldash-v3/.installed
                fi
                
                echo "Proceeding with Docker installation."
                if command -v docker &> /dev/null
                then
                    echo "Docker is already installed."
                else
                    echo "Docker not found, installing Docker..."
                    # Quick install using recommended method
                    curl -sSL https://get.docker.com/ | CHANNEL=stable bash

                    # Start and enable Docker on boot
                    sudo systemctl enable --now docker

                    # Add your user to docker group (optional, allows running docker without sudo)
                    sudo usermod -aG docker $USER
                    echo "Docker installation complete. Please log out and log back in for group changes to take effect."
                fi
                
                echo "Setting up MythicalDash-v3..."
                sudo mkdir -p /var/www/mythicaldash-v3
                cd /var/www/mythicaldash-v3
                sudo curl -Lo MythicalDash.zip https://github.com/MythicalLTD/MythicalDash/releases/latest/download/MythicalDash.zip
                sudo unzip -o MythicalDash.zip -d /var/www/mythicaldash-v3
                cd /var/www/mythicaldash-v3
                sudo docker compose up -d
                echo "MythicalDash-v3 setup complete."
                if [[ "$PTERO_CONFIGURE" =~ ^[yY]$ ]]; then
                    echo "Configuring Pterodactyl settings..."
                    echo "Waiting for backend container to start... (10 seconds)"
                    sleep 10
                    printf "y\n%s\n%s\ny\n" "$PTERO_URL" "$PTERO_API_KEY" | sudo docker exec -i mythicaldash_v3_backend php cli pterodactyl configure
                    echo "Pterodactyl configuration complete."
                else
                    echo "Configure your MythicalDash via running sudo docker exec -it mythicaldash_v3_backend php cli pterodactyl configure"
                fi
                if [[ "$CF_TUNNEL_SETUP" =~ ^[yY]$ ]]; then
                    if [ "$CF_TUNNEL_MODE" == "1" ]; then
                        setup_cloudflare_tunnel_full_auto
                        if [ $? -ne 0 ]; then
                            CF_TUNNEL_TOKEN=""
                        fi
                    fi
                    setup_cloudflare_tunnel_client
                fi

                sudo touch /var/www/mythicaldash-v3/.installed
                ;;
            1)
                if [ "$reinstall" = "y" ]; then
                    echo "Proceeding with reinstallation..."
                    sudo rm /var/www/mythicaldash-v3/.installed
                fi
                
                echo "Proceeding with installation without Docker."
                if [ "$OS" = "ubuntu" ] || [ "$OS" = "ubuntu-server" ]; then
                    echo "Installing dependencies for Ubuntu/Ubuntu Server..."
                    # Set DEBIAN_FRONTEND to noninteractive
                    export DEBIAN_FRONTEND=noninteractive

                    # Update the server
                    sudo apt -qq update && sudo apt -qq upgrade -y

                    # Install essential tools for repository management and other operations
                    install_packages software-properties-common curl apt-transport-https ca-certificates gnupg jq make unzip tar git zip redis-server dos2unix

                    # Re-run apt update after installing essential tools
                    sudo apt -qq update

                    # Add additional repositories for PHP, Redis, and MariaDB
                    LC_ALL=C.UTF-8 sudo add-apt-repository -y ppa:ondrej/php
                    # MariaDB repo setup script can be skipped on Ubuntu 22.04
                    curl -sS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | sudo bash
                    # Update repositories list
                    sudo apt -qq update
                    # Add universe repository if you are on Ubuntu 18.04
                    sudo apt-add-repository -y universe
                    # Install Dependencies
                    install_packages mariadb-server mariadb-client
                    echo "Ubuntu/Ubuntu Server dependencies installed."
                elif [ "$OS" = "debian" ]; then
                    echo "Installing dependencies for Debian..."
                    # Set DEBIAN_FRONTEND to noninteractive
                    export DEBIAN_FRONTEND=noninteractive

                    # Update the server
                    sudo apt -qq update && sudo apt -qq upgrade -y

                    # Install ALL essential tools and dependencies upfront
                    install_packages curl lsb-release gnupg2 software-properties-common make jq unzip tar git zip redis-server dos2unix \
                                     php8.3 php8.3-common php8.3-cli php8.3-gd php8.3-mysql php8.3-mbstring php8.3-bcmath php8.3-xml php8.3-fpm php8.3-curl php8.3-zip php8.3-redis \
                                     mariadb-server mariadb-client apt-transport-https

                    # Re-run apt update after installing essential tools (especially software-properties-common and lsb-release)
                    sudo apt -qq update

                    # Capture lsb_release output into a variable
                    DEBIAN_CODENAME=$(lsb_release -sc)

                    # Add additional repositories for PHP
                    echo "deb https://packages.sury.org/php/ $DEBIAN_CODENAME main" | sudo tee /etc/apt/sources.list.d/sury-php.list
                    curl -fsSL https://packages.sury.org/php/apt.gpg | sudo gpg --dearmor -o /etc/apt/trusted.gpg.d/sury-keyring.gpg

                    # Update repositories list again
                    sudo apt -qq update

                    # MariaDB repo setup script
                    curl -sS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | sudo bash

                    # Update repositories list again after MariaDB repo
                    sudo apt -qq update

                    echo "Debian dependencies installed."
                else
                    echo "Unsupported OS for without Docker installation: $OS"
                    exit 1
                fi

                if ! command -v composer &> /dev/null; then
                    echo "Installing Composer..."
                    curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
                    echo "Composer installed."
                else
                    echo "Composer is already installed."
                fi

                echo "Setting up MythicalDash-v3..."
                sudo mkdir -p /var/www/mythicaldash-v3
                cd /var/www/mythicaldash-v3
                sudo curl -Lo MythicalDash.zip https://github.com/MythicalLTD/MythicalDash/releases/latest/download/MythicalDash.zip
                sudo unzip -o MythicalDash.zip -d /var/www/mythicaldash-v3
                # Find the actual extracted directory (e.g., MythicalDash-main)
                EXTRACTED_DIR=$(sudo find /var/www/mythicaldash-v3 -maxdepth 1 -mindepth 1 -type d -name "MythicalDash-*" -print -quit)
                if [ -n "$EXTRACTED_DIR" ]; then
                    sudo mv "$EXTRACTED_DIR"/* /var/www/mythicaldash-v3/
                    sudo rm -r "$EXTRACTED_DIR"
                fi
                sudo chown -R www-data:www-data /var/www/mythicaldash-v3/*
                echo "MythicalDash-v3 downloaded and extracted."

                echo "Installing backend dependencies with Composer..."
                cd /var/www/mythicaldash-v3/backend
                COMPOSER_ALLOW_SUPERUSER=1 sudo composer install --no-dev --optimize-autoloader 
                echo "Backend dependencies installed."

                echo "Starting MariaDB service..."
                sudo systemctl enable --now mariadb
                echo "MariaDB service started."

                echo "Configuring MariaDB..."
                # Ensure MariaDB config file exists after installation
                if [ ! -f /etc/mysql/mariadb.conf.d/50-server.cnf ]; then
                    echo "Error: MariaDB configuration file /etc/mysql/mariadb.conf.d/50-server.cnf not found after installation. Aborting."
                    exit 1
                fi
                sudo sed -i '/^#collation-server/a collation-server = utf8mb4_general_ci' /etc/mysql/mariadb.conf.d/50-server.cnf
                sudo sed -i '/^character-set-server/s/^/#/g' /etc/mysql/mariadb.conf.d/50-server.cnf
                sudo sed -i '/^#character-set-server/a character-set-server = utf8mb4' /etc/mysql/mariadb.conf.d/50-server.cnf
                sudo sed -i '/^character-set-collations/s/^/#/g' /etc/mysql/mariadb.conf.d/50-server.cnf
                sudo sed -i '/^#character-set-collations/a character-set-collations = utf8mb4' /etc/mysql/mariadb.conf.d/50-server.cnf
                echo "MariaDB configured for utf8mb4."
                sudo systemctl restart mariadb

                echo "Creating MariaDB user and database..."
                # Ensure mysql client is available after installation
                if ! command -v mysql &> /dev/null; then
                    echo "Error: MySQL client not found after installation. Aborting."
                    exit 1
                fi
                DB_PASSWORD=$(head /dev/urandom | tr -dc A-Za-z0-9_ | head -c 16)
                sudo mysql -e "CREATE USER IF NOT EXISTS 'mythicaldash_remastered'@'127.0.0.1';"
                sudo mysql -e "ALTER USER 'mythicaldash_remastered'@'127.0.0.1' IDENTIFIED BY '$DB_PASSWORD';"
                sudo mysql -e "CREATE DATABASE IF NOT EXISTS mythicaldash_remastered;"
                sudo mysql -e "GRANT ALL PRIVILEGES ON mythicaldash_remastered.* TO 'mythicaldash_remastered'@'127.0.0.1' WITH GRANT OPTION;"
                echo "MariaDB user 'mythicaldash_remastered' created/updated with password: $DB_PASSWORD"
                echo "MariaDB database 'mythicaldash_remastered' created/ensured."

                echo "Setting application to production mode..."
                cd /var/www/mythicaldash-v3
                # Ensure make is available after installation
                if ! command -v make &> /dev/null; then
                    echo "Error: make command not found after installation. Aborting."
                    exit 1
                fi
                sudo make set-prod
                echo "Application set to production mode."

                echo "Adding/Updating cron jobs..."
                { crontab -l 2>/dev/null | grep -v -F "/var/www/mythicaldash-v3/backend/storage/cron/runner.bash"; \
                  crontab -l 2>/dev/null | grep -v -F "/var/www/mythicaldash-v3/backend/storage/cron/runner.php"; \
                  echo "* * * * * bash /var/www/mythicaldash-v3/backend/storage/cron/runner.bash >> /dev/null 2>&1"; \
                  echo "* * * * * php /var/www/mythicaldash-v3/backend/storage/cron/runner.php >> /dev/null 2>&1"; } | crontab -
                echo "Cron jobs added/updated."
                sudo chown -R www-data:www-data /var/www/mythicaldash-v3/*
                
                # Automate post-installation setup
                cd /var/www/mythicaldash-v3/

                # Setup database
                # Ensure php is available after installation
                if ! command -v php &> /dev/null; then
                    echo "Error: PHP command not found after installation. Aborting."
                    exit 1
                fi
                printf "xchacha20\nmythicaldash_remastered\n127.0.0.1\n3306\nmythicaldash_remastered\n%s\n" "$DB_PASSWORD" | sudo -u www-data php mythicaldash setup

                # Migrate database
                sudo -u www-data php mythicaldash migrate

                # Configure Pterodactyl
                if [[ "$PTERO_CONFIGURE" =~ ^[yY]$ ]]; then
                    printf "y\n%s\n%s\ny\n" "$PTERO_URL" "$PTERO_API_KEY" | sudo -u www-data php mythicaldash pterodactyl configure
                fi

                if [[ "$CF_TUNNEL_SETUP" =~ ^[yY]$ ]]; then
                    if [ "$CF_TUNNEL_MODE" == "1" ]; then
                        setup_cloudflare_tunnel_full_auto
                        if [ $? -ne 0 ]; then
                            CF_TUNNEL_TOKEN=""
                        fi
                    fi
                    setup_cloudflare_tunnel_client
                fi

                sudo touch /var/www/mythicaldash-v3/.installed
                ;;
            2) # Uninstall Docker
                if [ "$confirm" = "y" ]; then
                    uninstall_docker
                else
                    echo "Uninstallation cancelled."
                    exit 0
                fi
                ;;
            3) # Uninstall without Docker
                if [ "$confirm" = "y" ]; then
                    uninstall_no_docker
                else
                    echo "Uninstallation cancelled."
                    exit 0
                fi
                ;;
            *)
                echo "Invalid installation type."
                exit 1
                ;;
        esac
    else
        echo "Unsupported OS: $OS"
        exit 1
    fi
else
    echo "Cannot determine OS"
    exit 1
fi
