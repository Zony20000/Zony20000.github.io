// Seleccionamos todos los botones del acordeón
const accordionButtons = document.querySelectorAll('.accordion-header');

accordionButtons.forEach(button => {
    button.addEventListener('click', () => {
        
        // 1. CERRAR LOS DEMÁS:
        // Recorremos todos los botones para cerrar los que no sean el que acabamos de oprimir
        accordionButtons.forEach(otherButton => {
            if (otherButton !== button && otherButton.classList.contains('active')) {
                // Quitar la clase activa
                otherButton.classList.remove('active');
                // Colapsar el contenido (altura 0)
                otherButton.nextElementSibling.style.maxHeight = null;
            }
        });

        // 2. ALTERNAR EL ACTUAL:
        // Ahora abrimos o cerramos el que el usuario oprimió
        button.classList.toggle('active');
        const content = button.nextElementSibling;

        if (button.classList.contains('active')) {
            // Si está activo, le damos la altura de su contenido
            content.style.maxHeight = content.scrollHeight + "px";
        } else {
            // Si no, altura nula (cerrado)
            content.style.maxHeight = null;
        }
    });
});