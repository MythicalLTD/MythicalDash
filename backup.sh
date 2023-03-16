mysqldump dash > dash.sql
cd /var/www
zip -r client.zip client/
curl --upload-file ./client.zip https://transfer.sh/client.zip -H "Max-Downloads: 2"
rm client.zip
cd /var/www/client
rm dash.sql
