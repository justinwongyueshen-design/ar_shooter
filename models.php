<?php
declare(strict_types=1);
require_once __DIR__ . '/api/models_lib.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_config'])) {
        $config = get_models_config();
        $config['enemy'] = trim($_POST['enemy'] ?? 'random');
        $config['weapon'] = trim($_POST['weapon'] ?? 'fps-akm.glb');
        if (save_models_config($config)) {
            $success = 'Model configuration updated successfully.';
        } else {
            $error = 'Failed to save configuration.';
        }
    } elseif (isset($_FILES['modelFile'])) {
        $file = $_FILES['modelFile'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            if ($file['size'] > 40 * 1024 * 1024) {
                $error = 'GLB file exceeds 40 MB limit.';
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($ext !== 'glb') {
                    $error = 'Invalid extension. Only .glb files are permitted.';
                } else {
                    // Verify GLB binary magic number: "glTF" (0x46546C67)
                    $handle = fopen($file['tmp_name'], 'rb');
                    $magic = fread($handle, 4);
                    fclose($handle);
                    if ($magic !== "glTF") {
                        $error = 'Invalid GLB file structure (Missing glTF magic bytes).';
                    } else {
                        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($file['name']));
                        $modelsDir = __DIR__ . '/assets/models/';
                        if (!is_dir($modelsDir)) {
                            mkdir($modelsDir, 0755, true);
                        }
                        $destination = $modelsDir . $filename;
                        if (file_exists($destination)) {
                            $filename = pathinfo($filename, PATHINFO_FILENAME) . '_' . time() . '.glb';
                            $destination = $modelsDir . $filename;
                        }
                        if (move_uploaded_file($file['tmp_name'], $destination)) {
                            $success = 'Uploaded ' . htmlspecialchars($filename) . ' successfully.';
                        } else {
                            $error = 'Failed to move uploaded model file.';
                        }
                    }
                }
            }
        } else {
            $error = 'Upload error code: ' . $file['error'];
        }
    }
}

$config = get_models_config();
$candidates = scan_models_directory();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Model Management - AR Game Shooter</title>
    <link rel="stylesheet" href="assets/css/game.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">🎯 AR GAME SHOOTER</a>
        <nav>
            <a href="index.php">Home</a>
            <a href="game.php">Play</a>
            <a href="models.php" class="active">Models</a>
            <a href="compile-target.php">Compile</a>
        </nav>
    </header>

    <div class="container">
        <div class="hero">
            <h1>Model Manager</h1>
            <p>Configure game enemies, weapons, and upload custom GLB assets.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="badge badge-warning" style="display:block; margin-bottom:1.5rem; padding:1rem; text-align:center;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="badge badge-success" style="display:block; margin-bottom:1.5rem; padding:1rem; text-align:center;"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="grid-2">
            <div class="card">
                <h3>🎮 Active Game Model Settings</h3>
                <form action="models.php" method="POST">
                    <div class="form-group">
                        <label>Enemy Model Selection</label>
                        <select name="enemy" class="form-control">
                            <option value="random" <?php echo ($config['enemy'] === 'random') ? 'selected' : ''; ?>>🎲 Random (Rotates through available enemies)</option>
                            <?php foreach ($candidates as $c): ?>
                                <option value="<?php echo htmlspecialchars($c); ?>" <?php echo ($config['enemy'] === $c) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>FPS Weapon Model Selection</label>
                        <select name="weapon" class="form-control">
                            <?php foreach ($candidates as $c): ?>
                                <option value="<?php echo htmlspecialchars($c); ?>" <?php echo ($config['weapon'] === $c) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" name="save_config" value="1" class="btn" style="width: 100%;">Save Model Configuration</button>
                </form>
            </div>

            <div class="card">
                <h3>📦 Upload GLB 3D Asset</h3>
                <form action="models.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Select .glb file (Max 40MB)</label>
                        <input type="file" name="modelFile" class="form-control" accept=".glb" required>
                    </div>
                    <button type="submit" class="btn btn-secondary" style="width: 100%;">Upload GLB Model</button>
                </form>

                <div style="margin-top: 1.5rem;">
                    <h4 style="font-size:0.9rem; color:var(--text-muted); margin-bottom:0.5rem;">Available Server Models:</h4>
                    <ul style="list-style: inside; color:var(--text-color); font-size:0.85rem; max-height: 120px; overflow-y:auto;">
                        <?php if (empty($candidates)): ?>
                            <li>No GLB models uploaded yet.</li>
                        <?php else: ?>
                            <?php foreach ($candidates as $c): ?>
                                <li><?php echo htmlspecialchars($c); ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>