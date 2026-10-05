-- ========================================================
-- SecLab MySQL Realistic Enterprise Database Dump
-- Target Database: seclab_db
-- Compatible with phpMyAdmin / MySQL 5.7+ / MariaDB
-- ========================================================

CREATE DATABASE IF NOT EXISTS `seclab_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `seclab_db`;

-- --------------------------------------------------------
-- 1. Table `users`: Data Kredensial & Identitas Sensitif (Target Utama SQLi Data Breach)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(150) NOT NULL,
  `username` VARCHAR(80) NOT NULL UNIQUE,
  `email` VARCHAR(120) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL,
  `nik_ktp` VARCHAR(20) NOT NULL,
  `credit_card` VARCHAR(25) NOT NULL,
  `salary` BIGINT(20) NOT NULL,
  `api_secret_key` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `role`, `nik_ktp`, `credit_card`, `salary`, `api_secret_key`) VALUES
(1, 'Prof. Dr. Irwan Siregar, M.Kom', 'superadmin', 'irwan.siregar@enterprise-corp.id', 'AdminSecure#2026!$', 'Super Administrator', '3271041982050001', '4532-8921-3341-9012', 45000000, 'FLAG{sql1_b0c0r_d4t4_kr3d3ns14l_m4st3r}'),
(2, 'Siti Nurhaliza, S.T (Lead DevOps)', 'siti_devops', 'siti.devops@enterprise-corp.id', 'DevOpsVault@Pass99', 'DevOps Engineer', '3271041991080004', '5412-7512-9901-2210', 28000000, 'FLAG{k3b0c0r4n_4p1_k3y_s3rv3r_pr0d}'),
(3, 'Bambang Sudibyo (Direktur Keuangan)', 'bambang_finance', 'bambang.finance@enterprise-corp.id', 'DuitPerusahaan#2026', 'Finance Manager', '3174051978010003', '4000-1234-5678-9010', 38000000, 'FLAG{d4t4_g4j1_d4n_k4rtu_kr3d1t_l34k}'),
(4, 'Dinda Larasati (HR Recruitment)', 'dinda_hr', 'dinda.hr@enterprise-corp.id', 'Recruit2026Secret', 'HR Specialist', '3201081995120002', '4111-2222-3333-4444', 15000000, 'FLAG{hr_p3rs0n4l_d4t4_3xp0s3d}'),
(5, 'Ahmad Fauzi (Junior Programmer)', 'ahmad_dev', 'ahmad.fauzi@enterprise-corp.id', 'kopi_hitam123', 'Junior Developer', '3302061999030007', '5100-3344-5566-7788', 8500000, 'FLAG{jun10r_d3v_w34k_p4ssw0rd}');

-- --------------------------------------------------------
-- 2. Table `comments`: Forum Komentar Publik (Target Stored XSS)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `author` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `message` TEXT NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL DEFAULT '127.0.0.1',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `comments` (`id`, `author`, `email`, `message`, `ip_address`, `created_at`) VALUES
(1, 'Security Operation Center', 'soc@enterprise-corp.id', 'Perhatian: Sistem audit aktif 24/7. Segala aktivitas pengujian diawasi oleh tim SOC.', '192.168.1.10', NOW()),
(2, 'Helpdesk Internal', 'helpdesk@enterprise-corp.id', 'Portal tiket bantuan aktif. Silakan lapor kendala teknis di sini.', '192.168.1.15', NOW());

-- --------------------------------------------------------
-- 3. Table `products`: Katalog E-Commerce Publik (Target UNION Injection)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `sku` VARCHAR(30) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `price` BIGINT(20) NOT NULL,
  `stock` INT(11) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`id`, `sku`, `name`, `price`, `stock`, `category`) VALUES
(1, 'PROD-SEC-001', 'Enterprise Hardware Security Token (FIDO2/WebAuthn)', 1250000, 45, 'Hardware Security'),
(2, 'PROD-SEC-002', 'Next-Gen Firewall Appliance Rackmount 1U', 35000000, 12, 'Network Appliance'),
(3, 'PROD-SRV-003', 'Dedicated Server Xeon Silver 64GB ECC RAM', 48000000, 8, 'Infrastructure'),
(4, 'PROD-EDU-004', 'Buku Panduan Offensive Security & Certified PenTester', 275000, 150, 'Education');
