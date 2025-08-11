-- Social Media Website Database Schema
CREATE DATABASE IF NOT EXISTS social_media_db;
USE social_media_db;

-- Users table
CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       username VARCHAR(50) UNIQUE NOT NULL,
                       email VARCHAR(100) UNIQUE NOT NULL,
                       password_hash VARCHAR(255) NOT NULL,
                       user_level INT DEFAULT 1, -- 1 = regular user, 2 = admin user
                       profile_picture VARCHAR(255) DEFAULT 'default.jpg',
                       bio TEXT,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Posts table
CREATE TABLE posts (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       user_id INT NOT NULL,
                       content TEXT NOT NULL,
                       image_url VARCHAR(255),
                       likes_count INT DEFAULT 0,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                       FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Comments table
CREATE TABLE comments (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          post_id INT NOT NULL,
                          user_id INT NOT NULL,
                          content TEXT NOT NULL,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                          FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Likes table
CREATE TABLE likes (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       post_id INT NOT NULL,
                       user_id INT NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       UNIQUE KEY unique_like (post_id, user_id),
                       FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                       FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO users (username, email, password_hash, user_level, bio) VALUES
                                                                        ('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Hello! I love sharing photos and connecting with friends.'),
                                                                        ('jane_admin', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'Admin user managing the community.'),
                                                                        ('mike_smith', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Photographer and travel enthusiast.');

INSERT INTO posts (user_id, content, image_url) VALUES
                                                    (1, 'Just had an amazing day at the beach! The sunset was absolutely beautiful. Nothing beats the sound of waves and the feeling of sand between your toes. #BeachLife #Sunset', 'beach-sunset.jpg'),
                                                    (2, 'Welcome to our growing community! We are excited to have everyone here. Please remember to follow our community guidelines and be respectful to one another.', 'welcome-banner.jpg'),
                                                    (3, 'Captured this incredible shot during my morning hike. Nature never fails to amaze me with its beauty and tranquility. #Photography #Nature #Hiking', 'mountain-view.jpg');

INSERT INTO comments (post_id, user_id, content) VALUES
                                                     (1, 2, 'Wow, that looks absolutely stunning! I need to visit that beach sometime.'),
                                                     (1, 3, 'Great photo! The colors in that sunset are incredible.'),
                                                     (2, 1, 'Thank you for creating such a welcoming space for everyone!'),
                                                     (3, 1, 'Amazing shot! What camera did you use for this?'),
                                                     (3, 2, 'The composition is perfect. Well done!');

INSERT INTO likes (post_id, user_id) VALUES
                                         (1, 2), (1, 3),
                                         (2, 1), (2, 3),
                                         (3, 1), (3, 2);