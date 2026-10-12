# Web-Based AR Shooter Game (AR-Shooter)

A complete, production-ready Web-Based Augmented Reality First-Person Shooter game built with native PHP 8.3+, Vanilla JavaScript, Three.js, and MindAR Image Tracking.

## Features
- **WebAR Image Tracking**: Real-time smartphone rear-camera tracking using MindAR.
- **Three.js 3D Rendering**: High-performance dual-scene architecture rendering 3D AR enemies and foreground FPS weapons without depth clipping.
- **Dynamic Wave System**: Progressive enemy speed and health scaling across waves.
- **Weapon Mechanics**: Raycasting accuracy, recoil, ammo counters, reload animations, procedural Web Audio API gunshots and hits.
- **Target Compilation**: Client-side `targets.mind` binary compiler without requiring Node.js.
- **Model Management**: Custom `.glb` enemy and weapon uploading with validation and automatic bounding-box scaling.
- **Responsive HUD**: Designed for mobile touchscreens with desktop keyboard controls (`Space`/`F` to fire, `R` to reload).

---

## Requirements
- **PHP 8.3+** with GD and Fileinfo extensions enabled.
- **HTTPS** (Modern mobile browsers require secure contexts for rear camera permissions).
- **XAMPP** (Local development) or **cPanel** (Production shared hosting).

---

## Installation & Local Testing (XAMPP)

1. Clone or extract this repository into your XAMPP `htdocs` directory:
   ```text
   C:\xampp\htdocs\AR-Shooter\