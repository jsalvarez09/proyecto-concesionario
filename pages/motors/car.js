// ===== ENCABEZADO DINÁMICO (STICKY HEADER) =====
const header = document.querySelector('header');

window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// ===== MOSTRAR NOMBRE DEL ARCHIVO EN EL FORMULARIO DE AGREGAR VEHÍCULO =====
const fileInput = document.getElementById('imagenes');
const fileNameDisplay = document.getElementById('file-name-display');

// Este código solo se ejecutará si el formulario existe en la página
if (fileInput && fileNameDisplay) {
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            // Muestra la cantidad de archivos seleccionados
            if (fileInput.files.length === 1) {
                fileNameDisplay.textContent = fileInput.files[0].name;
            } else {
                fileNameDisplay.textContent = `${fileInput.files.length} archivos seleccionados`;
            }
        } else {
            // Si se cancela la selección, vuelve al texto original
            fileNameDisplay.textContent = 'Ningún archivo seleccionado';
        }
    });
}

// ===== GALERÍA DE IMÁGENES EN PÁGINA DE DETALLES =====
const mainCarImage = document.getElementById('mainCarImage');
const thumbnails = document.querySelectorAll('.thumbnail');

if (mainCarImage && thumbnails.length > 0) {
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Cambia la imagen principal a la de la miniatura clicada
            mainCarImage.src = this.src;

            // Actualiza la clase 'active' para el borde
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Marcar la primera miniatura como activa por defecto
    if (thumbnails[0]) {
        thumbnails[0].classList.add('active');
    }
}