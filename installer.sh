#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# ==========================================
# Configuration Variables
# ==========================================
REPO_URL="https://github.com/alonsogalcantara/activo-fijo-php.git"
INSTALL_DIR="/var/www/html/activoFijo"
DB_NAME="asset_manager"
DB_USER="asset_admin"
# Generate a random 12-character alphanumeric password for the database
DB_PASS=$(openssl rand -base64 12 | tr -dc 'a-zA-Z0-9' | head -c 12)

echo "Starting Installation Process..."

# ==========================================
# 1. Install Apache (check if it exists)
# ==========================================
if ! command -v apache2 > /dev/null 2>&1; then
    echo "Apache is not installed. Installing Apache..."
    sudo apt-get update
    sudo apt-get install -y apache2
else
    echo "Apache is already installed."
fi

# Install other required dependencies (Git, MySQL/MariaDB, PHP)
echo "Checking and installing other required dependencies..."
sudo apt-get install -y git mariadb-server php libapache2-mod-php php-mysql

# Ensure Apache is running and enabled on boot
sudo systemctl enable apache2
sudo systemctl start apache2

# Ensure MySQL/MariaDB is running and enabled on boot
sudo systemctl enable mariadb
sudo systemctl start mariadb

# ==========================================
# 2. Download from GitHub Repository
# ==========================================
if [ -d "$INSTALL_DIR/.git" ]; then
    echo "Directory $INSTALL_DIR already exists and is a git repository. Pulling latest changes..."
    cd "$INSTALL_DIR"
    sudo git pull origin main
else
    echo "Cloning repository from $REPO_URL..."
    # Remove directory if it exists but is empty or not a git repo to prevent clone errors
    sudo rm -rf "$INSTALL_DIR"
    sudo git clone "$REPO_URL" "$INSTALL_DIR"
fi

# Set proper permissions for the Apache web directory
sudo chown -R www-data:www-data "$INSTALL_DIR"
sudo chmod -R 755 "$INSTALL_DIR"

# ==========================================
# 3. Setup Database and User
# ==========================================
echo "Configuring the database..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`;"
sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
sudo mysql -e "ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
sudo mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# ==========================================
# 4. Import the SQL file to create the DB tables
# ==========================================
SQL_FILE="$INSTALL_DIR/src/SQL/DDL_Asset.sql"

if [ -f "$SQL_FILE" ]; then
    echo "Importing SQL schema from $SQL_FILE..."
    sudo mysql "$DB_NAME" < "$SQL_FILE"
    echo "Database imported successfully."
else
    echo "Warning: SQL file not found at $SQL_FILE within the repository."
    # Optional fallback if script is run locally where the file exists relative to the script
    if [ -f "./src/SQL/DDL_Asset.sql" ]; then
        echo "Found local copy of SQL file, importing..."
        sudo mysql "$DB_NAME" < "./src/SQL/DDL_Asset.sql"
    fi
fi

# ==========================================
# 5. Output Credentials
# ==========================================
echo ""
echo "========================================================"
echo "          INSTALLATION COMPLETED SUCCESSFULLY!          "
echo "========================================================"
echo " Application Path : $INSTALL_DIR"
echo " Repository URL   : $REPO_URL"
echo "--------------------------------------------------------"
echo " Database Name    : $DB_NAME"
echo " Database User    : $DB_USER"
echo " Database Password: $DB_PASS"
echo "========================================================"
echo "Please save these credentials securely!"
echo "You can access the application by navigating to your server's IP address or domain: http://$(hostname -I | awk '{print $1}')/activoFijo"
