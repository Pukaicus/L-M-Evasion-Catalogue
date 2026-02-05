<?php
// Désactiver l'affichage des erreurs brutes pour SonarQube et le confort visuel
error_reporting(0);

// 1. LE DICTIONNAIRE DES AUTONOMIES
$explicationsAutonomie = [
    "A111 - B121 BA" => "Voyageur très autonome physiquement et psychologiquement, capable de gérer seul ses activités quotidiennes.",
    "A111 - B221 BA" => "Voyageur autonome physiquement mais nécessitant un encadrement léger pour l'organisation de sa vie quotidienne.",
    "A111 - B222 BA" => "Voyageur autonome physiquement ayant besoin d'un soutien régulier pour les repères spatio-temporels.",
    "A111 - B222 PSM" => "Voyageur autonome physiquement avec un handicap psychique nécessitant une attention et un environnement stable.",
    "A111 - C432 PT" => "Voyageur autonome physiquement mais nécessitant une aide constante et une présence rassurante pour tous les actes.",
    "A311 - C421 MR" => "Voyageur à mobilité réduite nécessitant une aide technique et un encadrement pour les déplacements et la vie quotidienne."
];

// 2. FONCTION DATE (Corrigée pour SonarQube avec accolades)
function formatDatePHP($str) {
    if (!$str) {
        return "";
    }
    $y = substr($str, 0, 4);
    $m = substr($str, 4, 2);
    $d = substr($str, 6, 2);
    $mois = ["Janv.", "Févr.", "Mars", "Avril", "Mai", "Juin", "Juil.", "Août", "Sept.", "Oct.", "Nov.", "Déc."];
    return $d . " " . $mois[(int)$m - 1] . " " . $y;
}

// 3. CHARGEMENT ET NETTOYAGE DU XML (Double source : sejour.xml et data.xml)
$nomRecherche = isset($_GET['nom']) ? $_GET['nom'] : '';
$fichiersXml = ['sejour.xml', 'data.xml'];
$monSejour = null;

foreach ($fichiersXml as $fichier) {
    if (file_exists($fichier)) {
        $contenu = file_get_contents($fichier);
        // On nettoie les balises Canva qui font planter le XML (span, style, etc.)
        $balisesAutorisees = '<SEJOUR><NomSejour><DateDebut><Datefin><Prix_Sejour><Autonomie><Publication_Titre><Publication_TarifPublic><PHOTO1><Publication_Annonce><Theme>';
        $contenu_propre = strip_tags($contenu, $balisesAutorisees);
        $xml = @simplexml_load_string($contenu_propre);
        
        if ($xml) {
            foreach ($xml->SEJOUR as $s) {
                if ((string)$s->NomSejour === $nomRecherche) {
                    $monSejour = $s;
                    break 2; // On a trouvé le séjour, on arrête tout
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
    <title><?php echo $monSejour ? htmlspecialchars((string)$monSejour->NomSejour) : 'Détails du Séjour'; ?> | L&M Evasion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50">

    <nav class="bg-white border-b border-slate-200 p-6">
        <div class="max-w-5xl mx-auto">
            <a href="index.php" class="flex items-center gap-2 text-slate-600 hover:text-blue-600 font-bold transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                Retour au catalogue
            </a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto p-6 md:py-12">
        <div id="contenu-detail">
            <?php if ($monSejour):
                $codeAuto = (string)$monSejour->Autonomie;
                $explication = isset($explicationsAutonomie[$codeAuto]) ? $explicationsAutonomie[$codeAuto] : "Séjour adapté selon les critères d'autonomie spécifiés.";
                $photo = str_replace('##', '/', (string)$monSejour->PHOTO1);
            ?>
                <h1 class="text-4xl md:text-6xl font-black text-slate-900 mb-4 leading-tight">
                    <?php echo htmlspecialchars((string)$monSejour->NomSejour); ?>
                </h1>

                <div class="flex flex-wrap gap-2 mb-8">
                    <?php if (isset($monSejour->Theme)): ?>
                        <?php foreach ($monSejour->Theme as $theme): ?>
                            <span class="bg-slate-200 text-slate-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border border-slate-300">
                                <?php echo htmlspecialchars((string)$theme); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
                        <div class="mb-6 rounded-2xl overflow-hidden h-64 bg-slate-100">
                            <img src="<?php echo $photo; ?>" alt="Illustration séjour <?php echo htmlspecialchars((string)$monSejour->NomSejour); ?>" class="w-full h-full object-cover">
                        </div>

                        <h3 class="text-blue-600 font-bold uppercase text-xs mb-6 tracking-widest">Informations de voyage</h3>
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <i data-lucide="calendar" class="text-slate-400 w-6 h-6"></i>
                                <p class="text-slate-500 font-medium">Départ : <span class="text-slate-900 font-bold ml-2 text-xl"><?php echo formatDatePHP((string)$monSejour->DateDebut); ?></span></p>
                            </div>
                            <div class="flex items-center gap-4">
                                <i data-lucide="calendar" class="text-slate-400 w-6 h-6"></i>
                                <p class="text-slate-500 font-medium">Retour : <span class="text-slate-900 font-bold ml-2 text-xl"><?php echo formatDatePHP((string)$monSejour->Datefin); ?></span></p>
                            </div>
                        </div>

                        <div class="mt-8 pt-8 border-t border-slate-100">
                            <div class="flex items-start gap-4 p-4 bg-blue-50/50 rounded-2xl border border-blue-100/50">
                                <div class="bg-blue-600 p-2 rounded-lg text-white shadow-sm">
                                    <i data-lucide="user-check" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase text-blue-600 mb-1">Profil requis : <?php echo htmlspecialchars($codeAuto); ?></p>
                                    <p class="text-xs text-slate-600 leading-relaxed font-medium">
                                        <?php echo htmlspecialchars($explication); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4 flex flex-col">
                        <div class="bg-blue-600 p-8 rounded-3xl text-white shadow-xl flex flex-col justify-center flex-grow">
                            <h3 class="font-bold uppercase text-xs mb-4 opacity-70 tracking-widest">Prix du séjour</h3>
                            <p class="text-4xl font-black mb-2"><?php echo htmlspecialchars((string)$monSejour->Publication_TarifPublic); ?> €</p>
                            <p class="text-lg font-bold italic opacity-90">Bientôt disponible à la réservation</p>
                        </div>

                        <button type="button" onclick="copierLien()" class="w-full bg-white border-2 border-slate-200 p-4 rounded-2xl flex items-center justify-center gap-3 hover:bg-slate-50 transition-all group shadow-sm">
                            <i data-lucide="share-2" class="w-5 h-5 text-blue-600"></i>
                            <span id="share-text" class="text-slate-700 font-bold">Partager ce séjour</span>
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-20">
                    <h2 class="text-2xl font-bold text-slate-400">Séjour introuvable.</h2>
                    <a href="index.php" class="text-blue-600 font-bold hover:underline mt-4 inline-block">Retourner à l'accueil</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        async function copierLien() {
            const textBtn = document.getElementById('share-text');
            try {
                await navigator.clipboard.writeText(window.location.href);
                const originalText = textBtn.innerText;
                textBtn.innerText = "Lien copié !";
                textBtn.classList.add('text-green-600');
                setTimeout(() => {
                    textBtn.innerText = originalText;
                    textBtn.classList.remove('text-green-600');
                }, 2000);
            } catch (err) {
                console.error('Erreur lors de la copie', err);
            }
        }
        lucide.createIcons();
    </script>
</body>
</html>
