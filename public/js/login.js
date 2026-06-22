// Login Page 

// Password Toggle
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById('passwordIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Form Submit Handler
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    // Basic validation
    if (!email || !password) {
        alert('Mohon isi email dan password');
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Format email tidak valid');
        return;
    }
    
    this.submit();
});

// Enter key handler
document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('loginForm').dispatchEvent(new Event('submit'));
        }
    });
});


document.addEventListener('DOMContentLoaded', function() {
    const loginError = document.getElementById('errorData')?.dataset.message;
    
    if (loginError) {
        Swal.fire({
            icon: 'error',
            title: 'Login Gagal',
            text: loginError,
            confirmButtonColor: '#124170',
            iconColor: '#67C090'
        });
    }
});