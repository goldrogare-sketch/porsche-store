var filterForm = document.getElementById('filters');
var carsGrid = document.getElementById('grid');
var noResultsMessage = document.getElementById('noResults');
var allCarCards = carsGrid.querySelectorAll('.card');


function animateCard(card, delay) {
    card.style.opacity = '0';

    setTimeout(function () {
        card.style.opacity = '1';
    }, delay);
}


function applyFilters(shouldAnimate) {
    var chosenCategory = filterForm.category.value;
    var chosenEngine = filterForm.engine.value;
    var maxPrice = Number(filterForm.max.value);
    var chosenSort = filterForm.sort.value;

    var visibleCards = [];

    for (var i = 0; i < allCarCards.length; i++) {
        var card = allCarCards[i];
        var shouldShow = true;

        if (chosenCategory != '' && card.dataset.category != chosenCategory) {
            shouldShow = false;
        }

        if (chosenEngine != '' && card.dataset.engine != chosenEngine) {
            shouldShow = false;
        }

        if (maxPrice > 0 && Number(card.dataset.price) > maxPrice) {
            shouldShow = false;
        }

        if (shouldShow) {
            card.style.display = '';
            visibleCards.push(card);
        } else {
            card.style.display = 'none';
        }
    }

    visibleCards.sort(function (firstCard, secondCard) {
        if (chosenSort == 'price_desc') {
            return secondCard.dataset.price - firstCard.dataset.price;
        }

        if (chosenSort == 'newest') {
            return secondCard.dataset.year - firstCard.dataset.year;
        }

        return firstCard.dataset.price - secondCard.dataset.price;
    });

    for (var j = 0; j < visibleCards.length; j++) {
        carsGrid.appendChild(visibleCards[j]);

        if (shouldAnimate) {
            animateCard(visibleCards[j], 50 + j * 100);
        }
    }

    if (visibleCards.length == 0) {
        noResultsMessage.style.display = 'block';
    } else {
        noResultsMessage.style.display = 'none';
    }
}


filterForm.addEventListener('submit', function (event) {
    event.preventDefault();
    applyFilters(true);
});

filterForm.addEventListener('input', function () {
    applyFilters(true);
});

applyFilters(false);