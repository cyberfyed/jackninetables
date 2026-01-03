# Claude Code Notes - Jack Nine Tables

## Important Rules

**Always provide the relative filepath (from site root) of any modified files after each edit/feature change.**

Example format:
```
dashboard.php
admin/index.php
classes/Order.php
```

---

## Site Functionality Overview

### Purpose
Custom poker table builder and ordering system. Users design tables and request quotes, admin reviews and sends pricing, users pay deposits via PayPal.

### User Flow
1. User registers/logs in
2. Designs a custom poker table in the builder (`builder.php`)
3. Saves design (`my-designs.php`)
4. Requests a quote - creates an order
5. Admin sends price quote
6. User pays deposit via PayPal (`pay-deposit.php`)
7. Table is built
8. Admin sends final invoice
9. User pays remaining balance (`pay-final.php`)
10. Order complete

### Order Statuses
- `quote_started` - User requested quote, awaiting admin pricing
- `price_sent` - Admin sent quote, awaiting deposit payment
- `deposit_paid` - Deposit received, table in production
- `invoice_sent` - Table ready, awaiting final payment
- `paid_in_full` - Complete

### Key Files

| File | Purpose |
|------|---------|
| `builder.php` | Table design tool |
| `dashboard.php` | User dashboard with pending quotes banner |
| `my-designs.php` | User's saved designs |
| `my-orders.php` | User's orders/quotes list |
| `pay-deposit.php` | PayPal deposit payment |
| `pay-final.php` | PayPal final balance payment |
| `contact.php` | Contact form |
| `admin/index.php` | Admin dashboard |
| `admin/orders.php` | Admin order management |
| `classes/Order.php` | Order model |
| `classes/TableDesign.php` | Design model |
| `classes/PayPal.php` | PayPal integration |
| `config/config.php` | Site configuration |

### Tech Stack
- PHP (vanilla)
- MySQL database
- PayPal SDK for payments
- Custom CSS (`assets/css/style.css`)

---

## Gotchas & Coding Patterns

### Order Lookups
- `pay-deposit.php` and `pay-final.php` expect `?id=NUMERIC_ID` (not `?order=ORDER_NUMBER`)
- Always pass `$_SESSION['user_id']` when fetching orders to prevent unauthorized access
- Example: `$orderModel->getById($orderId, $_SESSION['user_id'])`

### Security
- Use `sanitize()` for all user output (XSS prevention)
- Use `getCSRFToken()` in forms and validate with POST requests
- Use `requireLogin()` at top of protected pages
- Use `requireAdmin()` for admin-only pages
- Validate redirects to prevent open redirect vulnerabilities

### Flash Messages
- Set: `setFlash('error', 'Message')` or `setFlash('success', 'Message')`
- Display is handled automatically in header.php

### Timezone
- Site uses `America/Chicago` timezone (set in config.php)
- Always use PHP date functions, not MySQL NOW() for consistency

### Database
- Use prepared statements: `$conn->prepare()` with `execute([$params])`
- Design data is stored as JSON in `design_data` column, auto-decoded by Order class

### Common Bugs Fixed (Don't Reintroduce)
1. Links using `?order=ORDER_NUMBER` when page expects `?id=NUMERIC_ID`
2. Timezone mismatch between PHP and MySQL for password reset tokens
3. Open redirect in login.php - always validate redirect URLs
4. Missing email verification checks before sensitive actions
