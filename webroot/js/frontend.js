// Frontend JavaScript for MEG Healthcare Platform

document.addEventListener('DOMContentLoaded', function() {
    // Initialize animations
    initializeAnimations();
    
    // Initialize smooth scrolling
    initializeSmoothScrolling();
    
    // Initialize login card interactions
    initializeLoginCards();
    
    // Initialize navigation
    initializeNavigation();
});

/**
 * Initialize scroll animations
 */
function initializeAnimations() {
    // Create intersection observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements that should animate on scroll
    const animatedElements = document.querySelectorAll('.stat-card, .login-card, .service-card, .feature-item');
    animatedElements.forEach(el => observer.observe(el));
}

/**
 * Initialize smooth scrolling for anchor links
 */
function initializeSmoothScrolling() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                
                const headerOffset = 80; // Account for fixed header
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Initialize login card interactions
 */
function initializeLoginCards() {
    const loginCards = document.querySelectorAll('.login-card');
    
    loginCards.forEach(card => {
        // Add ripple effect on click
        card.addEventListener('click', function(e) {
            createRippleEffect(this, e);
        });
        
        // Add hover sound effect (optional)
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
        });
        
        // Track login button clicks for analytics
        const loginButton = card.querySelector('.btn');
        if (loginButton) {
            loginButton.addEventListener('click', function(e) {
                const userType = this.textContent.toLowerCase();
                trackLoginAttempt(userType);
            });
        }
    });
}

/**
 * Create ripple effect on card click
 */
function createRippleEffect(element, event) {
    const ripple = document.createElement('div');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.cssText = `
        position: absolute;
        border-radius: 50%;
        background: rgba(233, 30, 99, 0.3);
        transform: scale(0);
        animation: ripple 0.6s linear;
        width: ${size}px;
        height: ${size}px;
        left: ${x}px;
        top: ${y}px;
        pointer-events: none;
    `;
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

/**
 * Initialize navigation behavior
 */
function initializeNavigation() {
    const navbar = document.querySelector('.navbar');
    
    if (navbar) {
        // Change navbar background on scroll
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
                navbar.style.backdropFilter = 'blur(10px)';
            } else {
                navbar.classList.remove('scrolled');
                navbar.style.backgroundColor = '';
                navbar.style.backdropFilter = '';
            }
        });
    }
    
    // Handle mobile menu
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function() {
            const isExpanded = navbarToggler.getAttribute('aria-expanded') === 'true';
            
            if (!isExpanded) {
                // Add animation class when opening
                navbarCollapse.style.animation = 'slideDown 0.3s ease-out';
            }
        });
    }
}

/**
 * Track login attempts for analytics
 */
function trackLoginAttempt(userType) {
    console.log(`Login attempt for user type: ${userType}`);
    
    // Here you could send analytics data to your tracking service
    // Example: gtag('event', 'login_attempt', { user_type: userType });
    
    // Show loading state (optional)
    showLoginProgress(userType);
}

/**
 * Show login progress feedback
 */
function showLoginProgress(userType) {
    // Create and show a loading toast or modal
    const toast = createToast(`Redirecting to ${userType} login...`, 'info');
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

/**
 * Create a toast notification
 */
function createToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-info-circle me-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-left: 4px solid var(--primary-color);
        z-index: 9999;
        animation: slideInRight 0.3s ease-out;
    `;
    
    return toast;
}

/**
 * Initialize stats counter animation
 */
function initializeStatsCounter() {
    const statsNumbers = document.querySelectorAll('.stat-number');
    
    const countUp = (element, target) => {
        const increment = target / 100;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            element.textContent = Math.floor(current).toLocaleString();
            
            if (current >= target) {
                element.textContent = target.toLocaleString();
                clearInterval(timer);
            }
        }, 20);
    };
    
    // Observe stats section
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const number = parseInt(entry.target.textContent.replace(/,/g, ''));
                countUp(entry.target, number);
                statsObserver.unobserve(entry.target);
            }
        });
    });
    
    statsNumbers.forEach(num => statsObserver.observe(num));
}

// CSS animations that need to be added dynamically
const additionalStyles = `
    @keyframes ripple {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .toast-notification {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .toast-content {
        display: flex;
        align-items: center;
        color: #333;
        font-weight: 500;
    }
`;

// Inject additional styles
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet);

// Initialize stats counter when DOM is ready
document.addEventListener('DOMContentLoaded', initializeStatsCounter);