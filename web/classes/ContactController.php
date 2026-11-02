<?php
// classes/ContactController.php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/ContactMessage.php';
require_once __DIR__ . '/ContactRepository.php';

class ContactController
{
    private ?PDO $pdo;

    public bool   $success  = false;
    public string $error    = '';
    public array  $oldInput = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        if (($_POST['action'] ?? '') !== 'send_message') {
            return;
        }

        $msg = new ContactMessage($_POST);

        if (!$msg->validate()) {
            $this->error    = $msg->getFirstError();
            $this->oldInput = $msg->toArray();
            return;
        }

        if (!$this->pdo) {
            $this->error    = 'Database connection unavailable. Please try again later.';
            $this->oldInput = $msg->toArray();
            return;
        }

        try {
            (new ContactRepository($this->pdo))->save($msg);

            $this->success  = true;
            $this->oldInput = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
        } catch (Throwable $e) {
            error_log('ContactController insert failed: ' . $e->getMessage());
            $this->error    = 'Database error. Please try again later.';
            $this->oldInput = $msg->toArray();
        }
    }

    public function viewData(): array
    {
        return [
            'formSuccess' => $this->success,
            'formError' => $this->error,
            'oldInput' => $this->oldInput,
        ];
    }

}
