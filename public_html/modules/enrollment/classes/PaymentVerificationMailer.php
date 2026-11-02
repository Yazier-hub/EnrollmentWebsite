<?php
require_once __DIR__ . '/Requirement.php';

/** Sends the registrar's payment verification notice. */
final class PaymentVerificationMailer
{
    /**
     * Kunin ang Gmail App Password mula sa:
     *   1. Environment variable (GMAIL_APP_PASSWORD)
     *   2. config/secrets.php (kung existing)
     */
    private function getGmailAppPassword(): string {
        $pwd = getenv('GMAIL_APP_PASSWORD');
        if (is_string($pwd) && $pwd !== '') {
            return $pwd;
        }

        $secretsFile = __DIR__ . '/../config/secrets.php';
        if (is_file($secretsFile)) {
            $secrets = require $secretsFile;
            if (is_array($secrets) && !empty($secrets['gmail_app_password'])) {
                return (string) $secrets['gmail_app_password'];
            }
        }

        return '';
    }

    /**
     * Mag-email sa student kapag verified na ang payment.
     */
    public function send($studentId) {
        if ($studentId <= 0) return ['success' => false, 'message' => 'Invalid student ID'];

        try {
            $reqModel = new Requirement();
            $student  = $reqModel->getStudentForPaymentEmail($studentId);

            if (!$student || empty($student['email'])) {
                error_log("sendPaymentVerifiedEmail: no email for student {$studentId}");
                return ['success' => false, 'message' => 'No email on file'];
            }

            $fullName = trim(
                ($student['first_name']  ?? '')
                . ' ' . ($student['middle_name'] ?? '')
                . ' ' . ($student['surname']     ?? '')
                . ' ' . ($student['suffix']      ?? '')
            );
            if ($fullName === '') $fullName = 'Student';

            $subject = 'Payment Verified — You Can Now Self-Enroll';
            $body =
                "Dear {$fullName},\n\n" .
                "Good news! Your payment has been VERIFIED.\n\n" .
                "Student Number: " . ($student['student_number'] ?? 'N/A') . "\n" .
                "Course: " . ($student['course_code'] ?? 'N/A') . "\n\n" .
                "You can now proceed to self-enroll for the next semester.\n\n" .
                "Thank you,\n" .
                "Registrar's Office";

            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                $appPassword = $this->getGmailAppPassword();
                if (!$appPassword) {
                    error_log('sendPaymentVerifiedEmail: App password is empty');
                    return ['success' => false, 'message' => 'SMTP password not configured'];
                }

                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'villanuevabea034@gmail.com';
                    $mail->Password   = $appPassword;
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('villanuevabea034@gmail.com', 'Bestlink College Contact Center');
                    $mail->addAddress($student['email'], $fullName);

                    $mail->Subject = $subject;
                    $mail->Body    = $body;

                    $mail->send();
                    return ['success' => true, 'message' => "Email sent to {$student['email']}"];
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                    error_log('PHPMailer error: ' . $mail->ErrorInfo);
                    return ['success' => false, 'message' => 'SMTP Error: ' . $mail->ErrorInfo];
                }
            }

            $headers = "From: Registrar Office <noreply@yourdomain.com>\r\n"
                     . "Reply-To: noreply@yourdomain.com\r\n"
                     . "MIME-Version: 1.0\r\n"
                     . "Content-Type: text/plain; charset=UTF-8\r\n";
            $sent = @mail($student['email'], $subject, $body, $headers);

            return $sent
                ? ['success' => true,  'message' => "Email sent to {$student['email']}"]
                : ['success' => false, 'message' => 'mail() returned false'];
        } catch (\Throwable $e) {
            error_log("sendPaymentVerifiedEmail error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

}
