<?php
declare(strict_types=1);
require_once __DIR__ . '/api/site_lib.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $baseUrl = trim($_POST['publicBaseUrl'] ?? '');
    $config = get_site_config();
    $config['publicBaseUrl'] = $baseUrl;
    save_site_config($config);
    header('Location: index.php?saved=1');
    exit;
}