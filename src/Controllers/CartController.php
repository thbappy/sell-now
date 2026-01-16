<?php

namespace SellNow\Controllers;

use SellNow\Foundation\Request;
use SellNow\Foundation\Response;
use SellNow\Services\CartService;
use SellNow\Services\ProductService;
use Twig\Environment;

/**
 * CartController: Refactored to be thin
 * Responsibility: Only HTTP request/response handling
 * Delegates business logic to CartService
 */
class CartController
{
    private CartService $cartService;
    private ProductService $productService;
    private Environment $twig;
    private Response $response;

    public function __construct(CartService $cartService, ProductService $productService, Environment $twig)
    {
        $this->cartService = $cartService;
        $this->productService = $productService;
        $this->twig = $twig;
        $this->response = new Response();
    }

    public function index(Request $request): void
    {
        $cartItems = $this->cartService->getCartItems();
        $total = $this->cartService->getCartTotal();

        $html = $this->twig->render('cart/index.html.twig', [
            'cart' => $cartItems,
            'total' => $total,
            'count' => $this->cartService->getCartCount(),
        ]);

        $this->response->html($html)->send();
    }

    public function add(Request $request): void
    {
        $productId = (int)$request->post('product_id', 0);
        $quantity = (int)$request->post('quantity', 1);

        if ($productId <= 0 || $quantity <= 0) {
            $this->response->json(['status' => 'error', 'message' => 'Invalid product or quantity'])->send();
            return;
        }

        $product = $this->productService->getProduct($productId);

        if (!$product) {
            $this->response->json(['status' => 'error', 'message' => 'Product not found'])->send();
            return;
        }

        $this->cartService->addToCart(
            $product->getId(),
            $product->getTitle(),
            $product->getPrice(),
            $quantity
        );

        $this->response->json([
            'status' => 'success',
            'count' => $this->cartService->getCartCount(),
            'total' => $this->cartService->getCartTotal(),
        ])->send();
    }

    public function clear(Request $request): void
    {
        $this->cartService->clearCart();
        $this->response->redirect('/cart');
    }
}
