/**
 * Register Page - Fixed & Revised
 * Fixes:
 * 1. Step navigation conflict between inline style & CSS class
 * 2. Ambiguous data-step="3" dual elements (companyData / personalData)
 * 3. Back button: step 3 re-renders correct form
 * 4. Step indicator: unmark 'completed' when going back
 * 5. Document validation: company-only docs skipped for personal users
 * 6. Form submits natively to Laravel route
 * 7. Progress bar updates correctly on back
 */

let currentStep = 2;
let cropper = null;
let selectedCategoryType = null;
let lastCropTrigger = null;

// ====================================
// STEP 1 — Account Category Selection
// ====================================
function selectCategory(category, element) {
    selectedCategoryType = category;
    document.getElementById('categoryInput').value = category;

    document.querySelectorAll('[data-step="2"] .account-card').forEach(card => {
        card.classList.remove('selected');
        card.setAttribute('aria-pressed', 'false');
    });
    element.classList.add('selected');
    element.setAttribute('aria-pressed', 'true');
}

// ====================================
// CORE: Show a specific step
// ====================================
function showStep(stepNumber) {
    // 1. Hide ALL .form-step elements
    document.querySelectorAll('.form-step').forEach(el => {
        el.classList.remove('active');
        el.style.display = 'none';
    });

    // 2. For step 3, show only the relevant sub-form
    if (stepNumber === 3) {
        const targetId = (selectedCategoryType === 'company') ? 'companyData' : 'personalData';
        const target = document.getElementById(targetId);
        target.style.display = 'block';
        // Force reflow before adding active for CSS animation
        void target.offsetWidth;
        target.classList.add('active');
    } else {
        // For all other steps, use data-step selector (these are unique)
        const target = document.querySelector(`.form-step[data-step="${stepNumber}"]:not(#companyData):not(#personalData)`);
        if (target) {
            target.style.display = 'block';
            void target.offsetWidth;
            target.classList.add('active');
        }
    }

    // 3. Handle step 5: show/hide company-only docs and toggle their required attr
    if (stepNumber === 5) {
        applyDocumentVisibility();
    }

    // 4. Update step indicators
    updateStepIndicators(stepNumber);

    // 5. Update progress bar
    updateProgressBar(stepNumber);

    // 6. Update nav buttons
    updateButtons(stepNumber);
}

// ====================================
// STEP INDICATORS
// ====================================
function updateStepIndicators(stepNumber) {
    const visibleStep = stepNumber - 1;
    document.querySelectorAll('.step').forEach(indicator => {
        const s = parseInt(indicator.dataset.step);
        indicator.classList.remove('active', 'completed');
        if (s < visibleStep) {
            indicator.classList.add('completed');
        } else if (s === visibleStep) {
            indicator.classList.add('active');
        }
    });
}

// ====================================
// PROGRESS BAR
// ====================================
function updateProgressBar(stepNumber) {
    const progressFill = document.getElementById('progressFill');
    const progress = ((stepNumber - 2) / 4) * 100;
    progressFill.style.width = progress + '%';
}

// ====================================
// NAV BUTTONS
// ====================================
function updateButtons(stepNumber) {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    prevBtn.style.setProperty('display', stepNumber === 2 ? 'none' : 'inline-flex', 'important');

    if (stepNumber === 6) {
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'inline-flex';
    } else {
        nextBtn.style.display = 'inline-flex';
        submitBtn.style.display = 'none';
    }
}

// ====================================
// CHANGE STEP (called by buttons)
// ====================================
function changeStep(direction) {
    if (direction === 1 && !validateStep(currentStep)) {
        return;
    }

    currentStep += direction;

    // Clamp
    if (currentStep < 2) currentStep = 2;
    if (currentStep > 6) currentStep = 6;

    showStep(currentStep);
}

// ====================================
// DOCUMENT VISIBILITY (Step 5)
// ====================================
function applyDocumentVisibility() {
    const companyDocs = document.querySelectorAll('.document-item.company-only');

    if (selectedCategoryType === 'personal') {
        // Hide company-only document items and remove their required attr
        companyDocs.forEach(el => {
            el.style.display = 'none';
            el.querySelectorAll('input[type="file"]').forEach(input => {
                input.removeAttribute('required');
            });
        });
    } else {
        // Show company-only document items and restore required attr
        companyDocs.forEach(el => {
            el.style.display = 'flex';
            // Only re-add required if the doc is actually required (has .required badge)
            el.querySelectorAll('input[type="file"]').forEach(input => {
                const badge = el.querySelector('.doc-badge.required');
                if (badge) input.setAttribute('required', 'required');
            });
        });
    }

}

