// --- CÓDIGO PARA EL BUSCADOR ---
let search = document.querySelector('.search-box');

document.querySelector('#search-icon').onclick = () => {
    search.classList.toggle('active');
}


// --- CÓDIGO ACTUALIZADO PARA EL HEADER DINÁMICO ---
const header = document.querySelector('header');

// Escuchamos el evento de scroll en la ventana
window.addEventListener('scroll', () => {
    // Si el scroll vertical es mayor a 50 píxeles...
    if (window.scrollY > 50) {
        // ...añadimos la clase 'header-scrolled'.
        header.classList.add('header-scrolled');
    } else {
        // ...si no, la quitamos.
        header.classList.remove('header-scrolled');
    }
});