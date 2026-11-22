(function () {
    'use strict';

    var filterForm = document.getElementById('filters');
    var carsGrid = document.getElementById('grid');
    var noResultsMessage = document.getElementById('noResults');
    var resultsCount = document.getElementById('resultsCount');
    var btnReset = document.getElementById('btnResetFilters');
    var btnClear = document.getElementById('btnClearFilters');

    if (!filterForm || !carsGrid) return;

    var allCarCards = Array.from(carsGrid.querySelectorAll('.luxury-car-card'));

    function applyFilters() {
        var searchTerm = (filterForm.search ? filterForm.search.value : '').trim().toLowerCase();
        var chosenCategory = filterForm.category ? filterForm.category.value.toLowerCase() : '';
        var chosenEngine = filterForm.engine ? filterForm.engine.value.toLowerCase() : '';
        var maxPrice = filterForm.max ? Number(filterForm.max.value) : 0;
        var chosenSort = filterForm.sort ? filterForm.sort.value : 'price_asc';

        var visibleCards = [];

        allCarCards.forEach(function (card) {
            var cardName = (card.dataset.name || '').toLowerCase();
            var cardCategory = (card.dataset.category || '').toLowerCase();
            var cardEngine = (card.dataset.engine || '').toLowerCase();
            var cardPrice = Number(card.dataset.price) || 0;

            var matchesSearch = searchTerm === '' || cardName.indexOf(searchTerm) !== -1;
            var matchesCategory = chosenCategory === '' || cardCategory === chosenCategory;
            var matchesEngine = chosenEngine === '' || cardEngine === chosenEngine;
            var matchesPrice = maxPrice <= 0 || cardPrice <= maxPrice;

            if (matchesSearch && matchesCategory && matchesEngine && matchesPrice) {
                card.style.display = '';
                visibleCards.push(card);
            } else {
                card.style.display = 'none';
            }
        });

        // Sorting
        visibleCards.sort(function (a, b) {
            var priceA = Number(a.dataset.price) || 0;
            var priceB = Number(b.dataset.price) || 0;
            var hpA = Number(a.dataset.hp) || 0;
            var hpB = Number(b.dataset.hp) || 0;
            var yearA = Number(a.dataset.year) || 0;
            var yearB = Number(b.dataset.year) || 0;

            if (chosenSort === 'price_desc') return priceB - priceA;
            if (chosenSort === 'hp_desc') return hpB - hpA;
            if (chosenSort === 'newest') return yearB - yearA;
            return priceA - priceB;
        });

        // Re-append in sorted order
        visibleCards.forEach(function (card) {
            carsGrid.appendChild(card);
        });

        // Update count & empty state
        if (resultsCount) {
            resultsCount.textContent = 'Showing ' + visibleCards.length + ' of ' + allCarCards.length + ' models';
        }

        if (visibleCards.length === 0) {
            if (noResultsMessage) noResultsMessage.style.display = 'block';
        } else {
            if (noResultsMessage) noResultsMessage.style.display = 'none';
        }
    }

    function resetFilters() {
        filterForm.reset();
        applyFilters();
    }

    filterForm.addEventListener('input', applyFilters);
    filterForm.addEventListener('change', applyFilters);
    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        applyFilters();
    });

    if (btnReset) btnReset.addEventListener('click', resetFilters);
    if (btnClear) btnClear.addEventListener('click', resetFilters);

    // Initial run
    applyFilters();
})();
