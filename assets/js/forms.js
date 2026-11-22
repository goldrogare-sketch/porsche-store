function selectCurrentValues() {
    var selectBoxes = document.querySelectorAll('select[data-selected]');

    for (var i = 0; i < selectBoxes.length; i++) {
        selectBoxes[i].value = selectBoxes[i].dataset.selected;
    }
}


function askBeforeDelete() {
    var deleteButtons = document.querySelectorAll('.confirm-delete');

    for (var i = 0; i < deleteButtons.length; i++) {
        deleteButtons[i].addEventListener('click', function (event) {
            var isSure = confirm('Are you sure?');

            if (isSure == false) {
                event.preventDefault();
            }
        });
    }
}


selectCurrentValues();
askBeforeDelete();