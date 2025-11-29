<?php

// === CÓDIGO CLAVE PARA EL SERVIDOR NATIVO DE PHP ===
// Si la solicitud es para un archivo estático que existe en 'public/',
// el servidor nativo lo sirve directamente y detiene la ejecución del script PHP.
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}
// ===================================================

/**
 * Función de autocarga para mapear namespaces a rutas de archivo (PSR-4 básico manual).
 * @param string $class Nombre de la clase con namespace (ej: App\Models\RecetaModel)
 */
spl_autoload_register(function ($class) {

    // 1. Definir el prefijo del namespace de la aplicación y el directorio base.
    $prefix = 'App\\';
    $base_dir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;

    // ¿La clase usa el prefijo 'App\'?
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        // No, el autoloader no es responsable por esta clase.
        return;
    }

    // 2. Obtener el nombre de clase relativo, quitando el prefijo.
    // Ej: App\Models\RecetaModel -> Models\RecetaModel
    $relative_class = substr($class, $len);

    // 3. Crear el path de archivo:
    // Reemplaza los separadores de namespace (\) con separadores de directorio (/)
    // y añade la extensión .php.
    // Ej: $base_dir . Models/RecetaModel.php
    $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

    // 4. Incluir el archivo si existe.
    if (file_exists($file)) {
        require $file;
    }
});

// === INICIO DE SESIÓN ===
// Usamos el SessionManager para iniciar la sesión de forma controlada
\App\SessionManager::start();

// ----------------------------------------------------
// CARGA DE LA CONFIGURACIÓN DE LA BASE DE DATOS
// ----------------------------------------------------

// __DIR__ . '/..' apunta al directorio raíz del proyecto (eco-nutri-tracker)
$dbConfig = require __DIR__ . '/../db.php';

// ----------------------------------------------------
// II. ENRUTAMIENTO (Simulación)
// ----------------------------------------------------

// Simulación de enrutamiento para obtener ControllerName y ActionName
// En un sistema real, esto lo haría una clase Router
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = explode('/', $uri);

// Simulación de enrutamiento para obtener ControllerName y ActionName
// $parts[0] será una cadena vacía si la URI es solo "/"
$controllerName = ucfirst(strtolower($parts[0] ?? '')); // Primero intentamos obtenerlo

// Si no se encontró ningún controlador, usamos el por defecto 'Receta'
if (empty($controllerName)) {
    $controllerName = 'Receta';
}

$actionName = strtolower($parts[1] ?? 'index');

// ----------------------------------------------------
// III. DESPACHO (Inyección y Ejecución)
// ----------------------------------------------------

// 4. Definición de las clases y métodos
$controllerClass = "App\\Controllers\\{$controllerName}Controller";
$actionMethod = $actionName;

// 5. Verificación y Creación de Dependencias
if (!class_exists($controllerClass)) {
    // Manejo de error 404 para controladores no encontrados
    http_response_code(404);
    die("Error 404: Controlador no encontrado.");
}

// 6. CREACIÓN E INYECCIÓN DE DEPENDENCIAS

// A. Crear la conexión a la DB (App\Database)
$db = new App\Database(
    $dbConfig['host'],
    $dbConfig['dbname'],
    $dbConfig['user'],
    $dbConfig['pass']
    // El charset se pasa internamente en el constructor de Database
);

// B. Crear la instancia del Modelo, inyectando la conexión a la DB
$recetaModel = new App\Models\RecetaModel($db);

// C. Crear la instancia del Controlador, inyectando las dependencias necesarias

// Determinar qué dependencias inyectar
$dependenciesToInject = [];

if (
    $controllerClass === 'App\\Controllers\\RecetaController'
    || $controllerClass === 'App\\Controllers\\ApiController'
) {
    // Si es un controlador que usa RecetaModel (como RecetaController o ApiController), inyectamos el modelo.
    $dependenciesToInject[] = $recetaModel;
}

// Crear la instancia del controlador pasando las dependencias
$controller = new $controllerClass(...$dependenciesToInject);

// 7. Ejecutar la Acción
if (method_exists($controller, $actionMethod)) {
    $controller->$actionMethod();
} else {
    // Manejo de error 404 para métodos/acciones no encontrados
    http_response_code(404);
    die("Error 404: Método de acción no encontrado.");
}
