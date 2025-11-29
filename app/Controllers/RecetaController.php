<?php

namespace App\Controllers;

// Importar dependencias clave
use App\CsrfToken;
use App\Models\RecetaModel;
use App\SessionManager;

class RecetaController
{
    private $recetaModel;

    // Inyección de Dependencias
    public function __construct(RecetaModel $recetaModel)
    {
        $this->recetaModel = $recetaModel;
    }

    // --- MÉTODOS PÚBLICOS (ACCIONES) ---

    /**
     * Muestra la página de inicio. Maneja SSR, paginación y AJAX.
     */
    public function index()
    {

        // 1. Lógica de Paginación
            $page = (int) ($_GET['page'] ?? 1);
            $limit = 5; // Definir el límite por página
            $page = max(1, $page); // Asegurar que la página sea al menos 1
            
            // 2. Obtener el total de recetas ACTIVAS desde el modelo
            $totalRecetas = $this->recetaModel->getTotalRecetas(); 

            // 3. Calcular el total de páginas
            $totalPages = (int) ceil($totalRecetas / $limit);
            
            // Asegurar que la página actual no exceda el total de páginas si no hay resultados
            if ($totalRecetas > 0 && $page > $totalPages) {
                $page = $totalPages;
            } elseif ($totalRecetas === 0) {
                $page = 1;
                $totalPages = 1;
            }

            // 4. Obtener solo el segmento de recetas para la página actual
            $recetas = $this->recetaModel->getRecetasPaginadas($page, $limit);

        // 5. Preparar los datos para la vista
            $viewData = [
                'title'         => 'Recetas Sostenibles',
                'recetas'       => $recetas,
                'currentPage'   => $page,
                'totalPages'    => $totalPages, 
                // -------------------------------------------------------------------
                'csrf_token'    => \App\CsrfToken::generate(), 
                'flash_message' => \App\SessionManager::getFlash('message'),
                'old_input'     => \App\SessionManager::getOldInput(),
            ];

        // 4. Determinar el modo de renderizado (AJAX vs. SSR)
        if ($this->isAjaxRequest()) {
            // Renderiza solo el fragmento (lista y paginación)
            return $this->renderPartial('recetas/recipe_list_fragment', $viewData);
        }

        // 5. Renderizado de la Vista Principal (SSR inicial)
        return $this->render('recetas/index', $viewData);
    }

    /**
     * Procesa la solicitud POST para guardar una nueva receta.
     */
    public function save()
    {
        // 1. Validación de Seguridad (CSRF)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfToken::validate($_POST['csrf_token'] ?? '')) {
            SessionManager::setFlash('message', ['error' => 'Error de seguridad o método incorrecto.']);
            header('Location: /receta/index');
            exit;
        }

        // 2. Extracción y Validación de Datos
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $ingredientesData = $_POST['ingredientes_data'] ?? '[]';
        $errors = [];

        if (strlen($titulo) < 5) {
            $errors[] = 'El título es muy corto.';
        }

        // 3. Manejo de Errores (Redirección con Flash Data)
        if (!empty($errors)) {
            SessionManager::setFlash('message', ['validation' => implode('<br>', $errors)]);
            SessionManager::setOldInput($_POST);
            header('Location: /receta/index');
            exit;
        }

        // 4. Lógica de Negocio y Guardado
        $score = $this->calculateSustainabilityScore($ingredientesData);

        try {
            $this->recetaModel->saveReceta($titulo, $descripcion, $ingredientesData, $score);
            $successMsg = "¡Receta guardada! Score Eco-Nutri: {$score}/10.";
            SessionManager::setFlash('message', ['success' => $successMsg]);
        } catch (\Exception $e) {
            SessionManager::setFlash('message', ['error' => 'Error interno: ' . $e->getMessage()]);
        }

        // 5. Redirección PRG
        header('Location: /receta/index');
        exit;
    }

      // --- MÉTODOS AUXILIARES Y RENDERIZADO ---

    private function calculateSustainabilityScore(string $ingredientes_json): int
    {
        // Simulación
        return rand(6, 9);
    }

    private function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    protected function render(string $template, array $data = [])
    {
        extract($data);
        ob_start();
        $viewPath = __DIR__ . '/../Views/' . $template . '.phtml';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "Error: La vista {$template}.phtml no existe.";
            ob_end_clean();

            return;
        }
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layout.phtml';
    }

    protected function renderPartial(string $template, array $data = [])
    {
        extract($data);
        ob_start();
        $viewPath = __DIR__ . '/../Views/' . $template . '.phtml';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            echo "Error: La vista parcial {$template}.phtml no existe.";
        }
        echo ob_get_clean();
    }
}
