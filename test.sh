#!/bin/bash
phpVersion="$1"
phpunitsDir="./phpunits"

# get PHPUnit Version
if [ "$phpVersion" == "7.0" ]; then
  phpunitVersion="6.5.14"
elif [ "$phpVersion" == "7.1" ]; then
  phpunitVersion="7.5.20"
elif [ "$phpVersion" == "7.2" ]; then
  phpunitVersion="8.5.26"
elif [ "$phpVersion" == "7.3" ]; then
  phpunitVersion="9.5.20"
elif [ "$phpVersion" == "7.4" ]; then
  phpunitVersion="9.5.20"
elif [ "$phpVersion" == "8.0" ]; then
  phpunitVersion="9.5.20"
elif [ "$phpVersion" == "8.1" ]; then
  phpunitVersion="9.5.20"
elif [ "$phpVersion" == "8.2" ]; then
    phpunitVersion="9.5.20"
else
  echo "unsupported PHP version $phpVersion"
  exit 1
fi

# download PHPUnit phar if not exist
phpunitFile="phpunit-${phpunitVersion}.phar"
phpunitPath="${phpunitsDir}/${phpunitFile}"
if [ ! -f "./${phpunitPath}" ]; then
  phpunitUrl="https://phar.phpunit.de/${phpunitFile}"
  echo "Downloading ${phpunitUrl}"
  curl -LO "$phpunitUrl"
  chmod +x "$phpunitFile"
  mkdir -p "$phpunitsDir"
  mv "$phpunitFile" "$phpunitsDir"
fi

# run PHP in docker with corresponding PHPUnit version
docker run --rm -t -v $(pwd):/app -w /app php:"$phpVersion" "$phpunitPath" "${@:2}"
