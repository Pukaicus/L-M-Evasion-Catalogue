<?php
// Désactiver l'affichage des erreurs pour le rendu final
error_reporting(0);

// 1. FONCTION DE DATE 
function formatDatePHP($str) {
    if (!$str || strlen($str) < 8) {
        return $str;
    }
    $y = substr($str, 0, 4);
    $m = substr($str, 4, 2);
    $d = substr($str, 6, 2);
    $mois = ["Janv.", "Févr.", "Mars", "Avril", "Mai", "Juin", "Juil.", "Août", "Sept.", "Oct.", "Nov.", "Déc."];
    return $d . " " . $mois[(int)$m - 1] . " " . $y;
}

// 2. RÉCUPÉRATION DU NOM DEPUIS L'URL
$nomRecherche = isset($_GET['nom']) ? trim($_GET['nom']) : '';
$monSejour = null;

// 3. CHARGEMENT DEPUIS LES FICHIERS XML
$fichiers = ['sejour.xml', 'data.xml'];
foreach ($fichiers as $f) {
    if (file_exists($f)) {
        $xml = @simplexml_load_file($f);
        if ($xml) {
            foreach ($xml->SEJOUR as $s) {
                if (trim((string)$s->NomSejour) == $nomRecherche) {
                    $monSejour = $s;
                    break 2;
                }
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
    <title><?php echo $monSejour ? htmlspecialchars((string)$monSejour->NomSejour) : 'Détails'; ?> | L&M Evasion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .slider-container { scroll-behavior: smooth; scroll-snap-type: x mandatory; }
        .slider-item { scroll-snap-align: center; flex-shrink: 0; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex flex-col min-h-screen">

    <nav class="p-6 bg-white border-b border-slate-200">
        <div class="max-w-6xl mx-auto">
            <a href="index.php" class="font-bold text-blue-600 flex items-center gap-2 hover:underline">
                <i data-lucide="arrow-left"></i> Retour au catalogue
            </a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-6 py-12 flex-grow">
        <?php if ($monSejour):
            // On prépare la liste des photos disponibles
            $photos = [];
            for($i=1; $i<=5; $i++) {
                $tag = "PHOTO" . $i;
                if (!empty((string)$monSejour->$tag)) {
                    $photos[] = str_replace('##', '/', (string)$monSejour->$tag);
                }
            }
            $prix = !empty((string)$monSejour->Publication_TarifPublic) ? (string)$monSejour->Publication_TarifPublic : (string)$monSejour->Prix_Sejour;
        ?>
            <h1 class="text-4xl md:text-5xl font-black uppercase mb-8 italic">
                <?php echo htmlspecialchars((string)$monSejour->NomSejour); ?>
            </h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                
                <div class="lg:col-span-2 space-y-6">
                    <div class="relative group">
                        <div id="slider" class="slider-container flex overflow-x-hidden rounded-3xl shadow-xl border border-slate-200 h-[500px]">
                            <?php foreach ($photos as $index => $url): ?>
                                <div class="slider-item w-full h-full">
                                    <img src="<?php echo $url; ?>" alt="Vue du séjour <?php echo $index + 1; ?>" class="w-full h-full object-contain">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($photos) > 1): ?>
                            <button onclick="moveSlider(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 backdrop-blur p-3 rounded-full shadow-lg hover:bg-white transition flex items-center justify-center">
                                <i data-lucide="chevron-left" class="text-blue-600"></i>
                            </button>
                            <button onclick="moveSlider(1)" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 backdrop-blur p-3 rounded-full shadow-lg hover:bg-white transition flex items-center justify-center">
                                <i data-lucide="chevron-right" class="text-blue-600"></i>
                            </button>
                            
                            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                                <?php foreach ($photos as $index => $url): ?>
                                    <div class="w-2 h-2 rounded-full bg-white/50"></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                        <h3 class="text-blue-600 font-bold uppercase text-xs mb-6 tracking-widest">Détails du voyage</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center gap-4">
                                <i data-lucide="calendar" class="text-slate-400 w-6 h-6"></i>
                                <p class="text-slate-600 font-medium">Dates : <span class="text-slate-900 font-bold ml-2">Du <?php echo formatDatePHP((string)$monSejour->DateDebut); ?> au <?php echo formatDatePHP((string)$monSejour->Datefin); ?></span></p>
                            </div>
                            <div class="flex items-center gap-4">
                                <i data-lucide="user-check" class="text-slate-400 w-6 h-6"></i>
                                <p class="text-slate-600 font-medium">Autonomie : <span class="text-slate-900 font-bold ml-2"><?php echo htmlspecialchars((string)$monSejour->Autonomie); ?></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-blue-600 p-10 rounded-3xl text-white flex flex-col justify-center shadow-xl sticky top-10">
                        <span class="text-xs font-bold uppercase opacity-70 tracking-widest mb-2">Tarif Public</span>
                        <p class="text-6xl font-black"><?php echo $prix; ?> €</p>
                        <p class="mt-6 text-lg font-bold italic opacity-90">Inscriptions ouvertes pour 2026</p>
                        <hr class="my-8 border-white/20">
                        <button class="w-full bg-white text-blue-600 font-black py-4 rounded-2xl shadow-lg hover:bg-slate-50 transition uppercase tracking-wider">Réserver maintenant</button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-slate-300">
                <h2 class="text-2xl font-bold text-slate-400 mb-4">Séjour introuvable.</h2>
                <a href="index.php" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition">Retourner à l'accueil</a>
            </div>
        <?php endif; ?>
    </main>

    <footer id="section-contact" class="bg-white border-t border-slate-200 pt-12 pb-8 mt-12">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-8">
                <div>
                    <h4 class="font-bold text-slate-900 mb-4">L&M Evasion</h4>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        3 allée du château de la mothe appt 7<br>
                        86240 Ligugé
                    </p>
                </div>
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3 text-slate-600">
                        <i data-lucide="phone" class="w-4 h-4 text-blue-600"></i>
                        <span class="font-bold">05 79 79 96 15</span>
                    </div>
                    <div class="flex items-center gap-3 text-slate-600">
                        <i data-lucide="mail" class="w-4 h-4 text-blue-600"></i>
                        <span class="font-bold">reservations@lmevasion.com</span>
                    </div>
                </div>
            </div>
            <div class="pt-8 border-t border-slate-100 flex justify-between items-center text-[10px] text-slate-400 font-bold uppercase italic">
                <p>© 2026 L&M Evasion</p>
                <p>Ce site est propulsé par IWAN.fr</p>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
        
        // Logique du défilé d'images
        function moveSlider(direction) {
            const slider = document.getElementById('slider');
            const scrollAmount = slider.offsetWidth;
            slider.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
        }
    </script>
</body>
</html>
