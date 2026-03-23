-- Library Management System Database Schema
-- Run this SQL file to set up the database

CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'user') DEFAULT 'user',
    membership_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Books Table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    publication_year YEAR,
    publisher VARCHAR(150),
    description TEXT,
    total_quantity INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    cover_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Issued Books Table
CREATE TABLE IF NOT EXISTS issued_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    issue_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    fine_paid ENUM('yes', 'no') DEFAULT 'no',
    status ENUM('issued', 'returned', 'overdue') DEFAULT 'issued',
    issued_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Default Admin User (password: Admin@123)
INSERT INTO users (name, email, password, role, status) VALUES 
('Library Admin', 'admin@library.com', '$2y$10$SlmmMLBabWENrRn5V/WnTOsZjXUR.YqU9xORkYVSciLoNtz74Cw7G', 'admin', 'active');

-- Sample Books
INSERT INTO books (title, author, category, isbn, publication_year, publisher, description, total_quantity, available_copies) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', 'Fiction', '9780743273565', 1925, 'Scribner', 'A story of the mysteriously wealthy Jay Gatsby and his love for Daisy Buchanan.', 5, 5),
('To Kill a Mockingbird', 'Harper Lee', 'Fiction', '9780061935466', 1960, 'J.B. Lippincott', 'The story of racial injustice and the loss of innocence in the American South.', 4, 4),
('1984', 'George Orwell', 'Dystopian', '9780451524935', 1949, 'Secker & Warburg', 'A dystopian social science fiction novel and cautionary tale about totalitarianism.', 6, 6),
('Harry Potter and the Sorcerer''s Stone', 'J.K. Rowling', 'Fantasy', '9780590353427', 1997, 'Bloomsbury', 'A young boy discovers he is a wizard on his 11th birthday.', 8, 8),
('The Alchemist', 'Paulo Coelho', 'Fiction', '9780062315007', 1988, 'HarperOne', 'A philosophical novel about following one''s dreams.', 5, 5),
('Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', 'Non-Fiction', '9780062316097', 2011, 'Harper', 'A narrative history of humankind from the Stone Age to the present.', 4, 4),
('The Lean Startup', 'Eric Ries', 'Business', '9780307887894', 2011, 'Crown Business', 'How today''s entrepreneurs use continuous innovation to create radically successful businesses.', 3, 3),
('Clean Code', 'Robert C. Martin', 'Technology', '9780132350884', 2008, 'Prentice Hall', 'A handbook of agile software craftsmanship.', 4, 4),
('Thinking, Fast and Slow', 'Daniel Kahneman', 'Psychology', '9780374533557', 2011, 'Farrar, Straus and Giroux', 'A groundbreaking tour of the mind explains the two systems that drive the way we think.', 3, 3),
('The Da Vinci Code', 'Dan Brown', 'Thriller', '9780307474278', 2003, 'Doubleday', 'A symbologist unravels a mystery hidden in the works of Leonardo da Vinci.', 5, 5),
('Brave New World', 'Aldous Huxley', 'Dystopian', '9780060850524', 1932, 'Chatto & Windus', 'A futuristic World State of genetically modified citizens.', 4, 4),
('The Catcher in the Rye', 'J.D. Salinger', 'Fiction', '9780316769174', 1951, 'Little, Brown', 'The story of Holden Caulfield, a teenager navigating adulthood.', 3, 3);

-- Admin login: admin@library.com / Admin@123
