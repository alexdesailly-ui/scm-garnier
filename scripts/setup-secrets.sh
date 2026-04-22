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
echo "Compte Hostinger : u398408214 (scm-garnier-infirmier.fr)"
echo ""

# FTP credentials
FTP_HOST="145.79.20.5"
FTP_USER="u398408214"
echo "FTP Host: $FTP_HOST"
echo "FTP User: $FTP_USER"
read -sp "FTP Password: " FTP_PASS
echo ""

FTP_DIR="/public_html/"
echo "FTP Dir: $FTP_DIR"

# MySQL
echo ""
echo "--- MySQL (hPanel > Bases de données) ---"
DB_HOST="localhost"
read -p "DB Name (default: u398408214_scm_garnier): " DB_NAME
DB_NAME=${DB_NAME:-u398408214_scm_garnier}
read -p "DB User (default: u398408214_admin): " DB_USER
DB_USER=${DB_USER:-u398408214_admin}
read -sp "DB Password: " DB_PASS
echo ""

SITE_URL="https://scm-garnier-infirmier.fr"

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
