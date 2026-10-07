/**
 * Connect MyUni — 3D Global Education Universe Matrix
 * ====================================================
 * An interactive, high-performance 3D visual animation
 * showcasing worldwide university hubs, particle flight arcs,
 * holographic globe spheres, and live interactive node focus.
 */
(function () {
    'use strict';

    function init3DMatrix() {
        var container = document.getElementById('cmi-3d-matrix-viewport');
        var canvas = document.getElementById('cmi-matrix-canvas');
        if (!container || !canvas || typeof window.THREE === 'undefined') {
            return;
        }

        var isWebGLAvailable = (function () {
            try {
                var c = document.createElement('canvas');
                return !!(window.WebGLRenderingContext && (c.getContext('webgl') || c.getContext('experimental-webgl')));
            } catch (e) {
                return false;
            }
        })();

        if (!isWebGLAvailable) {
            container.classList.add('cmi-webgl-fallback');
            return;
        }

        var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // ── Scene, Camera & Renderer ────────────────────────────────
        var scene = new THREE.Scene();
        var width = container.clientWidth || 540;
        var height = container.clientHeight || 520;

        var camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        camera.position.set(0, 7, 38);

        var renderer;
        try {
            renderer = new THREE.WebGLRenderer({
                canvas: canvas,
                alpha: true,
                antialias: true,
                powerPreference: 'high-performance'
            });
        } catch (e) {
            container.classList.add('cmi-webgl-fallback');
            return;
        }

        renderer.setSize(width, height);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

        // ── World & Globe Group ─────────────────────────────────────
        var globeGroup = new THREE.Group();
        scene.add(globeGroup);

        var GLOBE_RADIUS = 13.5;

        // 1. Inner Holographic Core Sphere
        var coreGeo = new THREE.SphereGeometry(GLOBE_RADIUS * 0.96, 36, 36);
        var coreMat = new THREE.MeshBasicMaterial({
            color: 0x09224d,
            transparent: true,
            opacity: 0.85,
            wireframe: false
        });
        var coreSphere = new THREE.Mesh(coreGeo, coreMat);
        globeGroup.add(coreSphere);

        // 2. Wireframe Lattice & Rings
        var wireGeo = new THREE.SphereGeometry(GLOBE_RADIUS, 28, 20);
        var wireMat = new THREE.MeshBasicMaterial({
            color: 0x1a56db,
            transparent: true,
            opacity: 0.22,
            wireframe: true
        });
        var wireSphere = new THREE.Mesh(wireGeo, wireMat);
        globeGroup.add(wireSphere);

        // 3. Globe Particle Constellation (World Dot Cloud)
        var dotsCount = 1300;
        var dotsGeo = new THREE.BufferGeometry();
        var dotPositions = new Float32Array(dotsCount * 3);
        var dotColors = new Float32Array(dotsCount * 3);

        var colorBlue = new THREE.Color(0x38bdf8);
        var colorOrange = new THREE.Color(0xf97316);
        var colorWhite = new THREE.Color(0xffffff);

        for (var i = 0; i < dotsCount; i++) {
            var phi = Math.acos(-1 + (2 * i) / dotsCount);
            var theta = Math.sqrt(dotsCount * Math.PI) * phi;

            var r = GLOBE_RADIUS + (Math.random() * 0.4 - 0.2);
            var x = r * Math.cos(theta) * Math.sin(phi);
            var y = r * Math.sin(theta) * Math.sin(phi);
            var z = r * Math.cos(phi);

            dotPositions[i * 3] = x;
            dotPositions[i * 3 + 1] = y;
            dotPositions[i * 3 + 2] = z;

            var c = Math.random() > 0.8 ? colorOrange : (Math.random() > 0.4 ? colorBlue : colorWhite);
            dotColors[i * 3] = c.r;
            dotColors[i * 3 + 1] = c.g;
            dotColors[i * 3 + 2] = c.b;
        }

        dotsGeo.setAttribute('position', new THREE.BufferAttribute(dotPositions, 3));
        dotsGeo.setAttribute('color', new THREE.BufferAttribute(dotColors, 3));

        var dotsMat = new THREE.PointsMaterial({
            size: 0.42,
            vertexColors: true,
            transparent: true,
            opacity: 0.85
        });
        var dotCloud = new THREE.Points(dotsGeo, dotsMat);
        globeGroup.add(dotCloud);

        // 4. Outer Orbital Rings
        function createOrbitalRing(radius, tiltX, tiltY, color, opacity) {
            var ringGeo = new THREE.RingGeometry(radius, radius + 0.12, 64);
            var ringMat = new THREE.MeshBasicMaterial({
                color: color,
                side: THREE.DoubleSide,
                transparent: true,
                opacity: opacity
            });
            var ringMesh = new THREE.Mesh(ringGeo, ringMat);
            ringMesh.rotation.x = tiltX;
            ringMesh.rotation.y = tiltY;
            return ringMesh;
        }

        var orbitRing1 = createOrbitalRing(GLOBE_RADIUS * 1.35, Math.PI / 2.8, 0.2, 0x38bdf8, 0.35);
        var orbitRing2 = createOrbitalRing(GLOBE_RADIUS * 1.55, -Math.PI / 3.2, 0.6, 0xf97316, 0.25);
        globeGroup.add(orbitRing1);
        globeGroup.add(orbitRing2);

        // ── University Hub Locations (Lat, Lon) ──────────────────────
        var HUBS = [
            { id: 'ng', name: 'Nigeria (Headquarters)', city: 'Lagos / Abuja', lat: 9.0820, lon: 8.6753, color: 0x22c55e, partner: 'West Africa Hub', badge: 'HQ Center' },
            { id: 'uk', name: 'United Kingdom', city: 'Nottingham & London', lat: 52.9548, lon: -1.1581, color: 0x38bdf8, partner: 'Nottingham Trent & UK Unis', badge: 'Top Destination' },
            { id: 'us', name: 'United States', city: 'Boston & New York', lat: 40.7128, lon: -74.0060, color: 0x60a5fa, partner: 'US Partner Colleges', badge: 'Global Ranking' },
            { id: 'ca', name: 'Canada', city: 'Toronto & Vancouver', lat: 43.6532, lon: -79.3832, color: 0xf43f5e, partner: 'Canadian Public Universities', badge: 'PR Pathways' },
            { id: 'my', name: 'Malaysia', city: 'Kuala Lumpur', lat: 3.1390, lon: 101.6869, color: 0xf59e0b, partner: 'Lincoln University College (LUC)', badge: '8,000+ Students' },
            { id: 'ph', name: 'Philippines', city: 'Cebu City', lat: 10.3157, lon: 123.8854, color: 0xf97316, partner: 'South Western University (SWU)', badge: '5,000+ African Students' },
            { id: 'au', name: 'Australia', city: 'Sydney & Melbourne', lat: -33.8688, lon: 151.2093, color: 0x10b981, partner: 'Australia Education Network', badge: 'High Visa Grant' }
        ];

        function latLonToVector3(lat, lon, radius) {
            var phi = (90 - lat) * (Math.PI / 180);
            var theta = (lon + 180) * (Math.PI / 180);
            var x = -(radius * Math.sin(phi) * Math.cos(theta));
            var z = (radius * Math.sin(phi) * Math.sin(theta));
            var y = (radius * Math.cos(phi));
            return new THREE.Vector3(x, y, z);
        }

        var hubObjects = [];
        var hubVectors = {};

        HUBS.forEach(function (hub) {
            var v = latLonToVector3(hub.lat, hub.lon, GLOBE_RADIUS + 0.15);
            hubVectors[hub.id] = v;

            var pinGroup = new THREE.Group();
            pinGroup.position.copy(v);
            pinGroup.lookAt(0, 0, 0);

            var beaconGeo = new THREE.SphereGeometry(0.55, 16, 16);
            var beaconMat = new THREE.MeshBasicMaterial({ color: hub.color });
            var beaconMesh = new THREE.Mesh(beaconGeo, beaconMat);
            pinGroup.add(beaconMesh);

            var haloGeo = new THREE.RingGeometry(0.7, 1.15, 24);
            var haloMat = new THREE.MeshBasicMaterial({
                color: hub.color,
                side: THREE.DoubleSide,
                transparent: true,
                opacity: 0.65
            });
            var haloMesh = new THREE.Mesh(haloGeo, haloMat);
            pinGroup.add(haloMesh);

            var stemGeo = new THREE.CylinderGeometry(0.08, 0.08, 1.4, 8);
            var stemMat = new THREE.MeshBasicMaterial({ color: hub.color, transparent: true, opacity: 0.7 });
            var stemMesh = new THREE.Mesh(stemGeo, stemMat);
            stemMesh.position.z = -0.7;
            stemMesh.rotation.x = Math.PI / 2;
            pinGroup.add(stemMesh);

            pinGroup.userData = { hub: hub };
            globeGroup.add(pinGroup);
            hubObjects.push({ group: pinGroup, halo: haloMesh, beacon: beaconMesh, hub: hub });
        });

        // ── 3D Flight / Trajectory Arcs (From Nigeria HQ) ────────────
        var originVec = hubVectors['ng'] || new THREE.Vector3(0, 0, GLOBE_RADIUS);
        var targetHubIds = ['uk', 'us', 'ca', 'my', 'ph', 'au'];
        var flightComets = [];

        targetHubIds.forEach(function (tid) {
            var destVec = hubVectors[tid];
            if (!destVec) return;

            var mid = new THREE.Vector3().addVectors(originVec, destVec).multiplyScalar(0.5);
            var distance = originVec.distanceTo(destVec);
            var altitude = GLOBE_RADIUS + Math.min(distance * 0.42, 6.5);
            mid.normalize().multiplyScalar(altitude);

            var curve = new THREE.QuadraticBezierCurve3(originVec, mid, destVec);
            var points = curve.getPoints(48);
            var curveGeo = new THREE.BufferGeometry().setFromPoints(points);

            var curveMat = new THREE.LineBasicMaterial({
                color: 0x38bdf8,
                transparent: true,
                opacity: 0.45
            });
            var curveLine = new THREE.Line(curveGeo, curveMat);
            globeGroup.add(curveLine);

            var cometGeo = new THREE.SphereGeometry(0.35, 10, 10);
            var cometMat = new THREE.MeshBasicMaterial({ color: 0xffedd5 });
            var cometMesh = new THREE.Mesh(cometGeo, cometMat);
            globeGroup.add(cometMesh);

            flightComets.push({
                mesh: cometMesh,
                curve: curve,
                t: Math.random(),
                speed: 0.0035 + Math.random() * 0.0025
            });
        });

        // ── Floating Stardust Particles in Background ───────────────
        var starsCount = 450;
        var starsGeo = new THREE.BufferGeometry();
        var starsPos = new Float32Array(starsCount * 3);
        for (var s = 0; s < starsCount; s++) {
            starsPos[s * 3] = (Math.random() - 0.5) * 110;
            starsPos[s * 3 + 1] = (Math.random() - 0.5) * 80;
            starsPos[s * 3 + 2] = (Math.random() - 0.5) * 80 - 15;
        }
        starsGeo.setAttribute('position', new THREE.BufferAttribute(starsPos, 3));
        var starsMat = new THREE.PointsMaterial({
            size: 0.7,
            color: 0x93c5fd,
            transparent: true,
            opacity: 0.45
        });
        var starField = new THREE.Points(starsGeo, starsMat);
        scene.add(starField);

        // Initial Orientation
        globeGroup.rotation.y = -0.7;
        globeGroup.rotation.x = 0.22;

        // ── Mouse Drag & Orbit Inertia ──────────────────────────────
        var isDragging = false;
        var previousMousePosition = { x: 0, y: 0 };
        var rotationVelocity = { x: 0, y: 0.003 };
        var isHovered = false;

        container.addEventListener('mousedown', function (e) {
            isDragging = true;
            previousMousePosition = { x: e.clientX, y: e.clientY };
        });

        window.addEventListener('mouseup', function () {
            isDragging = false;
        });

        window.addEventListener('mousemove', function (e) {
            if (!isDragging) return;
            var deltaX = e.clientX - previousMousePosition.x;
            var deltaY = e.clientY - previousMousePosition.y;

            rotationVelocity.y = deltaX * 0.005;
            rotationVelocity.x = deltaY * 0.005;

            globeGroup.rotation.y += rotationVelocity.y;
            globeGroup.rotation.x += rotationVelocity.x;

            previousMousePosition = { x: e.clientX, y: e.clientY };
        });

        container.addEventListener('touchstart', function (e) {
            if (e.touches.length === 1) {
                isDragging = true;
                previousMousePosition = { x: e.touches[0].clientX, y: e.touches[0].clientY };
            }
        }, { passive: true });

        window.addEventListener('touchend', function () {
            isDragging = false;
        });

        window.addEventListener('touchmove', function (e) {
            if (!isDragging || e.touches.length !== 1) return;
            var deltaX = e.touches[0].clientX - previousMousePosition.x;
            var deltaY = e.touches[0].clientY - previousMousePosition.y;

            globeGroup.rotation.y += deltaX * 0.006;
            globeGroup.rotation.x += deltaY * 0.006;

            previousMousePosition = { x: e.touches[0].clientX, y: e.touches[0].clientY };
        }, { passive: true });

        container.addEventListener('mouseenter', function () { isHovered = true; });
        container.addEventListener('mouseleave', function () { isHovered = false; });

        // ── UI Destination Filter Button Actions ────────────────────
        var hubCardTitle = document.getElementById('cmi-active-hub-title');
        var hubCardCity = document.getElementById('cmi-active-hub-city');
        var hubCardPartner = document.getElementById('cmi-active-hub-partner');
        var hubCardBadge = document.getElementById('cmi-active-hub-badge');
        var filterBtns = document.querySelectorAll('.cmi-3d-hub-btn');

        function setActiveHubUI(hub) {
            if (hubCardTitle) hubCardTitle.textContent = hub.name;
            if (hubCardCity) hubCardCity.textContent = hub.city;
            if (hubCardPartner) hubCardPartner.textContent = hub.partner;
            if (hubCardBadge) hubCardBadge.textContent = hub.badge;

            filterBtns.forEach(function (btn) {
                if (btn.getAttribute('data-hub') === hub.id) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        var REGION_ROTATIONS = {
            'all': { x: 0.22, y: -0.7 },
            'uk': { x: -0.35, y: -0.1 },
            'ng': { x: -0.05, y: -0.15 },
            'us': { x: -0.25, y: 1.35 },
            'ca': { x: -0.32, y: 1.45 },
            'my': { x: -0.05, y: -1.8 },
            'ph': { x: -0.15, y: -2.15 },
            'au': { x: 0.45, y: -2.6 }
        };

        var focusTargetRotation = null;

        filterBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var hubId = this.getAttribute('data-hub');
                if (hubId === 'all') {
                    focusTargetRotation = REGION_ROTATIONS['all'];
                    if (hubCardTitle) hubCardTitle.textContent = 'Global Network';
                    if (hubCardCity) hubCardCity.textContent = 'Worldwide University Placements';
                    if (hubCardPartner) hubCardPartner.textContent = '500+ Accredited International Universities';
                    if (hubCardBadge) hubCardBadge.textContent = 'Direct Advisory';
                    filterBtns.forEach(function (b) { b.classList.remove('active'); });
                    this.classList.add('active');
                    return;
                }

                var found = HUBS.find(function (h) { return h.id === hubId; });
                if (found) {
                    setActiveHubUI(found);
                    if (REGION_ROTATIONS[hubId]) {
                        focusTargetRotation = REGION_ROTATIONS[hubId];
                    }
                }
            });
        });

        // ── Responsive Resize ───────────────────────────────────────
        function onResize() {
            if (!container || !renderer || !camera) return;
            var w = container.clientWidth;
            var h = container.clientHeight || 520;
            camera.aspect = w / h;
            camera.updateProjectionMatrix();
            renderer.setSize(w, h);
        }
        window.addEventListener('resize', onResize);

        // ── Intersection Observer (Pause when offscreen) ────────────
        var isVisible = true;
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    isVisible = entry.isIntersecting;
                });
            }, { threshold: 0.1 });
            observer.observe(container);
        }

        // ── Animation Loop ──────────────────────────────────────────
        var clock = new THREE.Clock();
        var haloTime = 0;

        function animate() {
            requestAnimationFrame(animate);
            if (!isVisible) return;

            var delta = clock.getDelta();
            haloTime += delta * 2.5;

            if (focusTargetRotation) {
                globeGroup.rotation.y += (focusTargetRotation.y - globeGroup.rotation.y) * 0.06;
                globeGroup.rotation.x += (focusTargetRotation.x - globeGroup.rotation.x) * 0.06;
                if (Math.abs(focusTargetRotation.y - globeGroup.rotation.y) < 0.01 &&
                    Math.abs(focusTargetRotation.x - globeGroup.rotation.x) < 0.01) {
                    focusTargetRotation = null;
                }
            } else if (!isDragging) {
                if (!prefersReducedMotion) {
                    globeGroup.rotation.y += (isHovered ? 0.001 : 0.0028);
                }
            }

            hubObjects.forEach(function (item) {
                var s = 1 + Math.sin(haloTime + item.hub.lat) * 0.28;
                item.halo.scale.set(s, s, 1);
            });

            flightComets.forEach(function (comet) {
                comet.t += comet.speed;
                if (comet.t > 1) comet.t = 0;
                var pos = comet.curve.getPoint(comet.t);
                comet.mesh.position.copy(pos);
            });

            orbitRing1.rotation.z += 0.0015;
            orbitRing2.rotation.z -= 0.0012;
            starField.rotation.y += 0.0004;

            renderer.render(scene, camera);
        }

        animate();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init3DMatrix);
    } else {
        init3DMatrix();
    }
})();
