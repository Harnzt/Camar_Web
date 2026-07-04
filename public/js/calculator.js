

'use strict';


const EF_DB = {

    // ── BAHAN BAKAR STASIONER & BERGERAK (kgCO₂e/Liter kecuali disebutkan) ──
    fuel: {
        solar_cn53:   { ef: 2.626, unit: 'kgCO₂e/L',  ref: '' },
        solar_cn51:   { ef: 2.650, unit: 'kgCO₂e/L',  ref: '' },
        solar_cn48:   { ef: 2.673, unit: 'kgCO₂e/L',  ref: '' },
        diesel:       { ef: 2.779, unit: 'kgCO₂e/L',  ref: '' },
        ron98:        { ef: 2.310, unit: 'kgCO₂e/L',  ref: '' },
        ron92:        { ef: 2.305, unit: 'kgCO₂e/L',  ref: '' },
        ron90:        { ef: 2.309, unit: 'kgCO₂e/L',  ref: '' },
        ron88:        { ef: 2.315, unit: 'kgCO₂e/L',  ref: '' },
        lpg:          { ef: 3.150, unit: 'kgCO₂e/kg', ref: '' },
        coal_bit:     { ef: 1.974, unit: 'kgCO₂e/kg', ref: '' },
        coal_briket:  { ef: 2.018, unit: 'kgCO₂e/kg', ref: '' },
        charcoal:     { ef: 3.304, unit: 'kgCO₂e/kg', ref: '' },
        natgas:       { ef: 2.150, unit: 'kgCO₂e/m³', ref: '' },
        lgv:          { ef: 3.004, unit: 'kgCO₂e/L',  ref: '' },
        lng:          { ef: 2.699, unit: 'kgCO₂e/kg', ref: '' },
        avtur:        { ef: 2.549, unit: 'kgCO₂e/L',  ref: '' },
        kerosene:     { ef: 2.553, unit: 'kgCO₂e/L',  ref: '' },
        // Household
        cng:          { ef: 2.034, unit: 'kgCO₂e/m³', ref: '' },
        wood:         { ef: 1.747, unit: 'kgCO₂e/kg', ref: '' },
        // Distance-based (kgCO₂e/km) — IPCC 2006 Vol.2 T3.2.2
        car_petrol:   { ef: 0.210, unit: 'kgCO₂e/km', ref: '' },
        car_diesel:   { ef: 0.270, unit: 'kgCO₂e/km', ref: '' },
        motorcycle:   { ef: 0.113, unit: 'kgCO₂e/km', ref: '' },
        listrik:      { ef: 0.000, unit: 'kgCO₂e/km', ref: '' },
    },

    // ── LISTRIK PLN (kgCO₂e/kWh) ─────────────────────────────────────────────
    // Sumber: MEMR Permen 10/2022 
    grid: {
        jawa_bali:   { ef: 0.8099, display: '0,8099', unit: 'kgCO₂e/kWh', ref: 'Grid Jawa-Bali' },
        sumatra:     { ef: 0.8790, display: '0,8790', unit: 'kgCO₂e/kWh', ref: 'Grid Sumatra' },
        kalimantan:  { ef: 1.0460, display: '1,0460', unit: 'kgCO₂e/kWh', ref: 'Grid Kalimantan' },
        sulawesi:    { ef: 0.8450, display: '0,8450', unit: 'kgCO₂e/kWh', ref: 'Grid Sulawesi' },
        // Household (per kWh)
        household:   { ef: 0.8099, display: '0,8099', unit: 'kgCO₂e/kWh', ref: 'Grid Jawa-Bali' },
    },

    // ── TRANSPORTASI PENUMPANG (kgCO₂e/pax·km) ───────────────────────────────
    transit: {
        flight_dom:       { ef: 0.1330, unit: 'kgCO₂e/pax·km', ref: 'GHG Protocol Scope 3 Cat.6 · DEFRA 2023 · Domestik' },
        flight_int_short: { ef: 0.1530, unit: 'kgCO₂e/pax·km', ref: 'GHG Protocol Scope 3 Cat.6 · DEFRA 2023 · Intl <3700km' },
        flight_int_long:  { ef: 0.1950, unit: 'kgCO₂e/pax·km', ref: 'GHG Protocol Scope 3 Cat.6 · DEFRA 2023 · Intl >3700km' },
        train:            { ef: 0.0370, unit: 'kgCO₂e/pax·km', ref: 'GHG Protocol Scope 3 Cat.6 · Buku II Vol I 2012' },
        bus:              { ef: 0.0890, unit: 'kgCO₂e/pax·km', ref: 'Buku II Vol I KLHK 2012 · Bus/Angkot' },
        // Flight class multipliers for company (base = economy pax·km)
        economy:  { ef: 0.133, unit: 'kgCO₂e/pax·km', ref: 'DEFRA 2023 · Economy class' },
        business: { ef: 0.266, unit: 'kgCO₂e/pax·km', ref: 'DEFRA 2023 · Business class (2× economy)' },
        first:    { ef: 0.399, unit: 'kgCO₂e/pax·km', ref: 'DEFRA 2023 · First class (3× economy)' },
    },

    // ── KERETA API (kgCO₂e/pax·km) ───────────────────────────────────────────
    // Semua kelas kereta menggunakan EF kereta yang sama (infrastruktur sama)
    // perbedaan hanya faktor kenyamanan, emisi per pax·km konsisten
    train: {
        ekonomi:     { ef: 0.01219, unit: 'kgCO₂e/pax·km', ref: 'KAI' },
        bisnis:      { ef: 0.01308, unit: 'kgCO₂e/pax·km', ref: 'KAI' },
        eksekutif:   { ef: 0.01750, unit: 'kgCO₂e/pax·km', ref: 'KAI' },
        panoramic:   { ef: 0.02469, unit: 'kgCO₂e/pax·km', ref: 'KAI' },
        luxury:      { ef: 0.02804, unit: 'kgCO₂e/pax·km', ref: 'KAI' },
        priority:    { ef: 0.03044, unit: 'kgCO₂e/pax·km', ref: 'KAI' },
        compartment: { ef: 0.04390, unit: 'kgCO₂e/pax·km', ref: 'KAI' },
    },

    // ── HOTEL (kgCO₂e/kamar·malam) ───────────────────────────────────────────
    hotel: { ef: 49.37, unit: 'Bank Indonesia (2026), DEFRA (2025)' },

    // ── PANGAN (kgCO₂e/kg) ───────────────────────────────────────────────────
    // Sumber: IPCC 2006 Vol.4 AFOLU · FAO 2022 · GHG Protocol Scope 3
    food: {
        beef:    { ef: 27.0, unit: 'kgCO₂e/kg', ref: 'IPCC 2006 Vol.4 · Enteric fermentation' },
        poultry: { ef:  6.9, unit: 'kgCO₂e/kg', ref: 'IPCC 2006 Vol.4 AFOLU' },
        fish:    { ef:  5.4, unit: 'kgCO₂e/kg', ref: 'FAO 2022 · GHG Protocol Scope 3' },
        veg:     { ef:  0.4, unit: 'kgCO₂e/kg', ref: '' },
    },

    // ── SAMPAH & AIR ──────────────────────────────────────────────────────────
    waste: { ef: 0.52,  unit: 'kgCO₂e/kg', ref: 'IPCC 2006 Vol.5 · Buku II Vol I 2012 TPA' },
    water: { ef: 0.344, unit: 'kgCO₂e/m³', ref: 'GHG Protocol Scope 3 Cat.1' },

    // ── KENDARAAN PRIBADI (distance-based, kgCO₂e/km) ────────────────────────
    vehicle: {
        car_petrol: { ron98: 0.210, ron92: 0.212, ron90: 0.214, ron88: 0.216, listrik: 0 },
        car_diesel: { ron98: 0.270, ron92: 0.270, ron90: 0.270, ron88: 0.270, listrik: 0, diesel: 0.270 },
        motorcycle: { ron98: 0.108, ron92: 0.110, ron90: 0.112, ron88: 0.113, listrik: 0 },
    },
};

