-- SecLab Database Schema & Seed Data
-- SQLite3 Compatible

DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS products;

-- 1. Table Users (Target for SQL Injection & Flag Exfiltration)
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL,
    secret_flag TEXT NOT NULL
);

INSERT INTO users (username, password, role, secret_flag) VALUES 
('admin', 'SuperAdmin2026!#', 'Administrator', 'FLAG{sql1_un10n_m4st3r_4dm1n}'),
('dosen', 'rahasiaDosen123', 'Lecturer', 'FLAG{d0s3n_s3cur1ty_fl4g}'),
('mahasiswa1', 'mhs2026', 'Student', 'FLAG{mhs_fl4g_b4s1c}');

-- 2. Table Comments (Target for Stored XSS)
CREATE TABLE comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    author TEXT NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO comments (author, message) VALUES 
('Admin', 'Selamat datang di forum diskusi keamanan web. Jaga etika dan patuhi etika pengujian.'),
('Budi', 'Halo teman-teman, siap belajar cybersecurity hari ini!');

-- 3. Table Products (For UNION SQLi comparison)
CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    price INTEGER NOT NULL,
    category TEXT NOT NULL
);

INSERT INTO products (name, price, category) VALUES 
('Laptop CyberSec Pro', 15000000, 'Hardware'),
('Hardware Security Key', 750000, 'Security'),
('Buku Web Security 101', 125000, 'Books');
