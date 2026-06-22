/**
 * Buyer Transactions & Project Status Interactions — CAMAR
 */

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('buyerModal');
    const closeModal = document.querySelector('.close-buyer-modal');
    const modalDetails = document.getElementById('buyerModalDetails');

    // Menangani aksi klik tombol detail
    document.querySelectorAll('.btn-open-detail').forEach(button => {
        button.addEventListener('click', function () {
            const orderData = JSON.parse(this.getAttribute('data-order'));
            
            const projectName = orderData.project ? orderData.project.name : 'N/A';
            const projectLoc = orderData.project ? orderData.project.location : '-';
            const projectMethod = orderData.project ? (orderData.project.methodology || 'Standard Carbon Offset') : '-';
            const endDateStr = orderData.project_end_date ? orderData.project_end_date : '-';

            // Kerangka template untuk detail modal popup
            modalDetails.innerHTML = `
                <div class="row-modal-item"><span>ID Transaksi</span><span>#${orderData.id}</span></div>
                <div class="row-modal-item"><span>Nama Proyek Karbon</span><span>${projectName}</span></div>
                <div class="row-modal-item"><span>Lokasi Proyek</span><span>${projectLoc}</span></div>
                <div class="row-modal-item"><span>Metodologi</span><span>${projectMethod}</span></div>
                <div class="row-modal-item"><span>Kuantitas yang Anda Klaim</span><span>${Number(orderData.quantity).toLocaleString('id-ID')} ton CO₂</span></div>
                <div class="row-modal-item"><span>Total Pembayaran</span><span>Rp ${Number(orderData.total_price).toLocaleString('id-ID')}</span></div>
                <div class="row-modal-item" style="background: #f8fafc; padding: 0.75rem; border-radius: 6px; margin-top: 0.5rem;">
                    <span style="font-weight: 600; color: var(--color-navy)">Estimasi Kontrak Berakhir</span>
                    <span style="color: #ef4444; font-weight: bold;">${endDateStr}</span>
                </div>
                <div class="row-modal-item" style="border-bottom: none; margin-top: 0.75rem;">
                    <span>Status Keberjalanan Proyek</span>
                    <span class="project-badge-status ${orderData.project_status_class}">${orderData.project_status_label.toUpperCase()}</span>
                </div>
            `;

            modal.style.display = 'block';
        });
    });

    if (closeModal) {
        closeModal.addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});