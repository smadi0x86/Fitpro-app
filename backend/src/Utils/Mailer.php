<?php

namespace Smadi0x86wsl\Backend\Utils;

use SendGrid\Mail\Mail;
use SendGrid;

/**
 * Mailer class handles the email sending functionalities using SendGrid.
 *
 * TODO for Production:
 * - Remove the dependency on PHPMailer and local SMTP like MailHog.
 * - Use SendGrid's official PHP library for sending emails.
 * - Store the SendGrid API key in an environment variable for security.
 */
class Mailer {
    private $sendgrid;

    /**
     * Constructor for the Mailer class.
     * Initializes the SendGrid object with the API key from the environment variable.
     */
    public function __construct() {
        $apiKey = file_get_contents('/usr/src/app/sendgrid.env');
        $this->sendgrid = new SendGrid(trim($apiKey));
    }

    /**
     * Sends an email using SendGrid.
     *
     * @param string $to The recipient's email address.
     * @param string $subject The subject of the email.
     * @param string $body The HTML body of the email.
     */
    public function sendEmail($to, $subject, $body) {
        $email = new Mail();
        $email->setFrom('smadixd@gmail.com', 'Mailer');
        $email->setSubject($subject);
        $email->addTo($to);
        $email->addContent('text/html', $body);

        try {
            $response = $this->sendgrid->send($email);
            // Log the response status code, headers, body, etc. for debugging purposes
            global $logger;
            $logger->info('Email sent: ' . $response->statusCode());
            $logger->info('Headers: ' . json_encode($response->headers()));
            $logger->info('Body: ' . $response->body());
        } catch (\Exception $e) {
            global $logger;
            $logger->error('SendGrid Error: ' . $e->getMessage());
        }
    }
}
