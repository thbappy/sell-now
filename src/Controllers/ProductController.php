<?php

namespace SellNow\Controllers;

use SellNow\Foundation\Request;
use SellNow\Foundation\Response;
use SellNow\Services\ProductService;
use SellNow\Services\AuthService;
use SellNow\Security\Csrf;
use Twig\Environment;

/**
 * ProductController: Refactored to be thin
 * Responsibility: Only HTTP request/response handling
 * Delegates business logic to ProductService
 */
class ProductController
{
    private ProductService $productService;
    private AuthService $authService;
    private Environment $twig;
    private Response $response;

    public function __construct(ProductService $productService, AuthService $authService, Environment $twig)
    {
        $this->productService = $productService;
        $this->authService = $authService;
        $this->twig = $twig;
        $this->response = new Response();
    }

    public function create(Request $request): void
    {
        if (!$this->authService->isAuthenticated()) {
            $this->response->redirect('/login');
            return;
        }

        $html = $this->twig->render('products/add.html.twig', [
            'csrf_token' => Csrf::token(),
        ]);

        $this->response->html($html)->send();
    }

    public function store(Request $request): void
    {
        if (!$this->authService->isAuthenticated()) {
            $this->response->setStatusCode(401)->json(['error' => 'Unauthorized'])->send();
            return;
        }

        // Validate CSRF
        if (!Csrf::validateRequest()) {
            $this->response->setStatusCode(403)->json(['error' => 'Invalid CSRF token'])->send();
            return;
        }

        $user = $this->authService->getCurrentUser();
        $title = $request->post('title', '');
        $description = $request->post('description', '');
        $price = (float)$request->post('price', 0);
        $imageFile = $request->file('image');
        $productFile = $request->file('product_file');

        $result = $this->productService->createProduct(
            userId: $user->getId(),
            title: $title,
            description: $description,
            price: $price,
            imageFile: $imageFile,
            productFile: $productFile
        );

        if (!$result['success']) {
            $this->response->redirect('/products/add?error=Product creation failed');
            return;
        }

        $this->response->redirect('/dashboard');
    }

    public function index(Request $request): void
    {
        $products = $this->productService->getAllProducts();

        $html = $this->twig->render('products/index.html.twig', [
            'products' => array_map(fn($p) => $p->toArray(), $products),
        ]);

        $this->response->html($html)->send();
    }
}
