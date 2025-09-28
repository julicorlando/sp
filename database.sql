-- Sistema de Pacientes - Database Schema for PHP Migration
-- Migrated from Django models to pure MySQL

CREATE DATABASE IF NOT EXISTS stepsi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stepsi_db;

-- Users table (replaces Django's auth_user)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(150) NOT NULL UNIQUE,
    email VARCHAR(254) NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(150) DEFAULT '',
    last_name VARCHAR(150) DEFAULT '',
    is_active TINYINT(1) DEFAULT 1,
    date_joined DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Patients table (based on Django Paciente model)
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    data_nascimento DATE,
    cpf VARCHAR(11) NOT NULL UNIQUE,
    sexo VARCHAR(10) NOT NULL,
    email VARCHAR(254) NOT NULL,
    estado_civil VARCHAR(255) NOT NULL,
    filhos VARCHAR(10) NOT NULL,
    filhos_quantidade VARCHAR(10) DEFAULT '',
    atendimento VARCHAR(10) NOT NULL,
    atendimento_tipo_tempo_motivo VARCHAR(500) DEFAULT '',
    religiao VARCHAR(20) DEFAULT '',
    escolaridade VARCHAR(255) NOT NULL,
    trabalha_no_momento VARCHAR(10) NOT NULL,
    profissao VARCHAR(50) DEFAULT '',
    toma_algum_medicamento VARCHAR(10) NOT NULL,
    qual_medicamento VARCHAR(100) DEFAULT '',
    disponibilidade VARCHAR(100) DEFAULT '',
    rede_de_apoio VARCHAR(255) DEFAULT '',
    contato_de_emergencia VARCHAR(100) DEFAULT '',
    motivo_e_objetivo VARCHAR(500) DEFAULT '',
    observacoes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Payments table (based on Django Pagamento model)  
CREATE TABLE pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    data_pagamento DATE DEFAULT (CURDATE()),
    valor DECIMAL(10,2) NOT NULL,
    forma_pagamento VARCHAR(17) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
);

-- Files table (based on Django Arquivo model)
CREATE TABLE arquivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    arquivo_nome VARCHAR(255) NOT NULL,
    arquivo_path VARCHAR(500) NOT NULL,
    data_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
);

-- Evolutions table (based on Django Evolucao model)
CREATE TABLE evolucoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    conteudo TEXT NOT NULL,
    data DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_pacientes_usuario ON pacientes(usuario_id);
CREATE INDEX idx_pacientes_cpf ON pacientes(cpf);
CREATE INDEX idx_pagamentos_paciente ON pagamentos(paciente_id);
CREATE INDEX idx_arquivos_paciente ON arquivos(paciente_id);
CREATE INDEX idx_evolucoes_paciente ON evolucoes(paciente_id);
CREATE INDEX idx_users_username ON users(username);

-- Insert a default admin user (password: admin123 - should be changed in production)
-- Password hash for 'admin123' using PHP password_hash()
INSERT INTO users (username, email, password, first_name, last_name) VALUES 
('admin', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Sistema');