// ── EF lookup helpers ──────────────────────────────────────────────────────────
function getFuelEF(key) { return EF_DB.fuel[key] || null; }
function getGridEF(key) { return EF_DB.grid[key] || null; }
function getTransitEF(key) { return EF_DB.transit[key] || null; }
function getTrainEF(key)   { return EF_DB.train[key] || null; }
function getFoodEF(key)    { return EF_DB.food[key] || null; }

const AIRPORT_API_URL = '/api/airports';
const AIRPORT_LOOKUP = {
    byCode: new Map(),
    byName: new Map(),
    byDisplay: new Map(),
};
const AIRPORT_QUERY_CACHE = new Map();

function rememberAirport(airport) {
    const code = String(airport.iata_code || '').trim().toUpperCase();
    const name = String(airport.name || '').trim();
    if (!code || !name) return null;

    const normalisedAirport = {
        ...airport,
        iata_code: code,
        name,
        latitude_deg: parseFloat(airport.latitude_deg),
        longitude_deg: parseFloat(airport.longitude_deg),
    };
    const display = `${code} - ${name}`;

    AIRPORT_LOOKUP.byCode.set(code, normalisedAirport);
    AIRPORT_LOOKUP.byName.set(name.toLowerCase(), normalisedAirport);
    AIRPORT_LOOKUP.byDisplay.set(display.toLowerCase(), normalisedAirport);

    return normalisedAirport;
}

function renderAirportOptions(airports) {
    const datalist = document.getElementById('airport-options');
    if (!datalist) return;

    datalist.innerHTML = airports
        .map((airport) => {
            const display = airport.display || `${airport.iata_code} - ${airport.name}`;
            return `<option value="${escapeHtml(display)}"></option>`;
        })
        .join('');
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

async function fetchAirports(search) {
    const query = String(search || '').trim();
    if (query.length < 2) return [];

    const cacheKey = query.toLowerCase();
    if (AIRPORT_QUERY_CACHE.has(cacheKey)) {
        return AIRPORT_QUERY_CACHE.get(cacheKey);
    }

    const url = `${AIRPORT_API_URL}?search=${encodeURIComponent(query)}&limit=12`;
    const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!response.ok) return [];

    const airports = await response.json();
    const normalisedAirports = Array.isArray(airports)
        ? airports.map(rememberAirport).filter(Boolean)
        : [];

    AIRPORT_QUERY_CACHE.set(cacheKey, normalisedAirports);
    return normalisedAirports;
}

function scheduleAirportSearch(input) {
    if (!input) return;

    const query = String(input.value || '').trim();
    if (query.length < 2) return;
    if (input.dataset.airportSearchQuery === query) return;

    clearTimeout(input._airportSearchTimer);
    input._airportSearchTimer = setTimeout(() => {
        fetchAirports(query)
            .then((airports) => {
                input.dataset.airportSearchQuery = query;
                renderAirportOptions(airports);
                updateFlightRoute(input);
                calcLive();
            })
            .catch(() => {
                input.dataset.airportSearchQuery = '';
            });
    }, 250);
}

function findAirport(value) {
    const raw = String(value || '').trim();
    if (!raw) return null;

    const code = raw.toUpperCase();
    if (AIRPORT_LOOKUP.byCode.has(code)) return AIRPORT_LOOKUP.byCode.get(code);

    const lower = raw.toLowerCase();
    if (AIRPORT_LOOKUP.byName.has(lower)) return AIRPORT_LOOKUP.byName.get(lower);
    if (AIRPORT_LOOKUP.byDisplay.has(lower)) return AIRPORT_LOOKUP.byDisplay.get(lower);

    const codePrefix = raw.split('-')[0]?.trim().toUpperCase();
    if (AIRPORT_LOOKUP.byCode.has(codePrefix)) return AIRPORT_LOOKUP.byCode.get(codePrefix);

    return null;
}

async function normaliseAirportField(input) {
    if (!input) return null;

    let airport = findAirport(input.value);
    if (!airport) {
        await fetchAirports(input.value).catch(() => []);
        airport = findAirport(input.value);
    }

    if (airport) {
        input.value = airport.iata_code;
        updateFlightRoute(input);
        calcLive();
    }

    return airport;
}

function normaliseAllAirportFields() {
    return Promise.all([...document.querySelectorAll('.airport-input')].map(normaliseAirportField));
}

function haversineKm(origin, destination) {
    const toRad = (deg) => deg * Math.PI / 180;
    const lat1 = toRad(origin.latitude_deg);
    const lat2 = toRad(destination.latitude_deg);
    const dLat = toRad(destination.latitude_deg - origin.latitude_deg);
    const dLon = toRad(destination.longitude_deg - origin.longitude_deg);
    const a = Math.sin(dLat / 2) ** 2
        + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLon / 2) ** 2;

    return 6371.0088 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function updateFlightRoute(inputOrRow) {
    const row = inputOrRow?.classList?.contains('entry-row')
        ? inputOrRow
        : inputOrRow?.closest?.('.entry-row');
    if (!row) return 0;

    const group = row.dataset.group;
    const originInput = row.querySelector(`[name="${group}_origin[]"]`);
    const destinationInput = row.querySelector(`[name="${group}_destination[]"]`);
    const distanceInput = row.querySelector(`[name="${group}_km[]"]`);
    const hint = row.querySelector('.flight-distance-hint');
    const origin = findAirport(originInput?.value);
    const destination = findAirport(destinationInput?.value);

    if (!distanceInput) return 0;

    if (!origin || !destination) {
        scheduleAirportSearch(originInput);
        scheduleAirportSearch(destinationInput);
        distanceInput.value = '';
        if (hint) hint.textContent = 'Pilih asal dan tujuan bandara';
        return 0;
    }

    if (origin.iata_code === destination.iata_code) {
        distanceInput.value = '0';
        if (hint) hint.textContent = `${origin.iata_code} ke ${destination.iata_code}`;
        return 0;
    }

    const distance = haversineKm(origin, destination);
    distanceInput.value = distance.toFixed(2);
    if (hint) hint.textContent = `${origin.iata_code} ke ${destination.iata_code}`;

    return distance;
}

function updateAllFlightRoutes() {
    document.querySelectorAll('[data-group="p_flight"], [data-group="c_flight"]').forEach(updateFlightRoute);
}

// ═══════════════════════════════════════════════════════════════════════════════
// TAB NAVIGATION
// ═══════════════════════════════════════════════════════════════════════════════

