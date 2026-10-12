<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/models_lib.php';

$config = get_models_config();
$candidates = scan_models_directory();

// Filter enemies vs weapons (weapons often contain 'fps' or 'rig' or 'akm')
$enemyCandidates = [];
$weaponCandidates = [];

foreach ($candidates as $c) {
    $lower = strtolower($c);
    if (strpos($lower, 'fps') !== false || strpos($lower, 'rig') !== false || strpos($lower, 'akm') !== false) {
        $weaponCandidates[] = $c;
    } else {
        $enemyCandidates[] = $c;
    }
}

// Fallbacks if empty
if (empty($enemyCandidates)) {
    $enemyCandidates = $candidates;
}
if (empty($weaponCandidates)) {
    $weaponCandidates = $candidates;
}

$selectedEnemy = $config['enemy'];
$selectedWeapon = $config['weapon'];

$enemyUrl = '';
if ($selectedEnemy === 'random') {
    if (!empty($enemyCandidates)) {
        $enemyUrl = 'assets/models/' . $enemyCandidates[array_rand($enemyCandidates)];
    }
} else {
    $enemyUrl = 'assets/models/' . $selectedEnemy;
}

$weaponUrl = 'assets/models/' . $selectedWeapon;
if (!file_exists(__DIR__ . '/../' . $weaponUrl) && !empty($weaponCandidates)) {
    $weaponUrl = 'assets/models/' . $weaponCandidates[0];
}

echo json_encode([
    'ok' => true,
    'config' => $config,
    'enemyRandom' => ($selectedEnemy === 'random'),
    'weaponRandom' => ($selectedWeapon === 'random'),
    'enemyCandidates' => $enemyCandidates,
    'weaponCandidates' => $weaponCandidates,
    'enemyUrl' => $enemyUrl,
    'weaponUrl' => $weaponUrl
], JSON_UNESCAPED_SLASHES);