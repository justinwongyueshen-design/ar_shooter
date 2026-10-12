<?php
declare(strict_types=1);

function get_site_config(): array {
    $configFile = __DIR__ . '/../assets/config/site.json';
    if (!file_exists($configFile)) {
        return ['publicBaseUrl' => ''];
    }
    $content = file_get_contents($configFile);
    $data = json_decode($content, true);
    return is_array($data) ? $data : ['publicBaseUrl' => ''];
}

function save_site_config(array $data): bool {
    $configFile = __DIR__ . '/../assets/config/site.json';
    $dir = dirname($configFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return file_put_contents($configFile, json_encode($data, JSON_PRETTY_PRINT)) !== false;
}

function get_current_url(): string {
    $config = get_site_config();
    if (!empty($config['publicBaseUrl'])) {
        return rtrim($config['publicBaseUrl'], '/');
    }
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME']);
    $script = rtrim($script, '/\\');
    return $protocol . $host . $script;
}