CREATE DATABASE IF NOT EXISTS meu_banco
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE meu_banco;

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pendente', 'em andamento', 'concluída') DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tasks (title, description, status) VALUES
('Configurar ambiente', 'Instalar PHP, Composer e CodeIgniter 4', 'concluída'),
('Criar o CRUD de tarefas', 'Implementar Model, Controller e Views', 'em andamento'),
('Escrever a documentação', 'Detalhar instalação e uso da API', 'pendente');
