document.addEventListener('DOMContentLoaded', function () {
    const offsetCtx = document.getElementById('offsetChart');
    if (offsetCtx && typeof DASHBOARD_DATA !== 'undefined') {
        new Chart(offsetCtx, {
            type: 'doughnut',
            data: {
                labels: ['Ter-offset', 'Sisa Emisi'],
                datasets: [{
                    // Mengambil data real dari objek DASHBOARD_DATA di Blade
                    data: [DASHBOARD_DATA.totalOffsetKg, DASHBOARD_DATA.remainingKg], 
                    backgroundColor: ['#67C090', '#e2e8f0'],
                    hoverOffset: 4,
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '80%',
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    const scopeModal = document.getElementById('scope-detail-modal');
    const openScopeDetail = document.querySelector('[data-open-scope-detail]');

    if (scopeModal && openScopeDetail) {
        openScopeDetail.addEventListener('click', function () {
            scopeModal.showModal();
        });

        scopeModal.querySelectorAll('[data-close-scope-detail]').forEach(function (button) {
            button.addEventListener('click', function () {
                scopeModal.close();
            });
        });

        scopeModal.addEventListener('click', function (event) {
            if (event.target === scopeModal) {
                scopeModal.close();
            }
        });
    }
});
