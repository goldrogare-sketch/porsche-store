/**
 * Porsche 3D Visualizer & Studio Engine
 * Uses Three.js to render an interactive, customizable Porsche sports car.
 */

(function () {
    'use strict';

    window.Porsche3D = function (containerId, options) {
        options = options || {};
        var container = document.getElementById(containerId);
        if (!container) {
            console.error('Porsche 3D container not found:', containerId);
            return null;
        }

        var scene, camera, renderer, controls;
        var carGroup, bodyMesh, spoilerMesh, spoilerStands, interiorMesh, seatsMesh, steeringMesh;
        var wheelMeshes = [];
        var caliperMeshes = [];
        var headlightLenses = [], taillightMesh;
        var studioLights = [];
        var groundPodium, shadowPlane;
        var isRotating = false;
        var lightsOn = true;
        var studioTheme = 'dark'; // 'dark' or 'light'
        var currentCarType = options.carType || 'sports'; // 'sports', 'sedan', 'suv'

        // Current Configuration State
        var state = {
            color: options.color || '#D5001C',
            wheelColor: options.wheelColor || '#d4d8dd',
            wheelStyle: options.wheelStyle || 'classic',
            caliperColor: options.caliperColor || '#1e1f22',
            interiorColor: options.interiorColor || '#212224',
            aeroCode: options.aeroCode || 'standard',
            carType: currentCarType
        };

        var width = container.clientWidth || 800;
        var height = container.clientHeight || 550;

        function init() {
            // 1. Scene
            scene = new THREE.Scene();
            scene.background = new THREE.Color(studioTheme === 'dark' ? 0x090a0d : 0x1a1c23);
            scene.fog = new THREE.FogExp2(studioTheme === 'dark' ? 0x090a0d : 0x1a1c23, 0.04);

            // 2. Camera
            camera = new THREE.PerspectiveCamera(40, width / height, 0.1, 100);
            camera.position.set(4.2, 1.6, 4.4);

            // 3. Renderer
            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, preserveDrawingBuffer: true });
            renderer.setSize(width, height);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            if (renderer.toneMapping !== undefined) {
                renderer.toneMapping = THREE.ACESFilmicToneMapping;
                renderer.toneMappingExposure = 1.05;
            }
            container.innerHTML = '';
            container.appendChild(renderer.domElement);

            // 4. OrbitControls
            if (typeof THREE.OrbitControls !== 'undefined') {
                controls = new THREE.OrbitControls(camera, renderer.domElement);
                controls.enableDamping = true;
                controls.dampingFactor = 0.06;
                controls.minDistance = 2.4;
                controls.maxDistance = 9.0;
                controls.maxPolarAngle = Math.PI / 2 - 0.03; // don't go below floor
                controls.target.set(0, 0.55, 0);
            }

            // 5. Studio Environment & Lighting
            setupStudioLighting();
            setupStudioStage();

            // 6. Build 3D Car
            buildCar();

            // 7. Window Resize Listener
            window.addEventListener('resize', onWindowResize);

            // 8. Animation Loop
            animate();
        }

        function setupStudioLighting() {
            // Ambient showroom glow
            var ambient = new THREE.AmbientLight(0xffffff, 0.65);
            scene.add(ambient);

            // Main key softbox overhead
            var keyLight = new THREE.DirectionalLight(0xffffff, 1.2);
            keyLight.position.set(4, 7, 5);
            keyLight.castShadow = true;
            keyLight.shadow.mapSize.width = 2048;
            keyLight.shadow.mapSize.height = 2048;
            keyLight.shadow.camera.near = 0.5;
            keyLight.shadow.camera.far = 25;
            keyLight.shadow.camera.left = -4;
            keyLight.shadow.camera.right = 4;
            keyLight.shadow.camera.top = 4;
            keyLight.shadow.camera.bottom = -4;
            keyLight.shadow.bias = -0.0005;
            scene.add(keyLight);
            studioLights.push(keyLight);

            // Rim / Contour backlight (Porsche iconic shoulder highlight)
            var rimLight = new THREE.DirectionalLight(0xdbe7ff, 0.9);
            rimLight.position.set(-5, 4, -4);
            scene.add(rimLight);
            studioLights.push(rimLight);

            // Front fill light for bumper and headlights
            var frontLight = new THREE.DirectionalLight(0xffffff, 0.75);
            frontLight.position.set(0, 2, 6);
            scene.add(frontLight);
            studioLights.push(frontLight);

            // Subtle colored side accent light
            var sideAccent = new THREE.PointLight(0xd5001c, 0.5, 10);
            sideAccent.position.set(3, 0.4, -2.5);
            scene.add(sideAccent);
        }

        function setupStudioStage() {
            // Circular Showroom Turntable Podium
            var podiumGeo = new THREE.CylinderGeometry(3.6, 3.8, 0.12, 64);
            var podiumMat = new THREE.MeshStandardMaterial({
                color: 0x14151a,
                roughness: 0.35,
                metalness: 0.7
            });
            groundPodium = new THREE.Mesh(podiumGeo, podiumMat);
            groundPodium.position.y = -0.06;
            groundPodium.receiveShadow = true;
            scene.add(groundPodium);

            // Brushed metal rim around the podium
            var rimGeo = new THREE.TorusGeometry(3.6, 0.035, 16, 64);
            var rimMat = new THREE.MeshStandardMaterial({
                color: 0xd4af37, // gold accent ring
                metalness: 0.9,
                roughness: 0.2
            });
            var podiumRim = new THREE.Mesh(rimGeo, rimMat);
            podiumRim.rotation.x = Math.PI / 2;
            podiumRim.position.y = 0.005;
            scene.add(podiumRim);

            // Ground floor plane with dark grid
            var floorGeo = new THREE.PlaneGeometry(30, 30);
            var floorMat = new THREE.MeshStandardMaterial({
                color: 0x08090c,
                roughness: 0.8,
                metalness: 0.2
            });
            var floor = new THREE.Mesh(floorGeo, floorMat);
            floor.rotation.x = -Math.PI / 2;
            floor.position.y = -0.061;
            floor.receiveShadow = true;
            scene.add(floor);

            // Realistic Soft Contact Shadow Texture Plane under the car
            var shadowCanvas = document.createElement('canvas');
            shadowCanvas.width = 512;
            shadowCanvas.height = 512;
            var ctx = shadowCanvas.getContext('2d');
            var grad = ctx.createRadialGradient(256, 256, 40, 256, 256, 240);
            grad.addColorStop(0, 'rgba(0, 0, 0, 0.85)');
            grad.addColorStop(0.4, 'rgba(0, 0, 0, 0.6)');
            grad.addColorStop(0.8, 'rgba(0, 0, 0, 0.2)');
            grad.addColorStop(1, 'rgba(0, 0, 0, 0)');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, 512, 512);

            var shadowTex = new THREE.CanvasTexture(shadowCanvas);
            var shadowMat = new THREE.MeshBasicMaterial({
                map: shadowTex,
                transparent: true,
                opacity: 0.85,
                depthWrite: false
            });
            var shadowGeo = new THREE.PlaneGeometry(4.6, 2.4);
            shadowPlane = new THREE.Mesh(shadowGeo, shadowMat);
            shadowPlane.rotation.x = -Math.PI / 2;
            shadowPlane.position.set(0, 0.004, 0);
            scene.add(shadowPlane);
        }

        // Procedural Porsche 3D Geometry
        function buildCar() {
            if (carGroup) {
                scene.remove(carGroup);
            }

            carGroup = new THREE.Group();
            wheelMeshes = [];
            caliperMeshes = [];
            headlightLenses = [];

            var scaleY = 1.0;
            var scaleZ = 1.0;
            var cabinHeight = 0.7;
            var groundClearance = 0.32;

            if (state.carType === 'suv') {
                scaleY = 1.25;
                cabinHeight = 0.95;
                groundClearance = 0.46;
            } else if (state.carType === 'sedan') {
                scaleZ = 1.15;
                cabinHeight = 0.72;
                groundClearance = 0.33;
            }

            // --- 1. Materials ---
            // High-Gloss Automotive Paint Material
            var paintMaterial = new THREE.MeshPhysicalMaterial({
                color: new THREE.Color(state.color),
                metalness: 0.85,
                roughness: 0.2,
                clearcoat: 1.0,
                clearcoatRoughness: 0.04,
                reflectivity: 0.9
            });

            // Dark Carbon / Gloss Trim Material
            var carbonMaterial = new THREE.MeshStandardMaterial({
                color: 0x111215,
                roughness: 0.3,
                metalness: 0.8
            });

            // Glass Material
            var glassMaterial = new THREE.MeshPhysicalMaterial({
                color: 0x050810,
                metalness: 0.1,
                roughness: 0.05,
                transmission: 0.85,
                transparent: true,
                opacity: 0.88,
                reflectivity: 0.95
            });

            // Interior Leather Material
            var interiorMaterial = new THREE.MeshStandardMaterial({
                color: new THREE.Color(state.interiorColor),
                roughness: 0.65,
                metalness: 0.15
            });

            // Chrome Material (Exhaust tips, badges)
            var chromeMaterial = new THREE.MeshStandardMaterial({
                color: 0xf5f7fa,
                metalness: 0.95,
                roughness: 0.1
            });

            // --- 2. Lower Chassis & Aerodynamic Floorpan ---
            var floorpanGeo = new THREE.BoxGeometry(1.68, 0.15 * scaleY, 3.8 * scaleZ);
            var floorpan = new THREE.Mesh(floorpanGeo, carbonMaterial);
            floorpan.position.set(0, groundClearance, 0);
            floorpan.castShadow = true;
            floorpan.receiveShadow = true;
            carGroup.add(floorpan);

            // --- 3. Sculpted Porsche Main Body ---
            // Main Lower Body Shell
            var bodyGroup = new THREE.Group();

            // Lower curved tub
            var lowerBodyGeo = new THREE.BoxGeometry(1.72, 0.42 * scaleY, 3.7 * scaleZ);
            bodyMesh = new THREE.Mesh(lowerBodyGeo, paintMaterial);
            bodyMesh.position.set(0, groundClearance + 0.2 * scaleY, 0);
            bodyMesh.castShadow = true;
            bodyMesh.receiveShadow = true;
            bodyGroup.add(bodyMesh);

            // Front sloping hood (Iconic Porsche nose taper)
            var hoodGeo = new THREE.CylinderGeometry(0.72, 0.86, 1.4 * scaleZ, 16, 1, false, -Math.PI / 2, Math.PI);
            var hood = new THREE.Mesh(hoodGeo, paintMaterial);
            hood.rotation.x = Math.PI / 2;
            hood.rotation.z = Math.PI;
            hood.scale.set(1.0, 0.45 * scaleY, 1.0);
            hood.position.set(0, groundClearance + 0.32 * scaleY, 1.2 * scaleZ);
            hood.castShadow = true;
            bodyGroup.add(hood);

            // Front Fenders / Headlight Pods (Porsche iconic raised front shoulders)
            var fenderGeo = new THREE.SphereGeometry(0.35, 16, 16);
            fenderGeo.scale(0.65, 0.65 * scaleY, 1.4);
            var leftFender = new THREE.Mesh(fenderGeo, paintMaterial);
            leftFender.position.set(-0.68, groundClearance + 0.35 * scaleY, 1.35 * scaleZ);
            leftFender.castShadow = true;
            bodyGroup.add(leftFender);

            var rightFender = new THREE.Mesh(fenderGeo, paintMaterial);
            rightFender.position.set(0.68, groundClearance + 0.35 * scaleY, 1.35 * scaleZ);
            rightFender.castShadow = true;
            bodyGroup.add(rightFender);

            // Muscular Rear Wheel Arches (Porsche signature wide hips)
            var rearHaunchGeo = new THREE.SphereGeometry(0.48, 16, 16);
            rearHaunchGeo.scale(0.65, 0.68 * scaleY, 1.5);
            var leftHaunch = new THREE.Mesh(rearHaunchGeo, paintMaterial);
            leftHaunch.position.set(-0.72, groundClearance + 0.34 * scaleY, -0.9 * scaleZ);
            leftHaunch.castShadow = true;
            bodyGroup.add(leftHaunch);

            var rightHaunch = new THREE.Mesh(rearHaunchGeo, paintMaterial);
            rightHaunch.position.set(0.72, groundClearance + 0.34 * scaleY, -0.9 * scaleZ);
            rightHaunch.castShadow = true;
            bodyGroup.add(rightHaunch);

            // Rear fastback flyline / decklid
            var rearDeckGeo = new THREE.CylinderGeometry(0.85, 0.72, 1.3 * scaleZ, 16, 1, false, -Math.PI / 2, Math.PI);
            var rearDeck = new THREE.Mesh(rearDeckGeo, paintMaterial);
            rearDeck.rotation.x = Math.PI / 2;
            rearDeck.rotation.z = Math.PI;
            rearDeck.scale.set(0.98, 0.42 * scaleY, 1.0);
            rearDeck.position.set(0, groundClearance + 0.34 * scaleY, -1.15 * scaleZ);
            rearDeck.castShadow = true;
            bodyGroup.add(rearDeck);

            // --- 4. Cabin Greenhouse & Windows ---
            var cabinGeo = new THREE.CylinderGeometry(0.55, 0.72, 1.8 * scaleZ, 16, 1, false, -Math.PI / 2, Math.PI);
            var cabin = new THREE.Mesh(cabinGeo, glassMaterial);
            cabin.rotation.x = Math.PI / 2;
            cabin.rotation.z = Math.PI;
            cabin.scale.set(1.0, cabinHeight, 1.0);
            cabin.position.set(0, groundClearance + 0.45 * scaleY, 0.05 * scaleZ);
            cabin.castShadow = true;
            bodyGroup.add(cabin);

            // Roof Cap
            var roofGeo = new THREE.BoxGeometry(1.1, 0.04, 1.4 * scaleZ);
            var roofMat = (state.aeroCode === 'carbon_roof' || state.aeroCode === 'carbon_aero') ? carbonMaterial : paintMaterial;
            var roof = new THREE.Mesh(roofGeo, roofMat);
            roof.position.set(0, groundClearance + 0.45 * scaleY + cabinHeight * 0.48, 0.05 * scaleZ);
            bodyGroup.add(roof);

            // Side Mirrors
            var mirrorGeo = new THREE.BoxGeometry(0.2, 0.08, 0.12);
            var mirrorMat = (state.aeroCode === 'carbon_roof' || state.aeroCode === 'carbon_aero') ? carbonMaterial : paintMaterial;
            var leftMirror = new THREE.Mesh(mirrorGeo, mirrorMat);
            leftMirror.position.set(-0.92, groundClearance + 0.48 * scaleY, 0.65 * scaleZ);
            leftMirror.rotation.y = 0.2;
            bodyGroup.add(leftMirror);

            var rightMirror = new THREE.Mesh(mirrorGeo, mirrorMat);
            rightMirror.position.set(0.92, groundClearance + 0.48 * scaleY, 0.65 * scaleZ);
            rightMirror.rotation.y = -0.2;
            bodyGroup.add(rightMirror);

            carGroup.add(bodyGroup);

            // --- 5. Front Aerodynamics & Splitter ---
            var frontSplitterGeo = new THREE.BoxGeometry(1.68, 0.06 * scaleY, 0.4);
            var frontSplitter = new THREE.Mesh(frontSplitterGeo, carbonMaterial);
            frontSplitter.position.set(0, groundClearance + 0.02, 1.85 * scaleZ);
            carGroup.add(frontSplitter);

            // Front Air Intake Grilles
            var intakeGeo = new THREE.BoxGeometry(0.48, 0.14 * scaleY, 0.08);
            var leftIntake = new THREE.Mesh(intakeGeo, carbonMaterial);
            leftIntake.position.set(-0.52, groundClearance + 0.12 * scaleY, 1.9 * scaleZ);
            carGroup.add(leftIntake);

            var rightIntake = new THREE.Mesh(intakeGeo, carbonMaterial);
            rightIntake.position.set(0.52, groundClearance + 0.12 * scaleY, 1.9 * scaleZ);
            carGroup.add(rightIntake);

            var centerIntake = new THREE.Mesh(new THREE.BoxGeometry(0.38, 0.12 * scaleY, 0.08), carbonMaterial);
            centerIntake.position.set(0, groundClearance + 0.11 * scaleY, 1.92 * scaleZ);
            carGroup.add(centerIntake);

            // --- 6. Headlights with Porsche 4-Point LED Signature ---
            var headlightHousingGeo = new THREE.SphereGeometry(0.14, 16, 16);
            headlightHousingGeo.scale(1.0, 0.8, 1.4);
            var headlightMat = new THREE.MeshStandardMaterial({
                color: 0x111115,
                metalness: 0.9,
                roughness: 0.2
            });

            var leftHL = new THREE.Mesh(headlightHousingGeo, headlightMat);
            leftHL.position.set(-0.62, groundClearance + 0.44 * scaleY, 1.7 * scaleZ);
            carGroup.add(leftHL);

            var rightHL = new THREE.Mesh(headlightHousingGeo, headlightMat);
            rightHL.position.set(0.62, groundClearance + 0.44 * scaleY, 1.7 * scaleZ);
            carGroup.add(rightHL);

            // LED Emitters (4-point crystal LEDs)
            var ledMat = new THREE.MeshBasicMaterial({
                color: lightsOn ? 0xe6f3ff : 0x444455
            });

            var lensGeo = new THREE.CylinderGeometry(0.08, 0.08, 0.02, 16);
            var leftLens = new THREE.Mesh(lensGeo, ledMat);
            leftLens.rotation.x = Math.PI / 2 + 0.2;
            leftLens.position.set(-0.62, groundClearance + 0.44 * scaleY, 1.83 * scaleZ);
            carGroup.add(leftLens);
            headlightLenses.push(leftLens);

            var rightLens = new THREE.Mesh(lensGeo, ledMat);
            rightLens.rotation.x = Math.PI / 2 + 0.2;
            rightLens.position.set(0.62, groundClearance + 0.44 * scaleY, 1.83 * scaleZ);
            carGroup.add(rightLens);
            headlightLenses.push(rightLens);

            // --- 7. Rear Diffuser, Exhaust & Continuous LED Lightbar ---
            // Rear Diffuser
            var diffuserGeo = new THREE.BoxGeometry(1.64, 0.12 * scaleY, 0.35);
            var diffuser = new THREE.Mesh(diffuserGeo, carbonMaterial);
            diffuser.position.set(0, groundClearance + 0.05, -1.82 * scaleZ);
            carGroup.add(diffuser);

            // Chrome Quad Exhaust Tips
            var exhaustGeo = new THREE.CylinderGeometry(0.05, 0.05, 0.15, 16);
            var ex1 = new THREE.Mesh(exhaustGeo, chromeMaterial);
            ex1.rotation.x = Math.PI / 2;
            ex1.position.set(-0.52, groundClearance + 0.07 * scaleY, -1.9 * scaleZ);
            carGroup.add(ex1);

            var ex2 = new THREE.Mesh(exhaustGeo, chromeMaterial);
            ex2.rotation.x = Math.PI / 2;
            ex2.position.set(-0.62, groundClearance + 0.07 * scaleY, -1.9 * scaleZ);
            carGroup.add(ex2);

            var ex3 = new THREE.Mesh(exhaustGeo, chromeMaterial);
            ex3.rotation.x = Math.PI / 2;
            ex3.position.set(0.52, groundClearance + 0.07 * scaleY, -1.9 * scaleZ);
            carGroup.add(ex3);

            var ex4 = new THREE.Mesh(exhaustGeo, chromeMaterial);
            ex4.rotation.x = Math.PI / 2;
            ex4.position.set(0.62, groundClearance + 0.07 * scaleY, -1.9 * scaleZ);
            carGroup.add(ex4);

            // Full-Width Continuous Porsche Rear Lightbar
            var taillightGeo = new THREE.BoxGeometry(1.48, 0.035, 0.06);
            var taillightMat = new THREE.MeshStandardMaterial({
                color: lightsOn ? 0xff0020 : 0x550005,
                emissive: lightsOn ? 0xff0020 : 0x220000,
                emissiveIntensity: lightsOn ? 0.95 : 0.1,
                roughness: 0.1
            });
            taillightMesh = new THREE.Mesh(taillightGeo, taillightMat);
            taillightMesh.position.set(0, groundClearance + 0.42 * scaleY, -1.84 * scaleZ);
            carGroup.add(taillightMesh);

            // --- 8. Aerodynamics & Rear Wing System ---
            buildSpoiler(scaleY, scaleZ, groundClearance, paintMaterial, carbonMaterial);

            // --- 9. Interior Cabin (Seats, Dashboard, Steering Wheel) ---
            buildInterior(scaleY, scaleZ, groundClearance, interiorMaterial, carbonMaterial);

            // --- 10. Wheels, Rims, Calipers & Discs ---
            buildWheels(scaleY, scaleZ, groundClearance, carbonMaterial);

            scene.add(carGroup);
        }

        // Active Rear Wing / Spoiler
        function buildSpoiler(scaleY, scaleZ, groundClearance, paintMaterial, carbonMaterial) {
            if (spoilerMesh) carGroup.remove(spoilerMesh);
            if (spoilerStands) carGroup.remove(spoilerStands);

            var aero = state.aeroCode;
            if (aero === 'standard') {
                // Integrated flush ducktail spoiler
                var ducktailGeo = new THREE.BoxGeometry(1.25, 0.05 * scaleY, 0.28);
                spoilerMesh = new THREE.Mesh(ducktailGeo, paintMaterial);
                spoilerMesh.position.set(0, groundClearance + 0.46 * scaleY, -1.72 * scaleZ);
                spoilerMesh.rotation.x = -0.15;
                carGroup.add(spoilerMesh);
            } else if (aero === 'wing') {
                // Active Sport Aerodynamic Wing (Elevated on struts)
                spoilerStands = new THREE.Group();
                var strutGeo = new THREE.BoxGeometry(0.04, 0.24, 0.06);
                var strut1 = new THREE.Mesh(strutGeo, carbonMaterial);
                strut1.position.set(-0.35, groundClearance + 0.54 * scaleY, -1.68 * scaleZ);
                spoilerStands.add(strut1);

                var strut2 = new THREE.Mesh(strutGeo, carbonMaterial);
                strut2.position.set(0.35, groundClearance + 0.54 * scaleY, -1.68 * scaleZ);
                spoilerStands.add(strut2);
                carGroup.add(spoilerStands);

                var wingGeo = new THREE.BoxGeometry(1.42, 0.038, 0.32);
                spoilerMesh = new THREE.Mesh(wingGeo, paintMaterial);
                spoilerMesh.position.set(0, groundClearance + 0.65 * scaleY, -1.7 * scaleZ);
                spoilerMesh.rotation.x = -0.12;
                spoilerMesh.castShadow = true;
                carGroup.add(spoilerMesh);
            } else if (aero === 'carbon_aero' || aero === 'carbon_roof') {
                // GT3 / Motorsport Carbon Fiber High-Downforce Swan-Neck Wing
                spoilerStands = new THREE.Group();
                var gtStrutGeo = new THREE.BoxGeometry(0.04, 0.38, 0.08);
                var s1 = new THREE.Mesh(gtStrutGeo, carbonMaterial);
                s1.position.set(-0.4, groundClearance + 0.62 * scaleY, -1.68 * scaleZ);
                spoilerStands.add(s1);

                var s2 = new THREE.Mesh(gtStrutGeo, carbonMaterial);
                s2.position.set(0.4, groundClearance + 0.62 * scaleY, -1.68 * scaleZ);
                spoilerStands.add(s2);
                carGroup.add(spoilerStands);

                var gtWingGeo = new THREE.BoxGeometry(1.58, 0.04, 0.38);
                spoilerMesh = new THREE.Mesh(gtWingGeo, carbonMaterial);
                spoilerMesh.position.set(0, groundClearance + 0.78 * scaleY, -1.7 * scaleZ);
                spoilerMesh.rotation.x = -0.16;
                spoilerMesh.castShadow = true;

                // Wing endplates
                var endplateGeo = new THREE.BoxGeometry(0.02, 0.18, 0.42);
                var ep1 = new THREE.Mesh(endplateGeo, carbonMaterial);
                ep1.position.set(-0.79, groundClearance + 0.78 * scaleY, -1.7 * scaleZ);
                spoilerMesh.add(ep1);

                var ep2 = new THREE.Mesh(endplateGeo, carbonMaterial);
                ep2.position.set(0.79, groundClearance + 0.78 * scaleY, -1.7 * scaleZ);
                spoilerMesh.add(ep2);

                carGroup.add(spoilerMesh);
            }
        }

        // Detailed Cabin Interior (Visible through glass)
        function buildInterior(scaleY, scaleZ, groundClearance, interiorMaterial, carbonMaterial) {
            // Dashboard
            var dashGeo = new THREE.BoxGeometry(1.3, 0.18 * scaleY, 0.45);
            var dash = new THREE.Mesh(dashGeo, interiorMaterial);
            dash.position.set(0, groundClearance + 0.46 * scaleY, 0.65 * scaleZ);
            carGroup.add(dash);

            // Center Chrono Dial (Sport Chrono clock on dash)
            var chronoGeo = new THREE.CylinderGeometry(0.04, 0.04, 0.03, 16);
            var chronoMat = new THREE.MeshStandardMaterial({ color: 0x222225, metalness: 0.8 });
            var chrono = new THREE.Mesh(chronoGeo, chronoMat);
            chrono.position.set(0, groundClearance + 0.56 * scaleY, 0.62 * scaleZ);
            carGroup.add(chrono);

            // Sports Bucket Seats (Left Driver & Right Passenger)
            var seatGroup = new THREE.Group();
            var seatBaseGeo = new THREE.BoxGeometry(0.42, 0.12, 0.42);
            var seatBackGeo = new THREE.BoxGeometry(0.4, 0.48 * scaleY, 0.1);

            // Driver Seat
            var dSeatBase = new THREE.Mesh(seatBaseGeo, interiorMaterial);
            dSeatBase.position.set(-0.35, groundClearance + 0.3 * scaleY, 0.05 * scaleZ);
            seatGroup.add(dSeatBase);

            var dSeatBack = new THREE.Mesh(seatBackGeo, interiorMaterial);
            dSeatBack.position.set(-0.35, groundClearance + 0.52 * scaleY, -0.15 * scaleZ);
            dSeatBack.rotation.x = -0.15;
            seatGroup.add(dSeatBack);

            // Passenger Seat
            var pSeatBase = new THREE.Mesh(seatBaseGeo, interiorMaterial);
            pSeatBase.position.set(0.35, groundClearance + 0.3 * scaleY, 0.05 * scaleZ);
            seatGroup.add(pSeatBase);

            var pSeatBack = new THREE.Mesh(seatBackGeo, interiorMaterial);
            pSeatBack.position.set(0.35, groundClearance + 0.52 * scaleY, -0.15 * scaleZ);
            pSeatBack.rotation.x = -0.15;
            seatGroup.add(pSeatBack);

            // Steering Wheel
            var wheelRingGeo = new THREE.TorusGeometry(0.12, 0.016, 12, 24);
            var wheelRingMat = new THREE.MeshStandardMaterial({ color: 0x111113, roughness: 0.4 });
            var steering = new THREE.Mesh(wheelRingGeo, wheelRingMat);
            steering.position.set(-0.35, groundClearance + 0.48 * scaleY, 0.42 * scaleZ);
            steering.rotation.x = 0.35;
            seatGroup.add(steering);

            carGroup.add(seatGroup);
        }

        // Custom Wheels, Rims, Calipers & Drilled Brake Discs
        function buildWheels(scaleY, scaleZ, groundClearance, carbonMaterial) {
            var wheelPositions = [
                { x: -0.84, y: 0.31, z: 1.15 * scaleZ, isLeft: true },  // Front Left
                { x: 0.84, y: 0.31, z: 1.15 * scaleZ, isLeft: false },  // Front Right
                { x: -0.86, y: 0.32, z: -1.15 * scaleZ, isLeft: true }, // Rear Left
                { x: 0.86, y: 0.32, z: -1.15 * scaleZ, isLeft: false }  // Rear Right
            ];

            // Materials
            var tireMat = new THREE.MeshStandardMaterial({
                color: 0x18191b,
                roughness: 0.85,
                metalness: 0.1
            });

            var rimMat = new THREE.MeshStandardMaterial({
                color: new THREE.Color(state.wheelColor),
                metalness: 0.9,
                roughness: 0.18
            });

            var discMat = new THREE.MeshStandardMaterial({
                color: 0x8a929c,
                metalness: 0.92,
                roughness: 0.28
            });

            var caliperMat = new THREE.MeshStandardMaterial({
                color: new THREE.Color(state.caliperColor),
                metalness: 0.6,
                roughness: 0.25
            });

            var tireGeo = new THREE.CylinderGeometry(0.31, 0.31, 0.22, 28);
            var discGeo = new THREE.CylinderGeometry(0.22, 0.22, 0.02, 24);
            var caliperGeo = new THREE.BoxGeometry(0.08, 0.16, 0.06);

            wheelPositions.forEach(function (pos, index) {
                var singleWheelGroup = new THREE.Group();
                singleWheelGroup.position.set(pos.x, pos.y, pos.z);

                // Rubber Tire
                var tire = new THREE.Mesh(tireGeo, tireMat);
                tire.rotation.z = Math.PI / 2;
                tire.castShadow = true;
                singleWheelGroup.add(tire);

                // Brake Disc
                var disc = new THREE.Mesh(discGeo, discMat);
                disc.rotation.z = Math.PI / 2;
                disc.position.x = pos.isLeft ? 0.03 : -0.03;
                singleWheelGroup.add(disc);

                // Brake Caliper
                var caliper = new THREE.Mesh(caliperGeo, caliperMat);
                caliper.position.set(pos.isLeft ? 0.04 : -0.04, 0.12, 0.05);
                caliper.rotation.y = pos.isLeft ? 0 : Math.PI;
                singleWheelGroup.add(caliper);
                caliperMeshes.push(caliper);

                // Custom Rim Spokes Group
                var rimAssembly = new THREE.Group();
                rimAssembly.position.x = pos.isLeft ? -0.04 : 0.04;

                // Center Hub
                var hubGeo = new THREE.CylinderGeometry(0.08, 0.08, 0.04, 16);
                var hub = new THREE.Mesh(hubGeo, rimMat);
                hub.rotation.z = Math.PI / 2;
                rimAssembly.add(hub);

                // Center Crest
                var crestGeo = new THREE.CylinderGeometry(0.03, 0.03, 0.042, 16);
                var crestMat = new THREE.MeshStandardMaterial({ color: 0xd4af37, metalness: 0.9 });
                var crest = new THREE.Mesh(crestGeo, crestMat);
                crest.rotation.z = Math.PI / 2;
                rimAssembly.add(crest);

                // Spokes based on wheel style
                var spokeCount = 5;
                if (state.wheelStyle === 'spyder') spokeCount = 10;
                else if (state.wheelStyle === 'gt3') spokeCount = 7;
                else if (state.wheelStyle === 'mission_e') spokeCount = 5;
                else if (state.wheelStyle === 'carrera_s') spokeCount = 5;

                for (var s = 0; s < spokeCount; s++) {
                    var angle = (s / spokeCount) * Math.PI * 2;
                    var spokeGeo;
                    if (state.wheelStyle === 'spyder') {
                        spokeGeo = new THREE.BoxGeometry(0.02, 0.28, 0.02);
                    } else if (state.wheelStyle === 'gt3') {
                        spokeGeo = new THREE.BoxGeometry(0.026, 0.28, 0.022);
                    } else {
                        spokeGeo = new THREE.BoxGeometry(0.04, 0.28, 0.024);
                    }
                    var spoke = new THREE.Mesh(spokeGeo, rimMat);
                    spoke.position.set(0, Math.cos(angle) * 0.14, Math.sin(angle) * 0.14);
                    spoke.rotation.x = -angle;
                    rimAssembly.add(spoke);
                }

                // Outer Rim Lip
                var rimLipGeo = new THREE.TorusGeometry(0.28, 0.016, 12, 28);
                var rimLip = new THREE.Mesh(rimLipGeo, rimMat);
                rimLip.rotation.y = Math.PI / 2;
                rimAssembly.add(rimLip);

                singleWheelGroup.add(rimAssembly);
                carGroup.add(singleWheelGroup);
                wheelMeshes.push(singleWheelGroup);
            });
        }

        function onWindowResize() {
            if (!container) return;
            width = container.clientWidth || 800;
            height = container.clientHeight || 550;
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
            renderer.setSize(width, height);
        }

        function animate() {
            requestAnimationFrame(animate);

            if (controls) {
                controls.update();
            }

            if (isRotating && carGroup) {
                carGroup.rotation.y += 0.007;
                if (groundPodium) {
                    groundPodium.rotation.y += 0.007;
                }
            }

            renderer.render(scene, camera);
        }

        // Public API Methods
        return {
            setColor: function (hexColor) {
                state.color = hexColor;
                if (bodyMesh && bodyMesh.material) {
                    bodyMesh.material.color.set(hexColor);
                }
                // Update other body components
                carGroup.traverse(function (child) {
                    if (child.isMesh && child.material && child.material === bodyMesh.material) {
                        child.material.color.set(hexColor);
                    }
                });
            },

            setWheel: function (colorHex, styleName) {
                state.wheelColor = colorHex || state.wheelColor;
                state.wheelStyle = styleName || state.wheelStyle;
                buildCar();
            },

            setCaliper: function (hexColor) {
                state.caliperColor = hexColor;
                caliperMeshes.forEach(function (mesh) {
                    if (mesh && mesh.material) {
                        mesh.material.color.set(hexColor);
                    }
                });
            },

            setInterior: function (hexColor) {
                state.interiorColor = hexColor;
                buildCar();
            },

            setAero: function (aeroCode) {
                state.aeroCode = aeroCode;
                buildCar();
            },

            setCarModel: function (carType) {
                state.carType = carType;
                buildCar();
            },

            toggleTurntable: function () {
                isRotating = !isRotating;
                return isRotating;
            },

            toggleLights: function () {
                lightsOn = !lightsOn;
                headlightLenses.forEach(function (lens) {
                    lens.material.color.set(lightsOn ? 0xe6f3ff : 0x333344);
                });
                if (taillightMesh) {
                    taillightMesh.material.emissiveIntensity = lightsOn ? 0.95 : 0.08;
                    taillightMesh.material.color.set(lightsOn ? 0xff0020 : 0x550005);
                }
                return lightsOn;
            },

            setCameraPreset: function (preset) {
                if (!controls) return;
                var duration = 800;
                var targetPos = { x: 4.2, y: 1.6, z: 4.4 };
                var lookAt = { x: 0, y: 0.55, z: 0 };

                switch (preset) {
                    case 'front34':
                        targetPos = { x: 3.8, y: 1.3, z: 3.8 };
                        break;
                    case 'side':
                        targetPos = { x: 4.6, y: 0.9, z: 0.0 };
                        break;
                    case 'rear34':
                        targetPos = { x: -3.6, y: 1.4, z: -3.8 };
                        break;
                    case 'wheels':
                        targetPos = { x: 1.9, y: 0.5, z: 1.5 };
                        lookAt = { x: 0.8, y: 0.3, z: 1.15 };
                        break;
                    case 'cockpit':
                        targetPos = { x: 0.8, y: 1.2, z: 0.8 };
                        lookAt = { x: -0.2, y: 0.7, z: 0.2 };
                        break;
                    case 'top':
                        targetPos = { x: 0.01, y: 5.6, z: 0.01 };
                        break;
                }

                // Smooth camera transition
                var startPos = { x: camera.position.x, y: camera.position.y, z: camera.position.z };
                var startTime = performance.now();

                function transition(now) {
                    var elapsed = now - startTime;
                    var t = Math.min(elapsed / duration, 1);
                    var ease = t * (2 - t); // ease-out

                    camera.position.x = startPos.x + (targetPos.x - startPos.x) * ease;
                    camera.position.y = startPos.y + (targetPos.y - startPos.y) * ease;
                    camera.position.z = startPos.z + (targetPos.z - startPos.z) * ease;
                    controls.target.set(lookAt.x, lookAt.y, lookAt.z);

                    if (t < 1) {
                        requestAnimationFrame(transition);
                    }
                }
                requestAnimationFrame(transition);
            },

            captureScreenshot: function () {
                renderer.render(scene, camera);
                return renderer.domElement.toDataURL('image/png');
            },

            init: init
        };
    };

    // Synthetic Porsche Flat-Six Engine Sound Generator (Web Audio API)
    window.playPorscheEngineSound = function () {
        try {
            var AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            var ctx = new AudioContext();

            // Starter ignition sound
            var now = ctx.currentTime;
            var osc1 = ctx.createOscillator();
            var osc2 = ctx.createOscillator();
            var gain = ctx.createGain();

            // Flat-6 low rumble
            osc1.type = 'sawtooth';
            osc1.frequency.setValueAtTime(65, now);
            osc1.frequency.exponentialRampToValueAtTime(140, now + 0.3);
            osc1.frequency.exponentialRampToValueAtTime(320, now + 0.9);
            osc1.frequency.exponentialRampToValueAtTime(120, now + 1.8);

            osc2.type = 'triangle';
            osc2.frequency.setValueAtTime(130, now);
            osc2.frequency.exponentialRampToValueAtTime(280, now + 0.3);
            osc2.frequency.exponentialRampToValueAtTime(640, now + 0.9);
            osc2.frequency.exponentialRampToValueAtTime(240, now + 1.8);

            // Filter for throaty exhaust sound
            var filter = ctx.createBiquadFilter();
            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(350, now);
            filter.frequency.exponentialRampToValueAtTime(1800, now + 0.9);
            filter.frequency.exponentialRampToValueAtTime(450, now + 1.8);

            gain.gain.setValueAtTime(0.01, now);
            gain.gain.linearRampToValueAtTime(0.4, now + 0.2);
            gain.gain.linearRampToValueAtTime(0.55, now + 0.9);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 2.2);

            osc1.connect(filter);
            osc2.connect(filter);
            filter.connect(gain);
            gain.connect(ctx.destination);

            osc1.start(now);
            osc2.start(now);
            osc1.stop(now + 2.2);
            osc2.stop(now + 2.2);
        } catch (e) {
            console.log('Audio not supported or blocked by user gesture:', e);
        }
    };
})();
