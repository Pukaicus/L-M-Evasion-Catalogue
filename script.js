// On ne charge plus le catalogue ici, c'est le PHP qui s'en occupe !

// 1. Menu Mobile
function toggleMenu() {
    const menu = document.getElementById('mobile-menu');
    const icon = document.getElementById('menu-icon');
    if (menu && icon) {
        menu.classList.toggle('hidden');
        icon.dataset.lucide = menu.classList.contains('hidden') ? 'menu' : 'x';
        lucide.createIcons();
    }
}

// 2. Navigation fluide vers la recherche
function allerARecherche() {
    const section = document.getElementById('section-recherche');
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

// 3. Bouton Retour en haut
window.onscroll = () => {
    const btn = document.getElementById('back-to-top');
    if (btn) {
        (document.documentElement.scrollTop > 500) ? btn.classList.remove('hidden') : btn.classList.add('hidden');
    }
};

// 4. Filtrage des séjours (Logique JS sur les éléments déjà affichés par PHP)
function filtrerSejours() {
    const recherche = document.getElementById('search-input').value.toLowerCase();
    const auto = document.getElementById('filter-autonomie').value;
    const themeSelected = document.getElementById('filter-type').value.toLowerCase();
    const cards = document.querySelectorAll('.sejour-card');

    cards.forEach(card => {
        const nom = card.dataset.nom || "";
        const autonomie = card.dataset.autonomie || "";
        const themes = card.dataset.themes || "";

        const matchesSearch = nom.includes(recherche);
        const matchesAuto = auto === "" || autonomie === auto;
        const matchesTheme = themeSelected === "" || themes.includes(themeSelected) || nom.includes(themeSelected);

        if (matchesSearch && matchesAuto && matchesTheme) {
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    });
}

// Initialisation des icônes au chargement
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});