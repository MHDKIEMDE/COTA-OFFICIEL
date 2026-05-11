// COTA - Main JavaScript file (Blade/Livewire)

// Import Bootstrap JS
import 'bootstrap';

// Import CSS
import '../css/app.scss';

// Optimisations de performance
// Utiliser requestIdleCallback pour les tâches non critiques
const requestIdleCallback = window.requestIdleCallback || ((cb) => setTimeout(cb, 1));

// Debounce function pour optimiser les événements
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function pour limiter la fréquence des événements
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Optimisation du scroll avec Intersection Observer
let competitionHeadersObserver = null;

function initCompetitionHeadersObserver() {
    if ('IntersectionObserver' in window) {
        const competitionGroups = document.querySelectorAll('.competition-group');
        
        if (competitionGroups.length > 0) {
            // Assigner z-index progressif via JavaScript (plus performant que 50 règles CSS)
            competitionGroups.forEach((group, index) => {
                const header = group.querySelector('.competition-group__header');
                if (header) {
                    // Z-index progressif: chaque groupe suivant a un z-index plus élevé
                    header.style.zIndex = (10 + index).toString();
                }
            });
            
            // Observer pour optimiser le rendu des headers sticky
            competitionHeadersObserver = new IntersectionObserver(
                (entries) => {
                    entries.forEach(entry => {
                        const header = entry.target.querySelector('.competition-group__header');
                        if (header) {
                            if (entry.isIntersecting) {
                                // Header visible, activer will-change seulement si nécessaire
                                if (window.innerWidth > 768) {
                                    header.style.willChange = 'transform';
                                }
                            } else {
                                // Header hors vue, désactiver will-change pour économiser les ressources
                                header.style.willChange = 'auto';
                            }
                        }
                    });
                },
                {
                    rootMargin: '-56px 0px 0px 0px', // Prendre en compte le header fixe
                    threshold: 0
                }
            );
            
            competitionGroups.forEach(group => {
                competitionHeadersObserver.observe(group);
            });
        }
    }
}

// Lazy loading des images
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Optimisation du scroll avec debounce agressif
let ticking = false;
let lastScrollY = 0;
function optimizeScroll() {
    if (!ticking) {
        window.requestAnimationFrame(() => {
            // Désactiver will-change sur les éléments hors vue pour économiser les ressources
            const scrollY = window.scrollY;
            const viewportHeight = window.innerHeight;
            
            // Ne mettre à jour que si le scroll a significativement changé (éviter les micro-updates)
            if (Math.abs(scrollY - lastScrollY) > 50) {
                document.querySelectorAll('.competition-group').forEach(group => {
                    const rect = group.getBoundingClientRect();
                    const header = group.querySelector('.competition-group__header');
                    
                    if (header) {
                        // Si le groupe est complètement hors de la vue, désactiver will-change
                        if (rect.bottom < 0 || rect.top > viewportHeight) {
                            header.style.willChange = 'auto';
                        }
                    }
                });
                
                lastScrollY = scrollY;
            }
            
            ticking = false;
        });
        ticking = true;
    }
}

// Custom JS for COTA
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips (déféré pour améliorer les performances)
    requestIdleCallback(() => {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (tooltipTriggerList.length > 0) {
            [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }

        // Initialize popovers
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        if (popoverTriggerList.length > 0) {
            [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
        }
    });
    
    // Initialiser les optimisations (déféré pour ne pas bloquer le rendu initial)
    requestIdleCallback(() => {
        initCompetitionHeadersObserver();
        initLazyLoading();
    }, { timeout: 2000 });
    
    // Optimiser le scroll avec throttle agressif (limiter à 60fps max)
    window.addEventListener('scroll', throttle(optimizeScroll, 16), { passive: true });
    
    // Optimisation: désactiver les animations si l'appareil est lent
    if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
        document.documentElement.style.setProperty('--animation-speed', '0.1s');
    }
    
    // Optimisation: utiliser content-visibility pour les éléments hors vue
    if ('contentVisibility' in document.documentElement.style) {
        const competitionGroups = document.querySelectorAll('.competition-group');
        competitionGroups.forEach((group, index) => {
            // Activer content-visibility pour les groupes après les 5 premiers
            if (index > 4) {
                group.style.contentVisibility = 'auto';
            }
        });
    }
});

// Auto-hide alerts after 5 seconds
window.addEventListener('load', function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
