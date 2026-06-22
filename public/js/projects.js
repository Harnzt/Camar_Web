// ====================================
// 1. INITIALIZE ON LOAD
// ====================================
document.addEventListener('DOMContentLoaded', function() {
    loadEmissionData();
    initializeLiveSearch();
    initializeLiveSort();
});

// ====================================
// 2. LOAD EMISSION DATA FROM CALCULATOR
// ====================================
function loadEmissionData() {
    const savedInputs = localStorage.getItem('calculatorInputs');
    
    if (savedInputs) {
        const inputs = JSON.parse(savedInputs);
        
        const fuelConsumption = parseFloat(inputs['fuel-consumption']) || 0;
        const fuelFactor = parseFloat(inputs['fuel-factor']) || 2.68;
        const electricityConsumption = parseFloat(inputs['electricity-consumption']) || 0;
        const electricityFactor = parseFloat(inputs['electricity-factor']) || 0.85;
        const transportDistance = parseFloat(inputs['transport-distance']) || 0;
        const transportFactor = parseFloat(inputs['transport-factor']) || 0.21;
        const wasteAmount = parseFloat(inputs['waste-amount']) || 0;
        const wasteFactor = parseFloat(inputs['waste-factor']) || 0.5;
        
        const scope1 = fuelConsumption * fuelFactor;
        const scope2 = electricityConsumption * electricityFactor;
        const scope3 = (transportDistance * transportFactor) + (wasteAmount * wasteFactor);
        const total = scope1 + scope2 + scope3;
        
        const treeEquivalent = Math.ceil(total / 21.77);
        const offsetNeeded = (total / 1000).toFixed(2);
        
        const totalEmissionsEl = document.getElementById('total-emissions');
        const treeEquivalentEl = document.getElementById('tree-equivalent');
        const offsetNeededEl = document.getElementById('offset-needed');

        if (totalEmissionsEl) totalEmissionsEl.textContent = total.toFixed(2);
        if (treeEquivalentEl) treeEquivalentEl.textContent = treeEquivalent.toLocaleString('id-ID');
        if (offsetNeededEl) offsetNeededEl.textContent = offsetNeeded;
    }

    if (typeof userEmission !== 'undefined' && userEmission.total_kg > 0) {
        const totalEmissionsEl = document.getElementById('total-emissions');
        const treeEquivalentEl = document.getElementById('tree-equivalent');
        const offsetNeededEl = document.getElementById('offset-needed');

        if (totalEmissionsEl) totalEmissionsEl.textContent = userEmission.total_kg.toLocaleString('id-ID');
        if (treeEquivalentEl) treeEquivalentEl.textContent = Math.round(userEmission.tree_equivalent).toLocaleString('id-ID');
        if (offsetNeededEl) offsetNeededEl.textContent = (userEmission.total_kg / 1000).toFixed(2);
    }
}

// ====================================
// 3. LIVE SEARCH (Portofolio & Kompetitor)
// ====================================
function initializeLiveSearch() {
    const searchInput = document.getElementById('search-input');
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const allProjectCards = document.querySelectorAll('.seller-own-projects-section .row > div, #projects-grid > div');
           
            allProjectCards.forEach(cardContainer => {
                if (cardContainer.classList.contains('col-12')) return;

                const titleEl = cardContainer.querySelector('.project-card__title');
                const companyEl = cardContainer.querySelector('.project-card__company');
                const categoryEl = cardContainer.querySelector('.project-card__category');

                const title = titleEl ? titleEl.innerText.toLowerCase() : '';
                const company = companyEl ? companyEl.innerText.toLowerCase() : '';
                const category = categoryEl ? categoryEl.innerText.toLowerCase() : '';

                if (title.includes(searchTerm) || company.includes(searchTerm) || category.includes(searchTerm)) {
                    cardContainer.style.setProperty('display', 'block', 'important');
                } else {
                    cardContainer.style.setProperty('display', 'none', 'important');
                }
            });
        });
    }
}

// ====================================
// 4. LIVE SORT (DOM Sorting untuk Kedua Section)
// ====================================
function initializeLiveSort() {
    const sortSelect = document.getElementById('sort-select');
    
    
    if (sortSelect) {
        const marketGrid = document.getElementById('projects-grid');
        const ownGrid = document.querySelector('.seller-own-projects-section .row');
        // Simpan urutan asli masing-masing section
        const originalMarketOrder = marketGrid ? Array.from(marketGrid.children) : [];
        const originalOwnOrder = ownGrid ? Array.from(ownGrid.children) : [];

        sortSelect.addEventListener('change', function(e) {
            const sortBy = e.target.value;
            
            // Jika kembali ke 'relevant', kembalikan ke urutan asli database
            if (sortBy === 'relevant') {
                if (marketGrid) originalMarketOrder.forEach(node => marketGrid.appendChild(node));
                if (ownGrid) originalOwnOrder.forEach(node => ownGrid.appendChild(node));
                return;
            }

            // Fungsi helper untuk menyortir isi grid container
            const sortGridElements = (container) => {
                if (!container) return;
                const cardsArray = Array.from(container.children);
                
                const validCards = cardsArray.filter(node => !node.classList.contains('col-12'));
                
                // Jangan sortir jika isinya adalah teks kosong/fallback
                if (cardsArray.length === 1 && cardsArray[0].classList.contains('text-center')) return;

                cardsArray.sort((a, b) => {
                    const priceA_Text = a.querySelector('.project-card__price')?.innerText || '0';
                    const priceA = parseInt(priceA_Text.replace(/[^0-9]/g, '')) || 0;
                    
                    const priceB_Text = b.querySelector('.project-card__price')?.innerText || '0';
                    const priceB = parseInt(priceB_Text.replace(/[^0-9]/g, '')) || 0;

                    const stockA_Text = a.querySelector('.project-card__capacity')?.innerText || '0';
                    const stockA = parseInt(stockA_Text.replace(/[^0-9]/g, '')) || 0;

                    const stockB_Text = b.querySelector('.project-card__capacity')?.innerText || '0';
                    const stockB = parseInt(stockB_Text.replace(/[^0-9]/g, '')) || 0;

                    if (sortBy === 'price-low') return priceA - priceB;
                    if (sortBy === 'price-high') return priceB - priceA;
                    if (sortBy === 'capacity') return stockB - stockA;
                    
                    return 0;
                });

                cardsArray.forEach(node => container.appendChild(node));
            };

            // Eksekusi sortir ke kedua area grid secara adil
            sortGridElements(marketGrid);
            sortGridElements(ownGrid);
        });
    }
}