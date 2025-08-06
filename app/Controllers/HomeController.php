<?php

namespace App\Controllers;

use App\Models\User;
use Stackvel\Request;

/**
 * Home Controller
 * 
 * Handles home page and general site functionality.
 */
class HomeController extends Controller
{
    /**
     * Display the home page
     */
    public function index(): string
    {
        $data = [
            'title' => 'Welcome to Stackvel Framework',
            'description' => 'Minimal MVC. Maximum Control.',
            'features' => [
                'Lightweight and Fast',
                'Secure by Default',
                'Eloquent-style ORM',
                'Blade Templating',
                'Email Support',
                'Easy to Extend'
            ]
        ];

        return $this->view('home.index', $data);
    }

    /**
     * Display the about page
     */
    public function about(): string
    {
        $data = [
            'title' => 'About Stackvel Framework',
            'version' => $this->app->version(),
            'description' => 'A lightweight, secure PHP MVC framework inspired by Laravel.'
        ];

        return $this->view('home.about', $data);
    }

    /**
     * Display the contact page
     */
    public function contact(): string
    {
        $data = [
            'title' => 'Contact Us',
            'errors' => $this->getErrors(),
            'old' => [] // We'll handle old input in the view
        ];

        return $this->view('home.contact', $data);
    }

    /**
     * Handle contact form submission
     */
    public function sendContact(): void
    {
        $data = $this->input();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|string|min:2',
            'email' => 'required|email',
            'message' => 'required|string|min:10'
        ]);

        if (!empty($errors)) {
            $this->redirect('/contact');
            return;
        }

        // Send email
        $emailData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message']
        ];

        $sent = $this->sendEmailView(
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'admin@example.com',
            'New Contact Form Submission',
            'emails.contact',
            $emailData
        );

        if ($sent) {
            $this->flash('success', 'Thank you for your message! We will get back to you soon.');
        } else {
            $this->flash('error', 'Sorry, there was an error sending your message. Please try again.');
        }

        $this->redirect('/contact');
    }

    /**
     * Display system information
     */
    public function info(): array
    {
        return $this->json([
            'framework' => 'Stackvel',
            'version' => $this->app->version(),
            'php_version' => PHP_VERSION,
            'environment' => $_ENV['APP_ENV'] ?? 'production',
            'debug' => $_ENV['APP_DEBUG'] ?? false,
            'database' => [
                'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql',
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'database' => $_ENV['DB_DATABASE'] ?? 'stackvel'
            ],
            'mail' => [
                'driver' => $_ENV['MAIL_MAILER'] ?? 'smtp',
                'host' => $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io'
            ]
        ]);
    }

    /**
     * Example method showing Request parameter usage
     */
    public function requestExample(Request $request): array
    {
        dd($request->all());
    }
} 