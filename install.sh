# Update the server
apt update && apt upgrade -y
# Add "add-apt-repository" command
apt -y install software-properties-common curl apt-transport-https ca-certificates gnupg
# Add additional repositories for PHP, Redis, and MariaDB
LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
# MariaDB repo setup script can be skipped on Ubuntu 22.04
curl -sS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | sudo bash
# Update repositories list
apt update
# Add universe repository if you are on Ubuntu 18.04
apt-add-repository universe
# Install Dependencies
apt -y install php8.2 php8.2-{common,cli,gd,mysql,mbstring,bcmath,xml,fpm,curl,zip} mariadb-server nginx tar unzip zip git redis-server dos2unix
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer 

mkdir -p /var/www/mythicaldash
cd /var/www/mythicaldash
curl -Lo MythicalDash.zip https://github.com/mythicalltd/mythicaldash/releases/latest/download/MythicalDash.zip
unzip -o MythicalDash.zip -d /var/www/mythicaldash


chown -R www-data:www-data /var/www/mythicaldash/*

cd /var/www/mythicaldash
composer install --no-dev --optimize-autoloader

echo # YOU NEED TO SETUP THE DB USING THE COMMANDS BELLOW.
echo "CREATE USER 'mythicaldash'@'127.0.0.1' IDENTIFIED BY 'yourPassword';"
echo "CREATE DATABASE mythicaldash;"
echo "GRANT ALL PRIVILEGES ON mythicaldash.* TO 'mythicaldash'@'127.0.0.1' WITH GRANT OPTION;"
echo "exit"

mysql -u root -p
echo "You Finished The DB Setup"

# Run this for our small checkup that we need to run for the cli to run
cd /var/www/mythicaldash
dos2unix arch.bash
bash arch.bash
chmod +x ./MythicalDash
./MythicalDash -environment:newconfig # Generate a custom config file
./MythicalDash -key:generate # Reset the encryption key
./MythicalDash -environment:database # Setup the database connection
./MythicalDash -migrate # Migrate the database
./MythicalDash -environment:setup # Start a custom setup for the dash