function switchTab(prefix, step, total) {
    // Hide all panes for this prefix
    for (let i = 1; i <= total; i++) {
        const pane = document.getElementById(`${prefix}-step-${i}`);
        if (pane) {
            const isTarget = i === step;
            pane.classList.toggle('active', isTarget);
            pane.hidden = !isTarget;
        }
    }

    // Update tab buttons
    for (let i = 1; i <= total; i++) {
        const isTarget = i === step;
        document.querySelectorAll(`[data-step="${prefix}${i}"]`).forEach(b => {
            b.classList.toggle('active', isTarget);
            b.setAttribute('aria-selected', isTarget ? 'true' : 'false');
            b.tabIndex = isTarget ? 0 : -1;
        });
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function switchSubTab(group, key) {
    // Hide all panes in group
    document.querySelectorAll(`[id^="${group}-"]`).forEach(p => {
        const isTarget = p.id === `${group}-${key}`;
        p.classList.toggle('active', isTarget);
        p.hidden = !isTarget;
    });
    // Activate
    const target = document.getElementById(`${group}-${key}`);
    if (target) target.classList.add('active');

    document.querySelectorAll(`.sub-tab-btn[data-sub-tab-group="${group}"]`).forEach((button) => {
        const isTarget = button.dataset.subTabKey === key;
        button.classList.toggle('active', isTarget);
        button.setAttribute('aria-selected', isTarget ? 'true' : 'false');
        button.tabIndex = isTarget ? 0 : -1;
    });
}

function switchMethod(method) {
    document.getElementById('method-fuel')?.classList.remove('active');
    document.getElementById('method-dist')?.classList.remove('active');
    document.getElementById(`method-${method}`)?.classList.add('active');
    document.getElementById('method-fuel')?.toggleAttribute('hidden', method !== 'fuel');
    document.getElementById('method-dist')?.toggleAttribute('hidden', method !== 'dist');

    document.getElementById('methodFuelBtn')?.classList.remove('active');
    document.getElementById('methodDistBtn')?.classList.remove('active');
    const fuelBtn = document.getElementById('methodFuelBtn');
    const distBtn = document.getElementById('methodDistBtn');
    const activeBtn = method === 'fuel' ? fuelBtn : distBtn;

    [fuelBtn, distBtn].forEach((btn) => {
        if (!btn) return;
        const isActive = btn === activeBtn;
        btn.classList.toggle('active', isActive);
        btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        btn.tabIndex = isActive ? 0 : -1;
    });
}

// ═══════════════════════════════════════════════════════════════════════════════
// MODULE ACCORDION
// ═══════════════════════════════════════════════════════════════════════════════

function toggleModule(id) {
    const content = document.getElementById(`${id}-content`);
    const header  = content?.previousElementSibling;
    const icon    = header?.querySelector('.module-toggle');
    if (!content) return;

    const isOpen = content.classList.contains('open');
    content.classList.toggle('open', !isOpen);
    content.hidden = isOpen;
    header?.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
    icon?.classList.toggle('open', !isOpen);
}

function enhanceCalculatorAccessibility() {
    document.querySelectorAll('.module-header[onclick]').forEach((header) => {
        const match = header.getAttribute('onclick')?.match(/toggleModule\('([^']+)'\)/);
        if (!match) return;

        const moduleId = match[1];
        const button = document.createElement('button');
        button.type = 'button';
        button.className = header.className;
        button.dataset.moduleToggle = moduleId;
        button.setAttribute('aria-expanded', 'false');
        button.setAttribute('aria-controls', `${moduleId}-content`);
        button.innerHTML = header.innerHTML;
        header.replaceWith(button);
    });

    document.querySelectorAll('[data-module-toggle]').forEach((button) => {
        button.addEventListener('click', () => toggleModule(button.dataset.moduleToggle));
    });

    document.querySelectorAll('.module-content[id$="-content"]').forEach((content) => {
        content.hidden = !content.classList.contains('open');
    });

    document.querySelectorAll('.step-tab-nav').forEach((nav) => {
        nav.setAttribute('role', 'tablist');
        nav.querySelectorAll('.step-tab-btn').forEach((button) => {
            const step = button.dataset.step || '';
            const prefix = step.charAt(0);
            const number = step.slice(1);
            const pane = document.getElementById(`${prefix}-step-${number}`);

            button.type = 'button';
            button.setAttribute('role', 'tab');
            button.id ||= `${step}-tab`;
            if (pane) {
                button.setAttribute('aria-controls', pane.id);
                pane.setAttribute('role', 'tabpanel');
                pane.setAttribute('aria-labelledby', button.id);
                pane.hidden = !pane.classList.contains('active');
            }
            button.setAttribute('aria-selected', button.classList.contains('active') ? 'true' : 'false');
            button.tabIndex = button.classList.contains('active') ? 0 : -1;
            button.addEventListener('click', () => switchTab(prefix, Number(number), nav.querySelectorAll('.step-tab-btn').length));
        });

        nav.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            const tabs = [...nav.querySelectorAll('.step-tab-btn')];
            const current = tabs.indexOf(document.activeElement);
            if (current === -1) return;

            event.preventDefault();
            const nextIndex = event.key === 'Home'
                ? 0
                : event.key === 'End'
                    ? tabs.length - 1
                    : (current + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
            tabs[nextIndex].focus();
            tabs[nextIndex].click();
        });
    });

    document.querySelectorAll('.sub-step-tabs').forEach((nav) => {
        nav.setAttribute('role', 'tablist');
        nav.querySelectorAll('.sub-tab-btn').forEach((button) => {
            const group = button.dataset.subTabGroup;
            const key = button.dataset.subTabKey;
            if (!group || !key) return;

            const panelId = `${group}-${key}`;
            const panel = document.getElementById(panelId);
            button.type = 'button';
            button.setAttribute('role', 'tab');
            button.id ||= `${panelId}-tab`;
            button.setAttribute('aria-controls', panelId);
            button.setAttribute('aria-selected', button.classList.contains('active') ? 'true' : 'false');
            button.tabIndex = button.classList.contains('active') ? 0 : -1;
            if (panel) {
                panel.setAttribute('role', 'tabpanel');
                panel.setAttribute('aria-labelledby', button.id);
                panel.hidden = !panel.classList.contains('active');
            }
            button.addEventListener('click', () => switchSubTab(group, key));
        });

        nav.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            const tabs = [...nav.querySelectorAll('.sub-tab-btn')];
            const current = tabs.indexOf(document.activeElement);
            if (current === -1) return;

            event.preventDefault();
            const nextIndex = event.key === 'Home'
                ? 0
                : event.key === 'End'
                    ? tabs.length - 1
                    : (current + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
            tabs[nextIndex].focus();
            tabs[nextIndex].click();
        });
    });

    const methodButtons = [
        ['methodFuelBtn', 'method-fuel'],
        ['methodDistBtn', 'method-dist'],
    ];
    methodButtons.forEach(([buttonId, panelId]) => {
        const button = document.getElementById(buttonId);
        const panel = document.getElementById(panelId);
        if (!button || !panel) return;

        button.type = 'button';
        button.setAttribute('role', 'tab');
        button.setAttribute('aria-controls', panelId);
        button.setAttribute('aria-selected', button.classList.contains('active') ? 'true' : 'false');
        button.tabIndex = button.classList.contains('active') ? 0 : -1;
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', buttonId);
        panel.hidden = !panel.classList.contains('active');
    });

    document.querySelectorAll('[data-method]').forEach((button) => {
        button.addEventListener('click', () => switchMethod(button.dataset.method));
    });
}

// ═══════════════════════════════════════════════════════════════════════════════
// DYNAMIC ROWS — ADD / REMOVE
// ═══════════════════════════════════════════════════════════════════════════════

const ROW_TEMPLATES = {};

document.addEventListener('DOMContentLoaded', () => {
    organisePersonalCalculator();

    // Cache first row of each group as template
    document.querySelectorAll('[data-group]').forEach(row => {
        const g = row.dataset.group;
        if (!ROW_TEMPLATES[g]) ROW_TEMPLATES[g] = row.outerHTML;
    });
    initAll();
});

