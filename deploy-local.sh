#!/bin/bash

# ===========================================
# SASAMPA POS - LOCAL DEPLOYMENT TRIGGER
# ===========================================
# Run this script from your local machine to:
# 1. Optionally commit and push your changes
# 2. Deploy to production server
# ===========================================

# Server Configuration
# Production: Contabo VPS SERVER_IP_REDACTED (moved from Hostinger KVM 8, SERVER_IP_REDACTED, on 2026-11-03).
# Auth is by SSH key (the new server disables password logins). No password lives in this file.
SERVER_HOST="${DEPLOY_HOST_IP:-SERVER_IP_REDACTED}"
SERVER_USER="root"
APP_DIR="/var/www/sasampa"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo ""
echo "==========================================="
echo -e "   ${BLUE}SASAMPA LOCAL DEPLOYMENT${NC}"
echo "==========================================="
echo ""

# Navigate to project directory
cd "$(dirname "$0")"

# Check for uncommitted changes
if [[ -n $(git status --porcelain) ]]; then
    echo -e "${YELLOW}You have uncommitted changes:${NC}"
    echo ""
    git status --short
    echo ""

    read -p "Do you want to commit these changes? (y/n): " -n 1 -r
    echo ""

    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo ""
        read -p "Enter commit message: " COMMIT_MSG

        if [ -z "$COMMIT_MSG" ]; then
            COMMIT_MSG="Update $(date +%Y-%m-%d)"
        fi

        echo ""
        echo -e "${BLUE}Committing changes...${NC}"
        git add -A
        git commit -m "$COMMIT_MSG"
        echo -e "${GREEN}✓${NC} Changes committed"
    else
        echo ""
        echo -e "${YELLOW}⚠${NC} Proceeding without committing local changes"
        echo "  (Only pushed changes will be deployed)"
    fi
fi

# Check if we need to push
LOCAL_COMMIT=$(git rev-parse HEAD)
REMOTE_COMMIT=$(git rev-parse origin/main 2>/dev/null || echo "none")

if [ "$LOCAL_COMMIT" != "$REMOTE_COMMIT" ]; then
    echo ""
    echo -e "${BLUE}Pushing to GitHub...${NC}"

    UNPUSHED=$(git log origin/main..HEAD --oneline 2>/dev/null)
    if [ -n "$UNPUSHED" ]; then
        echo "Commits to push:"
        echo "$UNPUSHED"
        echo ""
    fi

    git push origin main
    echo -e "${GREEN}✓${NC} Pushed to GitHub"
else
    echo ""
    echo -e "${GREEN}✓${NC} Already up to date with GitHub"
fi

# Deploy to server
echo ""
echo "==========================================="
echo -e "${BLUE}Deploying to production server...${NC}"
echo "==========================================="
echo ""

# Run the deployment script on the server
ssh -o BatchMode=yes -o ConnectTimeout=30 "$SERVER_USER@$SERVER_HOST" \
    "cd $APP_DIR && git config --global --add safe.directory $APP_DIR && bash deploy.sh"

DEPLOY_STATUS=$?

echo ""
if [ $DEPLOY_STATUS -eq 0 ]; then
    echo "==========================================="
    echo -e "   ${GREEN}DEPLOYMENT COMPLETE${NC}"
    echo "==========================================="
    echo ""
    echo "Your changes are now live at:"
    echo "  https://sasampa.com"
    echo ""
else
    echo "==========================================="
    echo -e "   ${RED}DEPLOYMENT FAILED${NC}"
    echo "==========================================="
    echo ""
    echo "Check the server logs for details:"
    echo "  ssh $SERVER_USER@$SERVER_HOST 'ls -lt /var/backups/sasampa/deploy_*.log | head -1'"
    echo ""
fi
