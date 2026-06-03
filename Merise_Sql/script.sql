CREATE DATABASE bijoux_db;
USE bijoux_db;

CREATE TABLE categories(
id INT AUTO_INCREMENT PRIMARY KEY,
nom VARCHAR(100) NOT NULL
);

CREATE TABLE products(
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
price DECIMAL(10,2),
quantity INT,
category_id INT,

FOREIGN KEY(category_id)
REFERENCES categories(id)
);
INSERT INTO categories(nom)
VALUES
('Bracelets'),
('Colliers'),
('Bagues'),
('Boucles');
