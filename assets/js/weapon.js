import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

export class WeaponSystem {
    constructor(camera, overlayScene) {
        this.camera = camera;
        this.overlayScene = overlayScene;
        this.weaponMesh = null;
        this.mixer = null;
        this.animations = {};
        this.ammo = 30;
        this.maxAmmo = 30;
        this.isReloading = false;
        this.lastFireTime = 0;
        this.fireCooldown = 0.12; // seconds
    }

    async load(url) {
        const loader = new GLTFLoader();
        return new Promise((resolve) => {
            loader.load(url, (gltf) => {
                this.weaponMesh = gltf.scene;
                
                // Scale and position weapon at bottom-center of screen overlay
                const box = new THREE.Box3().setFromObject(this.weaponMesh);
                const size = box.getSize(new THREE.Vector3());
                const maxDim = Math.max(size.x, size.y, size.z);
                if (maxDim > 0) {
                    const scale = 0.8 / maxDim;
                    this.weaponMesh.scale.set(scale, scale, scale);
                }

                this.weaponMesh.position.set(0.2, -0.4, -0.8);
                this.weaponMesh.rotation.set(0, Math.PI, 0);

                this.overlayScene.add(this.weaponMesh);

                if (gltf.animations && gltf.animations.length > 0) {
                    this.mixer = new THREE.AnimationMixer(this.weaponMesh);
                    gltf.animations.forEach((clip) => {
                        this.animations[clip.name.toLowerCase()] = this.mixer.clipAction(clip);
                    });
                }
                resolve();
            }, undefined, (err) => {
                console.warn('Failed to load weapon model, using fallback geometric gun.', err);
                this.createFallbackWeapon();
                resolve();
            });
        });
    }

    createFallbackWeapon() {
        const geom = new THREE.BoxGeometry(0.1, 0.1, 0.6);
        const mat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.3 });
        this.weaponMesh = new THREE.Mesh(geom, mat);
        this.weaponMesh.position.set(0.18, -0.35, -0.6);
        this.overlayScene.add(this.weaponMesh);
    }

    fire() {
        const now = performance.now() / 1000;
        if (now - this.lastFireTime < this.fireCooldown) return false;
        if (this.isReloading) return false;
        if (this.ammo <= 0) {
            this.reload();
            return false;
        }

        this.ammo--;
        this.lastFireTime = now;

        // Recoil kick effect
        if (this.weaponMesh) {
            this.weaponMesh.position.z += 0.05;
            setTimeout(() => {
                if (this.weaponMesh) this.weaponMesh.position.z -= 0.05;
            }, 50);
        }

        return true;
    }

    reload() {
        if (this.isReloading || this.ammo >= this.maxAmmo) return;
        this.isReloading = true;

        setTimeout(() => {
            this.ammo = this.maxAmmo;
            this.isReloading = false;
        }, 1200);
    }

    update(dt) {
        if (this.mixer) {
            this.mixer.update(dt);
        }
    }
}