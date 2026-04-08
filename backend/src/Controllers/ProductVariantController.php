<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

/**
 * Product variant endpoints
 */
final class ProductVariantController
{
    private Response $response;
    private ProductVariant $variantModel;
    private Product $productModel;
    private User $userModel;

    public function __construct()
    {
        $this->response = new Response();
        $this->variantModel = new ProductVariant();
        $this->productModel = new Product();
        $this->userModel = new User();
    }

    public function index(Request $request, string $productId): void
    {
        Auth::user($request, $this->response, $this->userModel);

        $pid = (int) $productId;
        if ($pid <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid product id']);
        }

        $product = $this->productModel->findById($pid);
        if ($product === null) {
            $this->response->error('Product not found', 404);
        }

        $variants = $this->variantModel->findByProductId($pid);

        $this->response->success([
            'variants' => array_map(static fn(array $v): array => ProductVariant::formatForResponse($v), $variants),
            'total' => count($variants),
        ]);
    }

    public function create(Request $request, string $productId): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        Auth::requireAdmin($user, $this->response);

        $pid = (int) $productId;
        if ($pid <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid product id']);
        }

        $product = $this->productModel->findById($pid);
        if ($product === null) {
            $this->response->error('Product not found', 404);
        }

        $name = trim((string) $request->getBodyParam('name', ''));
        $price = $request->getBodyParam('price');
        $stock = $request->getBodyParam('stock', 0);

        if ($name === '') {
            $this->response->error('Validation failed', 400, ['message' => 'Variant name is required']);
        }

        if (!is_numeric($price) || (float) $price <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Price must be greater than 0']);
        }

        if (!is_numeric($stock) || (int) $stock < 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Stock must be 0 or greater']);
        }

        $attributes = $request->getBodyParam('attributes');
        if ($attributes !== null && !is_array($attributes)) {
            $this->response->error('Validation failed', 400, ['message' => 'Attributes must be an object']);
        }

        $variant = $this->variantModel->create($pid, [
            'name' => $name,
            'sku' => $request->getBodyParam('sku'),
            'price' => (float) $price,
            'stock' => (int) $stock,
            'attributes' => $attributes,
        ]);

        if ($variant === null) {
            $this->response->error('Failed to create variant', 500);
        }

        $this->response->success(ProductVariant::formatForResponse($variant), 201);
    }

    public function update(Request $request, string $productId, string $id): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        Auth::requireAdmin($user, $this->response);

        $pid = (int) $productId;
        if ($pid <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid product id']);
        }

        $variantId = (int) $id;
        if ($variantId <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid variant id']);
        }

        $product = $this->productModel->findById($pid);
        if ($product === null) {
            $this->response->error('Product not found', 404);
        }

        $existing = $this->variantModel->findById($variantId);
        if ($existing === null || (int) $existing['product_id'] !== $pid) {
            $this->response->error('Variant not found', 404);
        }

        $body = $request->getBody();
        $fields = [];

        if (array_key_exists('name', $body)) {
            $name = trim((string) $body['name']);
            if ($name === '') {
                $this->response->error('Validation failed', 400, ['message' => 'Variant name cannot be empty']);
            }
            $fields['name'] = $name;
        }

        if (array_key_exists('sku', $body)) {
            $fields['sku'] = $body['sku'];
        }

        if (array_key_exists('price', $body)) {
            if (!is_numeric($body['price']) || (float) $body['price'] <= 0) {
                $this->response->error('Validation failed', 400, ['message' => 'Price must be greater than 0']);
            }
            $fields['price'] = (float) $body['price'];
        }

        if (array_key_exists('stock', $body)) {
            if (!is_numeric($body['stock']) || (int) $body['stock'] < 0) {
                $this->response->error('Validation failed', 400, ['message' => 'Stock must be 0 or greater']);
            }
            $fields['stock'] = (int) $body['stock'];
        }

        if (array_key_exists('attributes', $body)) {
            $attributes = $body['attributes'];
            if ($attributes !== null && !is_array($attributes)) {
                $this->response->error('Validation failed', 400, ['message' => 'Attributes must be an object']);
            }
            $fields['attributes'] = $attributes !== null ? json_encode($attributes) : null;
        }

        $updated = $this->variantModel->update($variantId, $fields);
        if ($updated === null) {
            $this->response->error('Variant not found', 404);
        }

        $this->response->success(ProductVariant::formatForResponse($updated));
    }

    public function delete(Request $request, string $productId, string $id): void
    {
        $user = Auth::user($request, $this->response, $this->userModel);
        Auth::requireAdmin($user, $this->response);

        $pid = (int) $productId;
        if ($pid <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid product id']);
        }

        $variantId = (int) $id;
        if ($variantId <= 0) {
            $this->response->error('Validation failed', 400, ['message' => 'Invalid variant id']);
        }

        $product = $this->productModel->findById($pid);
        if ($product === null) {
            $this->response->error('Product not found', 404);
        }

        $existing = $this->variantModel->findById($variantId);
        if ($existing === null || (int) $existing['product_id'] !== $pid) {
            $this->response->error('Variant not found', 404);
        }

        $this->variantModel->delete($variantId);
        $this->response->noContent();
    }
}
