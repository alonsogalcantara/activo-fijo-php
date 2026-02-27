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
if [ -d "$INSTALL_DIR" ]; then
    echo "Directory $INSTALL_DIR already exists. Skipping clone process..."
else
    echo "Cloning repository from $REPO_URL..."
    sudo git clone "$REPO_URL" "$INSTALL_DIR"
fi

# Set proper permissions for the Apache web directory
sudo chown -R www-data:www-data "$INSTALL_DIR"
sudo chmod -R 755 "$INSTALL_DIR"

# Configure Apache Default Page to serve the application
echo "Configuring Apache to serve the application..."
# Remove the default index.html if it exists
sudo rm -f /var/www/html/index.html
# Update the default Apache configuration document root
sudo sed -i "s|DocumentRoot /var/www/html|DocumentRoot $INSTALL_DIR|g" /etc/apache2/sites-available/000-default.conf
# Enable Apache rewrite module
sudo a2enmod rewrite
# Create an override allowed configuration for the directory
sudo tee /etc/apache2/conf-available/activoFijo.conf > /dev/null <<EOF
<Directory $INSTALL_DIR>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF
sudo a2enconf activoFijo
sudo systemctl restart apache2

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
    # Ensure compatibility with older MySQL/MariaDB versions
    sudo sed -i 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' "$SQL_FILE"
    sudo mysql "$DB_NAME" < "$SQL_FILE"
    echo "Database imported successfully."
else
    echo "Warning: SQL file not found at $SQL_FILE within the repository."
    # Optional fallback if script is run locally where the file exists relative to the script
    if [ -f "./src/SQL/DDL_Asset.sql" ]; then
        echo "Found local copy of SQL file, importing..."
        # Ensure compatibility with older MySQL/MariaDB versions
        sed -i 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' "./src/SQL/DDL_Asset.sql"
        sudo mysql "$DB_NAME" < "./src/SQL/DDL_Asset.sql"
    fi
fi

# ==========================================
# 5. Create Default Admin User
# ==========================================
ADMIN_EMAIL="admin@admin.com"
# Check if the user already exists
USER_EXISTS=$(sudo mysql -N -B -e "SELECT COUNT(*) FROM \`$DB_NAME\`.users WHERE email='$ADMIN_EMAIL';")

if [ "$USER_EXISTS" -eq 0 ]; then
    echo "Creating default admin user..."
    ADMIN_PASS=$(openssl rand -base64 8 | tr -dc 'a-zA-Z0-9' | head -c 8)
    
    # Generate the bcrypt hash using PHP (it's already installed on the system at this point)
    ADMIN_HASH=$(php -r "echo password_hash('$ADMIN_PASS', PASSWORD_DEFAULT);")
    
    sudo mysql -e "INSERT INTO \`$DB_NAME\`.users (name, first_name, email, role, system_role, password_hash, status) VALUES ('Super Admin', 'Super', '$ADMIN_EMAIL', 'Administrator', 'admin', '$ADMIN_HASH', 'Activo');"
    ADMIN_CREATED=true
else
    echo "Default admin user already exists. Skipping creation."
    ADMIN_CREATED=false
fi

# ==========================================
# 6. Output Credentials
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
echo "--------------------------------------------------------"
if [ "$ADMIN_CREATED" = true ]; then
    echo " Default Admin Email   : $ADMIN_EMAIL"
    echo " Default Admin Password: $ADMIN_PASS"
else
    echo " Default Admin Email   : $ADMIN_EMAIL"
    echo " Default Admin Password: (Already Created - use existing)"
fi
echo "========================================================"
echo "Please save these credentials securely!"
echo "You can access the application by navigating to your server's IP address or domain: http://$(hostname -I | awk '{print $1}')/activoFijo"
