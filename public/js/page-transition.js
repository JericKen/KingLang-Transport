document.addEventListener('DOMContentLoaded', function() {
    // First check if we're on client pages, not admin pages
    const isClientPage = window.location.pathname.includes('/home/');
    const isAdminPage = window.location.pathname.includes('/admin/');
    
    if (!isClientPage && !isAdminPage) return; // Skip all the animation code if not on client or admin pages
    
    // Store the current page type (login or signup)
    const currentPage = window.location.pathname.includes('signup') ? 'signup' : 'login';
    const contentDiv = document.querySelector('.content');
    
    // Always animate on login/signup pages, regardless of sessionStorage
    if (contentDiv && (window.location.pathname.includes('login') || window.location.pathname.includes('signup'))) {
        // Remove animation classes first (in case they're already there)
        contentDiv.classList.remove('animate-in');
        contentDiv.classList.remove('animation-complete');
        
        // Force browser reflow to ensure the animation runs again
        void contentDiv.offsetWidth;
        
        // Add animation class
        contentDiv.classList.add('animate-in');
        
        // After animations complete, add animation-complete class to maintain visibility
        setTimeout(function() {
            contentDiv.classList.add('animation-complete');
        }, 2000); // Increased time to allow for staggered animations to complete
    }
    
    // Get navigation links - specifically for client pages
    const loginLinks = document.querySelectorAll('a[href="/home/login"]');
    const signupLinks = document.querySelectorAll('a[href="/home/signup"]');
    const adminLoginLinks = document.querySelectorAll('a[href="/admin/login"]');
    
    // Add event listeners to login links when on signup page
    if (currentPage === 'signup') {
        loginLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                contentDiv.classList.remove('animation-complete');
                contentDiv.classList.add('signup-to-login');
                
                // Redirect after animation completes
                setTimeout(function() {
                    window.location.href = link.getAttribute('href');
                }, 700);
            });
        });
    }
    
    // Add event listeners to signup links when on login page
    if (currentPage === 'login' && isClientPage) {
        signupLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                contentDiv.classList.remove('animation-complete');
                contentDiv.classList.add('login-to-signup');
                
                // Redirect after animation completes
                setTimeout(function() {
                    window.location.href = link.getAttribute('href');
                }, 700);
            });
        });
    }
    
    // Add smooth transition to admin login from home page
    adminLoginLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only prevent default if we're on the home page with content
            if (contentDiv) {
                e.preventDefault();
                
                // Fade out the entire page
                document.body.style.transition = 'opacity 0.5s ease';
                document.body.style.opacity = '0';
                
                // Redirect after fade out
                setTimeout(function() {
                    window.location.href = link.getAttribute('href');
                }, 500);
            }
        });
    });
}); 