function organisePersonalCalculator() {
    const scope2Slot = document.getElementById('personal-scope2-modules');
    const scope3Slot = document.getElementById('personal-scope3-modules');
    const electricityModule = document.getElementById('p_electricity-content')?.closest('.calc-module');
    const flightModule = document.getElementById('p_flight-content')?.closest('.calc-module');

    if (scope2Slot && electricityModule) {
        scope2Slot.appendChild(electricityModule);
    }

    if (scope3Slot && flightModule) {
        scope3Slot.appendChild(flightModule);
    }
}

function addRow(group) {
    const container = document.getElementById(`rows_${group}`);
    if (!container || !ROW_TEMPLATES[group]) return;

    const tmp  = document.createElement('div');
    tmp.innerHTML = ROW_TEMPLATES[group];
    const newRow = tmp.firstElementChild;

    // Reset values
    newRow.querySelectorAll('input').forEach(i => i.value = '');
    newRow.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    newRow.querySelectorAll('.flight-distance-hint').forEach(h => h.textContent = 'Pilih asal dan tujuan bandara');
    newRow.querySelectorAll('.ef-chip[data-ef-group]').forEach(c => c.textContent = '—');

    container.appendChild(newRow);
    newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function removeRow(btn) {
    const row = btn.closest('.entry-row');
    const container = row?.parentElement;
    if (!container) return;
    if (container.querySelectorAll('.entry-row').length <= 1) {
        // Reset instead of remove
        row.querySelectorAll('input').forEach(i => i.value = '');
        row.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        row.querySelectorAll('.flight-distance-hint').forEach(h => h.textContent = 'Pilih asal dan tujuan bandara');
        row.querySelectorAll('.ef-chip[data-ef-group]').forEach(c => c.textContent = '—');
    } else {
        row.remove();
    }
    calcLive();
}

// ═══════════════════════════════════════════════════════════════════════════════
// EF CHIP UPDATER
// ═══════════════════════════════════════════════════════════════════════════════

function updateEfChip(selectEl, group) {
    const val  = selectEl.value;
    const row  = selectEl.closest('.entry-row');
    const chip = row?.querySelector(`.ef-chip[data-ef-group="${group}"]`);
    if (!chip) return;

    let efData = null;

    if (['c_stat','c_mobile_fuel'].includes(group)) efData = getFuelEF(val);
    else if (group === 'c_mobile_dist')              efData = getFuelEF(val);  // distance-based per-km keys overlap
    else if (group === 'c_elec')                     efData = getGridEF(val);
    else if (group === 'c_flight')                   efData = EF_DB.transit[val] || null;
    else if (group === 'c_train')                    efData = getTrainEF(val);
    else if (group === 'p_energy')                   efData = getFuelEF(val);
    else if (group === 'p_food')                     efData = getFoodEF(val);
    else if (group === 'p_flight')                   efData = getTransitEF(val);
    else if (group === 'p_transit')                  efData = getTransitEF(val);

    if (efData) {
        chip.innerHTML = `${efData.display || efData.ef} ${efData.unit}<br><small>${efData.ref}</small>`;
    } else {
        chip.textContent = '—';
    }
}

function updateVehicleFuel(typeSelect) {
    // Optionally lock fuel options based on vehicle type (UX nicety)
    const fuelSel = typeSelect.closest('.entry-fields')?.querySelector('[name="p_vehicle_fuel[]"]');
    if (!fuelSel) return;
    const t = typeSelect.value;
    // For diesel cars, suggest diesel; for motorcycle, suggest RON
    if (t === 'car_diesel') {
        fuelSel.querySelector('option[value="ron98"]') && (fuelSel.value = 'ron88');
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// LIVE CALCULATION ENGINE
// ═══════════════════════════════════════════════════════════════════════════════

function calcLive() {
    updateAllFlightRoutes();

    // Run full calc quietly and update previews
    const r = calcPersonalAll();
    setPreview('p_energy',      r.energy_rt);
    setPreview('p_vehicle',     r.vehicle);
    setPreview('p_electricity', r.electricity);
    setPreview('p_flight',      r.flight);
    setPreview('p_food',        r.food);
    setPreview('p_water',       r.water);
    setPreview('p_waste',       r.waste);
    setSub('p_energy',      r.energy_rt);
    setSub('p_electricity', r.electricity);
    setSub('p_vehicle',     r.vehicle);
    setSub('p_flight',      r.flight);
    setSub('p_food',        r.food);
    setSub('p_water',       r.water);
    setSub('p_waste',       r.waste);

    const c = calcCompanyAll();
    setPreview('c_stat',   c.stat);
    setPreview('c_mobile', c.mobile);
    setPreview('c_elec',   c.elec);
    setPreview('c_flight', c.flight);
    setPreview('c_hotel',  c.hotel);
    setPreview('c_train',  c.train);
    setSub('c_stat',   c.stat);
    setSub('c_mobile', c.mobile);
    setSub('c_elec',   c.elec);
    setSub('c_flight', c.flight);
    setSub('c_hotel',  c.hotel);
    setSub('c_train',  c.train);
}

function setPreview(id, val) {
    const el = document.getElementById(`prev_${id}`);
    if (!el) return;
    const unit = el.querySelector('span');
    const num  = val >= 1000
        ? (val / 1000).toFixed(2) + ' ton'
        : val.toFixed(1) + ' kg';
    el.childNodes[0].textContent = num + ' ';
}

function setSub(id, val) {
    const el = document.getElementById(`sub_${id}`);
    if (!el) return;
    el.textContent = val >= 1000
        ? (val / 1000).toFixed(3) + ' ton'
        : val.toFixed(2);
}

function v(id) { return parseFloat(document.getElementById(id)?.value) || 0; }
function rows(name) {
    return [...document.querySelectorAll(`[name="${name}[]"]`)];
}
function rowPairs(nameA, nameB) {
    const a = rows(nameA), b = rows(nameB);
    return a.map((el, i) => [el.value, parseFloat(b[i]?.value) || 0]);
}
function rowTriples(nameA, nameB, nameC) {
    const a = rows(nameA), b = rows(nameB), c = rows(nameC);
    return a.map((el, i) => [el.value, parseFloat(b[i]?.value)||0, parseFloat(c[i]?.value)||0]);
}

// ── Personal totals ────────────────────────────────────────────────────────────
function calcPersonalAll() {
    // Energy RT (monthly × 12)
    let energy_rt = 0;
    rowPairs('p_energy_fuel','p_energy_qty').forEach(([fuel, qty]) => {
        const ef = getFuelEF(fuel);
        if (ef) energy_rt += qty * 12 * ef.ef;
    });

    // Kendaraan (jarak monthly × 12)
    let vehicle = 0;
    rowTriples('p_vehicle_type','p_vehicle_fuel','p_vehicle_km').forEach(([type, fuel, km]) => {
        const vEF = EF_DB.vehicle[type];
        if (vEF) {
            const efVal = vEF[fuel] !== undefined ? vEF[fuel] : (vEF.ron88 || 0);
            vehicle += km * 12 * efVal;
        }
    });

    // Listrik (monthly kWh × 12 × 0.8099)
    const electricity = v('p_elec_kwh') * 12 * EF_DB.grid.household.ef;

    // Penerbangan (pax x km x ef)
    let flight = 0;
    rowTriples('p_flight_class','p_flight_pax','p_flight_km').forEach(([cls, pax, km]) => {
        const ef = getTransitEF(cls);
        if (ef) flight += pax * km * ef.ef;
    });

    // Pangan (monthly × 12)
    let food = 0;
    rowPairs('p_food_type','p_food_kg').forEach(([type, kg]) => {
        const ef = getFoodEF(type);
        if (ef) food += kg * 12 * ef.ef;
    });

    // Air (monthly × 12)
    const water = v('p_water_m3') * 12 * EF_DB.water.ef;

    // Sampah (monthly × 12)
    const waste = v('p_waste_kg') * 12 * EF_DB.waste.ef;

    return { energy_rt, vehicle, electricity, flight, food, water, waste };
}

// ── Company totals ─────────────────────────────────────────────────────────────
function calcCompanyAll() {
    // Stasioner (L or kg/year × EF)
    let stat = 0;
    rowPairs('c_stat_fuel','c_stat_qty').forEach(([fuel, qty]) => {
        const ef = getFuelEF(fuel);
        if (ef) stat += qty * ef.ef;
    });

    // Mobile — BBM
    let mobileFuel = 0;
    rowPairs('c_mobile_fuel_type','c_mobile_fuel_qty').forEach(([fuel, qty]) => {
        const ef = getFuelEF(fuel);
        if (ef) mobileFuel += qty * ef.ef;
    });

    // Mobile — Jarak (distance-based: km × kgCO₂e/km by fuel)
    let mobileDist = 0;
    rowPairs('c_mobile_dist_fuel','c_mobile_dist_km').forEach(([fuel, km]) => {
        // Use vehicle EF proxy: diesel ≈ 0.27/km, petrol ≈ 0.21/km
        let ef_km = 0;
        if (['solar_cn53','solar_cn51','solar_cn48','diesel'].includes(fuel)) ef_km = 0.270;
        else if (['ron98','ron92','ron90','ron88'].includes(fuel)) ef_km = 0.210;
        mobileDist += km * ef_km;
    });

    const mobile = mobileFuel + mobileDist;

    // Scope 2 — Listrik (kWh × kgCO₂e/kWh)
    let elec = 0;
    rowPairs('c_elec_src','c_elec_kwh').forEach(([src, kwh]) => {
        const ef = getGridEF(src);
        if (ef) elec += kwh * ef.ef;
    });

    // Scope 3 — Pesawat (pax × km × ef)
    let flight = 0;
    rowTriples('c_flight_class','c_flight_pax','c_flight_km').forEach(([cls, pax, km]) => {
        const ef = EF_DB.transit[cls];
        if (ef) flight += pax * km * ef.ef;
    });

    // Scope 3 — Hotel (kamar × malam × ef)
    let hotel = 0;
    rowPairs('c_hotel_nights','c_hotel_rooms').forEach(([nights, rooms]) => {
        hotel += (parseFloat(nights)||0) * rooms * EF_DB.hotel.ef;
    });

    // Scope 3 — Kereta (km × ef, 1 penumpang per baris)
    let train = 0;
    rowPairs('c_train_class','c_train_km').forEach(([cls, km]) => {
        const ef = getTrainEF(cls);
        if (ef) train += km * ef.ef;
    });

    return { stat, mobile, elec, flight, hotel, train };
}

// ═══════════════════════════════════════════════════════════════════════════════
// MAIN CALCULATE BUTTONS
// ═══════════════════════════════════════════════════════════════════════════════
// Pastikan fungsi ini ada dan tidak terbungkus di dalam const atau let yang tertutup
    async function calculateAll(mode) {
        console.log("Tombol hitung ditekan, mode:", mode);
        await normaliseAllAirportFields();
        updateAllFlightRoutes();
        
        // 1. Ambil data kalkulasi
        let totalKg = 0;
        let details = {};

        if (mode === 'personal') {
            details = calcPersonalAll();
            totalKg = Object.values(details).reduce((s, v) => s + v, 0);
        } else {
            const c = calcCompanyAll();
            totalKg = c.stat + c.mobile + c.elec + c.flight + c.hotel + c.train;
            details = c;
        }

        if (totalKg <= 0) {
            alert('Silakan masukkan data konsumsi Anda terlebih dahulu sebelum menghitung!');
            return;
        }

        if (mode === 'company') {
            window.currentCalculationData = {
                mode: mode,
                total_kg: totalKg,
                details: details
            };
            renderCompanyResult(details, totalKg);
            switchTab('c', 4, 4);
            document.getElementById('result-box-company')?.scrollIntoView({ behavior: 'smooth' });
            return;
        }

        window.currentCalculationData = {
            mode: mode,
            total_kg: totalKg,
            details: details
        };

        renderPersonalResult(details, totalKg);
        switchTab('p', 4, 4);
        document.getElementById('result-box')?.scrollIntoView({ behavior: 'smooth' });
    }

    function showResults(data) {
        const box = document.getElementById('result-box'); // Sesuai ID di blade
        box.style.display = 'block';
        
        // Isi elemen HTML dengan data hasil
        document.getElementById('total-emisi-display').textContent = data.total_kg + ' kg CO2';
        document.getElementById('biaya-display').textContent = 'Rp ' + data.biaya.toLocaleString();
        
        // Tampilkan tombol "Lanjut ke Dashboard" agar user bisa pindah manual
        document.getElementById('btn-to-dashboard').style.display = 'inline-block';
    }

// ═══════════════════════════════════════════════════════════════════════════════
// RENDER RESULTS
// ═══════════════════════════════════════════════════════════════════════════════

function renderPersonalResult(cats, totalKg) {
    const box = document.getElementById('result-box');
    if (!box) return;
    box.style.display = 'block';

    const totalTon = totalKg / 1000;
    const scope1 = cats.energy_rt + cats.vehicle;
    const scope2 = cats.electricity;
    const scope3 = cats.flight + cats.food + cats.water + cats.waste;
    const items = [
        { label: 'Scope 1 — Energi Rumah Tangga', kg: cats.energy_rt },
        { label: 'Scope 1 — Kendaraan Pribadi', kg: cats.vehicle },
        { label: 'Scope 2 — Listrik', kg: cats.electricity },
        { label: 'Scope 3 — Penerbangan', kg: cats.flight },
        { label: 'Scope 3 — Pangan', kg: cats.food },
        { label: 'Scope 3 — Air Bersih', kg: cats.water },
        { label: 'Scope 3 — Limbah', kg: cats.waste },
    ];

    let gridHTML = items.map((item) => {
        const ton = item.kg / 1000;
        return `<div class="result-item">
            <div class="result-label">${item.label}</div>
            <div class="result-value">${ton < 1 ? item.kg.toFixed(1) : ton.toFixed(3)}</div>
            <div class="result-unit">${ton < 1 ? 'kg CO₂e' : 'ton CO₂e'}</div>
        </div>`;
    }).join('');

    gridHTML += `<div class="result-item result-item-total">
        <div class="result-label">Total Emisi</div>
        <div class="result-value result-value-total">${totalTon.toFixed(3)}</div>
        <div class="result-unit">ton CO₂e/tahun</div>
    </div>`;

    const scope1Pct = totalKg > 0 ? ((scope1 / totalKg) * 100).toFixed(1) : 0;
    const scope2Pct = totalKg > 0 ? ((scope2 / totalKg) * 100).toFixed(1) : 0;
    const scope3Pct = totalKg > 0 ? ((scope3 / totalKg) * 100).toFixed(1) : 0;
    const trees = Math.round(totalTon / 0.022);
    const saveButton = window.CARBON_STORAGE_KEY
        ? `<button type="button" class="btn-calculate" data-save-calculation>
                <i class="fas fa-cloud-upload-alt"></i> Simpan Hasil ke Dashboard
           </button>`
        : `<a href="/register" class="btn-calculate result-register-link">
                <i class="fas fa-user-plus"></i> Daftar Akun untuk Simpan Hasil
           </a>`;

    box.innerHTML = `
        <h3 class="result-title"><i class="fas fa-chart-bar"></i> Hasil Kalkulasi Emisi Individu</h3>
        <div class="result-grid">${gridHTML}</div>

        <div class="distribution-section">
            <div class="distribution-title">Distribusi Emisi per Scope (GHG Protocol)</div>
            <div class="distribution-row">
                <span class="distribution-label">Scope 1</span>
                <div class="distribution-track"><div class="distribution-fill fill-scope1" id="pbar_s1" style="width:0%"></div></div>
                <span class="distribution-pct">${scope1Pct}%</span>
            </div>
            <div class="distribution-row">
                <span class="distribution-label">Scope 2</span>
                <div class="distribution-track"><div class="distribution-fill fill-scope2" id="pbar_s2" style="width:0%"></div></div>
                <span class="distribution-pct">${scope2Pct}%</span>
            </div>
            <div class="distribution-row">
                <span class="distribution-label">Scope 3</span>
                <div class="distribution-track"><div class="distribution-fill fill-scope3" id="pbar_s3" style="width:0%"></div></div>
                <span class="distribution-pct">${scope3Pct}%</span>
            </div>
        </div>

        <div class="result-footer">
            <p class="result-info">
                Setara dengan <strong>${trees.toLocaleString('id-ID')} pohon</strong> yang harus ditanam untuk offset emisi ini selama 30 tahun.
            </p>
        </div>

        <div class="save-action">
            ${saveButton}
            <a href="/proyek" class="btn-calculate result-project-link">
                <i class="fas fa-seedling"></i> Cari Proyek Offset
            </a>
            <button class="btn-reset" type="button" data-reset-calculator>
                <i class="fas fa-redo"></i> Hitung Ulang
            </button>
        </div>

        <div class="result-disclaimer">
            <i class="fas fa-info-circle"></i>
            Kalkulasi menggunakan faktor emisi dari GHG Protocol, ISO 14064-1:2018, IPCC 2006 Guidelines,
            MEMR Permen 10/2022, dan Pedoman Inventarisasi GRK Nasional Buku II Volume I KLHK 2012.
        </div>`;

    requestAnimationFrame(() => {
        [
            ['pbar_s1', scope1Pct],
            ['pbar_s2', scope2Pct],
            ['pbar_s3', scope3Pct],
        ].forEach(([id, width], index) => {
            setTimeout(() => {
                const bar = document.getElementById(id);
                if (bar) bar.style.width = width + '%';
            }, 80 * (index + 1));
        });
    });
}

function renderCompanyResult(c, totalKg) {
    const box = document.getElementById('result-box-company');
    if (!box) return;
    const totalTon = totalKg / 1000;
    const scope1 = c.stat + c.mobile;
    const scope2 = c.elec;
    const scope3 = c.flight + c.hotel + c.train;

    const items = [
        { label: 'Scope 1 — Stasioner', kg: c.stat, cls: 'fill-scope1' },
        { label: 'Scope 1 — Bergerak',  kg: c.mobile, cls: 'fill-scope1' },
        { label: 'Scope 2 — Listrik',   kg: c.elec,   cls: 'fill-scope2' },
        { label: 'Scope 3 — Penerbangan Dinas', kg: c.flight, cls: 'fill-scope3' },
        { label: 'Scope 3 — Hotel',     kg: c.hotel,  cls: 'fill-scope3' },
        { label: 'Scope 3 — Kereta',    kg: c.train,  cls: 'fill-scope3' },
    ];

    let gridHTML = items.map(it => {
        const ton = it.kg / 1000;
        return `<div class="result-item">
            <div class="result-label">${it.label}</div>
            <div class="result-value">${ton < 1 ? it.kg.toFixed(1) : ton.toFixed(3)}</div>
            <div class="result-unit">${ton < 1 ? 'kg CO₂e' : 'ton CO₂e'}</div>
        </div>`;
    }).join('');
    gridHTML += `<div class="result-item result-item-total">
        <div class="result-label">Total Emisi</div>
        <div class="result-value result-value-total">${totalTon.toFixed(3)}</div>
        <div class="result-unit">ton CO₂e/tahun</div>
    </div>`;

    const s1pct = totalKg > 0 ? ((scope1 / totalKg) * 100).toFixed(1) : 0;
    const s2pct = totalKg > 0 ? ((scope2 / totalKg) * 100).toFixed(1) : 0;
    const s3pct = totalKg > 0 ? ((scope3 / totalKg) * 100).toFixed(1) : 0;
    const trees = Math.round(totalTon / 0.022);
    const saveButton = window.CARBON_STORAGE_KEY
        ? `<button type="button" class="btn-calculate" data-save-calculation>
                <i class="fas fa-cloud-upload-alt"></i> Simpan Hasil ke Dashboard
           </button>`
        : '';

    box.innerHTML = `
        <h3 class="result-title"><i class="fas fa-chart-bar"></i> Hasil Kalkulasi Emisi Perusahaan</h3>
        <div class="result-grid">${gridHTML}</div>

        <div class="distribution-section">
            <div class="distribution-title">Distribusi Emisi per Scope (GHG Protocol)</div>
            <div class="distribution-row">
                <span class="distribution-label">Scope 1</span>
                <div class="distribution-track"><div class="distribution-fill fill-scope1" id="cbar_s1" style="width:0%"></div></div>
                <span class="distribution-pct">${s1pct}%</span>
            </div>
            <div class="distribution-row">
                <span class="distribution-label">Scope 2</span>
                <div class="distribution-track"><div class="distribution-fill fill-scope2" id="cbar_s2" style="width:0%"></div></div>
                <span class="distribution-pct">${s2pct}%</span>
            </div>
            <div class="distribution-row">
                <span class="distribution-label">Scope 3</span>
                <div class="distribution-track"><div class="distribution-fill fill-scope3" id="cbar_s3" style="width:0%"></div></div>
                <span class="distribution-pct">${s3pct}%</span>
            </div>
        </div>

        <div class="result-footer">
            <p class="result-info">
                Setara dengan <strong>${trees.toLocaleString('id-ID')} pohon</strong> yang harus ditanam untuk offset emisi ini selama 30 tahun.
            </p>
        </div>

        <div class="save-action">
            ${saveButton}
            <a href="/proyek" class="btn-calculate" style="text-decoration:none; display:inline-flex; align-items:center; gap:.5rem;">
                <i class="fas fa-seedling"></i> Cari Proyek Offset
            </a>
            <button class="btn-reset" type="button" data-reset-calculator>
                <i class="fas fa-redo"></i> Hitung Ulang
            </button>
        </div>

        <div class="result-disclaimer">
            <i class="fas fa-info-circle"></i>
            Kalkulasi menggunakan faktor emisi dari GHG Protocol, ISO 14064-1:2018, IPCC 2006 Guidelines (Vol.2,3,4,5),
            MEMR Permen 10/2022, DEFRA 2023, dan Pedoman Inventarisasi GRK Nasional Buku II Volume I KLHK 2012.
        </div>`;

    // Animate bars
    requestAnimationFrame(() => {
        setTimeout(() => {
            const bar = document.getElementById('cbar_s1');
            if (bar) bar.style.width = s1pct + '%';
        }, 80);
        setTimeout(() => {
            const bar = document.getElementById('cbar_s2');
            if (bar) bar.style.width = s2pct + '%';
        }, 160);
        setTimeout(() => {
            const bar = document.getElementById('cbar_s3');
            if (bar) bar.style.width = s3pct + '%';
        }, 240);
    });
}

// ═══════════════════════════════════════════════════════════════════════════════
// RESET
// ═══════════════════════════════════════════════════════════════════════════════

function resetCalculator() {
    document.querySelectorAll('.input-field').forEach(el => {
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else el.value = '';
    });
    document.querySelectorAll('.flight-distance-hint').forEach(h => h.textContent = 'Pilih asal dan tujuan bandara');
    document.querySelectorAll('.ef-chip[data-ef-group]').forEach(c => c.textContent = '—');
    document.querySelectorAll('.preview-value').forEach(el => el.textContent = '0');

    const rBox = document.getElementById('result-box');
    if (rBox) rBox.style.display = 'none';

    const rComp = document.getElementById('result-box-company');
    if (rComp) rComp.innerHTML = '';

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── INIT ───────────────────────────────────────────────────────────────────────
function initAll() {
    enhanceCalculatorAccessibility();

    document.querySelectorAll('select[name$="_fuel[]"], select[name$="_src[]"], select[name$="_mode[]"], select[name$="_type[]"], select[name$="_class[]"], select[name$="_fuel_type[]"], select[name$="_dist_fuel[]"]').forEach(sel => {
        sel.addEventListener('change', () => {
            const grpAttr = sel.closest('.entry-row')?.querySelector('.ef-chip[data-ef-group]');
            if (grpAttr) {
                const g = grpAttr.dataset.efGroup;
                updateEfChip(sel, g);
            }
            calcLive();
        });
    });
    document.querySelectorAll('input[type=number]').forEach(inp => {
        inp.addEventListener('input', calcLive);
    });
}

// ====================================
// 6. AJAX STORAGE & MULTI-LOCALSTORAGE ENGINE (Anti-Replace)
// ====================================
window.saveCalculationToDatabase = function(e) {
    
    // PERBAIKAN 1: Ambil data dinamis dari DOM jika window.currentCalculationData belum di-set oleh fungsi hitung
    if (!window.currentCalculationData) {
        let rawEmisiText = document.getElementById('hasil-emisi')?.textContent?.trim() || '';
        
        if (!rawEmisiText || rawEmisiText.includes('0 ton CO₂e') || rawEmisiText === '0') {
            alert('Tidak ada data hasil perhitungan yang bisa disimpan. Mohon lakukan kalkulasi terlebih dahulu.');
            return;
        }

        // Konversi text "X.XX ton CO₂e" dari layar menjadi angka murni Kg untuk backend
        let cleanValue = rawEmisiText.replace(/ton\sCO₂e/gi, '').replace(/kg\sCO₂e/gi, '').trim();
        let totalTon = parseFloat(cleanValue);
        let totalKg = totalTon * 1000;

        window.currentCalculationData = {
            mode: 'personal',
            total_kg: totalKg,
            details: {
                electricity: v('p_elec_kwh') * 12 * EF_DB.grid.household.ef,
                water: v('p_water_m3') * 12 * EF_DB.water.ef,
                waste: v('p_waste_kg') * 12 * EF_DB.waste.ef
            }
        };
    }

    // PERBAIKAN 2: Menggunakan penanganan event target yang aman dan kompatibel di semua browser
    const currentEvent = e || window.event;
    const btn = currentEvent ? currentEvent.currentTarget || currentEvent.target : null;
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'Menyimpan ke Dashboard...';
    }

    // 1. Kirim data ke database Laravel via AJAX POST
    fetch('/calculator/store', { 
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(window.currentCalculationData)
    })
    .then(res => {
        if (!res.ok) throw new Error('Server bermasalah');
        return res.json();
    })
    .then(res => {
        if (res.success) {
            alert('Selamat! Hasil kalkulasi karbon Anda berhasil disimpan ke database dashboard.');

            const recommendationEl = document.getElementById('rekomendasi-proyek');
            if (recommendationEl && res.data.proyek) {
                const scoreLabel = res.data.skor_rekomendasi !== null
                    ? ` (${Number(res.data.skor_rekomendasi).toFixed(0)}% cocok)`
                    : '';
                recommendationEl.textContent = res.data.proyek + scoreLabel;
                recommendationEl.title = (res.data.alasan_rekomendasi || []).join(' ');
            }
            
            if (btn) {
                btn.innerHTML = 'Berhasil Disimpan';
                btn.style.background = '#67C090';
                btn.style.borderColor = '#67C090';
            }

            // 2. Ambil daftar histori lama, jika belum ada buat array kosong []
            const storageKey = window.CARBON_STORAGE_KEY || 'carbon_history_guest';
            let historyList = JSON.parse(localStorage.getItem(storageKey)) || [];

            // Susun data objek baru lengkap dengan waktu simpan (timestamp)
            const newHistoryItem = {
                id: Date.now(),
                date: new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit' }),
                total_kg: window.currentCalculationData.total_kg,
                biaya: res.data.biaya,
                proyek: res.data.proyek
            };

            // Masukkan data baru ke dalam daftar teratas (unshift)
            historyList.unshift(newHistoryItem);

            // Simpan kembali daftar array utuh ke localStorage browser
            localStorage.setItem(storageKey, JSON.stringify(historyList));
            
            // Muat ulang daftar tampilan riwayat di bawah secara realtime (pastikan fungsi ini ada)
            if (typeof renderHistoryContainer === 'function') {
                renderHistoryContainer();
            }
        } else {
            alert('Gagal menyimpan ke database: ' + res.message);
            resetButton(btn);
        }
    })
    .catch((err) => {
        console.error(err);
        alert('Terjadi kendala jaringan saat mencoba menghubungi server.');
        resetButton(btn);
    });
};

// Fungsi pembantu untuk mengembalikan state tombol jika gagal
function resetButton(btn) {
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Simpan Hasil ke Dashboard';
    }
}

// ====================================
// 7. RENDER DAFTAR SEJARAH RIWAYAT PERHITUNGAN (Multi-List)
// ====================================
function renderHistoryContainer() {
    // Cari apakah ada container khusus riwayat, jika tidak ada kita buat dinamis di bawah result-box
    let historyWrapper = document.getElementById('history-log-wrapper');
    const resultBox = document.getElementById('result-box');
    
    if (!resultBox) return;

    if (!historyWrapper) {
        historyWrapper = document.createElement('div');
        historyWrapper.id = 'history-log-wrapper';
        historyWrapper.style.cssText = 'margin-top: 25px; padding: 20px; background: #fff; border-radius: 16px; border: 2px solid #e9ecef; box-shadow: 0 4px 20px rgba(0,0,0,.08);';
        // Sisipkan container riwayat tepat di bawah kotak result box utama
        resultBox.parentNode.insertBefore(historyWrapper, resultBox.nextSibling);
    }

    const historyList = JSON.parse(localStorage.getItem('carbon_history_list')) || [];
    if (historyList.length > 0) {
        historyWrapper.style.display = 'none';
        return;
    }

    historyWrapper.style.display = 'block';
    
    // Set judul header daftar riwayat kamu
    let htmlContent = `
        <h4 style="font-family: 'Manrope', sans-serif; font-weight: 800; color: #124170; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-history" style="color: #67C090;"></i> Riwayat Perhitungan Kalkulator Anda
        </h4>
        <div style="display: flex; flex-direction: column; gap: 10px; max-height: 300px; overflow-y: auto; padding-right: 5px;">
    `;

    // Looping cetak baris demi baris riwayat kalkulasi yang pernah disimpan
    historyList.forEach((item, index) => {
        const ton = (item.total_kg / 1000).toFixed(3);
        htmlContent += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #f8f9fa; border-radius: 10px; border: 1px solid #dee2e6; font-size: .85rem;">
                <div>
                    <span style="color: #6c757d; font-size: .72rem; display: block; font-weight: 600;">📋 Perhitungan #${historyList.length - index} — ${item.date}</span>
                    <strong style="color: #124170; font-size: .95rem;">${ton} ton CO₂e</strong> 
                    <span style="color: #495057; font-size: .8rem; margin-left: 10px;">(Estimasi: ${item.biaya})</span>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: .75rem; background: rgba(38,102,127,.1); color: #26667F; padding: 3px 8px; border-radius: 20px; font-weight: 700;">
                        📍 ${item.proyek}
                    </span>
                </div>
            </div>
        `;
    });

    htmlContent += `</div>`;
    
    // Tambahkan tombol opsional untuk menghapus semua log jejak memori lokal
    htmlContent += `
        <button type="button" data-clear-carbon-history style="background: none; border: none; color: #e74c3c; font-size: .75rem; font-weight: 700; cursor: pointer; margin-top: 12px; padding: 0; display: flex; align-items: center; gap: 4px;">
            Hapus Semua Riwayat Lokal
        </button>
    `;

    historyWrapper.innerHTML = htmlContent;
}

// Fungsi pembantu untuk clear data list jika user mau membersihkan browsernya
window.clearCarbonHistory = function() {
    if (confirm('Apakah Anda yakin ingin menghapus seluruh daftar riwayat kalkulasi, baik di browser ini maupun di database dashboard?')) {
        
        fetch('/calculator/clear', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                // 2. Jika di database sukses terhapus, bersihkan juga yang di localStorage browser
                localStorage.removeItem('carbon_history_list');
                
                // Sembunyikan kotak log riwayat di layar
                const wrapper = document.getElementById('history-log-wrapper');
                if (wrapper) wrapper.style.display = 'none';

                // Kosongkan atau sembunyikan juga result-box utama agar bersih kembali
                const box = document.getElementById('result-box');
                if (box) box.style.display = 'none';

                alert('Sukses! Semua riwayat kalkulasi Anda telah bersih terhapus dari server dan browser.');
            } else {
                alert('Gagal menghapus riwayat di server: ' + res.message);
            }
        })
        .catch(() => {
            alert('Terjadi kendala jaringan saat mencoba menghapus riwayat di server.');
        });
    }
};

// Pastikan saat halaman dimuat ulang pertama kali, seluruh daftar list langsung digambar otomatis
document.addEventListener('DOMContentLoaded', function() {
    
    // PERIKSA STATUS: Jika window.CARBON_STORAGE_KEY bernilai null (User Belum Login)
    if (!window.CARBON_STORAGE_KEY) {
        console.log("Status: Guest. Mengosongkan riwayat kalkulator.");
        
        const historyContainer = document.getElementById('history-container');
        if (historyContainer) historyContainer.innerHTML = '';
        
        const box = document.getElementById('result-box');
        if (box) box.style.display = 'none';
        
        return; // Hentikan eksekusi di sini untuk Guest
    }

    // JALUR USER LOGIN: Gambar ulang daftar riwayat dari localStorage miliknya
    function renderHistoryContainer() {
        let historyWrapper = document.getElementById('history-log-wrapper');
        const resultBox = document.getElementById('result-box');
        
        if (!resultBox) return;

        if (!historyWrapper) {
            historyWrapper = document.createElement('div');
            historyWrapper.id = 'history-log-wrapper';
            historyWrapper.style.cssText = 'margin-top: 25px; padding: 20px; background: #fff; border-radius: 16px; border: 2px solid #e9ecef; box-shadow: 0 4px 20px rgba(0,0,0,.08);';
            resultBox.parentNode.insertBefore(historyWrapper, resultBox.nextSibling);
        }

        // ── PERBAIKAN DI SINI: Gunakan window.CARBON_STORAGE_KEY, jangan string statis 'carbon_history_list' ──
        const currentKey = window.CARBON_STORAGE_KEY || 'carbon_history_guest';
        const historyList = JSON.parse(localStorage.getItem(currentKey)) || [];

        if (historyList.length === 0) {
            historyWrapper.style.display = 'none';
            return;
        }

        historyWrapper.style.display = 'block';
    
        let htmlContent = `
            <h4 style="font-family: 'Manrope', sans-serif; font-weight: 800; color: #124170; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-history" style="color: #67C090;"></i> Riwayat Perhitungan Kalkulator Anda
            </h4>
            <div style="display: flex; flex-direction: column; gap: 10px; max-height: 300px; overflow-y: auto; padding-right: 5px;">
        `;

        historyList.forEach((item, index) => {
            const ton = (item.total_kg / 1000).toFixed(3);
            htmlContent += `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #f8f9fa; border-radius: 10px; border: 1px solid #dee2e6; font-size: .85rem;">
                    <div>
                        <span style="color: #6c757d; font-size: .72rem; display: block; font-weight: 600;">📋 Perhitungan #${historyList.length - index} — ${item.date}</span>
                        <strong style="color: #124170; font-size: .95rem;">${ton} ton CO₂e</strong> 
                        <span style="color: #495057; font-size: .8rem; margin-left: 10px;">(Estimasi: ${item.biaya})</span>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: .75rem; background: rgba(38,102,127,.1); color: #26667F; padding: 3px 8px; border-radius: 20px; font-weight: 700;">
                            📍 ${item.proyek}
                        </span>
                    </div>
                </div>
            `;
        });

        htmlContent += `</div>`;
        
        htmlContent += `
            <button type="button" data-clear-carbon-history style="background: none; border: none; color: #e74c3c; font-size: .75rem; font-weight: 700; cursor: pointer; margin-top: 12px; padding: 0; display: flex; align-items: center; gap: 4px;">
                Hapus Semua Riwayat Lokal
            </button>
        `;

        historyWrapper.innerHTML = htmlContent;
    }
});

document.addEventListener('click', function(event) {
    const removeButton = event.target.closest('[data-remove-row]');
    if (removeButton) {
        window.removeRow(removeButton);
        return;
    }

    const addButton = event.target.closest('[data-add-row]');
    if (addButton) {
        window.addRow(addButton.dataset.addRow);
        return;
    }

    const switchButton = event.target.closest('[data-switch-tab-prefix]');
    if (switchButton) {
        window.switchTab(
            switchButton.dataset.switchTabPrefix,
            Number(switchButton.dataset.switchTabStep),
            Number(switchButton.dataset.switchTabTotal)
        );
        return;
    }

    const calculateButton = event.target.closest('[data-calculate-mode]');
    if (calculateButton) {
        window.calculateAll(calculateButton.dataset.calculateMode);
        return;
    }

    const saveButton = event.target.closest('[data-save-calculation]');
    if (saveButton) {
        window.saveCalculationToDatabase(event);
        return;
    }

    if (event.target.closest('[data-reset-calculator]')) {
        window.resetCalculator();
        return;
    }

    if (event.target.closest('[data-clear-carbon-history]')) {
        window.clearCarbonHistory();
    }
});

// Expose globals
window.switchTab      = switchTab;
window.switchSubTab   = switchSubTab;
window.switchMethod   = switchMethod;
window.toggleModule   = toggleModule;
window.addRow         = addRow;
window.removeRow      = removeRow;
window.updateEfChip   = updateEfChip;
window.updateVehicleFuel = updateVehicleFuel;
window.normaliseAirportField = normaliseAirportField;
window.updateFlightRoute = updateFlightRoute;
window.calcLive       = calcLive;
window.calculateAll   = calculateAll;
window.resetCalculator = resetCalculator;
