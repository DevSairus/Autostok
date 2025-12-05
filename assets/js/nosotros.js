// Archivo JS externo — maneja navegación entre secciones, accesibilidad y animaciones con GSAP si está disponible


document.addEventListener('DOMContentLoaded', () => {
const container = document.getElementById('main-content');
const buttons = Array.from(document.querySelectorAll('.section-btn'));
const sections = Array.from(document.querySelectorAll('.section'));
let currentIndex = 0;


// Activar observador para añadir clase .visible (fallback) cuando entren en viewport
const io = new IntersectionObserver((entries) => {
entries.forEach(en => {
if (en.isIntersecting) {
en.target.classList.add('visible');
}
});
}, { threshold: 0.15, root: container });


sections.forEach(sec => {
sec.classList.add('fade-in');
io.observe(sec);
});


/