document.addEventListener('DOMContentLoaded', function () {

    // Winkelwagen wissen na succesvolle bestelling
    if (new URLSearchParams(window.location.search).get('succes') === '1') {
        localStorage.removeItem('snackcorner_cart');
        return;
    }

    // Hamburger menu (mobiel)
    var hamburger = document.getElementById('hamburger');
    var mobileNav = document.getElementById('mobileNav');
    hamburger.addEventListener('click', function () {
        var isOpen = hamburger.classList.toggle('open');
        mobileNav.classList.toggle('open', isOpen);
        hamburger.setAttribute('aria-expanded', String(isOpen));
        mobileNav.setAttribute('aria-hidden', String(!isOpen));
    });

    // Winkelwagen ophalen uit de browser-opslag
    var cartItems = JSON.parse(localStorage.getItem('snackcorner_cart') || '[]');

    // Bestellingoverzicht tonen
    if (cartItems.length === 0) {
        document.getElementById('summaryEmpty').style.display = 'block';
        document.getElementById('summaryTotal').style.display = 'none';
        document.getElementById('formCard').style.display     = 'none';
    } else {
        document.getElementById('summaryEmpty').style.display = 'none';
        document.getElementById('summaryTotal').style.display = 'flex';

        var totaal = 0;
        var lijst  = document.getElementById('summaryList');

        cartItems.forEach(function (item) {
            totaal += item.price * item.quantity;
            var li = document.createElement('li');
            li.className = 'summary-item';
            li.innerHTML =
                '<span class="summary-item-name">' + item.name + '</span>' +
                '<span class="summary-item-qty">x' + item.quantity + '</span>' +
                '<span class="summary-item-price">€' + (item.price * item.quantity).toFixed(2).replace('.', ',') + '</span>';
            lijst.appendChild(li);
        });

        document.getElementById('summaryTotalBedrag').textContent = '€' + totaal.toFixed(2).replace('.', ',');
    }

    // Bij het verzenden van het formulier: producten als losse velden toevoegen
    // PHP ontvangt ze dan als gewone $_POST waarden (geen JSON nodig)
    document.getElementById('checkoutForm').addEventListener('submit', function () {
        cartItems.forEach(function (item) {
            voegVeldToe('product_naam[]',   item.name);
            voegVeldToe('product_prijs[]',  item.price);
            voegVeldToe('product_aantal[]', item.quantity);
        });
    });

    function voegVeldToe(naam, waarde) {
        var veld   = document.createElement('input');
        veld.type  = 'hidden';
        veld.name  = naam;
        veld.value = waarde;
        document.getElementById('checkoutForm').appendChild(veld);
    }

});
