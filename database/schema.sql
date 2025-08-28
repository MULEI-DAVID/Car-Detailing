-- Car Detailing Booking and Management System Database Schema
-- Created: 2024
-- Description: Complete database schema for the car detailing booking system

-- Create database
CREATE DATABASE IF NOT EXISTS car_detailing_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE car_detailing_db;

-- Drop tables if they exist (for clean installation)
DROP TABLE IF EXISTS booking_services;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS users;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(255),
    reset_token VARCHAR(255),
    reset_token_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_admin (is_admin)
) ENGINE=InnoDB;

-- Vehicles table
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nickname VARCHAR(100) NOT NULL,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    type VARCHAR(20) NOT NULL,
    color VARCHAR(30),
    license_plate VARCHAR(20),
    vin VARCHAR(17),
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_default (is_default)
) ENGINE=InnoDB;

-- Services table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    category ENUM('package', 'addon') DEFAULT 'package',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    service_type ENUM('mobile', 'facility') NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    location TEXT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_vehicle_id (vehicle_id),
    INDEX idx_status (status),
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_service_type (service_type)
) ENGINE=InnoDB;

-- Booking services (many-to-many relationship)
CREATE TABLE booking_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    service_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_service_id (service_id)
) ENGINE=InnoDB;

-- Insert default services (prices in Kenyan Shillings)
INSERT INTO services (name, description, price, duration, category) VALUES
('Basic Wash', 'Exterior wash, interior vacuum, and basic cleaning', 2500.00, 60, 'package'),
('Premium Detail', 'Complete interior and exterior detailing with wax', 7500.00, 180, 'package'),
('Ultimate Detail', 'Full service including paint correction and ceramic coating', 15000.00, 300, 'package'),
('Interior Deep Clean', 'Complete interior sanitization and deep cleaning', 4500.00, 90, 'addon'),
('Paint Protection', 'Wax application and paint protection treatment', 3500.00, 60, 'addon'),
('Engine Bay Cleaning', 'Engine compartment cleaning and degreasing', 2500.00, 45, 'addon');

-- Create admin user (password: admin123)
INSERT INTO users (full_name, email, phone, password, is_admin, email_verified) VALUES
('Admin User', 'admin@cardetailing.com', '555-0000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);

-- Create sample user for testing (password: user123)
INSERT INTO users (full_name, email, phone, password, is_admin, email_verified) VALUES
('John Doe', 'john@example.com', '555-1234', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 1);

-- Create sample vehicles for the test user
INSERT INTO vehicles (user_id, nickname, make, model, year, type, color, license_plate, vin, is_default) VALUES
(2, 'My Daily Driver', 'Honda', 'CR-V', 2018, 'SUV', 'Black', 'ABC123', '1HGBH41JXMN109186', 1),
(2, 'Weekend Car', 'BMW', '3 Series', 2020, 'Sedan', 'White', 'XYZ789', 'WBA8E9G50JNU12345', 0);

-- Create sample booking (amounts in Kenyan Shillings)
INSERT INTO bookings (user_id, vehicle_id, service_type, appointment_date, appointment_time, location, total_amount, status) VALUES
(2, 1, 'mobile', '2024-02-15', '10:00:00', '123 Main St, City, State', 12000.00, 'confirmed');

-- Link services to the sample booking
INSERT INTO booking_services (booking_id, service_id, price) VALUES
(1, 2, 7500.00),  -- Premium Detail
(1, 4, 4500.00);  -- Interior Deep Clean

-- Show table structure
SHOW TABLES;

-- Show sample data
SELECT 'Users:' as info;
SELECT id, full_name, email, is_admin FROM users;

SELECT 'Services:' as info;
SELECT id, name, price, category FROM services;

SELECT 'Vehicles:' as info;
SELECT id, user_id, nickname, make, model, year FROM vehicles;

SELECT 'Bookings:' as info;
SELECT id, user_id, vehicle_id, appointment_date, status, total_amount FROM bookings;
