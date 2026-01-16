<?php

namespace SellNow\Services;

use SellNow\Models\Product;
use SellNow\Repositories\ProductRepository;
use SellNow\Security\Validator;
use SellNow\Security\FileUploadHandler;

/**
 * ProductService: Business logic for products
 * Responsibility: Create, update, list products
 */
class ProductService
{
    private ProductRepository $productRepository;
    private Validator $validator;
    private FileUploadHandler $fileHandler;

    public function __construct(ProductRepository $productRepository, FileUploadHandler $fileHandler)
    {
        $this->productRepository = $productRepository;
        $this->validator = new Validator();
        $this->fileHandler = $fileHandler;
    }

    /**
     * Create a new product
     */
    public function createProduct(
        int $userId,
        string $title,
        string $description,
        float $price,
        ?array $imageFile = null,
        ?array $productFile = null
    ): array {
        // Validate inputs
        $this->validator->clearErrors();
        $this->validator->validateTitle($title);
        $this->validator->validatePrice($price);

        if ($this->validator->hasErrors()) {
            return [
                'success' => false,
                'errors' => $this->validator->getErrors(),
            ];
        }

        // Handle file uploads
        $imagePath = '';
        $filePath = '';

        if ($imageFile && $imageFile['error'] !== UPLOAD_ERR_NO_FILE) {
            $imagePath = $this->fileHandler->handleImageUpload($imageFile);
            if (!$imagePath) {
                return [
                    'success' => false,
                    'errors' => ['image' => 'Invalid image file'],
                ];
            }
        }

        if ($productFile && $productFile['error'] !== UPLOAD_ERR_NO_FILE) {
            $filePath = $this->fileHandler->handleProductFileUpload($productFile);
            if (!$filePath) {
                return [
                    'success' => false,
                    'errors' => ['file' => 'Invalid product file'],
                ];
            }
        }

        // Create product
        $product = new Product(
            user_id: $userId,
            title: $title,
            description: $description,
            price: $price,
            image_path: $imagePath,
            file_path: $filePath,
            is_active: true
        );

        try {
            $productId = $this->productRepository->create($product);
            $product = $this->productRepository->findById($productId);

            return [
                'success' => true,
                'product' => $product,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => 'Failed to create product: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Get products by user
     */
    public function getUserProducts(int $userId): array
    {
        return $this->productRepository->findByUserId($userId);
    }

    /**
     * Get a single product
     */
    public function getProduct(int $productId): ?Product
    {
        return $this->productRepository->findById($productId);
    }

    /**
     * Get all active products
     */
    public function getAllProducts(): array
    {
        return $this->productRepository->findActive();
    }

    /**
     * Check if user owns product
     */
    public function canModifyProduct(int $productId, int $userId): bool
    {
        return $this->productRepository->belongsToUser($productId, $userId);
    }
}
