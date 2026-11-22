var configForm = document.getElementById('cfg');
var summaryBox = document.getElementById('summary');
var modelSelect = document.getElementById('modelSelect');
var shownTotal = 0;
var countTimer = null;


function formatMoney(number) {
    return '$' + number.toLocaleString('en-US');
}


function getSelectedPrice(inputName) {
    var selectedInput = configForm.querySelector('input[name="' + inputName + '"]:checked');

    if (selectedInput) {
        return Number(selectedInput.dataset.price);
    }

    return 0;
}


function animateTotal(newTotal) {
    clearInterval(countTimer);

    var startValue = shownTotal;
    var difference = newTotal - startValue;
    var totalSteps = 10;
    var currentStep = 1;

    countTimer = setInterval(function () {
        currentStep = currentStep + 1;
        shownTotal = startValue + (difference * currentStep / totalSteps);

        var totalBox = document.getElementById('totalValue');
        if (totalBox) {
            totalBox.textContent = formatMoney(Math.round(shownTotal));
        }

        if (currentStep >= totalSteps) {
            clearInterval(countTimer);
        }
    }, 20);
}


function updatePrice() {
    var basePrice = Number(configForm.dataset.base);
    var colorPrice = getSelectedPrice('color');
    var wheelPrice = getSelectedPrice('wheel');

    var featuresPrice = 0;
    var checkedFeatures = configForm.querySelectorAll('input[name="features[]"]:checked');

    for (var i = 0; i < checkedFeatures.length; i++) {
        featuresPrice = featuresPrice + Number(checkedFeatures[i].dataset.price);
    }

    var totalPrice = basePrice + colorPrice + wheelPrice + featuresPrice;

    summaryBox.innerHTML =
        '<p>Base: ' + formatMoney(basePrice) + '</p>' +
        '<p>Colour: ' + formatMoney(colorPrice) + '</p>' +
        '<p>Wheels: ' + formatMoney(wheelPrice) + '</p>' +
        '<p>Options: ' + formatMoney(featuresPrice) + '</p>' +
        '<p class="price">Total <span id="totalValue">' + formatMoney(Math.round(shownTotal)) + '</span></p>';

    animateTotal(totalPrice);
}


function highlightSelectedColor() {
    var colorLabels = configForm.querySelectorAll('.sw');

    for (var i = 0; i < colorLabels.length; i++) {
        var colorInput = colorLabels[i].querySelector('input');
        var colorBox = colorLabels[i].querySelector('span');

        colorBox.style.transition = 'box-shadow 0.2s ease';

        if (colorInput.checked) {
            colorBox.style.boxShadow = '0 0 0 3px #D5001C';
        } else {
            colorBox.style.boxShadow = 'none';
        }
    }
}


function startConfigurator() {
    configForm.querySelector('input[name="color"]').checked = true;
    configForm.querySelector('input[name="wheel"]').checked = true;
    modelSelect.value = configForm.dataset.id;

    configForm.addEventListener('change', function (event) {
        updatePrice();
        highlightSelectedColor();

        if (event.target.type == 'checkbox' && typeof toast == 'function') {
            if (event.target.checked) {
                toast('Option added');
            } else {
                toast('Option removed');
            }
        }
    });

    modelSelect.addEventListener('change', function () {
        window.location = 'configurator.php?id=' + modelSelect.value;
    });

    updatePrice();
    highlightSelectedColor();
}


if (configForm && summaryBox) {
    startConfigurator();
}