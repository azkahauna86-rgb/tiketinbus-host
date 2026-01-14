CREATE DATABASE bus_ticket_system;
USE bus_ticket_system;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    user_type ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bus operators
CREATE TABLE bus_operators (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20),
    email VARCHAR(100),
    rating DECIMAL(3,2) DEFAULT 0.0
);

-- Buses
CREATE TABLE buses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bus_number VARCHAR(20) UNIQUE NOT NULL,
    operator_id INT,
    bus_type ENUM('AC', 'Non-AC', 'Sleeper', 'Seater') NOT NULL,
    total_seats INT DEFAULT 40,
    amenities TEXT,
    FOREIGN KEY (operator_id) REFERENCES bus_operators(id)
);

-- Routes
CREATE TABLE routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bus_id INT,
    departure_city VARCHAR(100) NOT NULL,
    arrival_city VARCHAR(100) NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    duration VARCHAR(20),
    distance DECIMAL(8,2),
    price DECIMAL(10,2) NOT NULL,
    available_seats INT,
    FOREIGN KEY (bus_id) REFERENCES buses(id)
);

-- Bookings
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_number VARCHAR(20) UNIQUE NOT NULL,
    user_id INT,
    route_id INT,
    seats_booked INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('confirmed', 'pending', 'cancelled') DEFAULT 'confirmed',
    payment_status ENUM('paid', 'pending', 'failed') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (route_id) REFERENCES routes(id)
);

-- Passengers
CREATE TABLE passengers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    name VARCHAR(100) NOT NULL,
    age INT,
    gender ENUM('Male', 'Female', 'Other'),
    seat_number VARCHAR(10),
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);