<?php
declare(strict_types=1);
$targetExists = file_exists(__DIR__ . '/assets/targets/targets.mind');
$targetVersion = $targetExists ? filemtime(__DIR__ . '/assets/targets/targets.mind') : 0;
$scriptVersion = filemtime(__DIR__ . '/assets/js/main.js') . '-' . filemtime(__DIR__ . '/assets/js/game.js');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="target-version" content="<?php echo $targetVersion; ?>">
    <meta name="script-version" content="<?php echo $scriptVersion; ?>">
    <title>AR Game Shooter - Play</title>
    <link rel="stylesheet" href="assets/css/game.css">

    <script type="importmap">
        {
            "imports": {
                "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
                "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/",
                "mind-ar": "https://cdn.jsdelivr.net/npm/mind-ar@1.2.5/dist/mindar-image-three.prod.js"
            }
        }
    </script>
</head>
<body class="game-body">

    <?php if (!$targetExists): ?>
        <div class="modal-overlay">
            <div class="modal-content">
                <h2>Target Missing</h2>
                <p>The compiled AR target (`targets.mind`) is missing. Please upload and compile a target before playing.</p>
                <a href="compile-target.php" class="btn">Compile Target Now</a>
            </div>
        </div>
    <?php else: ?>
        <div id="ar-container"></div>

        <div class="screen-flash" id="screenFlash"></div>

        <div class="hud">
            <div class="hud-top">
                <div class="hud-panel">
                    <div class="hud-stat"><span>SCORE</span><div id="scoreVal">0</div></div>
                    <div class="hud-stat"><span>WAVE</span><div id="waveVal">1</div></div>
                    <div class="hud-stat"><span>HP</span><div id="hpVal">5</div></div>
                </div>
                <div class="hud-panel">
                    <div class="hud-stat"><span>AMMO</span><div id="ammoVal">30/30</div></div>
                </div>
            </div>

            <div class="crosshair" id="crosshair"></div>

            <div class="status-toast" id="statusToast">INITIALIZING AR...</div>

            <div class="hud-bottom">
                <button class="control-btn" id="reloadBtn">
                    <span>RELOAD</span>
                    <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 500;">[R]</span>
                </button>
                <button class="control-btn fire-btn" id="fireBtn">
                    <span>FIRE</span>
                </button>
            </div>
        </div>

        <div class="modal-overlay" id="gameOverModal" style="display: none;">
            <div class="modal-content">
                <h2>GAME OVER</h2>
                <p id="finalStats">Score: 0<br>Reached Wave: 1</p>
                <button class="btn" id="restartBtn" style="width: 100%;">Play Again</button>
            </div>
        </div>

        <!-- ES Module Game Entry -->
        <script type="module" src="assets/js/main.js?v=<?php echo $scriptVersion; ?>"></script>
    <?php endif; ?>
</body>
</html>