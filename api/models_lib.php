<?php
declare(strict_types=1);

function get_models_config(): array {
    $configFile = __DIR__ . '/../assets/config/models.json';
    $default = [
        'enemy' => 'random',
        'weapon' => 'fps-akm.glb',
        'updatedAt' => date('c')
    ];
    if (!file_exists($configFile)) {
        return $default;
    }
    $data = json_decode(file_get_contents($configFile), true);
    return is_array($data) ? array_merge($default, $data) : $default;
}

function save_models_config(array $config): bool {
    $configFile = __DIR__ . '/../assets/config/models.json';
    $dir = dirname($configFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $config['updatedAt'] = date('c');
    return file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT)) !== false;
}

function scan_models_directory(): array {
    $modelsDir = __DIR__ . '/../assets/models/';
    if (!is_dir($modelsDir)) {
        mkdir($modelsDir, 0755, true);
    }
    $files = scandir($modelsDir);
    $candidates = [];
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'glb') {
            $candidates[] = $file;
        }
    }
    return $candidates;
}