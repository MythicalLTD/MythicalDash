#!/bin/bash

cd /var/www/mythicaldash
mariadb-dump -p mythicaldash > mythicaldash_backup.sql
cd /var/www
file_name="clientbackup.zip"

if [ -e "$file_name" ]; then
    rm "$file_name"
    echo "$file_name deleted successfully."
else
    echo "$file_name does not exist."
fi
zip -r clientbackup.zip mythicaldash/

cd /var/www/mythicaldash
curl -Lo MythicalDash.zip https://github.com/mythicalltd/mythicaldash/releases/latest/download/MythicalDash.zip
unzip -o MythicalDash.zip -d /var/www/mythicaldash
dos2unix arch.bash
sudo bash arch.bash
rm /var/www/mythicaldash/public/FIRST_USER
composer install --no-dev --optimize-autoloader
./MythicalDash -migrate
chown -R www-data:www-data /var/www/mythicaldash/*