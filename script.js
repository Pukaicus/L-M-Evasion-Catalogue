let tousLesSejours = [];

function formatDate(str) {
    if (!str) return "";
    const y = str.substring(0, 4);
    const m = str.substring(4, 6);
    const d = str.substring(6, 8);
    const mois = ["Janv.", "Févr.", "Mars", "Avril", "Mai", "Juin", "Juil.", "Août", "Sept.", "Oct.", "Nov.", "Déc."];
    return `${d} ${mois[Number.parseInt(m) - 1]} ${y}`;
}

async function chargerCatalogue() {
    try {
        const response = await fetch('data.xml');
        const text = await response.text();
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(text, "text/xml");
        const sejoursXml = xmlDoc.getElementsByTagName("SEJOUR");
        
        tousLesSejours = Array.from(sejoursXml).map(sejour => {
            let photoRaw = sejour.getElementsByTagName("PHOTO1")[0].textContent;
            return {
                nom: sejour.getElementsByTagName("NomSejour")[0].textContent,
                debut: sejour.getElementsByTagName("DateDebut")[0].textContent,
                fin: sejour.getElementsByTagName("Datefin")[0].textContent,
                autonomie: sejour.getElementsByTagName("Autonomie")[0].textContent,
                photo: encodeURI(photoRaw.replaceAll(String.raw`##`, '/'))
            };
        });
        afficherSejours(tousLesSejours);
    } catch (error) {
        console.error("Erreur :", error);
    }
}

function afficherSejours(liste) {
    const grid = document.getElementById('sejours-grid');
    grid.innerHTML = "";
    liste.forEach(s => {
        const card = `
            <div class="group bg-white rounded-3xl shadow-sm hover:shadow-2xl transition-all duration-500 overflow-hidden border border-slate-100">
                <div class="relative h-64 overflow-hidden bg-slate-200">
                    <img src="${s.photo}" alt="${s.nom}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 shadow-inner">
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest text-blue-600 shadow-sm">${s.autonomie}</span>
                    </div>
                </div>
                <div class="p-8">
                    <h3 class="text-2xl font-extrabold text-slate-800 mb-4 group-hover:text-blue-600 transition">${s.nom}</h3>
                    <div class="space-y-3 mb-8 text-sm text-slate-500 font-medium">
                        <div class="flex items-center"><i data-lucide="calendar" class="w-4 h-4 mr-3 text-blue-500"></i>Du ${formatDate(s.debut)}</div>
                        <div class="flex items-center"><i data-lucide="chevron-right" class="w-4 h-4 mr-3 text-transparent"></i>Au ${formatDate(s.fin)}</div>
                    </div>
                    <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Tarif</p>
                            <p class="text-lg font-black text-slate-900 tracking-tight">Sur devis</p>
                        </div>
                        <button type="button" onclick="allerAuxDetails('${s.nom.replaceAll("'", String.raw`\'`)}')" class="bg-slate-900 text-white p-4 rounded-2xl group-hover:bg-blue-600 transition-colors shadow-lg">
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
            </div>`;
        grid.innerHTML += card;
    });
    lucide.createIcons();
}

function allerAuxDetails(nomSejour) {
    const url = `details.html?nom=${encodeURIComponent(nomSejour)}`;
    globalThis.location.href = url;
}

function filtrerSejours() {
    const recherche = document.getElementById('search-input').value.toLowerCase();
    const auto = document.getElementById('filter-autonomie').value;
    const type = document.getElementById('filter-type').value.toLowerCase();
    const res = tousLesSejours.filter(s => {
        return s.nom.toLowerCase().includes(recherche) && 
               (auto === "" || s.autonomie === auto) && 
               (type === "" || s.nom.toLowerCase().includes(type));
    });
    afficherSejours(res);
}

function toggleMenu() {
    const menu = document.getElementById('mobile-menu');
    const icon = document.getElementById('menu-icon');
    menu.classList.toggle('hidden');
    icon.dataset.lucide = menu.classList.contains('hidden') ? 'menu' : 'x';
    lucide.createIcons();
}
function allerARecherche() {
    const section = document.getElementById('section-recherche');
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

globalThis.onscroll = () => {
    const btn = document.getElementById('back-to-top');
    (document.documentElement.scrollTop > 500) ? btn.classList.remove('hidden') : btn.classList.add('hidden');
};

globalThis.document.addEventListener('DOMContentLoaded', chargerCatalogue);