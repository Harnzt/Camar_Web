/**
 * CAMAR Edukasi Page JavaScript
 * Author: CAMAR Team
 */

(function() {
    'use strict';

    // ====================================
    // Scroll Animation for Elements
    // ====================================
    const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe roadmap items
    document.querySelectorAll('.roadmap-item').forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = index % 2 === 0 ? 'translateX(-50px)' : 'translateX(50px)';
        item.style.transition = 'all 0.6s ease';
        item.style.transitionDelay = `${index * 0.1}s`;
        observer.observe(item);
    });

    // Observe dampak cards
    document.querySelectorAll('.dampak-card').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';
        card.style.transitionDelay = `${index * 0.1}s`;
        observer.observe(card);
    });

    // Observe offset modules
    document.querySelectorAll('.offset-module').forEach((module, index) => {
        module.style.opacity = '0';
        module.style.transform = 'translateY(30px)';
        module.style.transition = 'all 0.6s ease';
        module.style.transitionDelay = `${index * 0.15}s`;
        observer.observe(module);
    });

    // Add animate-visible class styles
    const style = document.createElement('style');
    style.textContent = `
        .animate-visible {
            opacity: 1 !important;
            transform: translate(0, 0) !important;
        }
    `;
    document.head.appendChild(style);

    // ====================================
    // Roadmap Progress Line Animation
    // ====================================
    function animateProgressLine() {
        const roadmapItems = document.querySelectorAll('.roadmap-item');
        
        const progressObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const connector = entry.target.querySelector('.roadmap-connector');
                    if (connector) {
                        connector.style.opacity = '0';
                        connector.style.height = '0';
                        connector.style.transition = 'all 0.6s ease 0.3s';
                        
                        setTimeout(() => {
                            connector.style.opacity = '1';
                            connector.style.height = '3rem';
                        }, 100);
                    }
                }
            });
        }, { threshold: 0.5 });

        roadmapItems.forEach(item => {
            progressObserver.observe(item);
        });
    }

    animateProgressLine();

    // ====================================
    // Number Counter Animation
    // ====================================
    function animateCounter(element, target, suffix = '') {
        const duration = 2000;
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = formatNumber(target) + suffix;
                clearInterval(timer);
            } else {
                element.textContent = formatNumber(Math.floor(current)) + suffix;
            }
        }, 16);
    }

    function formatNumber(num) {
        return num.toLocaleString('id-ID');
    }

    // Animate stat numbers when visible
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                entry.target.classList.add('counted');
                
                const statValue = entry.target.querySelector('.stat-value');
                if (statValue) {
                    const text = statValue.textContent;
                    const numMatch = text.match(/[\d.]+/);
                    if (numMatch) {
                        const targetValue = parseFloat(numMatch[0]);
                        const suffix = text.replace(numMatch[0], '');
                        statValue.textContent = '0' + suffix;
                        
                        setTimeout(() => {
                            animateCounter(statValue, targetValue, suffix);
                        }, 300);
                    }
                }
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.dampak-stats').forEach(stats => {
        statsObserver.observe(stats);
    });

    // ====================================
    // Smooth Scroll for Anchor Links
    // ====================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const targetElement = document.querySelector(href);
            
            if (targetElement) {
                const navbar = document.querySelector('.navbar');
                const navbarHeight = navbar ? navbar.offsetHeight : 0;
                const targetPosition = targetElement.offsetTop - navbarHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ====================================
    // Parallax Effect for Hero
    // ====================================
    const heroBackground = document.querySelector('.edukasi-hero-bg');
    
    if (heroBackground) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallaxSpeed = 0.5;
            heroBackground.style.transform = `translateY(${scrolled * parallaxSpeed}px)`;
        });
    }

    // ====================================
    // Highlight Active Section in View
    // ====================================
    const sections = document.querySelectorAll('section[id]');
    
    function highlightActiveSection() {
        const scrollY = window.pageYOffset;
        
        sections.forEach(section => {
            const sectionHeight = section.offsetHeight;
            const sectionTop = section.offsetTop - 100;
            const sectionId = section.getAttribute('id');
            
            if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                // Section is in view
                section.classList.add('section-active');
            } else {
                section.classList.remove('section-active');
            }
        });
    }

    window.addEventListener('scroll', highlightActiveSection);

    // ====================================
    // Hover Effect Enhancement
    // ====================================
    const dampakCards = document.querySelectorAll('.dampak-card');
    
    dampakCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });

    // ====================================
    // Workflow Steps Sequential Animation
    // ====================================
    const workflowSteps = document.querySelectorAll('.workflow-step');
    
    const workflowObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                workflowSteps.forEach((step, index) => {
                    step.style.opacity = '0';
                    step.style.transform = 'translateY(20px)';
                    step.style.transition = `all 0.4s ease ${index * 0.1}s`;
                    
                    setTimeout(() => {
                        step.style.opacity = '1';
                        step.style.transform = 'translateY(0)';
                    }, 100);
                });
                
                workflowObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });

    const workflowContainer = document.querySelector('.workflow-steps');
    if (workflowContainer) {
        workflowObserver.observe(workflowContainer);
    }

    // ====================================
    // Policy Timeline Animation
    // ====================================
    const policyItems = document.querySelectorAll('.policy-item');
    
    const policyObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateX(0)';
            }
        });
    }, { threshold: 0.3 });

    policyItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-30px)';
        item.style.transition = `all 0.5s ease ${index * 0.1}s`;
        policyObserver.observe(item);
    });

    // ====================================
    // CTA Section Animation
    // ====================================
    const ctaSection = document.querySelector('.cta-section');
    
    if (ctaSection) {
        const ctaObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.transform = 'scale(1)';
                    entry.target.style.opacity = '1';
                }
            });
        }, { threshold: 0.2 });

        ctaSection.style.opacity = '0';
        ctaSection.style.transform = 'scale(0.95)';
        ctaSection.style.transition = 'all 0.6s ease';
        ctaObserver.observe(ctaSection);
    }

    // ====================================
    // Add Ripple Effect to Buttons
    // ====================================
    const buttons = document.querySelectorAll('.btn-cta');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple-effect');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Add ripple effect styles
    const rippleStyle = document.createElement('style');
    rippleStyle.textContent = `
        .btn-cta {
            position: relative;
            overflow: hidden;
        }
        
        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            transform: scale(0);
            animation: ripple-animation 0.6s ease-out;
            pointer-events: none;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(rippleStyle);

    // ====================================
    // Console Welcome Message
    // ====================================
    console.log('%c🌱 CAMAR - Edukasi Carbon Offset', 
        'color: #2D9F6E; font-size: 18px; font-weight: bold;');
    console.log('%cBersama Wujudkan Indonesia Net Zero 2060', 
        'color: #124170; font-size: 14px;');

    // ====================================
    // Performance Optimization
    // ====================================
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Handle resize events if needed
        }, 250);
    });

    // ====================================
    // Prevent Content Flash on Load
    // ====================================
    window.addEventListener('load', function() {
        document.body.style.opacity = '0';
        document.body.style.transition = 'opacity 0.5s ease';
        
        setTimeout(() => {
            document.body.style.opacity = '1';
        }, 100);
    });

})();