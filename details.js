// On ne charge plus le XML ici, le PHP s'en occupe avant l'affichage !

/**
 * Fonction de copie du lien dans le presse-papier
 * Gardée en JS car elle nécessite une action directe sur le navigateur de l'utilisateur.
 */
async function copierLien() {
    const textBtn = document.getElementById('share-text');
    if (!textBtn) return;

    try {
        // On utilise l'API moderne du presse-papier
        await navigator.clipboard.writeText(globalThis.location.href);
        
        const originalText = textBtn.innerText;
        textBtn.innerText = "Lien copié !";
        textBtn.classList.add('text-green-600');
        
        // Petit délai avant de remettre le texte original
        setTimeout(() => {
            textBtn.innerText = originalText;
            textBtn.classList.remove('text-green-600');
        }, 2000);
    } catch (err) {
        console.error('Erreur lors de la copie du lien :', err);
    }
}

/**
 * Initialisation au chargement de la page
 */
document.addEventListener('DOMContentLoaded', () => {
    // On s'assure que les icônes Lucide sont bien générées sur les éléments PHP
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});