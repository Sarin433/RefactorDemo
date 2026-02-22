SET NAMES utf8mb4;
ALTER DATABASE omsdb CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) DEFAULT 'user'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Products (
  product_number VARCHAR(50) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  stock_quantity INT NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Status_Reference (
  status_id INT AUTO_INCREMENT PRIMARY KEY,
  status_name VARCHAR(100) NOT NULL UNIQUE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Orders (
  order_number VARCHAR(50) PRIMARY KEY,
  user_email VARCHAR(255) NOT NULL,
  status_id INT NOT NULL,
  shipping_address TEXT,
  order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_email) REFERENCES Users(email),
  FOREIGN KEY (status_id) REFERENCES Status_Reference(status_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Order_Details (
  detail_id INT AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(50) NOT NULL,
  product_number VARCHAR(50) NOT NULL,
  quantity INT NOT NULL,
  FOREIGN KEY (order_number) REFERENCES Orders(order_number),
  FOREIGN KEY (product_number) REFERENCES Products(product_number)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
