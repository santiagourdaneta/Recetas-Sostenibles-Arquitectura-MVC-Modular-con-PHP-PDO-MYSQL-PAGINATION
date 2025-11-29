<?php

use App\Controllers\RecetaController;
use App\Database;

// Definir el punto de entrada de la aplicación
define('APP_ROOT', __DIR__ . '/../');

require APP_ROOT . 'vendor/autoload.php';
// Requerir archivos de configuración y clases core 
require APP_ROOT . 'app/Database.php';
require APP_ROOT . 'app/View.php';
require APP_ROOT . 'app/SessionManager.php';
require APP_ROOT . 'app/CsrfToken.php';
require APP_ROOT . 'app/Controllers/Controller.php'; // Clase base
require APP_ROOT . 'app/Controllers/RecetaController.php'; 
require APP_ROOT . 'app/Models/Model.php';
require APP_ROOT . 'app/Models/RecetaModel.php';

// Inicialización de la sesión (primero en ejecutarse)
\App\SessionManager::start();

// Configuración de la base de datos 
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'eco_nutri',
    'user' => 'root', 
    'pass' => '',    
];
$db = new Database($dbConfig['host'], $dbConfig['dbname'], $dbConfig['user'], $dbConfig['pass']);


// --- Lógica de Ruteo Segura (Evita Path Traversal y Dynamic Eval) ---

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/', '/');
$parts = explode('/', $uri);

// 1. Sanitización y Validación del Controlador (Evita Path Traversal)
// El valor debe ser alfanumérico y se convierte a formato capitalizado (Ej: receta -> Receta)
$controllerName = trim(ucfirst(strtolower($parts[0] ?? 'Receta')));

// Whitelist / Validación estricta
if (!preg_match('/^[A-Za-z]+$/', $controllerName)) {
    // Si la entrada no es alfanumérica, forzar el controlador por defecto
    $controllerName = 'Receta';
}

$controllerClass = "App\\Controllers\\{$controllerName}Controller";

// 2. Sanitización y Validación del Método (Evita Eval-type Functions)
$methodName = trim(strtolower($parts[1] ?? 'index'));

// Whitelist / Validación estricta
if (!preg_match('/^[A-Za-z]+$/', $methodName)) {
    // Si la entrada no es alfanumérica, forzar el método por defecto
    $methodName = 'index';
}

// 3. Despacho
try {
    if (!class_exists($controllerClass)) {
        throw new \Exception("Controller not found: {$controllerClass}");
    }

    $controllerInstance = new $controllerClass($db);
    
    if (!method_exists($controllerInstance, $methodName)) {
        throw new \Exception("Method not found: {$methodName}");
    }

    // 4. Invocación Segura (Método de Clase)
    // Se invoca el método del objeto, no se usa 'eval' ni cadenas de texto sin validar
    call_user_func([$controllerInstance, $methodName]);

} catch (\Exception $e) {

    http_response_code(500);
    error_log($e->getMessage()); // Registrar error detallado
    echo "<h1>Error del Sistema</h1><p>Algo salió mal.</p>";
    
}
