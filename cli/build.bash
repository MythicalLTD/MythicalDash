#!/bin/bash

runtimes=("linux-x64" "linux-x86" "linux-arm64" "linux-arm")
rm -r bin/Release
rm -r obj/
rm /var/www/mythicaldash/MythicalDashARM32
rm /var/www/mythicaldash/MythicalDashARM64
rm /var/www/mythicaldash/MythicalDash64

for runtime in "${runtimes[@]}"; do
    echo "Publishing for runtime: $runtime"
    dotnet clean
    dotnet restore
    dotnet publish -c Release -r "$runtime" --self-contained true /p:PublishSingleFile=true -p:Version=1.0.0.1 
    echo "----------------------------------"
done
mv /var/www/mythicaldash/cli/bin/Release/net7.0/linux-arm/publish/MythicalDash /var/www/mythicaldash/
mv /var/www/mythicaldash/MythicalDash /var/www/mythicaldash/MythicalDashARM32
mv /var/www/mythicaldash/cli/bin/Release/net7.0/linux-arm64/publish/MythicalDash /var/www/mythicaldash/
mv /var/www/mythicaldash/MythicalDash /var/www/mythicaldash/MythicalDashARM64
mv /var/www/mythicaldash/cli/bin/Release/net7.0/linux-x64/publish/MythicalDash /var/www/mythicaldash/
mv /var/www/mythicaldash/MythicalDash /var/www/mythicaldash/MythicalDash64