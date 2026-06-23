-- Script de criação do banco "auth_seguro" (MySQL/MariaDB).
-- Equivalente ao resultado de `php artisan migrate` — fornecido aqui
-- como referência/entregável, conforme pedido. Em uso real, prefira
-- rodar as migrations do Laravel (database/migrations/), que são a
-- fonte da verdade e ficam versionadas junto do código.

CREATE DATABASE IF NOT EXISTS auth_seguro
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE auth_seguro;

-- Usuário dedicado da aplicação, com privilégios só no próprio banco
-- (nunca usar o usuário root da instância MySQL na aplicação).
CREATE USER IF NOT EXISTS 'auth_seguro_app'@'localhost' IDENTIFIED BY 'TROQUE_ESTA_SENHA';
GRANT ALL PRIVILEGES ON auth_seguro.* TO 'auth_seguro_app'@'localhost';
FLUSH PRIVILEGES;

-- Usuários da aplicação.
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    password VARCHAR(255) NOT NULL, -- hash argon2id, nunca texto claro
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Auditoria de tentativas de login (base da proteção contra brute force).
-- Sem FK pra users: precisa registrar tentativas mesmo com e-mail
-- inexistente, pra detectar enumeração de contas.
CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL, -- suporta IPv6
    successful TINYINT(1) NOT NULL DEFAULT 0,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY login_attempts_email_created_at_index (email, created_at),
    KEY login_attempts_ip_address_created_at_index (ip_address, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tokens de redefinição de senha (mecanismo nativo do Laravel — só o
-- HASH do token fica salvo aqui, nunca o token em texto claro).
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessões em banco (SESSION_DRIVER=database) — permite invalidar
-- sessões de um usuário específico e funciona em ambiente multi-servidor.
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) NOT NULL,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    PRIMARY KEY (id),
    KEY sessions_user_id_index (user_id),
    KEY sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
