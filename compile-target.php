<?php
declare(strict_types=1);
$targetImageExists = file_exists(__DIR__ . '/assets/targets/picture.jpg');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compile AR Target - AR Game Shooter</title>
    <link rel="stylesheet" href="assets/css/game.css">
    <!-- MindAR Image Compiler CDN -->
    <script type="module" src="https://cdn.jsdelivr.net/npm/mind-ar@1.2.5/dist/mindar-image.prod.js"></script>
</head>
<body>
    <header>
        <a href="index.php" class="logo">🎯 AR GAME SHOOTER</a>
        <nav>
            <a href="index.php">Home</a>
            <a href="game.php">Play</a>
            <a href="models.php">Models</a>
            <a href="compile-target.php" class="active">Compile</a>
        </nav>
    </header>

    <div class="container" style="max-width: 600px;">
        <div class="card" style="text-align: center;">
            <h3>MindAR Target Compiler</h3>
            <p style="color: var::text-muted; margin-bottom: 1.5rem;">Compile your uploaded target image into a binary `targets.mind` file directly in your browser without Node.js.</p>

            <?php if (!$targetImageExists): ?>
                <div class="badge badge-warning" style="margin-bottom: 1rem; padding: 0.75rem;">No target image found! Please upload one on the home page first.</div>
                <br><a href="index.php" class="btn">Go to Home</a>
            <?php else: ?>
                <div style="margin-bottom: 1.5rem;">
                    <img id="targetImg" src="assets/targets/picture.jpg?v=<?php echo time(); ?>" style="max-width: 250px; border-radius: 0.5rem; border: 1px solid var(--border-color);" crossorigin="anonymous">
                </div>

                <div id="statusBox" style="font-weight: 700; color: var(--accent-gold); margin-bottom: 1.5rem;">Ready to compile...</div>

                <button id="compileBtn" class="btn" style="width: 100%;" onclick="startCompilation()">Start Compilation</button>
            <?php endif; ?>
        </div>
    </div>

    <script>
        async function startCompilation() {
            const btn = document.getElementById('compileBtn');
            const statusBox = document.getElementById('statusBox');
            const img = document.getElementById('targetImg');

            btn.disabled = true;
            btn.style.opacity = 0.5;

            try {
                statusBox.innerText = 'Loading MindAR compiler...';
                const compiler = new MINDAR.IMAGE.Compiler();

                statusBox.innerText = 'Loading target image into memory...';
                await new Promise((resolve, reject) => {
                    if (img.complete) resolve();
                    else {
                        img.onload = resolve;
                        img.onerror = reject;
                    }
                });

                statusBox.innerText = 'Compiling target features... (this may take a few seconds)';
                const _images = [img];
                
                await compiler.compileImageTargets(_images, (progress) => {
                    statusBox.innerText = `Compiling... ${Math.round(progress)}%`;
                });

                statusBox.innerText = 'Exporting compiled binary data...';
                const buffer = await compiler.exportData();

                statusBox.innerText = 'Saving compiled target to server...';
                
                // Convert ArrayBuffer to Base64 to transmit safely
                const bytes = new Uint8Array(buffer);
                let binary = '';
                const chunkSize = 0x8000;
                for (let offset = 0; offset < bytes.length; offset += chunkSize) {
                    binary += String.fromCharCode(...bytes.subarray(offset, offset + chunkSize));
                }
                const base64 = btoa(binary);

                const res = await fetch('save-target.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mindData: base64 })
                });

                const json = await res.json();
                if (json.ok) {
                    statusBox.innerText = 'Done! Target successfully compiled and saved.';
                    btn.innerText = 'Compilation Complete';
                    btn.style.opacity = 1;
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    throw new Error(json.error || 'Failed to save on server');
                }

            } catch (err) {
                console.error(err);
                statusBox.innerText = 'Compilation failed: ' + err.message;
                btn.disabled = false;
                btn.style.opacity = 1;
            }
        }
    </script>
</body>
</html>