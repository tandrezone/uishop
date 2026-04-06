<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\JwtHandler;
use App\Models\User;

/**
 * Authentication controller
 */
final class AuthController
{
    private User $userModel;
    private Response $response;

    public function __construct()
    {
        $this->userModel = new User();
        $this->response = new Response();
    }

    /**
     * Handle login request
     */
    public function login(Request $request): void
    {
        //print_r($request->getBody());
        // Get request body
        $username = $request->getBodyParam('username');
        $password = $request->getBodyParam('password');

        // Validate input
        if (empty($username) || empty($password)) {
            $this->response->error('Username and password are required', 400);
        }

        // Find user
        $user = $this->userModel->findByUsername($username);

        if ($user === null) {
            $this->response->error('Invalid username or password', 401);
        }

        // Verify password
        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            $this->response->error('Invalid username or password', 401);
        }

        // Generate token
        $token = JwtHandler::encode([
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'sub' => $user['id'],
        ]);

        $this->response->success([
            'token' => $token,
            'user' => User::formatForResponse($user),
        ], 200);
    }

    /**
     * Handle register request
     */
    public function register(Request $request): void
    {
        // Get request body
        $username = $request->getBodyParam('username');
        $password = $request->getBodyParam('password');
        $email = $request->getBodyParam('email');

        // Validate input
        if (empty($username) || empty($password)) {
            $this->response->error('Username and password are required', 400);
        }

        if (strlen($username) < 3 || strlen($username) > 20) {
            $this->response->error('Username must be between 3 and 20 characters', 400);
        }

        if (strlen($password) < 6) {
            $this->response->error('Password must be at least 6 characters', 400);
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->response->error('Invalid email format', 400);
        }

        // Check if username already exists
        if ($this->userModel->findByUsername($username) !== null) {
            $this->response->error('Username already exists', 409);
        }

        // Check if email already exists
        if (!empty($email) && $this->userModel->findByEmail($email) !== null) {
            $this->response->error('Email already exists', 409);
        }

        // Create user
        $user = $this->userModel->create($username, $password, $email);

        if ($user === null) {
            $this->response->error('Failed to create user', 500);
        }

        // Generate token
        $token = JwtHandler::encode([
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'sub' => $user['id'],
        ]);

        $this->response->success([
            'token' => $token,
            'user' => User::formatForResponse($user),
        ], 201);
    }
}
