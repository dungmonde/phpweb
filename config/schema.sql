-- =============================================
-- CRM System - Database Schema
-- Import vào phpMyAdmin hoặc chạy: mysql -u root -p < schema.sql
-- =============================================

CREATE DATABASE IF NOT EXISTS crm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crm_db;

-- 1. Loại khách hàng
CREATE TABLE IF NOT EXISTS customer_type (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  description TEXT,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Khách hàng
CREATE TABLE IF NOT EXISTS customer (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  customer_type_id INT,
  full_name        VARCHAR(200) NOT NULL,
  phone            VARCHAR(20),
  email            VARCHAR(100),
  address          TEXT,
  status           TINYINT DEFAULT 1 COMMENT '1=hoat dong, 0=ngung',
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_type_id) REFERENCES customer_type(id) ON DELETE SET NULL
);

-- 3. Người dùng hệ thống
CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  full_name  VARCHAR(200) NOT NULL,
  username   VARCHAR(100) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  role       ENUM('admin','staff') DEFAULT 'staff',
  status     TINYINT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Dịch vụ
CREATE TABLE IF NOT EXISTS services (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(200) NOT NULL,
  price       DECIMAL(15,2) DEFAULT 0,
  description TEXT,
  status      TINYINT DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Lịch hẹn
CREATE TABLE IF NOT EXISTS appointments (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  customer_id      INT,
  user_id          INT,
  service_id       INT,
  title            VARCHAR(200),
  appointment_date DATETIME NOT NULL,
  status           ENUM('pending','confirmed','done','cancelled') DEFAULT 'pending',
  note             TEXT,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customer(id)  ON DELETE CASCADE,
  FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE SET NULL,
  FOREIGN KEY (service_id)  REFERENCES services(id)  ON DELETE SET NULL
);

-- 6. Ghi chú / Lịch sử tương tác khách hàng
CREATE TABLE IF NOT EXISTS customer_notes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT,
  user_id     INT,
  note        TEXT NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)     REFERENCES users(id)    ON DELETE SET NULL
);

-- =============================================
-- Dữ liệu mẫu
-- =============================================
INSERT INTO customer_type (name, description) VALUES
  ('Khách thường',  'Khách hàng cá nhân thông thường'),
  ('Khách VIP',     'Khách hàng ưu tiên, doanh thu cao'),
  ('Doanh nghiệp',  'Khách hàng là tổ chức / công ty');

INSERT INTO users (full_name, username, password, role) VALUES
  ('Admin', 'admin', MD5('admin123'), 'admin'),
  ('Nhân viên A', 'nhanvien_a', MD5('123456'), 'staff');

INSERT INTO services (name, price, description) VALUES
  ('Tư vấn',       500000,  'Tư vấn giải pháp cho khách hàng'),
  ('Bảo trì',     1000000,  'Bảo trì định kỳ hệ thống'),
  ('Triển khai',  5000000,  'Triển khai dự án mới');

INSERT INTO customer (customer_type_id, full_name, phone, email, address, status) VALUES
  (2, 'Nguyễn Văn An',   '0901234567', 'an@email.com',    'Hà Nội',    1),
  (1, 'Trần Thị Bình',   '0912345678', 'binh@email.com',  'TP.HCM',    1),
  (3, 'Công ty XYZ',     '0281234567', 'xyz@company.vn',  'Đà Nẵng',   1);
