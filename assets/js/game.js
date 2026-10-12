import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { MindARThree } from 'mind-ar';
import { WeaponSystem } from './weapon.js';
import { Enemy } from './enemy.js';
import { EffectsSystem } from './effects.js';
import { AudioManager } from './audio.js';

const mindarResize = MindARThree.prototype.resize;
MindARThree.prototype.resize = function () {
    if (!this.controller) return;
    mindarResize.call(this);
};

export class GameManager {
    constructor() {
        this.score = 0;
        this.wave = 1;
        this.hp = 5;
        this.maxHp = 5;
        this.isRunning = false;
        this.targetFound = false;
        this.enemies = [];
        this.enemyTemplate = null;
        this.enemyAnimations = [];
    }

    async init() {
        const container = document.getElementById('ar-container');
        const statusToast = document.getElementById('statusToast');

        statusToast.innerText = 'FETCHING CONFIG...';
        const res = await fetch('api/models.php');
        const data = await res.json();

        statusToast.innerText = 'INITIALIZING MINDAR...';
        this.mindarThree = new MindARThree({
            container: container,
            imageTargetSrc: `assets/targets/targets.mind?v=${document.querySelector('meta[name="target-version"]')?.content || ''}`,
            uiLoading: 'yes',
            uiScanning: 'no',
            uiError: 'yes'
        });

        const { renderer, scene, camera } = this.mindarThree;
        scene.add(new THREE.AmbientLight(0xffffff, 1.2));
        const dirLight = new THREE.DirectionalLight(0xffffff, 1.5);
        dirLight.position.set(5, 10, 5);
        scene.add(dirLight);

        // Separate Overlay Scene for FPS Weapon depth clearing
        this.overlayScene = new THREE.Scene();
        this.overlayCamera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 10);

        this.audio = new AudioManager();
        this.effects = new EffectsSystem(scene);
        this.weapon = new WeaponSystem(this.overlayCamera, this.overlayScene);
        await this.weapon.load(data.weaponUrl);

        // Load Enemy Template
        await this.loadEnemyTemplate(data.enemyUrl);

        const anchor = this.mindarThree.addAnchor(0);
        
        anchor.onTargetFound = () => {
            this.targetFound = true;
            statusToast.innerText = 'TARGET LOCKED';
            setTimeout(() => { if (this.targetFound) statusToast.innerText = ''; }, 1500);
            if (!this.isRunning && this.hp > 0) {
                this.startWave();
            }
        };

        anchor.onTargetLost = () => {
            this.targetFound = false;
            statusToast.innerText = 'TARGET LOST';
        };

        // Attach enemy anchor group
        this.enemyGroup = new THREE.Group();
        anchor.group.add(this.enemyGroup);

        await this.mindarThree.start();
        statusToast.innerText = 'POINT CAMERA AT TARGET';

        this.setupControls();
        this.clock = new THREE.Clock();

        renderer.autoClear = false;
        this.renderer = renderer;
        this.scene = scene;
        this.camera = camera;

