<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

/**
 * User profile endpoints
 */
final class UserController
{
    private Response $response;
    private User $userModel;

    public function __construct()
    {
        $this->response = new Response();
        $this->userModel = new User();
    }

    public function profile(Request $request): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        $this->response->success(User::formatForResponse($user));
    }

    public function updateProfile(Request $request): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        $userId = (int) $user['id'];

        $body = $request->getBody();
        $fields = [];

        if (array_key_exists('name', $body)) {
            $fields['name'] = trim((string) $body['name']);
        }

        if (array_key_exists('email', $body)) {
            $email = trim((string) $body['email']);
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->response->error('Validation failed', 400, ['message' => 'Invalid email format']);
            }
            $fields['email'] = $email === '' ? null : $email;
        }

        if (array_key_exists('avatar', $body)) {
            $fields['avatar'] = $body['avatar'];
        }

        if (array_key_exists('newPassword', $body)) {
            $currentPassword = (string) $request->getBodyParam('currentPassword', '');
            $newPassword = (string) $body['newPassword'];

            if ($currentPassword === '' || !$this->userModel->verifyPassword($currentPassword, $user['password'])) {
                $this->response->error('Missing or invalid token', 401, ['message' => 'Current password is incorrect']);
            }

            if (strlen($newPassword) < 6) {
                $this->response->error('Validation failed', 400, ['message' => 'New password must be at least 6 characters']);
            }

            $fields['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        if (isset($fields['email']) && $fields['email'] !== null) {
            $existing = $this->userModel->findByEmail((string) $fields['email']);
            if ($existing !== null && (int) $existing['id'] !== $userId) {
                $this->response->error('Email already exists', 409);
            }
        }

        $updated = $this->userModel->updateById($userId, $fields);

        if ($updated === null) {
            $this->response->error('Failed to update profile', 500);
        }

        $this->response->success(User::formatForResponse($updated));
    }
}
