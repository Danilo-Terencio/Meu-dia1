-- ============================================================
-- MeuDia+ | Banco de dados (MySQL)
-- Importe este arquivo no phpMyAdmin ou via: mysql -u root -p < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS meudia_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE meudia_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Dados de cada usuário (tarefas, agenda, finanças, etc.)
-- Cada linha pertence a UM usuário, por isso as contas não se misturam
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS dados_usuario (
    usuario_id INT NOT NULL,
    chave VARCHAR(50) NOT NULL,
    valor LONGTEXT NOT NULL,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, chave),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Controle de tentativas de login erradas (limita tentativas)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS login_tentativas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email, criado_em),
    INDEX idx_ip (ip, criado_em)
) ENGINE=InnoDB;