// ====================================
// VALIDATION
// ====================================
function validateStep(step) {
    switch (step) {
        case 2:
            if (!selectedCategoryType) {
                showAlert('Pilih jenis pengguna terlebih dahulu');
                return false;
            }
            return true;

        case 3: {
            const targetId = (selectedCategoryType === 'company') ? 'companyData' : 'personalData';
            const activeForm = document.getElementById(targetId);
            const requiredInputs = activeForm.querySelectorAll('[required]');

            for (let input of requiredInputs) {
                if (!input.value.trim()) {
                    showAlert('Mohon lengkapi semua field yang wajib diisi!');
                    input.focus();
                    return false;
                }
            }

            const postalInput = activeForm.querySelector('input[name="postal_code"]');
            if (postalInput && !/^\d{5}$/.test(postalInput.value.trim())) {
                showAlert('Kode pos harus terdiri dari 5 digit angka!');
                postalInput.focus();
                return false;
            }

            const prefix = (selectedCategoryType === 'company') ? 'Company' : 'Personal';
            const passwordField = document.getElementById('password' + prefix);
            const confirmField = document.getElementById('confirmPassword' + prefix);

            if (passwordField && confirmField) {
                if (passwordField.value.length < 8) {
                    showAlert('Password minimal 8 karakter');
                    passwordField.focus();
                    return false;
                }
                if (passwordField.value !== confirmField.value) {
                    showAlert('Konfirmasi password tidak cocok');
                    confirmField.focus();
                    return false;
                }
            }
            return true;
        }

        case 4:
            // Photo is optional
            return true;

        case 5: {
            // Only validate visible required docs
            const allDocInputs = document.querySelectorAll('.form-step[data-step="5"] input[type="file"][required]');

            for (let input of allDocInputs) {
                // Check if the parent document-item is visible
                const docItem = input.closest('.document-item');
                if (docItem && docItem.offsetParent !== null) {
                    if (!input.files || input.files.length === 0) {
                        const docName = docItem.querySelector('h4')?.innerText || 'dokumen';
                        showAlert(`Mohon upload dokumen: ${docName}`);
                        return false;
                    }
                }
            }

            return true;
        }

        case 6: {
            const termsCheck = document.getElementById('termsCheck');
            if (!termsCheck.checked) {
                showAlert('Anda harus menyetujui Syarat & Ketentuan');
                return false;
            }
            return true;
        }

        default:
            return true;
    }
}

// ====================================
// ALERT HELPER (replaces raw alert())
// ====================================
function showAlert(message) {
    // Use a styled toast/modal if available, else fallback
    const existing = document.getElementById('customAlert');
    if (existing) existing.remove();

    const alert = document.createElement('div');
    alert.id = 'customAlert';
    alert.style.cssText = `
        position: fixed;
        top: 2rem;
        left: 50%;
        transform: translateX(-50%);
        background: #dc3545;
        color: white;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        z-index: 99999;
        box-shadow: 0 8px 24px rgba(220, 53, 69, 0.4);
        animation: slideDown 0.3s ease;
        max-width: 90vw;
        text-align: center;
    `;
    alert.textContent = message;
    document.body.appendChild(alert);

    setTimeout(() => {
        alert.style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

// ====================================
// PASSWORD TOGGLE
// ====================================
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// ====================================
// PHOTO CROP FUNCTIONALITY
// ====================================
document.getElementById('profilePhotoInput').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        showAlert('Ukuran file maksimal 5MB');
        return;
    }
    if (!file.type.match('image.*')) {
        showAlert('File harus berupa gambar');
        return;
    }

    const reader = new FileReader();
    reader.onload = function (event) {
        document.getElementById('cropImage').src = event.target.result;
        openCropModal();
        initCropper();
    };
    reader.readAsDataURL(file);
});

function openCropModal() {
    const modal = document.getElementById('cropModal');
    lastCropTrigger = document.activeElement;
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    modal.querySelector('.crop-content')?.focus();
}

function initCropper() {
    const image = document.getElementById('cropImage');
    if (cropper) cropper.destroy();

    cropper = new Cropper(image, {
        aspectRatio: 1,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 1,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
    });
}

