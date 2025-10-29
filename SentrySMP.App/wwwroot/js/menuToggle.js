// Simple helper to toggle the navigation menu active class
window.sentrySMP = window.sentrySMP || {};
window.sentrySMP.toggleNav = function() {
    try {
        const el = document.getElementById('navLinks');
        if (!el) return;
        el.classList.toggle('active');
    } catch (e) {
        console.error('sentrySMP.toggleNav error', e);
    }
};
