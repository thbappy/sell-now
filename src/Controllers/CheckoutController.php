<?php

namespace SellNow\Controllers;

use SellNow\Foundation\Request;
use SellNow\Foundation\Response;
use SellNow\Services\CheckoutService;
use SellNow\Services\AuthService;
use SellNow\Services\CartService;
use SellNow\Payments\PaymentGatewayFactory;
use Twig\Environment;

/**
 * CheckoutController: Refactored to be thin
 * Responsibility: Only HTTP request/response handling
 * Delegates business logic to CheckoutService and PaymentGateway
 */
class CheckoutController
{
    private CheckoutService $checkoutService;
    private AuthService $authService;
    private CartService $cartService;
    private Environment $twig;
    private Response $response;

    public function __construct(
        CheckoutService $checkoutService,
        AuthService $authService,
        CartService $cartService,
        Environment $twig
    ) {
        $this->checkoutService = $checkoutService;
        $this->authService = $authService;
        $this->cartService = $cartService;
        $this->twig = $twig;
        $this->response = new Response();
    }

    public function index(Request $request): void
    {
        if ($this->cartService->isEmpty()) {
            $this->response->redirect('/cart');
            return;
        }

        $total = $this->cartService->getCartTotal();
        $providers = ['Stripe', 'PayPal', 'Razorpay'];

        $html = $this->twig->render('checkout/index.html.twig', [
            'total' => $total,
            'providers' => $providers,
        ]);

        $this->response->html($html)->send();
    }

    public function process(Request $request): void
    {
        if ($this->cartService->isEmpty()) {
            $this->response->redirect('/cart');
            return;
        }

        if (!$this->authService->isAuthenticated()) {
            $this->response->redirect('/login');
            return;
        }

        $provider = $request->post('provider', 'Unknown');
        $total = $this->cartService->getCartTotal();

        // Create order
        $user = $this->authService->getCurrentUser();
        $orderResult = $this->checkoutService->createOrder($user->getId(), $provider);

        if (!$orderResult['success']) {
            $this->response->redirect('/checkout?error=Failed to create order');
            return;
        }

        $order = $orderResult['order'];

        // Redirect to payment page
        $this->response->redirect('/payment?order_id=' . $order->getId() . '&provider=' . urlencode($provider));
    }

    public function payment(Request $request): void
    {
        $orderId = (int)$request->get('order_id', 0);
        $provider = $request->get('provider', 'Unknown');

        if ($orderId <= 0) {
            $this->response->redirect('/cart');
            return;
        }

        $html = $this->twig->render('checkout/payment.html.twig', [
            'order_id' => $orderId,
            'provider' => $provider,
            'total' => $this->cartService->getCartTotal(),
        ]);

        $this->response->html($html)->send();
    }

    public function success(Request $request): void
    {
        if (!$this->authService->isAuthenticated()) {
            $this->response->redirect('/login');
            return;
        }

        $orderId = (int)$request->post('order_id', 0);
        $provider = $request->post('provider', 'Unknown');

        if ($orderId > 0) {
            $transactionId = 'TXN_' . time() . '_' . random_int(1000, 9999);
            $this->checkoutService->completePayment($orderId, $transactionId);
        }

        $html = $this->twig->render('layouts/base.html.twig', [
            'content' => "<h1>Thank you for your purchase!</h1><p>Payment via $provider successful.</p><a href='/dashboard' class='btn btn-primary'>Go to Dashboard</a>"
        ]);

        $this->response->html($html)->send();
    }
}
