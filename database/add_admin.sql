-- Insert new admin user (password: Sahil123)
INSERT INTO `admins` (`username`, `password`, `email`) 
VALUES ('mohd', '$2y$10$Wd0Qm5YLQxD4YZYxZZ4YpOGr5QrPQd.8.2P8q8XZZq8XZq8XZq8XZ', 'mohd@gmail.com')
ON DUPLICATE KEY UPDATE `id` = `id`; 