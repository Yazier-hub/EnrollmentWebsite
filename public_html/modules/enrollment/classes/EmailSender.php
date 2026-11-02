<?php
/**
 * EmailSender.php — All-in-one version
 * Path: public_html/classes/EmailSender.php
 *
 * Lahat ng kailangan ay nasa file na ito:
 * - SMTP credentials from environment or local config
 * - PHPMailer setup
 * - Error handling
 * - Logging
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSender
{
    // ============================================================
    // CREDENTIALS — PALITAN ANG PASSWORD KUNG MAY BAGO
    // ============================================================
    private $smtpHost     = 'smtp.gmail.com';
    private $smtpPort     = 587;
    private $smtpSecure   = 'tls';  // 'tls' para 587, 'ssl' para 465
    private $smtpUsername = 'villanuevabea034@gmail.com';
    private $smtpPassword = '';  // <-- PALITAN ITO kung may bagong App Password
    private $fromEmail    = 'villanuevabea034@gmail.com';
    private $fromName     = 'Bestlink College Enrollment System';
    private $portalUrl    = 'https://www.bestlink.edu.ph/';

    // Debug: 0 = off, 2 = verbose (para makita ang SMTP errors)
    private $debug = 0;

    private $logFile;

    public function __construct()
    {
        $smtpPassword = getenv('GMAIL_APP_PASSWORD');
        if (!is_string($smtpPassword) || $smtpPassword === '') {
            $secretsFile = __DIR__ . '/../config/secrets.php';
            $secrets = is_file($secretsFile) ? require $secretsFile : [];
            $smtpPassword = is_array($secrets) ? (string) ($secrets['gmail_app_password'] ?? '') : '';
        }
        $this->smtpPassword = $smtpPassword;

        // Log file
        $this->logFile = __DIR__ . '/../logs/email_log.txt';
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        // Alisin ang spaces sa App Password (kung meron)
        $this->smtpPassword = str_replace(' ', '', $this->smtpPassword);
    }

    // ============================================================
    // PUBLIC METHODS
    // ============================================================

    /**
     * Mag-send ng account credentials sa student.
     *
     * @return array{success: bool, message: string}
     */
    public function sendAccountEmail($studentName, $studentNumber, $password, $recipientEmail)
    {
        $recipientEmail = trim($recipientEmail);

        if ($recipientEmail === '') {
            return $this->fail('Student email address is empty.', $studentNumber, '');
        }

        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('Invalid student email: ' . $recipientEmail, $studentNumber, $recipientEmail);
        }

        if (empty($this->smtpUsername) || empty($this->smtpPassword)) {
            return $this->fail('SMTP credentials missing.', $studentNumber, $recipientEmail);
        }

        $subject      = 'Welcome to Bestlink College - Your Student Account';
        $htmlMessage  = $this->buildHtmlMessage($studentName, $studentNumber, $password);
        $plainMessage = $this->buildPlainMessage($studentName, $studentNumber, $password);

        $result = $this->sendWithPHPMailer($recipientEmail, $subject, $htmlMessage, $plainMessage);

        $this->logEmail(
            $recipientEmail,
            $studentNumber,
            $result['success'] ? 'sent' : 'failed',
            $result['message']
        );

        return $result;
    }

    /**
     * Para sa testing lang.
     */
    public function sendTestEmail($recipient)
    {
        return $this->sendAccountEmail(
            'Test Student',
            'TEST001',
            'TestPass123!',
            $recipient
        );
    }

    // ============================================================
    // SENDING
    // ============================================================

    private function sendWithPHPMailer($recipient, $subject, $htmlMessage, $plainMessage)
    {
        $mail = new PHPMailer(true);

        try {
            // SMTP setup
            $mail->isSMTP();
            $mail->Host       = $this->smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtpUsername;
            $mail->Password   = $this->smtpPassword;
            $mail->Port       = $this->smtpPort;
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 30;
            $mail->SMTPDebug  = $this->debug;

            $mail->Debugoutput = function ($str, $level) {
                error_log('[PHPMailer] ' . trim($str));
            };

            // Encryption
            if ($this->smtpPort === 465 || $this->smtpSecure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            // Para sa XAMPP/localhost na may cert issue, i-uncomment ang 3 lines sa ibaba:
            // $mail->SMTPOptions = [
            //     'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
            // ];

            // Headers
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($recipient);
            $mail->addReplyTo($this->fromEmail, $this->fromName);

            // Body
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlMessage;
            $mail->AltBody = $plainMessage;

            $mail->send();

            return [
                'success' => true,
                'message' => 'Email sent to ' . $recipient,
            ];

        } catch (Exception $e) {
            $error = $mail->ErrorInfo ?: $e->getMessage();
            error_log('[EmailSender] PHPMailer error: ' . $error);
            return [
                'success' => false,
                'message' => 'SMTP Error: ' . $error,
            ];
        } catch (Throwable $e) {
            error_log('[EmailSender] Unexpected: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Unexpected error: ' . $e->getMessage(),
            ];
        }
    }

    // ============================================================
    // MESSAGE BUILDERS
    // ============================================================

    private function buildHtmlMessage($studentName, $studentNumber, $password)
    {
        $studentName   = htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8');
        $studentNumber = htmlspecialchars($studentNumber, ENT_QUOTES, 'UTF-8');
        $password      = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
        $portalUrl     = htmlspecialchars($this->portalUrl, ENT_QUOTES, 'UTF-8');
        $year          = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Account</title>
<style>
body { margin:0; padding:0; background:#f4f6f8; font-family:Arial,sans-serif; color:#333; }
.container { max-width:600px; margin:30px auto; background:#fff; border-radius:10px; overflow:hidden; border:1px solid #ddd; }
.header { background:#1a3c6e; color:#fff; padding:30px; text-align:center; }
.header h1 { margin:0; font-size:26px; }
.content { padding:30px; }
.credentials { background:#f1f7ff; border-left:5px solid #1a3c6e; padding:20px; margin:20px 0; }
.row { margin:12px 0; }
.label { font-weight:bold; }
.value { display:inline-block; background:#eee; padding:6px 10px; border-radius:4px; font-family:monospace; }
.warning { background:#fff3cd; border:1px solid #ffc107; padding:15px; border-radius:5px; }
.button { display:inline-block; background:#1a3c6e; color:#fff !important; text-decoration:none; padding:12px 25px; border-radius:5px; margin-top:15px; }
.footer { text-align:center; padding:20px; font-size:12px; color:#777; border-top:1px solid #ddd; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Bestlink College</h1>
        <p>Student Enrollment System</p>
    </div>
    <div class="content">
        <p>Dear <strong>{$studentName}</strong>,</p>
        <p>Congratulations! Your application has been approved and your student account has been created.</p>
        <div class="credentials">
            <h3>Your Login Credentials</h3>
            <div class="row"><span class="label">Student Number:</span> <span class="value">{$studentNumber}</span></div>
            <div class="row"><span class="label">Password:</span> <span class="value">{$password}</span></div>
        </div>
        <div class="warning">
            <strong>Security Notice</strong>
            <ul>
                <li>Do not share your password.</li>
                <li>Change your password after logging in.</li>
                <li>Keep your account credentials secure.</li>
            </ul>
        </div>
        <p style="text-align:center;"><a href="{$portalUrl}" class="button">Access Student Portal</a></p>
        <p>If you have questions, please contact the Admissions Office.</p>
        <p>Best regards,<br><strong>Bestlink College Enrollment System</strong></p>
    </div>
    <div class="footer">
        This is an automated email. Please do not reply.<br><br>
        &copy; {$year} Bestlink College
    </div>
</div>
</body>
</html>
HTML;
    }

    private function buildPlainMessage($studentName, $studentNumber, $password)
    {
        return "BESTLINK COLLEGE\n"
             . "Student Enrollment System\n\n"
             . "Dear {$studentName},\n\n"
             . "Congratulations!\n\n"
             . "Your application has been approved and your student account has been created.\n\n"
             . "LOGIN CREDENTIALS\n"
             . "------------------------------\n"
             . "Student Number: {$studentNumber}\n"
             . "Password: {$password}\n"
             . "------------------------------\n\n"
             . "SECURITY NOTICE\n"
             . "- Do not share your password.\n"
             . "- Change your password after logging in.\n"
             . "- Keep your account credentials secure.\n\n"
             . "Student Portal:\n{$this->portalUrl}\n\n"
             . "Best regards,\n\n"
             . "Bestlink College Enrollment System\n\n"
             . "This is an automated email.";
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function fail($message, $studentNumber, $recipient)
    {
        $this->logEmail($recipient, $studentNumber, 'failed', $message);
        return ['success' => false, 'message' => $message];
    }

    private function logEmail($recipient, $studentNumber, $status, $detail = '')
    {
        try {
            $timestamp = date('Y-m-d H:i:s');
            $entry = "[{$timestamp}] Student: {$studentNumber} | Email: {$recipient} | Status: {$status}";
            if ($detail !== '') {
                $entry .= " | Detail: {$detail}";
            }
            $entry .= PHP_EOL;

            file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
        } catch (Throwable $e) {
            error_log('[EmailSender] Log error: ' . $e->getMessage());
        }
    }
}