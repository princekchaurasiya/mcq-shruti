// Basic JavaScript functionality
document.addEventListener('DOMContentLoaded', () => {
    // Close alerts when the close button is clicked
    document.querySelectorAll('.alert .close').forEach(button => {
        button.addEventListener('click', () => {
            button.closest('.alert').remove();
        });
    });

    // Toggle mobile navigation
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', () => {
            const nav = document.getElementById('mobile-nav');
            if (nav) {
                nav.classList.toggle('hidden');
            }
        });
    }
}); 