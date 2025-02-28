// Menu Hambúrguer
const menuToggle = document.querySelector('.menu-toggle');
const navMenu = document.querySelector('nav ul');
const menuOverlay = document.querySelector('.menu-overlay');

function toggleMenu() {
    menuToggle.classList.toggle('active');
    navMenu.classList.toggle('active');
    menuOverlay.classList.toggle('active');
    document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
}

menuToggle.addEventListener('click', toggleMenu);
menuOverlay.addEventListener('click', toggleMenu);

// Fechar menu ao clicar em um link
document.querySelectorAll('nav a').forEach(link => {
    link.addEventListener('click', () => {
        toggleMenu();
    });
});

// Fechar menu ao pressionar a tecla ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && navMenu.classList.contains('active')) {
        toggleMenu();
    }
}); 