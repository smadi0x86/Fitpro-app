<?php

namespace Smadi0x86wsl\Backend\Service;

class VerificationService {
    private $db;
    private $logger;
    private $mailer;

    public function __construct($dbConnection, $logger, $mailer) {
        $this->db = $dbConnection;
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    public function verifyEmailToken($token) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE verification_token = :token");
            $stmt->execute(['token' => $token]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user) {
                $updateStmt = $this->db->prepare("UPDATE users SET is_email_verified = TRUE WHERE id = :id");
                $updateStmt->execute(['id' => $user['id']]);
                $this->logger->info("Email verified for user: {$user['email']}");
                return true;
            } else {
                $this->logger->info("Invalid or expired email verification token.");
                return false;
            }
        } catch (\PDOException $e) {
            $this->logger->error("Database error in verifyEmailToken: " . $e->getMessage());
            return false;
        }
    }

    public function sendVerificationEmail($email, $token) {
        $verificationLink = "https://fitpro.smadi0x86.me/api/verify-email?token=" . urlencode($token);
        $subject = "Verify Your Email";
        $body = "Please click on the following link to verify your email: <a href='" . $verificationLink . "'>Verify Email</a>";

        try {
            $this->mailer->sendEmail($email, $subject, $body);
            $this->logger->info("Verification email sent to: {$email}");
        } catch (\Exception $e) {
            $this->logger->error("Error sending verification email: " . $e->getMessage());
        }
    }
}
