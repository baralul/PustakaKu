-- Create database
CREATE DATABASE IF NOT EXISTS pustakaku;
USE pustakaku;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Books table
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    publisher VARCHAR(255),
    publication_year YEAR,
    category VARCHAR(100),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@pustakaku.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Insert sample books
INSERT INTO books (title, author, isbn, publisher, publication_year, category, total_copies, available_copies, description) VALUES
('Laskar Pelangi', 'Andrea Hirata', '9789792248074', 'Bentang Pustaka', 2005, 'Fiction', 5, 5, 'Novel tentang perjuangan anak-anak Belitung untuk mendapatkan pendidikan'),
('Bumi Manusia', 'Pramoedya Ananta Toer', '9789799731240', 'Hasta Mitra', 1980, 'Fiction', 3, 3, 'Novel sejarah tentang kehidupan di masa kolonial Belanda'),
('Negeri 5 Menara', 'Ahmad Fuadi', '9786020314051', 'Gramedia', 2009, 'Fiction', 4, 4, 'Novel tentang perjuangan santri menggapai mimpi'),
('Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', '9789792202694', 'Republika', 2004, 'Romance', 6, 6, 'Novel romantis berlatar belakang Mesir');
