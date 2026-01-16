<?php

namespace SellNow\Controllers;

use SellNow\Foundation\Request;
use SellNow\Foundation\Response;
use SellNow\Repositories\UserRepository;
use SellNow\Services\ProductService;
use Twig\Environment;

/**
 * PublicController: Refactored
 * Responsibility: Handle public pages (seller profiles, product browsing)
 */
class PublicController
{
    private UserRepository $userRepository;
    private ProductService $productService;
    private Environment $twig;
    private Response $response;

    public function __construct(UserRepository $userRepository, ProductService $productService, Environment $twig)
    {
        $this->userRepository = $userRepository;
        $this->productService = $productService;
        $this->twig = $twig;
        $this->response = new Response();
    }

    public function profile(Request $request, string $username): void
    {
        $user = $this->userRepository->findByUsername($username);

        if (!$user) {
            $this->response->setStatusCode(404)->html('User not found')->send();
            return;
        }

        $products = $this->productService->getUserProducts($user->getId());

        $html = $this->twig->render('public/profile.html.twig', [
            'seller' => $user->toArray(),
            'products' => array_map(fn($p) => $p->toArray(), $products),
        ]);

        $this->response->html($html)->send();
    }

    public function index(Request $request): void
    {
        $products = $this->productService->getAllProducts();

        $html = $this->twig->render('public/index.html.twig', [
            'products' => array_map(fn($p) => $p->toArray(), $products),
        ]);

        $this->response->html($html)->send();
    }
}
