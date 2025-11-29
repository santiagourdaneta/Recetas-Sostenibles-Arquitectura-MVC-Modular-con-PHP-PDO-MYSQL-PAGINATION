document.addEventListener('DOMContentLoaded', function() {
    // Contenedor que refresca la lista. Es CRUCIAL para el AJAX.
    const listaRecetasContainer = document.getElementById('recipe-list-fragment-container'); 

    if (!listaRecetasContainer) {
        return; 
    }

    // --- 3. Función AJAX principal (Paginación) ---
    function fetchRecipes(searchQuery, page) {
        // Construye la URL, incluyendo los parámetros 'page' y 'search'
        const url = `/receta/index?page=${page}`;
        
        fetch(url, {
            method: 'GET',
            headers: {
                // Encabezado para indicarle a PHP que es una petición AJAX (parcial)
                'X-Requested-With': 'XMLHttpRequest' 
            }
        })
        .then(response => {
            if (!response.ok) {
                console.error('Error en la solicitud AJAX:', response.statusText);
                return;
            }
            return response.text();
        })
        .then(html => {
            if (html) {
                // Reemplazamos el contenido del fragmento
                listaRecetasContainer.innerHTML = html;
                
                // Volver a vincular los eventos de paginación después de refrescar el HTML
                bindPaginationEvents();
            }
        })
        .catch(error => console.error('Error al realizar la búsqueda o paginación:', error));
    }
    
    // --- 4. Lógica para rebindear eventos de Paginación ---
    function bindPaginationEvents() {
        // Limpiamos los eventos antiguos clonando y reemplazando los nodos
        const paginationLinks = document.querySelectorAll('#recipe-list-fragment-container .pagination-link');
        
        paginationLinks.forEach(link => {
            // Clonamos el nodo para remover cualquier listener anterior
            const newLink = link.cloneNode(true);
            link.replaceWith(newLink); 

            // Añadimos el nuevo listener
            newLink.addEventListener('click', function(e) {
                e.preventDefault(); 
                
                const href = this.getAttribute('href');
                const urlParams = new URLSearchParams(href.split('?')[1]);
                const newPage = urlParams.get('page');
                
                // Llama a fetchRecipes con la nueva página
                fetchRecipes(newPage);
            });
        });
    }

    // Vinculamos la paginación inicial al cargar la página
    bindPaginationEvents();
});