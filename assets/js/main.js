var secondsToShowWelcome = 2;
var secondsToShowNotice = 5;

var allCards = [];
var pageHeader = null;
var progressBar = null;
var topButton = null;


function toast(message) {
    var toastBox = document.getElementById('toast');

    if (!toastBox) {
        return;
    }

    toastBox.textContent = message;
    toastBox.style.display = 'block';

    setTimeout(function () {
        toastBox.style.display = 'none';
    }, 2500);
}


function markActiveMenuLink() {
    var currentPage = window.location.pathname.split('/').pop();

    if (currentPage == '') {
        currentPage = 'index.php';
    }

    if (currentPage.indexOf('admin') == 0) {
        currentPage = 'admin.php';
    }

    if (currentPage == 'vehicle-details.php') {
        currentPage = 'models.php';
    }

    var menuLinks = document.querySelectorAll('nav a');

    for (var i = 0; i < menuLinks.length; i++) {
        if (menuLinks[i].getAttribute('href') == currentPage) {
            menuLinks[i].classList.add('active');
        }
    }
}


function showOneCard(card, delay) {
    setTimeout(function () {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, delay);
}


function addHoverEffect(card) {
    card.addEventListener('mouseenter', function () {
        card.style.transform = 'translateY(-8px)';
        card.style.boxShadow = '0 12px 30px rgba(0, 0, 0, 0.25)';
    });

    card.addEventListener('mouseleave', function () {
        card.style.transform = 'translateY(0)';
        card.style.boxShadow = 'none';
    });
}


function showVisibleCards() {
    var delay = 0;

    for (var i = 0; i < allCards.length; i++) {
        var card = allCards[i];
        var notShownYet = card.dataset.shown != 'yes';
        var isOnScreen = card.getBoundingClientRect().top < window.innerHeight - 40;

        if (notShownYet && isOnScreen) {
            card.dataset.shown = 'yes';
            showOneCard(card, delay);
            delay = delay + 150;
        }
    }
}


function setupCards() {
    allCards = document.querySelectorAll('.card');

    for (var i = 0; i < allCards.length; i++) {
        allCards[i].style.opacity = '0';
        allCards[i].style.transform = 'translateY(30px)';
        allCards[i].style.transition = 'opacity 0.6s ease, transform 0.4s ease, box-shadow 0.3s ease';
        addHoverEffect(allCards[i]);
    }

    showVisibleCards();
}


function animateHero() {
    var heroText = document.querySelector('.hero div');

    if (!heroText) {
        return;
    }

    heroText.style.opacity = '0';
    heroText.style.transform = 'translateY(25px)';
    heroText.style.transition = 'opacity 0.9s ease, transform 0.9s ease';

    setTimeout(function () {
        heroText.style.opacity = '1';
        heroText.style.transform = 'translateY(0)';
    }, 200);
}


function fadeOutLater(element, seconds) {
    element.style.transition = 'opacity 0.5s ease';

    setTimeout(function () {
        element.style.opacity = '0';

        setTimeout(function () {
            element.style.display = 'none';
        }, 500);
    }, seconds * 1000);
}


function hideMessages() {
    var welcomeBox = document.getElementById('welcomeBox');

    if (welcomeBox) {
        fadeOutLater(welcomeBox, secondsToShowWelcome);
    }

    var notices = document.querySelectorAll('.notice');

    for (var i = 0; i < notices.length; i++) {
        fadeOutLater(notices[i], secondsToShowNotice);
    }
}


function fadeInImage(image) {
    if (image.complete) {
        return;
    }

    image.style.opacity = '0';
    image.style.transition = 'opacity 0.8s ease';

    image.addEventListener('load', function () {
        image.style.opacity = '1';
    });
}


function setupImages() {
    var images = document.querySelectorAll('.card img, .two img');

    for (var i = 0; i < images.length; i++) {
        fadeInImage(images[i]);
    }
}


function countUp(element) {
    var target = Number(element.dataset.number);
    var current = 0;
    var step = Math.ceil(target / 30);

    var counter = setInterval(function () {
        current = current + step;

        if (current >= target) {
            current = target;
            clearInterval(counter);
        }

        element.textContent = current;
    }, 30);
}


function setupCounters() {
    var counters = document.querySelectorAll('.count-up');

    for (var i = 0; i < counters.length; i++) {
        countUp(counters[i]);
    }
}


function handleScroll() {
    var scrolled = window.scrollY;
    var maxScroll = document.documentElement.scrollHeight - window.innerHeight;

    if (maxScroll > 0) {
        progressBar.style.width = (scrolled / maxScroll * 100) + '%';
    }

    if (pageHeader) {
        if (scrolled > 20) {
            pageHeader.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.35)';
        } else {
            pageHeader.style.boxShadow = 'none';
        }
    }

    if (scrolled > 300) {
        topButton.style.display = 'block';
    } else {
        topButton.style.display = 'none';
    }

    showVisibleCards();
}


function setupScrollEffects() {
    pageHeader = document.querySelector('.nav');

    if (pageHeader) {
        pageHeader.style.transition = 'box-shadow 0.3s ease';
    }

    progressBar = document.createElement('div');
    progressBar.style.position = 'fixed';
    progressBar.style.top = '0';
    progressBar.style.left = '0';
    progressBar.style.height = '3px';
    progressBar.style.width = '0';
    progressBar.style.background = '#D5001C';
    progressBar.style.zIndex = '2000';
    progressBar.style.transition = 'width 0.1s ease';
    document.body.appendChild(progressBar);

    topButton = document.createElement('button');
    topButton.textContent = '↑';
    topButton.style.position = 'fixed';
    topButton.style.right = '20px';
    topButton.style.bottom = '20px';
    topButton.style.width = '44px';
    topButton.style.height = '44px';
    topButton.style.borderRadius = '50%';
    topButton.style.border = 'none';
    topButton.style.background = '#D5001C';
    topButton.style.color = '#ffffff';
    topButton.style.fontSize = '20px';
    topButton.style.cursor = 'pointer';
    topButton.style.display = 'none';
    topButton.style.zIndex = '999';
    document.body.appendChild(topButton);

    topButton.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    window.addEventListener('scroll', handleScroll);
}


markActiveMenuLink();
setupImages();
setupCards();
animateHero();
hideMessages();
setupCounters();
setupScrollEffects();