/**
 * Dashboard Seller — CAMAR
 * Chart.js + UI interactions
 */

document.addEventListener('DOMContentLoaded', function () {

    // =========================================================
    // 1. SALES CHART — Bar (Revenue) + Line (Carbon Sold)
    // =========================================================
    const salesCtx = document.getElementById('salesChart');

    if (salesCtx && typeof SELLER_DATA !== 'undefined') {
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: SELLER_DATA.chartLabels,
                datasets: [
                    {
                        label: 'Pendapatan (Rp)',
                        data: SELLER_DATA.chartRevenue,
                        backgroundColor: 'rgba(103, 192, 144, 0.25)',
                        borderColor: '#67C090',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                        yAxisID: 'yRevenue',
                    },
                    {
                        label: 'Carbon Sold (ton)',
                        data: SELLER_DATA.chartCarbon,
                        type: 'line',
                        borderColor: '#124170',
                        backgroundColor: 'rgba(18, 65, 112, 0.08)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#124170',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'yCarbon',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart',
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#124170',
                        titleColor: 'rgba(255,255,255,0.7)',
                        bodyColor: 'white',
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            label: function (ctx) {
                                if (ctx.dataset.yAxisID === 'yRevenue') {
                                    return ` Rp ${ctx.raw.toLocaleString('id-ID')}`;
                                }
                                return ` ${ctx.raw.toFixed(2)} ton CO₂`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Ubuntu', size: 11 },
                            color: 'rgba(18,65,112,0.55)',
                        }
                    },
                    yRevenue: {
                        position: 'left',
                        grid: { color: 'rgba(103,192,144,0.1)' },
                        ticks: {
                            font: { family: 'Ubuntu', size: 10 },
                            color: '#67C090',
                            callback: val => 'Rp ' + (val >= 1e6
                                ? (val / 1e6).toFixed(1) + 'jt'
                                : val.toLocaleString('id-ID')),
                        }
                    },
                    yCarbon: {
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: {
                            font: { family: 'Ubuntu', size: 10 },
                            color: '#124170',
                            callback: val => val + ' ton',
                        }
                    }
                }
            }
        });
    }

    // =========================================================
    // 2. ANIMATED PROGRESS BARS
    // =========================================================
    document.querySelectorAll('.scope-bar-fill').forEach(bar => {
        const w = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => { bar.style.width = w; }, 200);
    });

    // =========================================================
    // 3. FILE DROP ZONE — drag & drop visual feedback
    // =========================================================
    const fileDrop = document.getElementById('fileDrop');
    const projectImage = document.getElementById('projectImage');

    if (fileDrop && projectImage) {
        ['dragover', 'dragenter'].forEach(evt => {
            fileDrop.addEventListener(evt, e => {
                e.preventDefault();
                fileDrop.style.borderColor = '#67C090';
                fileDrop.style.background  = 'rgba(103,192,144,0.15)';
            });
        });

        ['dragleave', 'drop'].forEach(evt => {
            fileDrop.addEventListener(evt, e => {
                e.preventDefault();
                fileDrop.style.borderColor = '';
                fileDrop.style.background  = '';
            });
        });

        fileDrop.addEventListener('drop', e => {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                // Transfer ke input file
                const dt = new DataTransfer();
                dt.items.add(files[0]);
                projectImage.files = dt.files;
                updateDropLabel(files[0].name);
            }
        });

        projectImage.addEventListener('change', function () {
            if (this.files.length > 0) {
                updateDropLabel(this.files[0].name);
            }
        });

        function updateDropLabel(name) {
            fileDrop.innerHTML = `
                <i class="fas fa-check-circle" style="color:#67C090;"></i>
                <span style="color:#124170;font-weight:700;">${name}</span>
                <small>Klik untuk ganti foto</small>
            `;
        }
    }

    // =========================================================
    // 4. DOCUMENT UPLOAD — feedback per item
    // =========================================================
    document.querySelectorAll('.doc-upload-btn input[type="file"]').forEach(input => {
        input.addEventListener('change', function () {
            if (this.files.length > 0) {
                const label = this.closest('.doc-upload-btn');
                const name  = this.files[0].name;
                const short = name.length > 18 ? name.substring(0, 15) + '...' : name;

                label.innerHTML = `<i class="fas fa-check"></i> ${short}`;
                label.style.background   = '#67C090';
                label.style.color        = 'white';
                label.style.borderColor  = '#67C090';
            }
        });
    });

    // =========================================================
    // 5. PROJECT ACTION HANDLERS (placeholder → connect to routes)
    // =========================================================
    window.editProject = function (id) {
        window.location.href = '/seller/projects/' + id + '/edit';
    }
    window.editProject = editProject;;

    window.updateStock = function (id) {
        // TODO: buka modal update stok
        const newStock = prompt('Masukkan jumlah stok baru (ton):');
        if (newStock !== null && !isNaN(parseFloat(newStock))) {
            // fetch(`/seller/projects/${id}/stock`, { method: 'PATCH', ... })
            alert(`Stok proyek ID ${id} akan diperbarui ke ${newStock} ton.\n(Hubungkan ke endpoint Laravel)`);
        }
    };

    window.updateImage = function (id) {
        // TODO: buka modal upload gambar baru
        alert(`Update gambar proyek ID: ${id}\n(Fitur segera tersedia)`);
    };

    // =========================================================
    // 6. FORM SUBMIT — New Project
    // =========================================================
    const newProjectForm = document.getElementById('newProjectForm');
    if (newProjectForm) {
        newProjectForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const btn = this.querySelector('.btn-submit-project');
            btn.disabled     = true;
            btn.innerHTML    = '<i class="fas fa-spinner fa-spin"></i> Mendaftarkan...';

            // TODO: ubah action form ke route yang benar
            // this.action = '{{ route("seller.projects.store") }}';
            // this.submit();

            // Sementara simulasi
            setTimeout(() => {
                alert('Proyek berhasil didaftarkan! (Hubungkan ke controller Laravel)');
                btn.disabled  = false;
                btn.innerHTML = '<i class="fas fa-leaf"></i> Daftarkan Proyek';
            }, 1500);
        });
    }

    // =========================================================
    // 7. STAT CARD HOVER TILT
    // =========================================================
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('mousemove', function (e) {
            const rect  = card.getBoundingClientRect();
            const x     = e.clientX - rect.left - rect.width  / 2;
            const y     = e.clientY - rect.top  - rect.height / 2;
            const tiltX = (y / rect.height *  2).toFixed(2);
            const tiltY = (x / rect.width  * -2).toFixed(2);
            card.style.transform = `translateY(-5px) rotateX(${tiltX}deg) rotateY(${tiltY}deg)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });

});