function closeCropModal() {
    const modal = document.getElementById('cropModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    document.getElementById('profilePhotoInput').value = '';
    if (lastCropTrigger instanceof HTMLElement) {
        lastCropTrigger.focus();
    }
}

function saveCroppedImage() {
    if (!cropper) return;

    const canvas = cropper.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    const croppedImageData = canvas.toDataURL('image/jpeg', 0.9);
    document.getElementById('croppedImage').value = croppedImageData;

    const photoPreview = document.getElementById('photoPreview');
    photoPreview.innerHTML = `<img src="${croppedImageData}" alt="Profile Photo" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;

    closeCropModal();
}

// ====================================
// FORM SUBMIT — Native Laravel POST
// ====================================
document.getElementById('registerForm').addEventListener('submit', function (e) {
    // Final validation on step 6
    if (!validateStep(6)) {
        e.preventDefault();
        return;
    }

    const inactiveFormId = (selectedCategoryType === 'company') ? 'personalData' : 'companyData';
    const inactiveForm = document.getElementById(inactiveFormId);
    if (inactiveForm) {
        inactiveForm.querySelectorAll('input, select, textarea').forEach(el => {
            el.disabled = true;
        });
    }

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendaftar...';

    this.submit();
});

// ====================================
// FILE UPLOAD LABEL UPDATE
// ====================================
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function () {
        const label = document.querySelector(`label[for="${this.id}"]`);
        if (label && this.files.length > 0) {
            const fileName = this.files[0].name;
            const shortName = fileName.length > 20 ? fileName.substring(0, 17) + '...' : fileName;
            label.innerHTML = `<i class="fas fa-check"></i> ${shortName}`;
            label.style.background = '#67C090';
            label.style.color = 'white';
            label.style.borderColor = '#67C090';
        }
    });
});

// ====================================
// WILAYAH INDONESIA CASCADING DROPDOWNS
// ====================================
const WILAYAH_API_BASE = 'https://www.emsifa.com/api-wilayah-indonesia/api';

const FALLBACK_PROVINCES = [
    { id: '11', name: 'ACEH' },
    { id: '12', name: 'SUMATERA UTARA' },
    { id: '13', name: 'SUMATERA BARAT' },
    { id: '14', name: 'RIAU' },
    { id: '15', name: 'JAMBI' },
    { id: '16', name: 'SUMATERA SELATAN' },
    { id: '17', name: 'BENGKULU' },
    { id: '18', name: 'LAMPUNG' },
    { id: '19', name: 'KEPULAUAN BANGKA BELITUNG' },
    { id: '21', name: 'KEPULAUAN RIAU' },
    { id: '31', name: 'DKI JAKARTA' },
    { id: '32', name: 'JAWA BARAT' },
    { id: '33', name: 'JAWA TENGAH' },
    { id: '34', name: 'DI YOGYAKARTA' },
    { id: '35', name: 'JAWA TIMUR' },
    { id: '36', name: 'BANTEN' },
    { id: '51', name: 'BALI' },
    { id: '52', name: 'NUSA TENGGARA BARAT' },
    { id: '53', name: 'NUSA TENGGARA TIMUR' },
    { id: '61', name: 'KALIMANTAN BARAT' },
    { id: '62', name: 'KALIMANTAN TENGAH' },
    { id: '63', name: 'KALIMANTAN SELATAN' },
    { id: '64', name: 'KALIMANTAN TIMUR' },
    { id: '65', name: 'KALIMANTAN UTARA' },
    { id: '71', name: 'SULAWESI UTARA' },
    { id: '72', name: 'SULAWESI TENGAH' },
    { id: '73', name: 'SULAWESI SELATAN' },
    { id: '74', name: 'SULAWESI TENGGARA' },
    { id: '75', name: 'GORONTALO' },
    { id: '76', name: 'SULAWESI BARAT' },
    { id: '81', name: 'MALUKU' },
    { id: '82', name: 'MALUKU UTARA' },
    { id: '91', name: 'PAPUA BARAT' },
    { id: '92', name: 'PAPUA BARAT DAYA' },
    { id: '94', name: 'PAPUA' },
    { id: '95', name: 'PAPUA SELATAN' },
    { id: '96', name: 'PAPUA TENGAH' },
    { id: '97', name: 'PAPUA PEGUNUNGAN' }
];

function formatWilayahName(name) {
    const specialUpper = new Set(['DKI', 'DI', 'NAD', 'NTB', 'NTT']);
    return String(name || '')
        .trim()
        .split(/\s+/)
        .map(word => {
            const upper = word.toUpperCase();
            if (specialUpper.has(upper)) return upper;
            return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
        })
        .join(' ');
}

async function fetchWilayahJson(url, fallback = []) {
    try {
        const res = await fetch(url);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        return Array.isArray(data) && data.length > 0 ? data : fallback;
    } catch {
        return fallback;
    }
}

async function initWilayahDropdowns() {
    const provinces = await fetchWilayahJson(`${WILAYAH_API_BASE}/provinces.json`, FALLBACK_PROVINCES);

    document.querySelectorAll('.form-step').forEach(container => {
        const provSelect = container.querySelector('.select-province');
        const regSelect  = container.querySelector('.select-regency');
        const distSelect = container.querySelector('.select-district');
        if (!provSelect || !regSelect || !distSelect) return;

        provSelect.innerHTML = '<option value="">Pilih Provinsi</option>' +
            provinces.map(p => {
                const label = formatWilayahName(p.name);
                return `<option value="${label}" data-id="${p.id}">${label}</option>`;
            }).join('');

        provSelect.addEventListener('change', async function () {
            const selectedOption = this.options[this.selectedIndex];
            const provId = selectedOption?.dataset?.id;

            regSelect.innerHTML = '<option value="">Memuat Kabupaten / Kota...</option>';
            regSelect.disabled = true;
            distSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            distSelect.disabled = true;

            if (!provId) {
                regSelect.innerHTML = '<option value="">Pilih Kabupaten / Kota</option>';
                return;
            }

            const regencies = await fetchWilayahJson(
                `${WILAYAH_API_BASE}/regencies/${provId}.json`,
                [{ id: `${provId}01`, name: `KOTA / KAB. ${selectedOption.value.toUpperCase()}` }]
            );

            regSelect.innerHTML = '<option value="">Pilih Kabupaten / Kota</option>' +
                regencies.map(r => {
                    const label = formatWilayahName(r.name);
                    return `<option value="${label}" data-id="${r.id}">${label}</option>`;
                }).join('');
            regSelect.disabled = false;
        });

        regSelect.addEventListener('change', async function () {
            const selectedOption = this.options[this.selectedIndex];
            const regId = selectedOption?.dataset?.id;

            distSelect.innerHTML = '<option value="">Memuat Kecamatan...</option>';
            distSelect.disabled = true;

            if (!regId) {
                distSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                return;
            }

            const districts = await fetchWilayahJson(
                `${WILAYAH_API_BASE}/districts/${regId}.json`,
                [{ id: `${regId}01`, name: `KECAMATAN ${selectedOption.value.toUpperCase()}` }]
            );

            distSelect.innerHTML = '<option value="">Pilih Kecamatan</option>' +
                districts.map(d => {
                    const label = formatWilayahName(d.name);
                    return `<option value="${label}" data-id="${d.id}">${label}</option>`;
                }).join('');
            distSelect.disabled = false;
        });
    });

    document.querySelectorAll('input[name="postal_code"]').forEach(input => {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 5);
        });
    });
}

// ====================================
// INITIALIZE ON LOAD
// ====================================
window.addEventListener('DOMContentLoaded', function () {
    initWilayahDropdowns();
    // Add animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideDown {
            from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
            to   { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to   { opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    document.getElementById('roleInput').value = 'buyer';

    document.querySelectorAll('[data-category-option]').forEach(button => {
        button.addEventListener('click', () => selectCategory(button.dataset.categoryOption, button));
    });

    document.querySelectorAll('[data-password-target]').forEach(button => {
        button.addEventListener('click', () => togglePassword(button.dataset.passwordTarget));
    });

    document.getElementById('profilePhotoButton')?.addEventListener('click', () => {
        document.getElementById('profilePhotoInput')?.click();
    });

    document.querySelectorAll('[data-close-crop]').forEach(button => {
        button.addEventListener('click', closeCropModal);
    });

    document.getElementById('saveCropButton')?.addEventListener('click', saveCroppedImage);

    document.querySelectorAll('[data-step-direction]').forEach(button => {
        button.addEventListener('click', () => changeStep(Number(button.dataset.stepDirection)));
    });

    document.addEventListener('keydown', (event) => {
        const modal = document.getElementById('cropModal');
        if (event.key === 'Escape' && modal?.getAttribute('aria-hidden') === 'false') {
            closeCropModal();
        }
    });

    showStep(2);
});
