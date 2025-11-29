// --- Lógica JS Mínima para Low-Spec Devices ---

const API_BASE = '/api'; // Punto de entrada al ApiController

// Almacena la data real de ingredientes seleccionados para enviar al servidor.
let selectedIngredients = [];

// ===============================================
// 1. Lógica de Ingredientes y Búsqueda FULLTEXT
// ===============================================

/**
 * Añade un ingrediente seleccionado de la búsqueda a la lista y al campo oculto.
 * @param {object} item Objeto del ingrediente devuelto por la API.
 */
function addIngredienteToRecipe(item) {
    const selectedList = document.getElementById('selected-ingredients');
    const hiddenInput = document.getElementById('ingredientes_data');

    // 1. Prevenir duplicados
    if (selectedIngredients.some(i => i.id === item.id)) {
        // Mejor práctica: usar un modal o mensaje flash en lugar de alert()
        console.warn('Este ingrediente ya fue añadido.');
        return;
    }

    // USAR SIEMPRE SANITIZACIÓN AL MOSTRAR DATOS DE USUARIO O API
    const ingredientName = item.nombre; 

    // Pedir cantidad al usuario (USO de prompt ES SOLO PARA DEMO)
    const cantidad = prompt(`Ingrese la cantidad en gramos de ${ingredientName} para la receta:`);
    const cantidadGramos = parseFloat(cantidad);

    if (isNaN(cantidadGramos) || cantidadGramos <= 0) {
        // Error genérico para el usuario 
        console.error('Cantidad inválida.');
        return;
    }

    // 2. Crear el objeto de datos para el servidor
    const dataToSend = {
        id: item.id,
        nombre: item.nombre,
        huella_carbono: item.huella_carbono,
        cantidad_gramos: cantidadGramos
    };
    
    // 3. Añadir a la lista de JS y actualizar el campo oculto
    selectedIngredients.push(dataToSend);
    hiddenInput.value = JSON.stringify(selectedIngredients);

    // 4. Añadir a la lista visible (DOM Manipulation SEGURA)
    const li = document.createElement('li');
    li.className = 'my-1 p-2 bg-green-50 rounded flex justify-between items-center';
    
    // Usamos textContent para insertar el nombre y la cantidad 
    const nameSpan = document.createElement('span');
    nameSpan.textContent = `${ingredientName}: `;
    
    const quantityStrong = document.createElement('strong');
    quantityStrong.textContent = `${cantidadGramos}g`;
    
    nameSpan.appendChild(quantityStrong);
    
    li.appendChild(nameSpan);

    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.setAttribute('data-id', item.id);
    removeButton.className = 'remove-btn text-red-500 font-bold ml-4';
    removeButton.textContent = 'X';
    
    li.appendChild(removeButton);

    selectedList.appendChild(li);

    // 5. Limpiar la búsqueda y resultados
    document.getElementById('ingrediente-search').value = '';
    document.getElementById('ingrediente-results').innerHTML = '';
    
    // 6. Reasignar eventos de eliminación (importante para elementos nuevos)
    setupRemoveButtons();
}

/**
 * Elimina un ingrediente de la lista y del array de datos.
 */
function setupRemoveButtons() {
    document.querySelectorAll('.remove-btn').forEach(button => {
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        newButton.addEventListener('click', (e) => {
            const ingredientId = parseInt(e.target.getAttribute('data-id'));
            
            selectedIngredients = selectedIngredients.filter(i => i.id !== ingredientId);
            document.getElementById('ingredientes_data').value = JSON.stringify(selectedIngredients);
            
            e.target.closest('li').remove();
        });
    });
}

/**
 * Configura la funcionalidad de "Search-as-you-type" para ingredientes.
 */
function setupSearchIngredientes() {
    const searchInput = document.getElementById('ingrediente-search');
    const resultsContainer = document.getElementById('ingrediente-results');
    let searchTimeout;

    if (!searchInput || !resultsContainer) return;

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        const query = searchInput.value.trim();

        if (query.length < 3) {
            resultsContainer.innerHTML = '';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`${API_BASE}/searchIngredientes?q=${encodeURIComponent(query)}`)
                .then(response => response.ok ? response.json() : [])
                .then(data => {
                    resultsContainer.innerHTML = '';
                    if (data.length === 0) {
                        resultsContainer.innerHTML = '<p class="text-sm p-2 text-gray-500">No se encontraron ingredientes.</p>';
                        return;
                    }
                    
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'p-2 border-b cursor-pointer hover:bg-gray-100 transition-colors';
                        
                        // Usamos textContent en lugar de innerHTML para los datos del API 
                        const nameText = document.createTextNode(item.nombre);
                        const huellaText = document.createTextNode(` (Huella: ${item.huella_carbono} CO2e)`);
                        
                        div.appendChild(nameText);
                        div.appendChild(huellaText);
                        
                        div.onclick = () => addIngredienteToRecipe(item);
                        resultsContainer.appendChild(div);
                    });
                })
                .catch(error => {
                    // Mensaje genérico para el usuario 
                    console.error('Error al buscar ingredientes.'); 
                });
        }, 300); 
    });
}


// ===============================================
// 2. Lógica de Paginación Sin Recarga (AJAX/SSR)
// ===============================================

/**
 * Configura los enlaces de paginación para cargar el contenido vía AJAX.
 */
function setupPagination() {
    const listContainer = document.getElementById('receta-list-container');
    const paginationLinks = listContainer.querySelectorAll('.pagination-link'); 

    if (!listContainer || paginationLinks.length === 0) return;

    paginationLinks.forEach(link => {
        const newLink = link.cloneNode(true);
        link.parentNode.replaceChild(newLink, link);

        newLink.addEventListener('click', (e) => {
            e.preventDefault();
            
            const url = newLink.href;
            listContainer.style.opacity = 0.4;

            fetch(url, { 
                headers: { 'X-Requested-With': 'XMLHttpRequest' } 
            })
                .then(response => {
                    if (response.ok) return response.text();
                    // Lanzar error con mensaje genérico
                    throw new Error(`Error al cargar la lista.`); 
                })
                .then(htmlFragment => {
                    listContainer.innerHTML = htmlFragment;
                    history.pushState(null, '', url);
                    
                    listContainer.style.opacity = 1;
                    setupPagination(); 
                })
                .catch(error => {
                    // Error genérico 
                    console.error('Error en Paginación. Intente de nuevo.');
                    listContainer.style.opacity = 1;
                });
        });
    });
}

// Inicialización de todas las funcionalidades al cargar la ventana
window.onload = function() {
    setupSearchIngredientes();
    setupPagination();
    setupRemoveButtons();
    // Exponer el array al scope global para que el script inyectado en index.phtml lo pueda modificar
    window.selectedIngredients = selectedIngredients; 
};
