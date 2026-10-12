<?php
declare(strict_types=1);
require_once __DIR__ . '/api/site_lib.php';

$currentUrl = get_current_url();
$gameUrl = $currentUrl . '/game.php';
$targetExists = file_exists(__DIR__ . '/assets/targets/targets.mind');
$targetImageExists = file_exists(__DIR__ . '/assets/targets/picture.jpg');
$siteConfig = get_site_config();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AR Game Shooter</title>
    <link rel="stylesheet" href="assets/css/game.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>
    <header>
        <a href="index.php" class="logo">🎯 AR GAME SHOOTER</a>
        <nav>
            <a href="index.php" class="active">Home</a>
            <a href="game.php">Play</a>
            <a href="models.php">Models</a>
            <a href="compile-target.php">Compile</a>
        </nav>
    </header>

    <div class="container">
        <div class="hero">
            <h1>AR GAME SHOOTER</h1>
            <p>Web AR · Immersive Image Target First-Person Shooter</p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="game.php" class="btn">🚀 Start Game</a>
                <a href="models.php" class="btn btn-secondary">⚙️ Change Models</a>
                <a href="compile-target.php" class="btn btn-secondary">📷 Target & Compile</a>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <h3>📱 Mobile Game Access</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">
                    Scan the QR code below using your smartphone camera to launch the AR game instantly.
                </p>
                <div id="qrcode" style="background: #fff; padding: 1rem; border-radius: 0.5rem; display: inline-block; margin-bottom: 1rem;"></div>
                <div class="form-group">
                    <label>Public Game URL</label>
                    <input type="text" class="form-control" id="gameUrlInput" value="<?php echo htmlspecialchars($gameUrl, ENT_QUOTES); ?>" readonly>
                </div>
                <form action="save-site.php" method="POST">
                    <div class="form-group">
                        <label>Custom Base URL Override (if hosting on LAN/Tunnel)</label>
                        <input type="text" class="form-control" name="publicBaseUrl" value="<?php echo htmlspecialchars($siteConfig['publicBaseUrl'] ?? '', ENT_QUOTES); ?>" placeholder="https://your-tunnel-url.ngrok-free.app">
                    </div>
                    <button type="submit" class="btn btn-secondary" style="width: 100%;">Save URL Configuration</button>
                </form>
            </div>

            <div class="card">
                <h3>🎯 AR Target Status</h3>
                <div style="margin-bottom: 1rem;">
                    <?php if ($targetExists): ?>
                        <span class="badge badge-success">Compiled · targets.mind ready</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Not compiled · Upload target image first</span>
                    <?php endif; ?>
                </div>

                <?php if ($targetImageExists): ?>
                    <div style="margin-bottom: 1rem; text-align: center;">
                        <a href="assets/targets/picture.jpg" target="_blank">
                            <img src="assets/targets/picture.jpg?v=<?php echo filemtime(__DIR__.'/assets/targets/picture.jpg'); ?>" alt="Target Preview" style="max-width: 100%; max-height: 200px; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                        </a>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Click image to view full screen</p>
                    </div>
                <?php endif; ?>

                <form action="upload-target.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Upload New Target Image (JPG, PNG, WEBP — Max 12MB)</label>
                        <input type="file" name="targetImage" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="compile_after" value="1" id="compile_after" checked>
                        <label for="compile_after" style="margin-bottom: 0;">Automatically compile target after upload</label>
                    </div>
                    <button type="submit" class="btn" style="width: 100%;">Upload & Process Target</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const gameUrl = document.getElementById('gameUrlInput').value;
        new QRCode(document.getElementById('qrcode'), {
            text: gameUrl,
            width: 160,
            height: 160,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
    </script>
</body>
</html>