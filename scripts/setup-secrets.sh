#!/bin/bash
# =============================================================
# Setup GitHub Secrets pour le déploiement automatique
# Même compte Hostinger que agilecorp.fr
# =============================================================
#
# Usage:
#   ./scripts/setup-secrets.sh
#
# Prérequis: gh CLI authentifié
# =============================================================

set -euo pipefail

REPO="alexdesailly-ui/scm-garnier-infirmier.fr"

echo "=== Configuration des secrets GitHub ==="
echo "Repository: $REPO"
echo ""
echo "Les secrets FTP sont les mêmes que pour agilecorp.fr"
echo "(même compte Hostinger, domaine différent)"
echo ""

# FTP credentials (same as agilecorp.fr)
read -p "FTP Host (ex: ftp.hostinger.com): " FTP_HOST
read -p "FTP User (ex: u123456789): " FTP_USER
read -sp "FTP Password: " FTP_PASS
echo ""

# Target directory for this domain
FTP_DIR="/domains/scm-garnier-infirmier.fr/public_html/"
echo "FTP Dir: $FTP_DIR"

# MySQL (from hPanel > Databases)
echo ""
echo "--- MySQL (hPanel > Bases de données) ---"
read -p "DB Host (default: localhost): " DB_HOST
DB_HOST=${DB_HOST:-localhost}
read -p "DB Name (ex: u123456789_scm_garnier): " DB_NAME
read -p "DB User (ex: u123456789_admin): " DB_USER
read -sp "DB Password: " DB_PASS
echo ""

# Site URL
read -p "Site URL (default: https://scm-garnier-infirmier.fr): " SITE_URL
SITE_URL=${SITE_URL:-https://scm-garnier-infirmier.fr}

# Generate security keys
APP_SECRET=$(openssl rand -hex 32)
ENCRYPTION_KEY=$(openssl rand -base64 32)

echo ""
echo "--- Envoi des secrets vers GitHub ---"

gh secret set FTP_HOST -R "$REPO" -b "$FTP_HOST"
gh secret set FTP_USER -R "$REPO" -b "$FTP_USER"
gh secret set FTP_PASS -R "$REPO" -b "$FTP_PASS"
gh secret set FTP_DIR  -R "$REPO" -b "$FTP_DIR"

gh secret set DB_HOST -R "$REPO" -b "$DB_HOST"
gh secret set DB_NAME -R "$REPO" -b "$DB_NAME"
gh secret set DB_USER -R "$REPO" -b "$DB_USER"
gh secret set DB_PASS -R "$REPO" -b "$DB_PASS"

gh secret set SITE_URL       -R "$REPO" -b "$SITE_URL"
gh secret set APP_SECRET     -R "$REPO" -b "$APP_SECRET"
gh secret set ENCRYPTION_KEY -R "$REPO" -b "$ENCRYPTION_KEY"

echo ""
echo "=== Tous les secrets configurés ! ==="
echo ""
echo "Résumé:"
echo "  FTP:  $FTP_HOST -> $FTP_DIR"
echo "  DB:   $DB_HOST / $DB_NAME"
echo "  URL:  $SITE_URL"
echo ""
echo "Le prochain push sur la branche déclenchera le déploiement automatique."
