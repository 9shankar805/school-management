#!/bin/bash
# ═══════════════════════════════════════════════════════════════════════════
# add-custom-domain.sh — Register a verified school custom domain with
# nginx + Apache so the web server routes it to the Laravel app.
#
# Usage:  sudo ./add-custom-domain.sh hls.edu.np
#         sudo ./add-custom-domain.sh mycollege.edu.np
# ═══════════════════════════════════════════════════════════════════════════
set -e

DOMAIN="$1"
if [ -z "$DOMAIN" ]; then
    echo "Usage: sudo $0 <domain>"
    echo "Example: sudo $0 hls.edu.np"
    exit 1
fi

echo "Adding custom domain: $DOMAIN"

# ── 1. nginx ────────────────────────────────────────────────────────────────
NGINX_CONF="/etc/nginx/sites-available/school-custom-domains.conf"

# Add to server_name lines if not already present
if ! grep -q "$DOMAIN" "$NGINX_CONF" 2>/dev/null; then
    sed -i "s/server_name \(.*\);/server_name \1 $DOMAIN www.$DOMAIN;/" "$NGINX_CONF"
    echo "  nginx: added $DOMAIN to server_name"
else
    echo "  nginx: $DOMAIN already present"
fi

# ── 2. Apache ──────────────────────────────────────────────────────────────
APACHE_CONF="/etc/apache2/sites-enabled/nshlms.conf"

if ! grep -q "$DOMAIN" "$APACHE_CONF" 2>/dev/null; then
    sed -i "s/ServerAlias \(.*\)/ServerAlias \1 $DOMAIN www.$DOMAIN/" "$APACHE_CONF"
    echo "  apache: added $DOMAIN to ServerAlias"
else
    echo "  apache: $DOMAIN already present"
fi

# ── 3. Test & reload ────────────────────────────────────────────────────────
echo "Testing configs..."
nginx -t 2>&1 | tail -1
apache2ctl configtest 2>&1

echo "Reloading..."
systemctl reload nginx
systemctl reload apache2

echo ""
echo "Done! $DOMAIN is now routed to the school management app."
echo "Make sure the domain's DNS A record points to the origin server IP."
