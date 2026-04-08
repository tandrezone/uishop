<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Supplier;
use App\Models\User;

/**
 * Supplier endpoints
 */
final class SupplierController
{
    private Response $response;
    private Supplier $supplierModel;
    private User $userModel;

    public function __construct()
    {
        $this->response = new Response();
        $this->supplierModel = new Supplier();
        $this->userModel = new User();
    }

    public function index(Request $request): void
    {
        Auth::user($request, $this->response, $this->userModel);

        $search = $request->getQueryParam('search');
        $limit = max(1, min(100, (int) $request->getQueryParam('limit', 20)));
        $offset = max(0, (int) $request->getQueryParam('offset', 0));

        $result = $this->supplierModel->findAll(is_string($search) ? $search : null, $limit, $offset);

        $this->response->success([
            'suppliers' => array_map(static fn(array $s): array => Supplier::formatForResponse($s), $result['suppliers']),
            'total' => $result['total'],
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function show(Request $request, string $id): void
    {
        Auth::user($request, $this->response, $this->userModel);

        $supplierId = (int) $id;
        if ($supplierId <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid supplier id']);
        }

        $supplier = $this->supplierModel->findById($supplierId);
        if ($supplier === null) {
            $this->response->error('Supplier not found', 404);
        }

        $this->response->success(Supplier::formatForResponse($supplier));
    }

    public function create(Request $request): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        Auth::requireAdmin($user, $this->response);

        $name = trim((string) $request->getBodyParam('name', ''));

        if ($name === '') {
            $this->response->error('Validation failed', 400, ['message' => 'Name is required']);
        }

        $globalRating = $request->getBodyParam('globalRating');
        if ($globalRating !== null) {
            if (!is_numeric($globalRating) || (float) $globalRating < 0 || (float) $globalRating > 5) {
                $this->response->error('Validation failed', 400, ['message' => 'Global rating must be between 0 and 5']);
            }
        }

        $supplier = $this->supplierModel->create([
            'name' => $name,
            'global_rating' => $globalRating !== null ? (float) $globalRating : null,
            'comments' => $request->getBodyParam('comments'),
        ]);

        if ($supplier === null) {
            $this->response->error('Failed to create supplier', 500);
        }

        $this->response->success(Supplier::formatForResponse($supplier), 201);
    }

    public function update(Request $request, string $id): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        Auth::requireAdmin($user, $this->response);

        $supplierId = (int) $id;
        if ($supplierId <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid supplier id']);
        }

        $existing = $this->supplierModel->findById($supplierId);
        if ($existing === null) {
            $this->response->error('Supplier not found', 404);
        }

        $body = $request->getBody();
        $fields = [];

        if (array_key_exists('name', $body)) {
            $name = trim((string) $body['name']);
            if ($name === '') {
                $this->response->error('Validation failed', 400, ['message' => 'Name cannot be empty']);
            }
            $fields['name'] = $name;
        }

        if (array_key_exists('globalRating', $body)) {
            $globalRating = $body['globalRating'];
            if ($globalRating !== null) {
                if (!is_numeric($globalRating) || (float) $globalRating < 0 || (float) $globalRating > 5) {
                    $this->response->error('Validation failed', 400, ['message' => 'Global rating must be between 0 and 5']);
                }
                $fields['global_rating'] = (float) $globalRating;
            } else {
                $fields['global_rating'] = null;
            }
        }

        if (array_key_exists('comments', $body)) {
            $fields['comments'] = $body['comments'];
        }

        $updated = $this->supplierModel->update($supplierId, $fields);
        if ($updated === null) {
            $this->response->error('Supplier not found', 404);
        }

        $this->response->success(Supplier::formatForResponse($updated));
    }

    public function delete(Request $request, string $id): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        Auth::requireAdmin($user, $this->response);

        $supplierId = (int) $id;
        if ($supplierId <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid supplier id']);
        }

        $existing = $this->supplierModel->findById($supplierId);
        if ($existing === null) {
            $this->response->error('Supplier not found', 404);
        }

        $this->supplierModel->delete($supplierId);
        $this->response->noContent();
    }
}
