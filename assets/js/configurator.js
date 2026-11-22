/**
 * Porsche 3D Configurator Client Controller
 * Manages UI interactions, 3D model synchronization, and dynamic pricing calculations.
 */

(function () {
    'use strict';

    var form = document.getElementById('configuratorForm');
    var modelSelector = document.getElementById('modelSelector');
    var priceTicker = document.getElementById('totalPriceTicker');
    var summaryBreakdown = document.getElementById('summaryBreakdown');

    if (!form || !modelSelector) return;

    var currentTotal = Number(form.dataset.base) || 120000;
    var displayedPrice = currentTotal;
    var priceAnimTimer = null;
    var porsche3D = null;

    // Helper: Format Currency
    function formatMoney(amount) {
        return '$' + Math.round(amount).toLocaleString('en-US');
    }

    // Number ticker animation
    function animatePriceTo(targetPrice) {
        clearInterval(priceAnimTimer);
        var startPrice = displayedPrice;
        var diff = targetPrice - startPrice;
        var steps = 15;
        var stepCount = 0;

        if (diff === 0) {
            priceTicker.textContent = Math.round(targetPrice).toLocaleString('en-US');
            return;
        }

        priceAnimTimer = setInterval(function () {
            stepCount++;
            displayedPrice = startPrice + (diff * (stepCount / steps));
            if (priceTicker) {
                priceTicker.textContent = Math.round(displayedPrice).toLocaleString('en-US');
            }
            if (stepCount >= steps) {
                clearInterval(priceAnimTimer);
                displayedPrice = targetPrice;
                if (priceTicker) {
                    priceTicker.textContent = Math.round(targetPrice).toLocaleString('en-US');
                }
            }
        }, 20);
    }

    // Initialize 3D Engine
    function init3DStudio() {
        var loader = document.getElementById('canvasLoader');
        var initialColorInput = form.querySelector('input[name="color"]:checked');
        var initialWheelInput = form.querySelector('input[name="wheel"]:checked');
        var initialCaliperInput = form.querySelector('input[name="caliper"]:checked');
        var initialInteriorInput = form.querySelector('input[name="interior"]:checked');
        var initialAeroInput = form.querySelector('input[name="aero"]:checked');

        var initialCarType = form.dataset.category || 'sports';

        if (typeof window.Porsche3D === 'function') {
            porsche3D = window.Porsche3D('porsche3DStage', {
                color: initialColorInput ? initialColorInput.dataset.hex : '#D5001C',
                wheelColor: initialWheelInput ? initialWheelInput.dataset.color : '#d4d8dd',
                wheelStyle: initialWheelInput ? initialWheelInput.dataset.style : 'classic',
                caliperColor: initialCaliperInput ? initialCaliperInput.dataset.hex : '#1e1f22',
                interiorColor: initialInteriorInput ? initialInteriorInput.dataset.hex : '#212224',
                aeroCode: initialAeroInput ? initialAeroInput.dataset.code : 'standard',
                carType: initialCarType
            });

            if (porsche3D) {
                porsche3D.init();
            }

            if (loader) {
                setTimeout(function () {
                    loader.style.opacity = '0';
                    setTimeout(function () { loader.style.display = 'none'; }, 400);
                }, 300);
            }
        }
    }

    // Recalculate Complete Pricing & Update Summary Breakdown
    function updateCalculations() {
        var basePrice = Number(form.dataset.base) || 0;

        var colorInput = form.querySelector('input[name="color"]:checked');
        var colorPrice = colorInput ? Number(colorInput.dataset.price) : 0;
        var colorName = colorInput ? colorInput.dataset.name : 'Standard';

        var wheelInput = form.querySelector('input[name="wheel"]:checked');
        var wheelPrice = wheelInput ? Number(wheelInput.dataset.price) : 0;
        var wheelName = wheelInput ? wheelInput.dataset.name : 'Standard';

        var caliperInput = form.querySelector('input[name="caliper"]:checked');
        var caliperPrice = caliperInput ? Number(caliperInput.dataset.price) : 0;
        var caliperName = caliperInput ? caliperInput.dataset.name : 'Standard';

        var interiorInput = form.querySelector('input[name="interior"]:checked');
        var interiorPrice = interiorInput ? Number(interiorInput.dataset.price) : 0;
        var interiorName = interiorInput ? interiorInput.dataset.name : 'Standard';

        var aeroInput = form.querySelector('input[name="aero"]:checked');
        var aeroPrice = aeroInput ? Number(aeroInput.dataset.price) : 0;
        var aeroName = aeroInput ? aeroInput.dataset.name : 'Standard';

        var featuresPrice = 0;
        var selectedFeaturesList = [];
        var checkedFeatures = form.querySelectorAll('input[name="features[]"]:checked');
        for (var i = 0; i < checkedFeatures.length; i++) {
            var fPrice = Number(checkedFeatures[i].dataset.price);
            featuresPrice += fPrice;
            selectedFeaturesList.push(checkedFeatures[i].dataset.name + ' (' + formatMoney(fPrice) + ')');
        }

        var grandTotal = basePrice + colorPrice + wheelPrice + caliperPrice + interiorPrice + aeroPrice + featuresPrice;
        currentTotal = grandTotal;

        // Animate Price Display
        animatePriceTo(grandTotal);

        // Update Summary Pane
        if (summaryBreakdown) {
            var selectedOption = modelSelector.options[modelSelector.selectedIndex];
            var carTitle = selectedOption ? selectedOption.text.split('•')[0].trim() : 'Porsche Model';

            var featuresHtml = selectedFeaturesList.length > 0 
                ? '<div class="summary-subitems">' + selectedFeaturesList.map(function (f) { return '<span>&bull; ' + f + '</span>'; }).join('') + '</div>'
                : '<span class="summary-val">Standard Equipment</span>';

            summaryBreakdown.innerHTML = 
                '<div class="summary-row"><span>Base Vehicle (' + carTitle + ')</span><span class="summary-val">' + formatMoney(basePrice) + '</span></div>' +
                '<div class="summary-row"><span>Exterior Paint (' + colorName + ')</span><span class="summary-val">' + (colorPrice === 0 ? 'Included' : formatMoney(colorPrice)) + '</span></div>' +
                '<div class="summary-row"><span>Wheels & Rims (' + wheelName + ')</span><span class="summary-val">' + (wheelPrice === 0 ? 'Included' : formatMoney(wheelPrice)) + '</span></div>' +
                '<div class="summary-row"><span>Brake System (' + caliperName + ')</span><span class="summary-val">' + (caliperPrice === 0 ? 'Included' : formatMoney(caliperPrice)) + '</span></div>' +
                '<div class="summary-row"><span>Interior Leather (' + interiorName + ')</span><span class="summary-val">' + (interiorPrice === 0 ? 'Included' : formatMoney(interiorPrice)) + '</span></div>' +
                '<div class="summary-row"><span>Aerodynamics & Trim (' + aeroName + ')</span><span class="summary-val">' + (aeroPrice === 0 ? 'Included' : formatMoney(aeroPrice)) + '</span></div>' +
                '<div class="summary-row"><span>Equipment Packages (' + checkedFeatures.length + ' selected)</span><span class="summary-val">' + formatMoney(featuresPrice) + '</span></div>' +
                featuresHtml +
                '<div class="summary-divider"></div>' +
                '<div class="summary-row summary-grand-total"><span>Total Configuration Price</span><span class="summary-highlight">' + formatMoney(grandTotal) + '</span></div>';
        }

        // Update Modal elements
        var modalCarName = document.getElementById('modalCarName');
        var modalTotalPrice = document.getElementById('modalTotalPrice');
        var modalSpecsSummary = document.getElementById('modalSpecsSummary');

        if (modalCarName) {
            var curOpt = modelSelector.options[modelSelector.selectedIndex];
            modalCarName.textContent = curOpt ? curOpt.text.split('•')[0].trim() : 'Porsche';
        }
        if (modalTotalPrice) {
            modalTotalPrice.textContent = formatMoney(grandTotal);
        }
        if (modalSpecsSummary) {
            modalSpecsSummary.innerHTML = 
                '<span class="spec-tag"><i class="fa-solid fa-palette"></i> ' + colorName + '</span>' +
                '<span class="spec-tag"><i class="fa-solid fa-dharmachakra"></i> ' + wheelName + '</span>' +
                '<span class="spec-tag"><i class="fa-solid fa-couch"></i> ' + interiorName + '</span>' +
                '<span class="spec-tag"><i class="fa-solid fa-wind"></i> ' + aeroName + '</span>';
        }
    }

    // Setup Customization Tabs
    function setupTabs() {
        var tabs = document.querySelectorAll('.cfg-tab');
        var panes = document.querySelectorAll('.cfg-tab-pane');

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var targetId = tab.dataset.tab;
                tabs.forEach(function (t) { t.classList.remove('active'); });
                panes.forEach(function (p) { p.classList.remove('active'); });

                tab.classList.add('active');
                var activePane = document.getElementById(targetId);
                if (activePane) activePane.classList.add('active');

                // Camera preset focus based on tab
                if (porsche3D) {
                    if (targetId === 'tab-color') porsche3D.setCameraPreset('front34');
                    else if (targetId === 'tab-wheels' || targetId === 'tab-brakes') porsche3D.setCameraPreset('wheels');
                    else if (targetId === 'tab-interior') porsche3D.setCameraPreset('cockpit');
                    else if (targetId === 'tab-aero') porsche3D.setCameraPreset('rear34');
                }
            });
        });

        // Quick button to open breakdown
        var btnOpenBreakdown = document.getElementById('btnOpenBreakdown');
        if (btnOpenBreakdown) {
            btnOpenBreakdown.addEventListener('click', function () {
                var summaryTabBtn = document.querySelector('.cfg-tab[data-tab="tab-summary"]');
                if (summaryTabBtn) summaryTabBtn.click();
            });
        }
    }

    // Setup UI Change Listeners & 3D Synchronization
    function setupInputListeners() {
        // 1. Color Selection
        var colorInputs = form.querySelectorAll('input[name="color"]');
        colorInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                var allCards = form.querySelectorAll('.color-swatch-card');
                allCards.forEach(function (c) { c.classList.remove('selected'); });
                input.closest('.color-swatch-card').classList.add('selected');

                if (porsche3D) {
                    porsche3D.setColor(input.dataset.hex);
                }
                updateCalculations();
            });
        });

        // 2. Wheel Selection
        var wheelInputs = form.querySelectorAll('input[name="wheel"]');
        wheelInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                var allCards = form.querySelectorAll('.wheel-card');
                allCards.forEach(function (c) { c.classList.remove('selected'); });
                input.closest('.wheel-card').classList.add('selected');

                if (porsche3D) {
                    porsche3D.setWheel(input.dataset.color, input.dataset.style);
                }
                updateCalculations();
            });
        });

        // 3. Caliper Selection
        var caliperInputs = form.querySelectorAll('input[name="caliper"]');
        caliperInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                var allCards = form.querySelectorAll('.caliper-card');
                allCards.forEach(function (c) { c.classList.remove('selected'); });
                input.closest('.caliper-card').classList.add('selected');

                if (porsche3D) {
                    porsche3D.setCaliper(input.dataset.hex);
                }
                updateCalculations();
            });
        });

        // 4. Interior Selection
        var interiorInputs = form.querySelectorAll('input[name="interior"]');
        interiorInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                var allCards = form.querySelectorAll('.interior-card');
                allCards.forEach(function (c) { c.classList.remove('selected'); });
                input.closest('.interior-card').classList.add('selected');

                if (porsche3D) {
                    porsche3D.setInterior(input.dataset.hex);
                }
                updateCalculations();
            });
        });

        // 5. Aerokit Selection
        var aeroInputs = form.querySelectorAll('input[name="aero"]');
        aeroInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                var allCards = form.querySelectorAll('.aero-card');
                allCards.forEach(function (c) { c.classList.remove('selected'); });
                input.closest('.aero-card').classList.add('selected');

                if (porsche3D) {
                    porsche3D.setAero(input.dataset.code);
                }
                updateCalculations();
            });
        });

        // 6. Features / Options Checkboxes
        var featureInputs = form.querySelectorAll('input[name="features[]"]');
        featureInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                var card = input.closest('.feature-checkbox-card');
                if (input.checked) {
                    card.classList.add('selected');
                    if (typeof window.toast === 'function') window.toast('Added: ' + input.dataset.name);
                } else {
                    card.classList.remove('selected');
                    if (typeof window.toast === 'function') window.toast('Removed: ' + input.dataset.name);
                }
                updateCalculations();
            });
        });

        // 7. Model Selector Dropdown
        modelSelector.addEventListener('change', function () {
            var selectedOption = modelSelector.options[modelSelector.selectedIndex];
            var newId = selectedOption.value;
            var basePrice = Number(selectedOption.dataset.base);
            var category = selectedOption.dataset.category || 'sports';

            // Update form dataset
            form.dataset.base = basePrice;
            form.dataset.id = newId;
            form.dataset.category = category;
            document.getElementById('inputCarId').value = newId;

            // Update quick specs display
            document.getElementById('specHp').textContent = selectedOption.dataset.hp + ' hp';
            document.getElementById('specAccel').textContent = selectedOption.dataset.accel + ' s';
            document.getElementById('specSpeed').textContent = selectedOption.dataset.speed + ' km/h';
            document.getElementById('specEngine').textContent = selectedOption.dataset.engine;

            // Update 3D car body model
            if (porsche3D) {
                porsche3D.setCarModel(category);
            }

            updateCalculations();
            if (typeof window.toast === 'function') {
                window.toast('Switched model to ' + selectedOption.text.split('•')[0].trim());
            }
        });
    }

    // Setup 3D Floating Dock Buttons
    function setupFloatingDock() {
        // Camera Presets
        var presetButtons = document.querySelectorAll('.dock-btn[data-preset]');
        presetButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                presetButtons.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                var preset = btn.dataset.preset;
                if (porsche3D) {
                    porsche3D.setCameraPreset(preset);
                }
            });
        });

        // Turntable Auto-Rotate
        var btnTurntable = document.getElementById('btnTurntable');
        if (btnTurntable) {
            btnTurntable.addEventListener('click', function () {
                if (porsche3D) {
                    var rotating = porsche3D.toggleTurntable();
                    btnTurntable.classList.toggle('active', rotating);
                    if (typeof window.toast === 'function') {
                        window.toast(rotating ? 'Studio Turntable Active' : 'Turntable Stopped');
                    }
                }
            });
        }

        // Toggle Lights
        var btnToggleLights = document.getElementById('btnToggleLights');
        if (btnToggleLights) {
            btnToggleLights.addEventListener('click', function () {
                if (porsche3D) {
                    var lightsActive = porsche3D.toggleLights();
                    btnToggleLights.classList.toggle('active', lightsActive);
                    if (typeof window.toast === 'function') {
                        window.toast(lightsActive ? 'Matrix LED Lights On' : 'Lights Off');
                    }
                }
            });
        }

        // Engine Rev Audio
        var btnEngineSound = document.getElementById('btnEngineSound');
        if (btnEngineSound) {
            btnEngineSound.addEventListener('click', function () {
                btnEngineSound.classList.add('revving');
                if (typeof window.playPorscheEngineSound === 'function') {
                    window.playPorscheEngineSound();
                }
                setTimeout(function () {
                    btnEngineSound.classList.remove('revving');
                }, 2200);
            });
        }

        // Screenshot Snapshot
        var btnTakeScreenshot = document.getElementById('btnTakeScreenshot');
        if (btnTakeScreenshot) {
            btnTakeScreenshot.addEventListener('click', function () {
                if (porsche3D) {
                    var dataUrl = porsche3D.captureScreenshot();
                    var link = document.createElement('a');
                    link.download = 'Porsche-Custom-Build-' + Date.now() + '.png';
                    link.href = dataUrl;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    if (typeof window.toast === 'function') {
                        window.toast('HD Configuration Snapshot Downloaded!');
                    }
                }
            });
        }
    }

    // Setup Reservation Modal
    function setupModal() {
        var launchBtn = document.getElementById('btnLaunchOrderModal');
        var modal = document.getElementById('orderModal');
        var closeBtn = document.getElementById('modalCloseBtn');
        var backdrop = document.getElementById('modalBackdrop');

        if (!modal) return;

        function openModal() {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }

        if (launchBtn) launchBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('open')) {
                closeModal();
            }
        });
    }

    // Initialize all components
    init3DStudio();
    setupTabs();
    setupInputListeners();
    setupFloatingDock();
    setupModal();
    updateCalculations();

})();
