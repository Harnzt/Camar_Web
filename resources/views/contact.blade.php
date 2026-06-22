@extends('layouts.app')

@section('title', 'Kontak - CAMAR')

@section('content')
<!-- Hero Section -->
<section class="hero-section" style="background: linear-gradient(135deg, #124170 0%, #2D9F6E 100%); padding: 150px 0 100px; color: white;">
    <div class="container">
        <div class="text-center">
            <h1 class="display-4 fw-bold mb-4">Hubungi Kami</h1>
            <p class="lead">
                Ada pertanyaan? Tim kami siap membantu Anda
            </p>
        </div>
    </div>
</section>

<!-- Contact Info & Form -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Contact Info -->
            <div class="col-lg-4">
                <div class="mb-4">
                    <h3 class="fw-bold mb-4">Informasi Kontak</h3>
                    
                    <div class="contact-item mb-4">
                        <div class="d-flex align-items-start">
                            <div class="icon-wrapper" style="background: #67C090; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Alamat</h5>
                                <p class="text-muted mb-0">
                                    Jakarta, Indonesia
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="contact-item mb-4">
                        <div class="d-flex align-items-start">
                            <div class="icon-wrapper" style="background: #2D9F6E; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Email</h5>
                                <p class="text-muted mb-0">
                                    info@camar.co.id
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="contact-item mb-4">
                        <div class="d-flex align-items-start">
                            <div class="icon-wrapper" style="background: #124170; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Telepon</h5>
                                <p class="text-muted mb-0">
                                    +62 21 1234 5678
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="social-links">
                    <h5 class="fw-bold mb-3">Ikuti Kami</h5>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-secondary btn-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="btn btn-outline-secondary btn-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-secondary btn-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="btn btn-outline-secondary btn-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-4">Kirim Pesan</h3>
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="name" placeholder="Masukkan nama Anda">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" placeholder="email@example.com">
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Nomor Telepon</label>
                                    <input type="tel" class="form-control" id="phone" placeholder="+62 xxx xxxx xxxx">
                                </div>
                                <div class="col-md-6">
                                    <label for="subject" class="form-label">Subjek</label>
                                    <select class="form-select" id="subject">
                                        <option selected>Pilih subjek</option>
                                        <option value="general">Pertanyaan Umum</option>
                                        <option value="partnership">Kemitraan</option>
                                        <option value="project">Informasi Proyek</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Pesan</label>
                                    <textarea class="form-control" id="message" rows="5" placeholder="Tulis pesan Anda di sini..."></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-lg" style="background: #67C090; color: white;">
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Kirim Pesan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section (Optional) -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center">
            <h3 class="fw-bold mb-4">Lokasi Kami</h3>
            <div class="ratio ratio-21x9" style="max-height: 400px;">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d253840.65707965053!2d106.68942995!3d-6.229386599999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e945e34b9d%3A0x5371bf0fdad786a2!2sJakarta!5e0!3m2!1sen!2sid!4v1234567890123!5m2!1sen!2sid" 
                    style="border:0; border-radius: 12px;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</section>
@endsection