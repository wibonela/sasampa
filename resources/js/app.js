// Bootstrap
import * as bootstrap from 'bootstrap';

// Make Bootstrap available globally
window.bootstrap = bootstrap;

// CSRF token for AJAX requests
document.addEventListener('DOMContentLoaded', function() {
    // Set up CSRF token for all fetch requests
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        window.csrfToken = token.getAttribute('content');
    }
});

// Utility function for fetch with CSRF
window.fetchWithCsrf = function(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json',
        },
    };

    return fetch(url, { ...defaultOptions, ...options });
};
