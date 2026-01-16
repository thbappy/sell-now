<?php

namespace SellNow\Controllers;

use SellNow\Foundation\Request;
use SellNow\Foundation\Response;
use SellNow\Services\AuthService;
use SellNow\Security\Csrf;
use Twig\Environment;

/**
 * AuthController: Refactored to be thin
 * Responsibility: Only HTTP request/response handling
 * Delegates business logic to AuthService
 */
class AuthController
{
    private AuthService $authService;
    private Environment $twig;
    private Response $response;

    public function __construct(AuthService $authService, Environment $twig)
    {
        $this->authService = $authService;
        $this->twig = $twig;
        $this->response = new Response();
    }

    public function loginForm(Request $request): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->response->redirect('/dashboard');
        }

        $html = $this->twig->render('auth/login.html.twig', [
            'csrf_token' => Csrf::token(),
        ]);

        $this->response->html($html)->send();
    }

    public function login(Request $request): void
    {
        // Validate CSRF token
        if (!Csrf::validateRequest()) {
            $this->response->setStatusCode(403)->html('Invalid CSRF token')->send();
            return;
        }

        $email = $request->post('email', '');
        $password = $request->post('password', '');

        $result = $this->authService->login($email, $password);

        if (!$result['success']) {
            // TODO: Pass errors to view
            $this->response->redirect('/login?error=' . urlencode('Invalid credentials'));
            return;
        }

        $this->authService->startSession($result['user']);
        $this->response->redirect('/dashboard');
    }

    public function registerForm(Request $request): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->response->redirect('/dashboard');
        }

        $html = $this->twig->render('auth/register.html.twig', [
            'csrf_token' => Csrf::token(),
        ]);

        $this->response->html($html)->send();
    }

    public function register(Request $request): void
    {
        // Validate CSRF token
        if (!Csrf::validateRequest()) {
            $this->response->setStatusCode(403)->html('Invalid CSRF token')->send();
            return;
        }

        $email = $request->post('email', '');
        $username = $request->post('username', '');
        $fullName = $request->post('fullname', '');
        $password = $request->post('password', '');

        $result = $this->authService->register($email, $username, $fullName, $password);

        if (!$result['success']) {
            // TODO: Pass errors to view
            $this->response->redirect('/register?error=Registration failed');
            return;
        }

        $this->response->redirect('/login?msg=Registered successfully');
    }

    public function dashboard(Request $request): void
    {
        if (!$this->authService->isAuthenticated()) {
            $this->response->redirect('/login');
        }

        $user = $this->authService->getCurrentUser();

        $html = $this->twig->render('dashboard.html.twig', [
            'user' => $user,
            'username' => $user?->getUsername() ?? '',
        ]);

        $this->response->html($html)->send();
    }

    public function logout(Request $request): void
    {
        $this->authService->logout();
        $this->response->redirect('/');
    }
}
