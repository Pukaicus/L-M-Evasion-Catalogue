<?php
// 1. CONFIGURATION ET FONCTIONS
// On affiche les erreurs pour comprendre le blocage pendant les tests
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Fonction pour transformer la date XML (AAAAMMJJ) en format lisible (JJ Mois AAAA)
 * Indispensable pour éviter l'erreur "Call to undefined function"
 */
function formatDateXML($str) {
    if (!$str) {
        return "";
    }
    $y = substr($str, 0, 4);
    $m = substr($str, 4, 2);
    $d = substr($str, 6, 2);
    $mois = ["Janv.", "Févr.", "Mars", "Avril", "Mai", "Juin", "Juil.", "Août", "Sept.", "Oct.", "Nov.", "Déc."];
    return $d . " " . $mois[(int)$m - 1] . " " . $y;
}

// 2. CHARGEMENT DES DEUX SOURCES XML (sejour.xml et data.xml)
$fichiersXml = ['sejour.xml', 'data.xml'];
$tousLesSejours = [];

foreach ($fichiersXml as $fichier) {
    if (file_exists($fichier)) {
        // On tente d'abord un chargement direct
        $xmlObj = @simplexml_load_file($fichier);
        
        if ($xmlObj === false) {
            // Si ça rate (à cause du code Canva), on nettoie avant de lire
            $contenu = file_get_contents($fichier);
            $balisesA = '<SEJOUR><NomSejour><DateDebut><Datefin><Prix_Sejour><Autonomie><Publication_Titre><Publication_TarifPublic><PHOTO1><Theme>';
            $contenu_propre = strip_tags($contenu, $balisesA);
            $xmlObj = @simplexml_load_string($contenu_propre);
        }

        if ($xmlObj) {
            // On gère les majuscules/minuscules des balises racines
            $items = $xmlObj->SEJOUR;
            if (count($items) == 0) { $items = $xmlObj->sejour; }
            
            foreach ($items as $s) {
                $tousLesSejours[] = $s;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L&M Evasion | Séjours 2026</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <nav class="sticky top-0 z-50 glass border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i data-lucide="map-pin" class="text-blue-600"></i>
                <span class="text-xl font-extrabold tracking-tight">L&M <span class="text-blue-600">EVASION</span></span>
            </div>
            <div class="hidden md:flex gap-8 font-semibold text-sm uppercase tracking-wider text-slate-600">
                <button type="button" onclick="window.scrollTo(0,0)" class="hover:text-blue-600 transition">Nos Séjours</button>
                <button type="button" onclick="allerARecherche()" class="hover:text-blue-600 transition">Destinations</button>
                <a href="#section-contact" class="hover:text-blue-600 transition">Contact</a>
            </div>
            <button type="button" onclick="toggleMenu()" class="md:hidden" aria-label="Menu"><i data-lucide="menu" id="menu-icon"></i></button>
        </div>
    </nav>

    <header class="relative py-12 px-6 min-h-[500px] flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="camping.jpg" alt="Paysage évasion L&M" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-white/20"></div>
        </div>
        <div class="max-w-4xl mx-auto relative z-10 text-center">
            <span class="bg-blue-50 text-blue-600 px-4 py-1 rounded-full text-xs font-bold uppercase shadow-sm">Catalogue Officiel 2026</span>
            <h1 class="text-6xl md:text-8xl font-black text-slate-900 mt-4 leading-none">Le voyage <br><span class="text-blue-600">sans limites.</span></h1>
        </div>
    </header>

    <section id="section-recherche" class="max-w-7xl mx-auto px-6 -mt-10 relative z-20">
        <div class="bg-white p-6 rounded-2xl shadow-xl border border-slate-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="search-input" class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Recherche</label>
                    <input type="text" id="search-input" onkeyup="filtrerSejours()" placeholder="Mot clé..." class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none text-sm">
                </div>
                <div>
                    <label for="filter-autonomie" class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Autonomie</label>
                    <select id="filter-autonomie" onchange="filtrerSejours()" class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none text-sm">
                        <option value="">Toutes</option>
                        <option value="A111 - C432 PT">A111 - C432 PT</option>
                        <option value="A111 - B222 BA">A111 - B222 BA</option>
                        <option value="A111 - B121 BA">A111 - B121 BA</option>
                        <option value="A111 - B221 BA">A111 - B221 BA</option>
                        <option value="A111 - B222 PSM">A111 - B222 PSM</option>
                        <option value="A311 - C421 MR">A311 - C421 MR</option>
                    </select>
                </div>
                <div>
                    <label for="filter-type" class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Thématique</label>
                    <select id="filter-type" onchange="filtrerSejours()" class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none text-sm">
                        <option value="">Tous les types</option>
                        <option value="Automne">Automne</option>
                        <option value="Calme">Calme</option>
                        <option value="Camping">Camping</option>
                        <option value="Culturel">Culturel</option>
                        <option value="Equestre">Equestre</option>
                        <option value="Eté">Eté</option>
                        <option value="Gastronomie">Gastronomie</option>
                        <option value="Hiver">Hiver</option>
                        <option value="Printemps">Printemps</option>
                        <option value="Séjours sportifs">Séjours sportifs</option>
                        <option value="Mer">Mer</option>
                        <option value="Montagne">Montagne</option>
                        <option value="Campagne">Campagne</option>
                        <option value="Villes">Villes</option>
                        <option value="Etrangers">Etrangers</option>
                        <option value="Détente & Bien-être">Détente & Bien-être</option>
                        <option value="Spectacles & Festivals & Parcs">Spectacles & Festivals & Parcs</option>
                        <option value="Animaux">Animaux</option>
                        <option value="Fêtes de fin d'année">Fêtes de fin d'année</option>
                    </select>
                </div>
                <button type="button" onclick="filtrerSejours()" class="bg-[#E65100] text-white font-bold py-3.5 rounded-xl uppercase text-sm shadow-lg">Rechercher</button>
            </div>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <div id="sejours-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php if (!empty($tousLesSejours)): ?>
                <?php foreach ($tousLesSejours as $s):
                    // LOGIQUE IMAGE : On remplace les ## par des / pour chaque séjour
                    $photo = str_replace('##', '/', (string)$s->PHOTO1);
                    
                    // Préparation des thèmes pour le filtre JS
                    $themes = "";
                    if (isset($s->Theme)) {
                        foreach ($s->Theme as $t) { $themes .= strtolower((string)$t) . " "; }
                    }
                ?>
                <div class="sejour-card group bg-white rounded-3xl shadow-sm hover:shadow-2xl transition-all duration-500 overflow-hidden border border-slate-100"
                     data-nom="<?php echo strtolower(htmlspecialchars((string)$s->NomSejour)); ?>"
                     data-autonomie="<?php echo htmlspecialchars((string)$s->Autonomie); ?>"
                     data-themes="<?php echo trim(htmlspecialchars($themes)); ?>">
                    
                    <div class="relative h-64 overflow-hidden bg-slate-200">
                        <img src="<?php echo $photo; ?>" alt="Séjour <?php echo htmlspecialchars((string)$s->NomSejour); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute top-4 left-4">
                            <span class="bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest text-blue-600 shadow-sm">
                                <?php echo htmlspecialchars((string)$s->Autonomie); ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-8">
                        <h3 class="text-2xl font-extrabold text-slate-800 mb-4 group-hover:text-blue-600 transition uppercase italic">
                            <?php echo htmlspecialchars((string)$s->NomSejour); ?>
                        </h3>
                        <div class="space-y-3 mb-8 text-sm text-slate-500 font-medium">
                            <div class="flex items-center"><i data-lucide="calendar" class="w-4 h-4 mr-3 text-blue-500"></i>Du <?php echo formatDateXML((string)$s->DateDebut); ?></div>
                            <div class="flex items-center"><i data-lucide="chevron-right" class="w-4 h-4 mr-3 text-transparent"></i>Au <?php echo formatDateXML((string)$s->Datefin); ?></div>
                        </div>
                        <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-slate-400">Tarif Public</p>
                                <p class="text-lg font-black text-slate-900 tracking-tight">
                                    <?php echo !empty((string)$s->Publication_TarifPublic) ? htmlspecialchars((string)$s->Publication_TarifPublic) . ' €' : 'Sur devis'; ?>
                                </p>
                            </div>
                            <button type="button" onclick="location.href='details.php?nom=<?php echo urlencode((string)$s->NomSejour); ?>'" class="bg-slate-900 text-white p-4 rounded-2xl group-hover:bg-blue-600 transition-colors shadow-lg" aria-label="Détails">
                                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-20 text-slate-400">
                    <p>Aucun séjour trouvé. Vérifie tes fichiers XML.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer id="section-contact" class="bg-white border-t border-slate-200 pt-16 pb-8 text-center text-sm text-slate-500">
        <p>© 2026 L&M Evasion | Propulsé par IWAN.fr</p>
    </footer>

    <script>
        function filtrerSejours() {
            const search = document.getElementById('search-input').value.toLowerCase();
            const auto = document.getElementById('filter-autonomie').value;
            const themeSelected = document.getElementById('filter-type').value.toLowerCase();
            const cards = document.querySelectorAll('.sejour-card');

            cards.forEach(card => {
                const nom = card.dataset.nom;
                const autonomie = card.dataset.autonomie;
                const themes = card.dataset.themes;

                const matchesSearch = nom.includes(search);
                const matchesAuto = auto === "" || autonomie === auto;
                const matchesTheme = themeSelected === "" || themes.includes(themeSelected) || nom.includes(themeSelected);

                card.style.display = (matchesSearch && matchesAuto && matchesTheme) ? "block" : "none";
            });
        }
        function toggleMenu() {
            const menu = document.getElementById('mobile-menu');
            if (menu) menu.classList.toggle('hidden');
        }
        function allerARecherche() {
            const el = document.getElementById('section-recherche');
            if (el) el.scrollIntoView({ behavior: 'smooth' });
        }
        lucide.createIcons();
    </script>
</body>
</html>
