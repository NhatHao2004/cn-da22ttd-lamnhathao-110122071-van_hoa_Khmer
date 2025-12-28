<?php
require_once __DIR__ . '/language.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLang() ?>" class="<?= getCurrentLang() === 'km' ? 'lang-km' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= __('site_name') ?> - Nền tảng số hóa và bảo tồn văn hóa Khmer Nam Bộ">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?><?= __('site_name') ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/chatbot.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/favicon.png">
    
    <!-- Additional Scripts -->
    <script src="<?= BASE_URL ?>/assets/js/components.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/auth.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/chatbot.js" defer></script>
</head>
<body class="<?= getCurrentLang() === 'km' ? 'lang-km' : '' ?>">
    <?= displayFlashMessage() ?>
