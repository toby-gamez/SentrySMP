// Simple helper to toggle / close the mobile navigation sidebar
window.sentrySMP = window.sentrySMP || {};
window.sentrySMP.toggleNav = function() {
    try {
        document.body.classList.toggle('nav-open');
    } catch (e) {
        console.error('sentrySMP.toggleNav error', e);
    }
};
window.sentrySMP.closeNav = function() {
    try {
        document.body.classList.remove('nav-open');
    } catch (e) {
        console.error('sentrySMP.closeNav error', e);
    }
};
