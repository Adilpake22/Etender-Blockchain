CREATE DATABASE IF NOT EXISTS etender CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE etender;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','bidder','evaluator') DEFAULT 'bidder',
    company_name VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tenders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    budget DECIMAL(15,2) NOT NULL,
    deadline DATETIME NOT NULL,
    reveal_deadline DATETIME NOT NULL,
    status ENUM('open','closed','awarded') DEFAULT 'open',
    document_path VARCHAR(255) DEFAULT NULL,
    tx_hash VARCHAR(66) DEFAULT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tender_id INT NOT NULL,
    bidder_id INT NOT NULL,
    bid_hash VARCHAR(64) NOT NULL,
    amount DECIMAL(15,2) DEFAULT NULL,
    technical_score INT DEFAULT NULL,
    financial_score INT DEFAULT NULL,
    final_score DECIMAL(5,2) DEFAULT NULL,
    commit_tx_hash VARCHAR(66) DEFAULT NULL,
    reveal_tx_hash VARCHAR(66) DEFAULT NULL,
    status ENUM('committed','revealed','evaluated','awarded','rejected') DEFAULT 'committed',
    notes TEXT DEFAULT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    revealed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (tender_id) REFERENCES tenders(id),
    FOREIGN KEY (bidder_id) REFERENCES users(id),
    UNIQUE KEY unique_bid (tender_id, bidder_id)
);

CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(100) NOT NULL,
    actor_id INT DEFAULT NULL,
    record_type VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    tx_hash VARCHAR(66) DEFAULT NULL,
    details TEXT DEFAULT NULL,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Demo users (password: password)
INSERT INTO users (name, email, password, role, company_name) VALUES
('System Admin', 'admin@etender.gov.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Government'),
('Evaluator One', 'evaluator@etender.gov.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'evaluator', 'Government'),
('ABC Construction Ltd', 'abc@construction.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bidder', 'ABC Construction Ltd'),
('XYZ Infra Pvt Ltd', 'xyz@infra.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bidder', 'XYZ Infra Pvt Ltd');

-- Demo tenders
INSERT INTO tenders (title, description, category, budget, deadline, reveal_deadline, status, tx_hash, created_by) VALUES
('Road Construction Project NH-48', 'Construction of 12km stretch of National Highway 48 including drainage and lighting', 'Infrastructure', 45000000.00, DATE_ADD(NOW(), INTERVAL 30 DAY), DATE_ADD(NOW(), INTERVAL 35 DAY), 'open', '0xabc123def456abc123def456abc123def456abc123def456abc123def456abc1', 1),
('Smart City IoT Sensors', 'Supply and installation of 500 IoT sensors across city zones', 'Technology', 8500000.00, DATE_ADD(NOW(), INTERVAL 15 DAY), DATE_ADD(NOW(), INTERVAL 20 DAY), 'open', '0xdef456abc123def456abc123def456abc123def456abc123def456abc123def4', 1);
