#!/bin/bash

arch=$(uname -m)

if [ "$arch" == "aarch64" ]; then
    echo "Host architecture is arm64."
    mv MythicalDashARM64 MythicalDash 
    chmod u+x ./MythicalDash
elif [ "$arch" == "armv7l" ]; then
    echo "Host architecture is arm32."
    mv MythicalDashARM32 MythicalDash
    chmod u+x ./MythicalDash
elif [ "$arch" == "x86_64" ]; then
    echo "Host architecture is amd64."
    mv MythicalDash64 MythicalDash
    chmod u+x ./MythicalDash
else
    echo "Unsupported architecture: $arch"
    exit 1
fi