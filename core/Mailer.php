<?php

namespace Stackvel;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Stackvel Framework - Mailer Class
 * 
 * Provides email functionality using PHPMailer with support
 * for HTML emails and SMTP configuration.
 */
class Mailer
{
    /**
     * PHPMailer instance
     */
    private PHPMailer $mailer;

    /**
     * Mail configuration
     */
    private array $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = [
            'driver' => $_ENV['MAIL_MAILER'] ?? 'smtp',
            'host' => $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io',
            'port' => $_ENV['MAIL_PORT'] ?? 2525,
            'username' => $_ENV['MAIL_USERNAME'] ?? null,
            'password' => $_ENV['MAIL_PASSWORD'] ?? null,
            'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? null,
            'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'hello@example.com',
            'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Stackvel'
        ];

        $this->initializeMailer();
    }

    /**
     * Initialize PHPMailer
     */
    private function initializeMailer(): void
    {
        $this->mailer = new PHPMailer(true);

        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = !empty($this->config['username']);
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];

            // Default settings
            $this->mailer->setFrom($this->config['from_address'], $this->config['from_name']);
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';

        } catch (Exception $e) {
            throw new \Exception("Mailer initialization failed: " . $e->getMessage());
        }
    }

    /**
     * Send an email
     */
    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        try {
            // Clear previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Set recipients
            $this->mailer->addAddress($to);

            // Set CC recipients
            if (isset($options['cc'])) {
                foreach ((array) $options['cc'] as $cc) {
                    $this->mailer->addCC($cc);
                }
            }

            // Set BCC recipients
            if (isset($options['bcc'])) {
                foreach ((array) $options['bcc'] as $bcc) {
                    $this->mailer->addBCC($bcc);
                }
            }

            // Set subject and body
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            // Set plain text version
            if (isset($options['alt_body'])) {
                $this->mailer->AltBody = $options['alt_body'];
            } else {
                $this->mailer->AltBody = strip_tags($body);
            }

            // Add attachments
            if (isset($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    if (is_array($attachment)) {
                        $this->mailer->addAttachment($attachment['path'], $attachment['name'] ?? '');
                    } else {
                        $this->mailer->addAttachment($attachment);
                    }
                }
            }

            // Send the email
            return $this->mailer->send();

        } catch (Exception $e) {
            throw new \Exception("Email sending failed: " . $e->getMessage());
        }
    }

    /**
     * Send an email using a view template
     */
    public function sendView(string $to, string $subject, string $view, array $data = [], array $options = []): bool
    {
        $viewInstance = new \Stackvel\View();
        $body = $viewInstance->render($view, $data);

        return $this->send($to, $subject, $body, $options);
    }

    /**
     * Send a raw email
     */
    public function raw(string $to, string $subject, string $body, array $options = []): bool
    {
        $options['alt_body'] = $body;
        return $this->send($to, $subject, $body, $options);
    }

    /**
     * Send an email to multiple recipients
     */
    public function sendToMany(array $recipients, string $subject, string $body, array $options = []): array
    {
        $results = [];

        foreach ($recipients as $recipient) {
            try {
                $results[$recipient] = $this->send($recipient, $subject, $body, $options);
            } catch (\Exception $e) {
                $results[$recipient] = false;
            }
        }

        return $results;
    }

    /**
     * Send an email using a view template to multiple recipients
     */
    public function sendViewToMany(array $recipients, string $subject, string $view, array $data = [], array $options = []): array
    {
        $viewInstance = new \Stackvel\View();
        $body = $viewInstance->render($view, $data);

        return $this->sendToMany($recipients, $subject, $body, $options);
    }

    /**
     * Set the from address and name
     */
    public function from(string $address, string $name = ''): self
    {
        $this->mailer->setFrom($address, $name);
        return $this;
    }

    /**
     * Add a reply-to address
     */
    public function replyTo(string $address, string $name = ''): self
    {
        $this->mailer->addReplyTo($address, $name);
        return $this;
    }

    /**
     * Add an attachment
     */
    public function attach(string $path, string $name = ''): self
    {
        $this->mailer->addAttachment($path, $name);
        return $this;
    }

    /**
     * Set email priority
     */
    public function priority(int $priority): self
    {
        $this->mailer->Priority = $priority;
        return $this;
    }

    /**
     * Enable debug mode
     */
    public function debug(bool $enabled = true): self
    {
        if ($enabled) {
            $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
        } else {
            $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
        }
        return $this;
    }

    /**
     * Get mailer configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get PHPMailer instance
     */
    public function getMailer(): PHPMailer
    {
        return $this->mailer;
    }

    /**
     * Test email configuration
     */
    public function test(): bool
    {
        try {
            $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            $this->mailer->addAddress('test@example.com');
            $this->mailer->Subject = 'Test Email';
            $this->mailer->Body = 'This is a test email from Stackvel Framework.';
            
            return $this->mailer->send();
        } catch (Exception $e) {
            return false;
        }
    }
} 