import * as THREE from 'three';

export class EffectsSystem {
    constructor(scene) {
        this.scene = scene;
        this.particles = [];
    }

    spawnHitSparks(position) {
        const count = 12;
        const geom = new THREE.BufferGeometry();
        const positions = [];
        const velocities = [];

        for (let i = 0; i < count; i++) {
            positions.push(position.x, position.y, position.z);
            velocities.push(
                (Math.random() - 0.5) * 2,
                (Math.random() - 0.5) * 2 + 1,
                (Math.random() - 0.5) * 2
            );
        }

        geom.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
        const mat = new THREE.PointsMaterial({ color: 0xfbbf24, size: 0.08, transparent: true, opacity: 1.0 });
        const points = new THREE.Points(geom, mat);
        this.scene.add(points);

        this.particles.push({ obj: points, velocities, life: 0.3, maxLife: 0.3 });
    }

    spawnDeathBurst(position) {
        const count = 30;
        const geom = new THREE.BufferGeometry();
        const positions = [];
        const velocities = [];

        for (let i = 0; i < count; i++) {
            positions.push(position.x, position.y, position.z);
            velocities.push(
                (Math.random() - 0.5) * 3,
                Math.random() * 3,
                (Math.random() - 0.5) * 3
            );
        }

        geom.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
        const mat = new THREE.PointsMaterial({ color: 0xef4444, size: 0.12, transparent: true, opacity: 1.0 });
        const points = new THREE.Points(geom, mat);
        this.scene.add(points);

        this.particles.push({ obj: points, velocities, life: 0.6, maxLife: 0.6 });
    }

    update(dt) {
        for (let i = this.particles.length - 1; i >= 0; i--) {
            const p = this.particles[i];
            p.life -= dt;
            if (p.life <= 0) {
                this.scene.remove(p.obj);
                p.obj.geometry.dispose();
                p.obj.material.dispose();
                this.particles.splice(i, 1);
            } else {
                const posArr = p.obj.geometry.attributes.position.array;
                const vels = p.velocities;
                for (let j = 0; j < vels.length / 3; j++) {
                    posArr[j * 3] += vels[j * 3] * dt;
                    posArr[j * 3 + 1] += vels[j * 3 + 1] * dt;
                    posArr[j * 3 + 2] += vels[j * 3 + 2] * dt;
                    vels[j * 3 + 1] -= 9.8 * dt; // gravity
                }
                p.obj.geometry.attributes.position.needsUpdate = true;
                p.mat = p.obj.material;
                p.mat.opacity = p.life / p.maxLife;
            }
        }
    }
}