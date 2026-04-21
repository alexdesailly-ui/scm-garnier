#!/bin/bash
# =============================================================
# SCM Garnier Infirmier - Script de déploiement Hostinger
# Compatible avec le même compte que agilecorp.fr
# =============================================================
#
# Usage:
#   ./scripts/deploy.sh
#
# Prérequis:
#   - lftp installé (apt install lftp / brew install lftp)
#   - Fichier .env.deploy à la racine (voir ci-dessous)
#
# =============================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=== SCM Garnier Infirmier - Déploiement Hostinger ===${NC}"
echo ""

# Load deploy config
DEPLOY_ENV="$PROJECT_DIR/.env.deploy"
if [ ! -f "$DEPLOY_ENV" ]; then
    echo -e "${YELLOW}Création du fichier .env.deploy...${NC}"
    cat > "$DEPLOY_ENV" << 'ENVEOF'
# Hostinger FTP credentials (same account as agilecorp.fr)
FTP_HOST="ftp.hostinger.com"
FTP_USER="u123456789"
FTP_PASS="your_ftp_password"

# Target directory on Hostinger
# agilecorp.fr    -> /public_html/
# scm-garnier     -> /domains/scm-garnier-infirmier.fr/public_html/
FTP_DIR="/domains/scm-garnier-infirmier.fr/public_html/"

# MySQL credentials (from hPanel > Databases)
DB_HOST="localhost"
DB_NAME="u123456789_scm_garnier"
DB_USER="u123456789_admin"
DB_PASS="your_mysql_password"

# Site config
SITE_URL="https://scm-garnier-infirmier.fr"
APP_SECRET="$(openssl rand -hex 32)"
ENCRYPTION_KEY="$(openssl rand -base64 32)"
ENVEOF
    echo -e "${RED}Remplissez .env.deploy avec vos identifiants Hostinger, puis relancez.${NC}"
    exit 1
fi

source "$DEPLOY_ENV"

# Validate
if [ "$FTP_PASS" = "your_ftp_password" ]; then
    echo -e "${RED}Erreur: Remplissez .env.deploy avec vos vrais identifiants.${NC}"
    exit 1
fi

echo -e "Cible: ${YELLOW}${FTP_HOST}${FTP_DIR}${NC}"
echo ""

# Step 1: Generate env.php for production
echo -e "${GREEN}[1/4] Génération de env.php...${NC}"
ENV_PHP="$PROJECT_DIR/env.php"
cat > "$ENV_PHP" << PHPEOF
<?php
return [
    'DB_HOST'        => '${DB_HOST}',
    'DB_NAME'        => '${DB_NAME}',
    'DB_USER'        => '${DB_USER}',
    'DB_PASS'        => '${DB_PASS}',
    'SITE_URL'       => '${SITE_URL}',
    'APP_SECRET'     => '${APP_SECRET}',
    'ENCRYPTION_KEY' => '${ENCRYPTION_KEY}',
    'APP_DEBUG'      => false,
];
PHPEOF
echo "  env.php généré"

# Step 2: Upload via FTP (lftp for reliability)
echo -e "${GREEN}[2/4] Upload vers Hostinger via FTP...${NC}"

EXCLUDE_LIST="--exclude .git/ --exclude docs/ --exclude .github/ --exclude scripts/deploy-local.sh --exclude .env.deploy --exclude preview.html --exclude env.example.php --exclude node_modules/ --exclude .DS_Store"

lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" << FTPEOF
set ssl:verify-certificate no
set net:max-retries 3
set net:reconnect-interval-base 5
mirror --reverse --delete --verbose \
    --exclude .git/ \
    --exclude .github/ \
    --exclude docs/ \
    --exclude .env.deploy \
    --exclude preview.html \
    --exclude env.example.php \
    --exclude .DS_Store \
    "$PROJECT_DIR" "$FTP_DIR"
quit
FTPEOF

echo "  Upload terminé"

# Step 3: Clean up local env.php
rm -f "$ENV_PHP"
echo -e "${GREEN}[3/4] env.php local supprimé${NC}"

# Step 4: Summary
echo ""
echo -e "${GREEN}[4/4] Déploiement terminé !${NC}"
echo ""
echo -e "Site public  : ${YELLOW}${SITE_URL}${NC}"
echo -e "Installation : ${YELLOW}${SITE_URL}/install.php${NC}"
echo -e "Admin        : ${YELLOW}${SITE_URL}/admin/login.php${NC}"
echo ""
echo -e "${YELLOW}Première installation ?${NC}"
echo "  1. Allez sur ${SITE_URL}/install.php"
echo "  2. Créez votre compte admin"
echo "  3. Supprimez install.php via hPanel > Gestionnaire de fichiers"
echo ""
echo -e "${GREEN}=== Déploiement réussi ===${NC}"
