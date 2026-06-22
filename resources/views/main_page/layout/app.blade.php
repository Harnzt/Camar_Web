<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    @stack('styles')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'CAMAR - Platform Carbon Offset terpercaya di Indonesia')">
    <meta name="keywords" content="carbon offset, kredit karbon, net zero, ESG, Indonesia">
    <meta name="author" content="CAMAR">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    
    <title>@yield('title', 'CAMAR') - CArbon MARket</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800;900&family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Navbar & Footer CSS (Global) -->
    @if(!in_array(Route::currentRouteName(), ['login', 'register']))
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}">
    @endif
    
    <!-- Page Specific CSS -->
    @stack('styles')
    
    <!-- Additional Head Content -->
    @yield('head')
</head>
<body class="@yield('body-class')">
    
    <!-- Include Navbar (kecuali login & register) -->
    @if(!in_array(Route::currentRouteName(), ['login', 'register']))
        @include('main_page.layout.navbar')
    @endif
    
    <!-- Main Content -->
    <main id="main-content">
        @yield('content')
    </main>
    
    <!-- Include Footer (kecuali login & register) -->
    @if(!in_array(Route::currentRouteName(), ['login', 'register']))
        @include('main_page.layout.footer')
    @endif
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Navbar JS (Global) -->
    @if(!in_array(Route::currentRouteName(), ['login', 'register']))
    <script src="{{ asset('js/navbar.js') }}"></script>
    @endif
    
    <!-- Page Specific JS -->
    @stack('scripts')
    
    <!-- Additional Scripts -->
    @yield('scripts')
    
</body>
</html>