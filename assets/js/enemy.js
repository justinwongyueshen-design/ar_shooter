import * as THREE from 'three';
import * as SkeletonUtils from 'three/addons/utils/SkeletonUtils.js';

export class Enemy {
    constructor(scene, modelTemplate, animations, wave) {
        this.scene = scene;
        this.mesh = SkeletonUtils.clone(modelTemplate);
        
        // Auto scale bounding box
        const box = new THREE.Box3().setFromObject(this.mesh);
        const size = box.getSize(new THREE.Vector3());
        const maxDim = Math.max(size.x, size.y, size.z);
        if (maxDim > 0) {
            const scale = 0.8 / maxDim;
            this.mesh.scale.set(scale, scale, scale);
        }

        // Spawn distance in AR anchor space
        const angle = Math.random() * Math.PI * 2;
        const dist = 1.5 + Math.random() * 0.5;
        this.mesh.position.set(Math.cos(angle) * dist, 0, Math.sin(angle) * dist - 1.0);
        
        this.scene.add(this.mesh);

        this.hp = 4 + (wave - 1);
        this.maxHp = this.hp;
        this.speed = 0.28 * (1 + 0.12 * (wave - 1));
        this.isDead = false;

        this.mixer = new THREE.AnimationMixer(this.mesh);
        this.actions = {};

        if (animations && animations.length > 0) {
            animations.forEach((clip) => {
                const name = clip.name.toLowerCase();
                this.actions[name] = this.mixer.clipAction(clip);
            });
        }

        this.currentAction = null;
        this.playAnimation('walk') || this.playAnimation('idle');
    }

    playAnimation(name) {
        for (let key in this.actions) {
            if (key.includes(name)) {
                if (this.currentAction !== this.actions[key]) {
                    if (this.currentAction) this.currentAction.fadeOut(0.2);
                    this.currentAction = this.actions[key];
                    this.currentAction.reset().fadeIn(0.2).play();
                }
                return true;
            }
        }
        return false;
    }

    update(dt, targetPos) {
        if (this.isDead) return;

        // Move toward target origin (0,0,0 in anchor space)
        const pos = this.mesh.position;
        const dir = new THREE.Vector3(targetPos.x - pos.x, 0, targetPos.z - pos.z);
        const dist = dir.length();

        if (dist > 0.2) {
            dir.normalize();
            pos.addScaledVector(dir, this.speed * dt);
            this.mesh.lookAt(targetPos.x, pos.y, targetPos.z);
        }

        if (this.mixer) {
            this.mixer.update(dt);
        }
    }

    hit(damage) {
        if (this.isDead) return false;
        this.hp -= damage;
        if (this.hp <= 0) {
            this.die();
            return true;
        }
        return false;
    }

    die() {
        this.isDead = true;
        this.playAnimation('death');
        setTimeout(() => {
            this.scene.remove(this.mesh);
        }, 1000);
    }
}