
CREATE DATABASE IF NOT EXISTS datve;
USE datve;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    event_type ENUM('movie', 'event') NOT NULL DEFAULT 'movie',
    description TEXT,
    location VARCHAR(255),
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS showtimes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    show_date DATE NOT NULL,
    show_time TIME NOT NULL,
    ticket_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_seats INT NOT NULL DEFAULT 100,
    FOREIGN KEY (event_id) REFERENCES events(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    showtime_id INT NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    seat_count INT NOT NULL DEFAULT 1,
    booking_code VARCHAR(30) NOT NULL UNIQUE,
    status ENUM('pending', 'confirmed', 'cancelled')
        NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (showtime_id) REFERENCES showtimes(id)
);

INSERT INTO events (title, event_type, description, location)
VALUES
('Đêm phim cuối tuần', 'movie',
 'Chương trình chiếu phim cuối tuần dành cho khán giả.',
 'Rạp chiếu phim Demo'),
('Đêm nhạc mùa thu', 'event',
 'Sự kiện âm nhạc giao lưu dành cho sinh viên.',
 'Hội trường Demo');
