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
        const xmlDoc = new DOMParser().parseFromString(text, "text/xml");
        const sejours = Array.from(xmlDoc.getElementsByTagName("SEJOUR"));

        const s = sejours.find(sejour => 
            sejour.getElementsByTagName("NomSejour")[0].textContent === nomRecherche
        );

        if (s) {
            const nom = s.getElementsByTagName("NomSejour")[0].textContent;
            const debutFormate = formatDate(s.getElementsByTagName("DateDebut")[0].textContent);
            const finFormate = formatDate(s.getElementsByTagName("Datefin")[0].textContent);

            document.getElementById('contenu-detail').innerHTML = `
                <h1 class="text-4xl md:text-6xl font-black text-slate-900 mb-8 leading-tight">${nom}</h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
                        <h3 class="text-blue-600 font-bold uppercase text-xs mb-6 tracking-widest font-bold">Informations de voyage</h3>
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
                    </div>
                    
                    <div class="bg-blue-600 p-8 rounded-3xl text-white shadow-xl flex flex-col justify-center">
                        <h3 class="font-bold uppercase text-xs mb-4 opacity-70 tracking-widest">Statut</h3>
                        <p class="text-2xl font-bold italic">Bientôt disponible à la réservation</p>
                    </div>
                </div>
            `;
        }
        lucide.createIcons();
    } catch (error) {
        console.error("Erreur :", error);
    }
}

document.addEventListener('DOMContentLoaded', chargerPageDetail);