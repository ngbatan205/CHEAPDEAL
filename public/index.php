<?php

require_once __DIR__ . '/../app/config/config.php';
require_once APP_ROOT . '/core/Database.php';
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Router.php';

foreach (glob(APP_ROOT . '/models/*.php') as $model) {
    require_once $model;
}

foreach (glob(APP_ROOT . '/controllers/*.php') as $controller) {
    require_once $controller;
}

$router = new Router();

$router->get('/', [PackageController::class, 'index']);
$router->get('/packages', [PackageController::class, 'index']);
$router->get('/package', [PackageController::class, 'detail']);

$router->get('/combos', [DealController::class, 'index']);
$router->get('/combo', [DealController::class, 'detail']);

$router->get('/offers', [OfferController::class, 'index']);

$router->get('/enquiry', [EnquiryController::class, 'create']);
$router->post('/enquiry', [EnquiryController::class, 'store']);
$router->get('/messages', [CustomerController::class, 'messages']);

$router->get('/checkout', [OrderController::class, 'checkout']);
$router->post('/checkout', [OrderController::class, 'store']);

$router->get('/order/success', [OrderController::class, 'success']);

$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/add-deal', [CartController::class, 'addDeal']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);

$router->get('/payment', [PaymentController::class, 'payment']);
$router->post('/payment', [PaymentController::class, 'store']);

/* ===========================
   Authentication
=========================== */

$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'authenticate']);

$router->get(
    '/forgot-password',
    [AuthController::class, 'forgotPassword']
);

$router->post(
    '/forgot-password/verify',
    [AuthController::class, 'verifyResetAccount']
);

$router->post(
    '/forgot-password/reset',
    [AuthController::class, 'resetPassword']
);

$router->get('/register', [AuthController::class, 'register']);
$router->post('/register', [AuthController::class, 'store']);

$router->get(
    '/register/success',
    [AuthController::class, 'registrationSuccess']
);

$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

/* ===========================
   Customer
=========================== */

$router->get('/account', [CustomerController::class, 'account']);
$router->get('/bill', [CustomerController::class, 'bill']);
$router->get('/subscription', [CustomerController::class, 'subscription']);
$router->post('/subscription/update', [CustomerController::class, 'updateSubscription']);

$router->get('/profile', [CustomerController::class, 'profile']);
$router->post('/profile', [CustomerController::class, 'update']);
$router->post(
    '/profile/payment',
    [CustomerController::class, 'updatePayment']
);

$router->post(
    '/profile/password',
    [CustomerController::class, 'changePassword']
);
/* ===========================
   CRM
=========================== */

$router->get('/crm', [CRMController::class, 'dashboard']);
$router->get('/crm/packages', [CRMController::class, 'packages']);
$router->get('/crm/orders', [CRMController::class, 'orders']);
$router->get('/crm/subscription-changes', [CRMController::class, 'subscriptionChanges']);
$router->get('/crm/customers', [CRMController::class, 'customers']);
$router->post('/crm/customers/create', [CRMController::class, 'createCustomer']);
$router->get('/crm/customer', [CRMController::class, 'customerDetail']);
$router->get('/crm/records', [CRMController::class, 'records']);
$router->get('/crm/record', [CRMController::class, 'recordDetail']);
$router->get('/crm/telephone-order', [CRMController::class, 'telephoneOrder']);
$router->post('/crm/telephone-order/verify', [CRMController::class, 'verifyTelephoneCustomer']);
$router->post('/crm/telephone-order/create-customer', [CRMController::class, 'createTelephoneCustomer']);
$router->post('/crm/telephone-order/place', [CRMController::class, 'placeTelephoneOrder']);
$router->post('/crm/telephone-order/cancel', [CRMController::class, 'cancelTelephoneOrder']);
$router->get('/crm/enquiries', [CRMController::class, 'enquiries']);
$router->get('/crm/enquiries/customer', [CRMController::class, 'customerEnquiries']);
$router->post('/crm/enquiries/reply', [CRMController::class, 'replyEnquiry']);
$router->get('/crm/package/add', [CRMController::class, 'addPackage']);
$router->get('/crm/package/edit', [CRMController::class, 'editPackage']);
$router->post('/crm/package/save', [CRMController::class, 'savePackage']);
$router->post('/crm/package/delete', [CRMController::class, 'deletePackage']);
$router->post('/crm/package/reactivate', [CRMController::class, 'reactivatePackage']);
$router->get('/crm/offers', [AdminOfferController::class, 'index']);
$router->get('/crm/offer/add', [AdminOfferController::class, 'add']);
$router->get('/crm/offer/edit', [AdminOfferController::class, 'edit']);
$router->post('/crm/offer/save', [AdminOfferController::class, 'save']);
$router->post('/crm/offer/archive', [AdminOfferController::class, 'archive']);
$router->post('/crm/offer/reactivate', [AdminOfferController::class, 'reactivate']);


$router->post(
    '/profile/payment/add',
    [CustomerController::class, 'addPaymentMethod']
);
$router->post(
    '/profile/payment/verify',
    [CustomerController::class, 'verifyPaymentMethod']
);

$router->post(
    '/profile/payment/delete',
    [CustomerController::class, 'deletePaymentMethod']
);

$router->post(
    '/profile/payment/default',
    [CustomerController::class, 'setDefaultPaymentMethod']
);

$router->dispatch();
