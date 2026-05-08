-- Blog Management System - Database Schema
-- Run this SQL in your MySQL database

CREATE DATABASE IF NOT EXISTS blog_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_system;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(191) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    short_description TEXT,
    content LONGTEXT NOT NULL,
    category_id INT NOT NULL,
    image VARCHAR(255),
    status ENUM('published','draft') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

INSERT INTO categories (name, slug) VALUES
('Admit Card', 'admit-card'),
('Latest Jobs', 'latest-jobs'),
('Results', 'results'),
('Answer Key', 'answer-key'),
('Syllabus', 'syllabus'),
('General Knowledge', 'general-knowledge');

-- Default Admin password: Admin@123
INSERT INTO admins (username, email, password) VALUES
('admin', 'admin@jobyaari.com', '$2y$12$LkxDCp5P5mdmhUz7gKz3R.OBaD1B0h7oO8Ag2g1p.uluJRQIvvb5e');

INSERT INTO blogs (title, slug, short_description, content, category_id, image, status) VALUES
('SSC CGL 2024 Admit Card Released - Download Now', 'ssc-cgl-2024-admit-card', 'Staff Selection Commission has released the admit card for CGL 2024 Tier 1 examination.', '<h2>SSC CGL 2024 Admit Card</h2><p>The Staff Selection Commission (SSC) has officially released the admit card for CGL 2024 Tier 1 examination.</p><h3>How to Download</h3><ul><li>Visit ssc.nic.in</li><li>Click admit card link</li><li>Enter registration number and DOB</li><li>Download and print</li></ul>', 1, NULL, 'published'),
('IBPS PO 2024 Notification Out - 4000+ Vacancies', 'ibps-po-2024-notification', 'IBPS has announced PO 2024 recruitment with over 4000 vacancies across public sector banks.', '<h2>IBPS PO 2024 Recruitment</h2><p>Over 4000 vacancies available across various public sector banks.</p><h3>Eligibility</h3><p>Graduation from recognized university. Age: 20-30 years.</p>', 2, NULL, 'published'),
('SSC MTS 2024 Result Declared - Check Merit List', 'ssc-mts-2024-result', 'SSC has declared the MTS 2024 examination results. Candidates can check their result online.', '<h2>SSC MTS 2024 Result</h2><p>Results officially declared. Check your status on ssc.nic.in.</p><h3>Steps</h3><ol><li>Go to ssc.nic.in</li><li>Navigate to Results</li><li>Enter roll number</li></ol>', 3, NULL, 'published');
