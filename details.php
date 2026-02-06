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

// 2. FONCTION POUR EXTRAIRE LE NOMBRE DE PARTICIPANTS (DYNAMIQUE)
function extraireParticipants($texte) {
    if (preg_match('/Nombre de participants\s*:\s*(\d+)/i', $texte, $matches)) {
        return (int)$matches[1];
    }
    return 0;
}

$nomRecherche = isset($_GET['nom']) ? trim($_GET['nom']) : '';
$monSejour = null;

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

    <nav class="p-6 bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto">
            <a href="index.php" class="font-bold text-blue-600 flex items-center gap-2">
                <i data-lucide="arrow-left"></i> Retour au catalogue
            </a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-6 py-12 flex-grow">
        <?php if ($monSejour):
            $photos = [];
            for($i=1; $i<=5; $i++) {
                $tag = "PHOTO" . $i;
                if (!empty((string)$monSejour->$tag)) {
                    $photos[] = str_replace('##', '/', (string)$monSejour->$tag);
                }
            }
            $prix = !empty((string)$monSejour->Publication_TarifPublic) ? (string)$monSejour->Publication_TarifPublic : (string)$monSejour->Prix_Sejour;
            $description_brute = (string)$monSejour->Publication_Annonce;
        ?>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <h1 class="text-4xl md:text-5xl font-black uppercase italic text-slate-900">
                    <?php echo htmlspecialchars((string)$monSejour->NomSejour); ?>
                </h1>
                <div class="flex flex-wrap gap-2 md:justify-end">
                    <?php
                    if (isset($monSejour->Theme)) {
                        foreach ($monSejour->Theme as $t) {
                            echo '<span class="px-3 py-1.5 bg-blue-50 text-blue-600 text-[10px] font-black uppercase rounded-full border border-blue-100 shadow-sm">'.htmlspecialchars(trim((string)$t)).'</span>';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                
                <div class="lg:col-span-2 space-y-10">
                    <div class="relative group">
                        <div id="slider" class="slider-container flex overflow-x-hidden rounded-[2.5rem] shadow-2xl border border-white h-[500px]">
                            <?php foreach ($photos as $index => $url): ?>
                                <div class="slider-item w-full h-full bg-slate-100">
                                    <img src="<?php echo $url; ?>" alt="Vue <?php echo $index + 1; ?>" class="w-full h-full object-cover">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($photos) > 1): ?>
                            <button onclick="moveSlider(-1)" class="absolute left-6 top-1/2 -translate-y-1/2 bg-white/90 p-4 rounded-2xl shadow-xl hover:bg-white transition">
                                <i data-lucide="chevron-left" class="text-blue-600"></i>
                            </button>
                            <button onclick="moveSlider(1)" class="absolute right-6 top-1/2 -translate-y-1/2 bg-white/90 p-4 rounded-2xl shadow-xl hover:bg-white transition">
                                <i data-lucide="chevron-right" class="text-blue-600"></i>
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="bg-white p-10 rounded-[2.5rem] border border-slate-100 shadow-sm min-h-[300px]">
                        <h3 class="text-2xl font-black italic uppercase text-slate-800 mb-8 border-b pb-4 border-slate-100 flex items-center gap-3">
                            <i data-lucide="map" class="text-blue-600"></i> Le Programme
                        </h3>
                        <div class="text-slate-600 leading-[1.8] text-lg font-medium border-l-4 border-blue-500/30 pl-6 py-2">
                            <?php
                                $description = trim($description_brute);
                                if ($description !== "") {
                                    echo nl2br(htmlspecialchars($description));
                                } else {
                                    echo '<p class="italic text-slate-400">Le programme détaillé de ce séjour est en cours de mise à jour.</p>';
                                }
                            ?>
                        </div>
                    </div>

                    <div class="bg-slate-900 p-10 rounded-[2.5rem] text-white shadow-lg">
                        <h3 class="text-blue-400 font-black uppercase text-xs mb-8 tracking-[0.2em]">Informations clés</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div class="flex items-start gap-5">
                                <div class="p-3 bg-white/10 rounded-2xl"><i data-lucide="calendar" class="text-blue-400 w-6 h-6"></i></div>
                                <div>
                                    <p class="text-slate-400 text-xs font-bold uppercase mb-1">Dates</p>
                                    <p class="font-bold text-lg italic">Du <?php echo formatDatePHP((string)$monSejour->DateDebut); ?> au <?php echo formatDatePHP((string)$monSejour->Datefin); ?></p>
                                </div>
                            </div>
                            <div class="flex items-start gap-5">
                                <div class="p-3 bg-white/10 rounded-2xl"><i data-lucide="user-check" class="text-blue-400 w-6 h-6"></i></div>
                                <div>
                                    <p class="text-slate-400 text-xs font-bold uppercase mb-1">Autonomie</p>
                                    <p class="font-bold text-lg italic uppercase"><?php echo htmlspecialchars((string)$monSejour->Autonomie); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white p-10 rounded-[2.5rem] border border-slate-100 shadow-2xl sticky top-28">
                        <span class="inline-block px-3 py-1 bg-green-50 text-green-600 text-[10px] font-black uppercase rounded-full mb-6">Inscriptions Ouvertes</span>
                        
                        <p class="text-7xl font-black tracking-tighter text-blue-600"><?php echo $prix; ?><span class="text-2xl ml-2 text-slate-900">€</span></p>
                        
                        <?php
                            // ON RÉCUPÈRE LE MAX (soit balise, soit texte)
                            $maxParticipants = (isset($monSejour->Participants)) ? (int)$monSejour->Participants : extraireParticipants($description_brute);
                            
                            if ($maxParticipants > 0):
                                $aleatoire = rand(1, $maxParticipants - 1);
                        ?>
                            <div class="mt-6 flex items-center gap-4 bg-orange-50 p-4 rounded-2xl border border-orange-100">
                                <div class="bg-orange-500 p-2 rounded-lg text-white">
                                    <i data-lucide="users" class="w-5 h-5"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-orange-700 font-black text-lg leading-none">
                                        Il reste <?php echo $aleatoire; ?> places
                                    </span>
                                    <span class="text-orange-400 text-[10px] font-black uppercase tracking-[0.15em] mt-1.5">
                                        sur <?php echo $maxParticipants; ?> participants
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mt-10 space-y-4">
                            <button class="w-full bg-slate-900 text-white font-black py-5 rounded-2xl shadow-xl hover:bg-blue-600 transition-all uppercase italic">Réserver ce séjour</button>
                            <button type="button" onclick="copierLien()" class="w-full bg-slate-100 text-slate-600 font-bold py-4 rounded-2xl hover:bg-slate-200 transition-colors flex items-center justify-center gap-2">
                            <i data-lucide="link" class="w-4 h-4 text-blue-600"></i>
                            <span id="texte-copier">Copier le lien</span>
                            </button>
                        </div>
                        <p class="mt-8 text-center text-xs text-slate-400 font-medium tracking-wide">Besoin d'aide ? 05 79 79 96 15</p>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-[2.5rem] border-2 border-dashed border-slate-200 italic">
                <i data-lucide="search-x" class="w-16 h-16 text-slate-300 mx-auto mb-6"></i>
                <h2 class="text-3xl font-black text-slate-400 mb-4 uppercase">Séjour introuvable</h2>
                <a href="index.php" class="inline-block bg-blue-600 text-white px-10 py-4 rounded-2xl font-black hover:bg-blue-700 transition uppercase tracking-widest">Retour au catalogue</a>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-white border-t p-16 mt-20 text-center text-[11px] text-slate-400 font-black uppercase italic tracking-[0.2em]">
        <p>© 2026 L&M Evasion - Propulsé par IWAN.fr</p>
    </footer>

<script>
        lucide.createIcons();
        function moveSlider(direction) {
            const slider = document.getElementById('slider');
            const scrollAmount = slider.offsetWidth;
            slider.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
        }
        function copierLien() {
            const url = window.location.href;
            
            navigator.clipboard.writeText(url).then(() => {
                const span = document.getElementById('texte-copier');
                const ancienTexte = span.innerText;
                
                span.innerText = "Lien copié !";
                span.style.color = "#16a34a";
                
                setTimeout(() => {
                    span.innerText = ancienTexte;
                    span.style.color = "";
                }, 2000);
            }).catch(err => {
                console.error('Erreur de copie : ', err);
            });
        }
    </script>
</body>
</html>
