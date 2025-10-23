CREATE DATABASE fm_radio;
USE fm_radio;

CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(255) UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE programs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    description TEXT,
    start_time TIME,
    end_time TIME,
    day_of_week VARCHAR(20),
    host VARCHAR(255),
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('station_name', 'FM Radio Station'),
('live_stream_url', 'http://10.1.0.29:8000/stream'),
('station_description', 'Your favorite FM radio station'),
('primary_color', '#ff6b35'),
('secondary_color', '#004e89'),
('logo', ''),
('facebook_url', ''),
('twitter_url', ''),
('instagram_url', ''),
('contact_email', 'info@fmradio.com'),
('contact_phone', '+1 234-567-8900'),
('location', 'Your City, Country');