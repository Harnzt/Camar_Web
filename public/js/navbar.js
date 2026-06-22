/**
 * Navbar JavaScript
 */

(function() {
    'use strict';

    // Navbar Scroll Effect
    const navbar = document.querySelector('.navbar');
    
    if (navbar) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Smooth Scroll for Navigation Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                const navbarHeight = navbar ? navbar.offsetHeight : 80;
                const targetPosition = targetElement.offsetTop - navbarHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
                        toggle: true
                    });
                }
            }
        });
    });

    // Contoh logika di dalam fungsi CART.addToCartAjax
    var CART = {
        addToCartAjax: function(projectId, quantity) {
            $.ajax({
                url: '/add-to-cart', // Sesuaikan dengan route kamu
                method: 'POST',
                data: {
                    id: projectId,
                    qty: quantity,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // response.newCount harus dikirimkan dari Controller setelah sukses nambah data
                    if(response.newCount !== undefined) {
                        $('#cart-count').text(response.newCount);
                        
                        // Opsional: Kasih animasi sedikit biar kelihatan nambah
                        $('#cart-count').fadeOut(100).fadeIn(100);
                    }
                    alert('Produk berhasil ditambahkan ke keranjang!');
                },
                error: function() {
                    alert('Gagal menambahkan produk.');
                }
            });
        }
    };

})();