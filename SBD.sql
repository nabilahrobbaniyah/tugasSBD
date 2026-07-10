CREATE DATABASE IF NOT EXISTS badminton_rental;
USE badminton_rental;

-- USERS (induk)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20)
);

-- COURTS (induk)
CREATE TABLE courts (
    court_id INT AUTO_INCREMENT PRIMARY KEY,
    court_name VARCHAR(100) NOT NULL,
    type ENUM('indoor','outdoor') NOT NULL,
    price_per_hour DECIMAL(10,2) NOT NULL
);

-- BOOKINGS (anak, relasi ke users & courts)
CREATE TABLE bookings (
    booking_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    court_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('pending','confirmed','canceled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (court_id) REFERENCES courts(court_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- PAYMENTS (anak, relasi ke bookings)
CREATE TABLE payments (
    payment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATETIME NOT NULL,
    method ENUM('cash','transfer','ewallet'),
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- SCHEDULES (anak, relasi ke courts)
CREATE TABLE schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    court_id INT NOT NULL,
    available_date DATE NOT NULL,
    open_time TIME NOT NULL,
    close_time TIME NOT NULL,
    FOREIGN KEY (court_id) REFERENCES courts(court_id) ON DELETE CASCADE ON UPDATE CASCADE
);

SELECT b.booking_id, u.name AS user_name, c.court_name, b.start_time, b.end_time, b.status
FROM bookings b
JOIN users u ON b.user_id = u.user_id
JOIN courts c ON b.court_id = c.court_id
WHERE b.status = 'confirmed';



