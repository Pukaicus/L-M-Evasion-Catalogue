const explicationsAutonomie = {
    "A111 - B121 BA": "Voyageur très autonome physiquement et psychologiquement, capable de gérer seul ses activités quotidiennes.",
    "A111 - B221 BA": "Voyageur autonome physiquement mais nécessitant un encadrement léger pour l'organisation de sa vie quotidienne.",
    "A111 - B222 BA": "Voyageur autonome physiquement ayant besoin d'un soutien régulier pour les repères spatio-temporels.",
    "A111 - B222 PSM": "Voyageur autonome physiquement avec un handicap psychique nécessitant une attention et un environnement stable.",
    "A111 - C432 PT": "Voyageur autonome physiquement mais nécessitant une aide constante et une présence rassurante pour tous les actes.",
    "A311 - C421 MR": "Voyageur à mobilité réduite nécessitant une aide technique et un encadrement pour les déplacements et la vie quotidienne."
};

function formatDate(str) {
    if (!str) return "";
    const y = str.substring(0, 4);
    const m = str.substring(4, 6);
    const d = str.substring(6, 8);
    const mois = ["Janv.", "Févr.", "Mars", "Avril", "Mai", "Juin", "Juil.", "Août", "Sept.", "Oct.", "Nov.", "Déc."];
    return `${d} ${mois[Number.parseInt(m) - 1]} ${y}`;
}

async function chargerPageDetail() {
    try {
        const params = new URLSearchParams(globalThis.location.search);
        const nomRecherche = params.get('nom');

        if (!nomRecherche) return;

        const response = await fetch('data.xml');
        const text = await response.text();
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(text, "text/xml");
        const sejours = Array.from(xmlDoc.getElementsByTagName("SEJOUR"));

        const s = sejours.find(sejour => 
            sejour.getElementsByTagName("NomSejour")[0].textContent === nomRecherche
        );

        if (s) {
            const nom = s.getElementsByTagName("NomSejour")[0].textContent;
            const debutFormate = formatDate(s.getElementsByTagName("DateDebut")[0].textContent);
            const finFormate = formatDate(s.getElementsByTagName("Datefin")[0].textContent);
            const autonomieCode = s.getElementsByTagName("Autonomie")[0].textContent;

            const explicationSpecifique = explicationsAutonomie[autonomieCode] || "Séjour adapté selon les critères d'autonomie spécifiés.";

            document.getElementById('contenu-detail').innerHTML = `
                <h1 class="text-4xl md:text-6xl font-black text-slate-900 mb-8 leading-tight">${nom}</h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
                        <h3 class="text-blue-600 font-bold uppercase text-xs mb-6 tracking-widest">Informations de voyage</h3>
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <i data-lucide="calendar" class="text-slate-400 w-6 h-6"></i>
                                <p class="text-slate-500 font-medium">Départ : <span class="text-slate-900 font-bold ml-2 text-xl">${debutFormate}</span></p>
                            </div>
                            <div class="flex items-center gap-4">
                                <i data-lucide="calendar" class="text-slate-400 w-6 h-6"></i>
                                <p class="text-slate-500 font-medium">Retour : <span class="text-slate-900 font-bold ml-2 text-xl">${finFormate}</span></p>
                            </div>
                        </div>

                        <div class="mt-8 pt-8 border-t border-slate-100">
                            <div class="flex items-start gap-4 p-4 bg-blue-50/50 rounded-2xl border border-blue-100/50">
                                <div class="bg-blue-600 p-2 rounded-lg text-white shadow-sm">
                                    <i data-lucide="user-check" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase text-blue-600 mb-1">Profil requis : ${autonomieCode}</p>
                                    <p class="text-xs text-slate-600 leading-relaxed font-medium">
                                        ${explicationSpecifique}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4 flex flex-col">
                        <div class="bg-blue-600 p-8 rounded-3xl text-white shadow-xl flex flex-col justify-center flex-grow">
                            <h3 class="font-bold uppercase text-xs mb-4 opacity-70 tracking-widest">Statut</h3>
                            <p class="text-2xl font-bold italic">Bientôt disponible à la réservation</p>
                        </div>

                        <button type="button" onclick="copierLien()" class="w-full bg-white border-2 border-slate-200 p-4 rounded-2xl flex items-center justify-center gap-3 hover:bg-slate-50 transition-all group shadow-sm">
                            <i data-lucide="share-2" class="w-5 h-5 text-blue-600"></i>
                            <span id="share-text" class="text-slate-700 font-bold">Partager ce séjour</span>
                        </button>
                    </div>
                </div>
            `;
        }
        lucide.createIcons();
    } catch (error) {
        console.error("Erreur de chargement des détails :", error);
    }
}

async function copierLien() {
    const textBtn = document.getElementById('share-text');
    try {
        await navigator.clipboard.writeText(globalThis.location.href);
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

document.addEventListener('DOMContentLoaded', chargerPageDetail);