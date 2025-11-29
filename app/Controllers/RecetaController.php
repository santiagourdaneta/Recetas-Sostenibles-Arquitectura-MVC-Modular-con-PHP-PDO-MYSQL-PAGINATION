<?php

namespace App\Controllers;

use App\Database;
use App\Models\RecetaModel;

class RecetaController extends Controller
{
    private RecetaModel $recetaModel;

    public function __construct(Database $db)
    {
        parent::__construct($db);
        $this->recetaModel = new RecetaModel($db);
    }

    // El método index está seguro gracias a la lógica de ruteo en index.php
    public function index()
    {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = 5; 
        $page = max(1, $page);
        
        $totalRecetas = $this->recetaModel->getTotalRecetas(); 
        $totalPages = (int) ceil($totalRecetas / $limit);
        
        if ($totalRecetas > 0 && $page > $totalPages) {
            $page = $totalPages;
        } elseif ($totalRecetas === 0) {
            $page = 1;
            $totalPages = 1;
        }

        $recetas = $this->recetaModel->getRecetasPaginadas($page, $limit); 

        $viewData = [
            'title'         => 'Recetas Sostenibles',
            'recetas'       => $recetas,
            'currentPage'   => $page,
            'totalPages'    => $totalPages, 
            'csrf_token'    => \App\CsrfToken::generate(), 
            'flash_message' => \App\SessionManager::getFlash('message'),
            'old_input'     => \App\SessionManager::getOldInput(),
        ];
        
        // La lógica para renderizado AJAX debe ser gestionada en el ruteador (index.php),
        // el método render() en View.php maneja el tipo de solicitud.
        
        \App\View::render('recetas/index', $viewData);
    }

    /**
     * Guarda una nueva receta y calcula el score.
     */
    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /receta/index');
            exit;
        }

        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!\App\CsrfToken::validate($csrfToken)) {
            // mensaje estático para evitar revelar detalles
            \App\SessionManager::setFlash('message', ['error' => 'Error de seguridad: Token CSRF inválido.']);
            header('Location: /receta/index');
            exit;
        }
        
        // 1. Sanitización de TODOS los inputs del usuario (XSS)
        $titulo = htmlspecialchars($_POST['titulo'] ?? '', ENT_QUOTES, 'UTF-8');
        $descripcion = htmlspecialchars($_POST['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
        $ingredientesData = $_POST['ingredientes_data'] ?? '[]'; // JSON, se validará en el modelo

        // Validación básica
        if (empty($titulo) || empty($descripcion)) {
            \App\SessionManager::setFlash('message', ['error' => 'El título y la descripción son obligatorios.']);
            \App\SessionManager::setOldInput($_POST);
            header('Location: /receta/index');
            exit;
        }
        
        // 2. Cálculo del Score (Simulado)
        // En una app real, esta lógica sería compleja
        $score = rand(1, 10);
        
        if ($this->recetaModel->saveReceta($titulo, $descripcion, $ingredientesData, $score)) {
            \App\SessionManager::setFlash('message', ['success' => 'Receta guardada y score calculado correctamente.']);
        } else {
            // Error de base de datos
            \App\SessionManager::setFlash('message', ['error' => 'Error interno al guardar la receta.']);
        }
        
        header('Location: /receta/index');
        exit;
    }

    /**
     * Desactiva una receta (Soft Delete).
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /receta/index');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        if (!$id || !\App\CsrfToken::validate($csrfToken)) {
            \App\SessionManager::setFlash('message', ['error' => 'Error de seguridad o ID inválido.']);
            header('Location: /receta/index');
            exit;
        }

        try {
            if ($this->recetaModel->deleteReceta($id)) {
                \App\SessionManager::setFlash('message', ['success' => 'Receta desactivada correctamente.']);
            } else {
                \App\SessionManager::setFlash('message', ['error' => 'No se pudo desactivar la receta (ID no encontrado).']);
            }
        } catch (\Exception $e) {
            // Se captura la excepción y se registra, pero el usuario recibe un mensaje genérico.
            error_log("Receta Delete Error: " . $e->getMessage());
            \App\SessionManager::setFlash('message', ['error' => 'Error interno del servidor al desactivar la receta.']);
        }
        
        header('Location: /receta/index');
        exit;
    }
}
