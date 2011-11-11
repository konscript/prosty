#!/bin/sh
Apache=$(stat -c %Y /var/run/apache2.pid)
Nginx=$(stat -c %Y /var/run/nginx.pid)

# output uptime in json
echo {\"apache\":$Apache,\"nginx\":$Nginx}
