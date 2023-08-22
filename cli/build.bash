#!/bin/bash

runtimes=( "linux-x64" "linux-musl-x64" "linux-arm64" "linux-arm")
rm -r bin/Release
rm -r obj/

for runtime in "${runtimes[@]}"; do
    echo "Publishing for runtime: $runtime"
    dotnet clean
    dotnet restore
    dotnet publish -c Release -r "$runtime" --self-contained true /p:PublishSingleFile=true
    echo "----------------------------------"
done

#dotnet build