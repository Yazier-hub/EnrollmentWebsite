<?php

final class ContactRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(ContactMessage $message): void
    {
        $this->ensureTableExists();
        $this->store($message);
    }

    private function ensureTableExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `enr_contact_messages` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(150) NOT NULL,
                `email` varchar(150) NOT NULL,
                `subject` varchar(255) NOT NULL,
                `message` text NOT NULL,
                `is_read` tinyint(1) NOT NULL DEFAULT 0,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `idx_is_read` (`is_read`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    private function store(ContactMessage $msg): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO `enr_contact_messages` (name, email, subject, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $msg->name,
            $msg->email,
            $msg->subject,
            $msg->message,
        ]);
    }

}
