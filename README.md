# SellNow - Digital Marketplace (Refactored)

A **modern, secure, and scalable** PHP application that has been transformed from a raw prototype into enterprise-grade code.

## 🎯 What This Project Demonstrates

### Architecture Excellence

- ✅ Clean, layered architecture
- ✅ Dependency Injection Container
- ✅ Repository Pattern
- ✅ Service Layer (reusable business logic)
- ✅ Dynamic Router (pattern-based routing)

### Security Implementation

- ✅ bcrypt Password hashing
- ✅ CSRF token protection
- ✅ SQL Injection prevention
- ✅ Input validation & sanitization
- ✅ Secure file upload handling

### Design Patterns

- ✅ Strategy Pattern (Payment gateways)
- ✅ Factory Pattern
- ✅ Repository Pattern
- ✅ Singleton Pattern
- ✅ Dependency Injection

### PHP 8.x Best Practices

- ✅ Strict type hints
- ✅ Named arguments
- ✅ Proper exception handling

---

## 🚀 Quick Start

### Requirements

- PHP 8.0+
- Composer
- SQLite 3 (or MySQL)

### Setup

```bash
# Install dependencies
composer install

# Database initialize (SQLite)
sqlite3 database/database.sqlite < database/schema.sql

# Start server
php -S localhost:8000 -t public
```

### To Use MySQL

Set environment variables:

```
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=sellnow
DB_USER=root
DB_PASSWORD=yourpassword
```

---

## 📖 Detailed Documentation

See **[ARCHITECTURE.md](ARCHITECTURE.md)** for complete design documentation.

Topics included:

- 🔍 Audit the original code
- 📐 Design decisions
- 🔒 Security implementation
- 📊 Data modeling
- ⚡ Performance considerations
- 📈 Scalability strategy

---

## 🔑 Key Features

### User Authentication

```php
$authService->register($email, $username, $fullname, $password);
$authService->login($email, $password);
```

### Product Management

```php
$productService->createProduct($userId, $title, $description, $price, $image, $file);
$productService->getUserProducts($userId);
```

### Shopping Cart

```php
$cartService->addToCart($productId, $title, $price, $quantity);
$cartService->getCartTotal();
```

### Checkout and Orders

```php
$checkoutService->createOrder($userId, $paymentProvider);
$checkoutService->completePayment($orderId, $transactionId);
```

---

## 🏗️ প্রকল্প কাঠামো

```
src/
├── Foundation/          # Core framework
│   ├── Request.php
│   ├── Response.php
│   ├── Router.php
│   └── Container.php
├── Models/              # Domain entities
│   ├── User.php
│   ├── Product.php
│   └── Order.php
├── Repositories/        # Data access layer
│   ├── UserRepository.php
│   ├── ProductRepository.php
│   └── OrderRepository.php
├── Services/            # Business logic
│   ├── AuthService.php
│   ├── ProductService.php
│   ├── CartService.php
│   └── CheckoutService.php
├── Controllers/         # HTTP handlers (thin)
│   ├── AuthController.php
│   ├── ProductController.php
│   ├── CartController.php
│   ├── CheckoutController.php
│   └── PublicController.php
├── Security/            # Security utilities
│   ├── Validator.php
│   ├── Csrf.php
│   └── FileUploadHandler.php
└── Payments/            # Payment abstraction
    ├── PaymentGatewayInterface.php
    └── MockPaymentGateway.php
```

---

## 🔒 Security Features

- **Password**: bcrypt hashing
- **CSRF**: Token verification
- **SQL**: Prepared statements everywhere
- **Input**: Complete validation
- **Files**: MIME type and size verification
- **Sessions**: Secure handling

---

## 🔌 Payment Gateway Extension

নতুন provider যোগ করুন:

```php
class StripeGateway implements PaymentGatewayInterface {
    public function charge($amount) { ... }
    public function verify($transactionId) { ... }
}

PaymentGatewayFactory::register('stripe', new StripeGateway());
```

---

## 📊 Database

### Setup:

```bash
sqlite3 database/database.sqlite < database/schema.sql
```

### Tables:

- `users` - User accounts
- `products` - Sellable products
- `orders` - Customer orders
- `payment_providers` - Payment setup

---

## 🧪 Testing Ready

```php
// Services are easy to test
$mockRepository = $this->createMock(UserRepository::class);
$service = new AuthService($mockRepository);
$result = $service->register(...);
```

---

## 📝 Code Quality

- Type hints: ✅
- Validation: ✅
- Error handling: ✅
- Comments: ✅
- No die()/exit: ✅

---

## 📚 More Information

- [Full Architecture Docs](ARCHITECTURE.md)
- [PHP 8.0+ Features](https://www.php.net/releases/8.0/)
- [Security Best Practices](https://owasp.org/www-project-top-ten/)

---

**Setup complete. Start the server and visit `http://localhost:8000`.**

```bash
php -S localhost:8000 -t public
```

🚀 Happy coding!
