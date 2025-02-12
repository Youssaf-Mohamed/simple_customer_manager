CREATE DATABASE hardware_store;

USE electric_store;

CREATE TABLE customers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    balance DECIMAL(10,2) NOT NULL
);

CREATE TABLE transactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    customer_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('deposit', 'withdraw') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);