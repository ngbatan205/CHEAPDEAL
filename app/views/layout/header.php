<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="description"
        content="Compare mobile, broadband and tablet plans with clear monthly pricing from CheapDeals."
    >

    <meta name="theme-color" content="#006d77">

    <title>
        <?= e($title ?? APP_NAME) ?> | <?= APP_NAME ?>
    </title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <!-- Google Font: Inter -->
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <!-- CheapDeals custom CSS -->
    <link
        rel="stylesheet"
        href="<?= url('/css/style.css') ?>?v=20260801.4"
    >

    <!-- Consolidated design-system layer -->
    <link
        rel="stylesheet"
        href="<?= url('/css/clean-system.css') ?>?v=20260801.4"
    >
</head>

<?php
$currentPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
) ?: '/';

$isAdminArea = str_contains($currentPath, '/crm');
?>

<body class="<?= $isAdminArea ? 'admin-page' : 'store-page' ?>">
<a class="skip-link" href="#main-content">Skip to main content</a>
