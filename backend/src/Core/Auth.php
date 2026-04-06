<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Authentication helpers
 */
final class Auth
{
    /**
     * Authenticate request and return current user.
     */
    public static function user(Request $request, Response $response, User $userModel): array
    {
        $token = $request->getBearerToken();

        if ($token === null || $token === '') {
            $response->error('Missing or invalid token', 401);
        }

        $payload = JwtHandler::decode($token);

        if ($payload === null || !isset($payload->id)) {
            $response->error('Missing or invalid token', 401);
        }

        $userId = (int) $payload->id;
        $user = $userModel->findById($userId);

        if ($user === null) {
            $response->error('Missing or invalid token', 401);
        }

        return $user;
    }

    /**
     * Enforce admin role.
     */
    public static function requireAdmin(array $user, Response $response): void
    {
        if (($user['role'] ?? 'user') !== 'admin') {
            $response->error('Forbidden', 403);
        }
    }
}