        this.animate();
    }

    async loadEnemyTemplate(url) {
        const loader = new GLTFLoader();
        return new Promise((resolve) => {
            loader.load(url, (gltf) => {
                this.enemyTemplate = gltf.scene;
                this.enemyAnimations = gltf.animations || [];
                resolve();
            }, undefined, (err) => {
                console.warn('Failed to load enemy GLB, using fallback geometry.', err);
                const geom = new THREE.BoxGeometry(0.5, 0.8, 0.5);
                const mat = new THREE.MeshStandardMaterial({ color: 0xef4444 });
                this.enemyTemplate = new THREE.Mesh(geom, mat);
                this.enemyAnimations = [];
                resolve();
            });
        });
    }

    startWave() {
        this.isRunning = true;
        document.getElementById('waveVal').innerText = this.wave;
        
        // Spawn enemies for this wave
        const count = this.wave;
        for (let i = 0; i < count; i++) {
            const enemy = new Enemy(this.enemyGroup, this.enemyTemplate, this.enemyAnimations, this.wave);
            this.enemies.push(enemy);
        }
    }

    setupControls() {
        const fireBtn = document.getElementById('fireBtn');
        const reloadBtn = document.getElementById('reloadBtn');
        const crosshair = document.getElementById('crosshair');

        const triggerFire = () => {
            this.audio.init();
            if (this.weapon.fire()) {
                crosshair.classList.add('fire');
                setTimeout(() => crosshair.classList.remove('fire'), 100);
                this.audio.playShoot();

                // Raycast against enemies
                if (this.targetFound && this.enemies.length > 0) {
                    const raycaster = new THREE.Raycaster();
                    raycaster.setFromCamera(new THREE.Vector2(0, 0), this.camera);
                    
                    const meshes = this.enemies.filter(e => !e.isDead).map(e => e.mesh);
                    const intersects = raycaster.intersectObjects(meshes, true);

                    if (intersects.length > 0) {
                        crosshair.classList.add('hit');
                        setTimeout(() => crosshair.classList.remove('hit'), 150);
                        this.audio.playHit();

                        const hitMesh = intersects[0].object;
                        // Find parent enemy
                        const enemy = this.enemies.find(e => {
                            let found = false;
                            e.mesh.traverse(child => { if (child === hitMesh) found = true; });
                            return found;
                        });

                        if (enemy) {
                            this.effects.spawnHitSparks(intersects[0].point);
                            const isDead = enemy.hit(1);
                            if (isDead) {
                                this.effects.spawnDeathBurst(enemy.mesh.position);
                                this.score += 100 + (this.wave * 25);
                                document.getElementById('scoreVal').innerText = this.score;

                                // Remove from array
                                this.enemies = this.enemies.filter(e => e !== enemy);

                                // Check if wave cleared
                                if (this.enemies.length === 0) {
                                    this.wave++;
                                    setTimeout(() => this.startWave(), 1500);
                                }
                            }
                        }
                    }
                }
            } else if (this.weapon.ammo <= 0) {
                this.audio.playReload();
            }
            document.getElementById('ammoVal').innerText = `${this.weapon.ammo}/${this.weapon.maxAmmo}`;
        };

        fireBtn.addEventListener('pointerdown', (e) => {
            e.preventDefault();
            triggerFire();
        });

        reloadBtn.addEventListener('click', () => {
            this.audio.playReload();
            this.weapon.reload();
            document.getElementById('ammoVal').innerText = `${this.weapon.ammo}/${this.weapon.maxAmmo}`;
        });

        // Desktop keyboard controls
        window.addEventListener('keydown', (e) => {
            if (e.code === 'Space' || e.code === 'KeyF') {
                e.preventDefault();
                triggerFire();
            } else if (e.code === 'KeyR') {
                e.preventDefault();
                this.weapon.reload();
                document.getElementById('ammoVal').innerText = `${this.weapon.ammo}/${this.weapon.maxAmmo}`;
            }
        });
    }

    damagePlayer() {
        this.hp--;
        document.getElementById('hpVal').innerText = this.hp;
        this.audio.playDamage();

        const flash = document.getElementById('screenFlash');
        flash.classList.add('damage');
        setTimeout(() => flash.classList.remove('damage'), 200);

        if (this.hp <= 0) {
            this.gameOver();
        }
    }

    gameOver() {
        this.isRunning = false;
        document.getElementById('finalStats').innerHTML = `Score: ${this.score}<br>Reached Wave: ${this.wave}`;
        document.getElementById('gameOverModal').style.display = 'flex';
    }

    animate() {
        requestAnimationFrame(() => this.animate());

        const dt = Math.min(this.clock.getDelta(), 0.05);

        this.weapon.update(dt);
        this.effects.update(dt);

        if (this.isRunning && this.targetFound) {
            const playerPos = new THREE.Vector3(0, 0, 0);
            for (let i = this.enemies.length - 1; i >= 0; i--) {
                const enemy = this.enemies[i];
                enemy.update(dt, playerPos);

                // Check distance to player
                if (!enemy.isDead && enemy.mesh.position.distanceTo(playerPos) < 0.4) {
                    enemy.die();
                    this.enemies.splice(i, 1);
                    this.damagePlayer();

                    if (this.enemies.length === 0 && this.hp > 0) {
                        this.wave++;
                        setTimeout(() => this.startWave(), 1500);
                    }
                }
            }
        }

        // Render AR Scene, then clear depth buffer and render Weapon Overlay
        this.renderer.clear();
        this.renderer.render(this.scene, this.camera);
        this.renderer.clearDepth();
        this.renderer.render(this.overlayScene, this.overlayCamera);
    }
}