#!/bin/bash

echo "🔧 SellNow MySQL Setup Script"
echo "=============================="

# Start MySQL
echo "🚀 Starting MySQL service..."
sudo service mysql start

# Create database and tables
echo "📁 Creating database and tables..."
sudo mysql << 'EOF'
-- Create database
CREATE DATABASE IF NOT EXISTS sellnow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sellnow;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    Full_Name VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255),
    slug VARCHAR(255),
    description TEXT,
    price DECIMAL(10,2),
    image_path VARCHAR(255),
    file_path VARCHAR(255),
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2),
    payment_provider VARCHAR(50),
    payment_status VARCHAR(20),
    transaction_id VARCHAR(100),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

SHOW TABLES;
SHOW DATABASES;
EOF

echo "✅ Database setup complete!"
echo "🌐 Start server with: php -S localhost:8000 -t public"
