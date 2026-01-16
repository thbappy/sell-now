<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SellNow\Config\Database;
use SellNow\Foundation\Container;
use SellNow\Foundation\Request;
use SellNow\Foundation\Response;
use SellNow\Foundation\Router;
use SellNow\Models\Model;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

session_start();

// ============================================================
// 1. BOOTSTRAP & SETUP
// ============================================================

// Database connection
$database = Database::getInstance();
$db = $database->getConnection();
Model::setConnection($db);

// Setup Twig
$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader, ['debug' => true]);
$twig->addGlobal('session', $_SESSION);

// Setup DI Container
$container = Container::getInstance();

// Create request/response
$request = new Request();
$response = new Response();

// ============================================================
// 2. REGISTER SERVICES IN CONTAINER
// ============================================================

// Register Repositories
$container->bind('UserRepository', function() use ($db) {
    return new \SellNow\Repositories\UserRepository($db);
});

$container->bind('ProductRepository', function() use ($db) {
    return new \SellNow\Repositories\ProductRepository($db);
});

$container->bind('OrderRepository', function() use ($db) {
    return new \SellNow\Repositories\OrderRepository($db);
});

// Register Services
$container->bind('AuthService', function($c) {
    return new \SellNow\Services\AuthService($c->make('UserRepository'));
});

$container->bind('CartService', function() {
    return new \SellNow\Services\CartService();
});

$container->bind('FileUploadHandler', function() {
    return new \SellNow\Security\FileUploadHandler(__DIR__ . '/uploads');
});

$container->bind('ProductService', function($c) {
    return new \SellNow\Services\ProductService(
        $c->make('ProductRepository'),
        $c->make('FileUploadHandler')
    );
});

$container->bind('CheckoutService', function($c) {
    return new \SellNow\Services\CheckoutService(
        $c->make('OrderRepository'),
        $c->make('CartService')
    );
});

// Register Controllers
$container->bind('AuthController', function($c) use ($twig) {
    return new \SellNow\Controllers\AuthController(
        $c->make('AuthService'),
        $twig
    );
});

$container->bind('ProductController', function($c) use ($twig) {
    return new \SellNow\Controllers\ProductController(
        $c->make('ProductService'),
        $c->make('AuthService'),
        $twig
    );
});

$container->bind('CartController', function($c) use ($twig) {
    return new \SellNow\Controllers\CartController(
        $c->make('CartService'),
        $c->make('ProductService'),
        $twig
    );
});

$container->bind('CheckoutController', function($c) use ($twig) {
    return new \SellNow\Controllers\CheckoutController(
        $c->make('CheckoutService'),
        $c->make('AuthService'),
        $c->make('CartService'),
        $twig
    );
});

$container->bind('PublicController', function($c) use ($twig) {
    return new \SellNow\Controllers\PublicController(
        $c->make('UserRepository'),
        $c->make('ProductService'),
        $twig
    );
});

// ============================================================
// 3. SETUP ROUTER
// ============================================================

$router = new Router($request, $container);

// Public routes
$router->get('/', function($req) use ($twig, $container) {
    $products = $container->make('ProductService')->getAllProducts();
    $html = $twig->render('layouts/base.html.twig', [
        'content' => '<h1>Welcome to SellNow</h1><a href="/products" class="btn">Browse Products</a> <a href="/login" class="btn">Login</a>'
    ]);
    $response = new Response();
    $response->html($html)->send();
});

// Auth routes (MUST be before dynamic routes like /{username})
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

// Dashboard
$router->get('/dashboard', 'AuthController@dashboard');

// Product routes (MUST be before dynamic routes)
$router->get('/products', 'ProductController@index');
$router->get('/products/add', 'ProductController@create');
$router->post('/products/add', 'ProductController@store');

// Cart routes (MUST be before dynamic routes)
$router->get('/cart', 'CartController@index');
$router->post('/cart/add', 'CartController@add');
$router->get('/cart/clear', 'CartController@clear');

// Checkout routes (MUST be before dynamic routes)
$router->get('/checkout', 'CheckoutController@index');
$router->post('/checkout/process', 'CheckoutController@process');
$router->get('/payment', 'CheckoutController@payment');
$router->post('/payment/success', 'CheckoutController@success');

// Dynamic routes (MUST be last)
$router->get('/{username}', 'PublicController@profile');

// ============================================================
// 4. DISPATCH REQUEST
// ============================================================

$router->dispatch();

