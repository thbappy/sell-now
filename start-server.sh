#!/bin/bash

# SellNow Application Server Setup & Run Script

clear

echo "=========================================="
echo "   SellNow Application Launcher"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if MySQL is running
echo -e "${YELLOW}[1/4] Checking MySQL...${NC}"
if ! pgrep -x "mysqld" > /dev/null; then
    echo -e "${RED}✗ MySQL is not running!${NC}"
    echo "Please start MySQL first:"
    echo "  sudo systemctl start mysql"
    exit 1
fi
echo -e "${GREEN}✓ MySQL is running${NC}"

# Check database
echo -e "${YELLOW}[2/4] Checking database...${NC}"
mysql -u root -p0k -e "USE sellnow; SELECT COUNT(*) FROM users;" 2>/dev/null > /dev/null
if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Cannot access 'sellnow' database!${NC}"
    echo "Please run setup first:"
    echo "  ./setup-mysql.sh"
    exit 1
fi
echo -e "${GREEN}✓ Database 'sellnow' is accessible${NC}"

# Check test user
echo -e "${YELLOW}[3/4] Checking test user...${NC}"
USER_COUNT=$(mysql -u root -p0k -e "USE sellnow; SELECT COUNT(*) FROM users;" 2>/dev/null | tail -1)
if [ "$USER_COUNT" -eq 0 ]; then
    echo -e "${YELLOW}No users found. Creating test user...${NC}"
    mysql -u root -p0k sellnow << 'EOF' 2>/dev/null
INSERT INTO users (email, username, Full_Name, password) 
VALUES ('test@example.com', 'testuser', 'Test User', '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890');
EOF
    echo -e "${GREEN}✓ Test user created (test@example.com)${NC}"
else
    echo -e "${GREEN}✓ Users table has data${NC}"
fi

# Kill existing PHP process
echo -e "${YELLOW}[4/4] Starting server...${NC}"
pkill -f "php -S" 2>/dev/null
sleep 1

# Start PHP server
cd "$(dirname "$0")/public"
PORT=8000

echo ""
echo -e "${GREEN}=========================================="
echo "   Server is running!"
echo "=========================================="
echo ""
echo -e "${GREEN}URL: http://localhost:${PORT}${NC}"
echo ""
echo "Test Accounts:"
echo "  Email: test@example.com"
echo "  Password: (check database)"
echo ""
echo "Quick Test Links:"
echo "  Home:       http://localhost:${PORT}/"
echo "  Login:      http://localhost:${PORT}/login"
echo "  Register:   http://localhost:${PORT}/register"
echo "  Products:   http://localhost:${PORT}/products"
echo "  Cart:       http://localhost:${PORT}/cart"
echo "  Profile:    http://localhost:${PORT}/testuser"
echo ""
echo -e "${YELLOW}Press Ctrl+C to stop the server${NC}"
echo ""

php -S localhost:${PORT} router.php
