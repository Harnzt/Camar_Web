@extends('main_page.layout.app')

@section('title', 'Login')
@section('description', 'Login ke akun CAMAR Anda untuk mengakses platform carbon offset')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<!-- Login Page -->
<div class="login-page">
    <div class="login-container">
        <!-- Left Side - Login Form -->
        <div class="login-form-section">
            <div class="login-header">
                <div class="login-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="CAMAR Logo">
                </div>
                <h1 class="login-title">Selamat Datang Kembali</h1>
                <p class="login-subtitle">Masuk ke akun CAMAR Anda</p>
            </div>

            <form id="loginForm" class="login-form" method="POST" action="{{ route('login.process') }}">
                @csrf
                
                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="nama@gmail.com" 
                        required
                        autocomplete="email"
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Masukkan password" 
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember & Forgot -->
                <div class="form-footer">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Ingat saya</span>
                    </label>
                    <a href="#" class="forgot-link">Lupa password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login">
                    <span>Masuk</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

                @if($errors->has('loginError'))
                    <div id="errorData" data-message="{{ $errors->first('loginError') }}"></div>
                @endif

                <!-- Divider -->
                <div class="divider">
                    <span>atau</span>
                </div>

                <!-- Register Link -->
                <div class="register-link">
                    <p>Belum punya akun?</p>
                    <a href="{{ route('register') }}" class="btn-register">
                        Daftar Sekarang
                        <i class="fas fa-user-plus"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Right Side - Image/Info -->
        <div class="login-info-section">
            <div class="info-content">
                <h2 class="info-title">Platform Carbon Offset Terpercaya</h2>
                <p class="info-description">
                    Bergabunglah dengan ratusan perusahaan yang telah mempercayai CAMAR untuk mencapai target Net-Zero emissions
                </p>
                
                <div class="info-features">
                    <div class="info-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Verifikasi Internasional</span>
                    </div>
                    <div class="info-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Dashboard Real-Time</span>
                    </div>
                    <div class="info-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Expert Support 24/7</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Home Button - Di bawah container -->
    <a href="{{ route('home') }}" class="btn-back-home">
        <i class="fas fa-arrow-left"></i>
        <span>Kembali ke Beranda</span>
    </a>
</div>
@endsection

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@push('scripts')
<script src="{{ asset('js/login.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush