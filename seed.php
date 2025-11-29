<?php

/**
 * Script de Poblamiento (Seeder) para la tabla 'recetas'.
 *
 * Este script genera 100 recetas de prueba con datos aleatorios
 * y las inserta en la base de datos utilizando la clase App\Database.
 *
 * USO: php seed.php
 */

// 1. Cargar dependencias y configuración (simulación del entorno index.php)
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/Database.php';

// **NOTA IMPORTANTE:** AJUSTA ESTAS CREDENCIALES A TU CONFIGURACIÓN LOCAL DE XAMPP/MYSQL
$dbConfig = [
    'host' => 'localhost',
    'port' => 3306,
    'dbname' => 'eco_nutri', // ¡Asegúrate de que este sea el nombre correcto de tu DB!
    'user' => 'root', // Usuario común de XAMPP
    'pass' => '',    // Contraseña común de XAMPP (vacía)
];

echo "Iniciando script de poblamiento...\n";

try {
    // 2. Inicializar la conexión a la base de datos
    $db = new App\Database(
        $dbConfig['host'],
        $dbConfig['dbname'],
        $dbConfig['user'],
        $dbConfig['pass']
    );

    // 3. Preparar datos de prueba
    $recetasGeneradas = 100;
    $titles = [
        "Ensalada de Quinoa y Aguacate", "Curry de Garbanzos y Espinacas", "Sopa Detox de Lentejas",
        "Tacos de Pescado Sostenible", "Bowl de Arroz Integral y Tofu", "Smoothie Verde Matutino",
        "Pizza de Vegetales de Temporada", "Pasta Integral con Pesto Casero", "Estofado de Setas Silvestres",
        "Hummus Casero con Zanahorias", "Bandeja de Desayuno con Frutas"
    ];

    $ingredientes = [
        "1 taza de quinoa\n2 aguacates\n1 limón\nCilantro\nSal marina",
        "400g de garbanzos\n200g de espinacas\nLeche de coco\Jengibre\nPasta de curry",
        "1 taza de lentejas\n2 zanahorias\nApio\nCaldo de verduras\Pimienta negra",
        "2 filetes de pescado\nTortillas de maíz\nRepollo morado\Salsa de yogur\Limón"
    ];

    // 4. Ejecutar la inserción
    $count = 0;
    for ($i = 1; $i <= $recetasGeneradas; $i++) {
        $titulo = $titles[array_rand($titles)] . " #" . $i;
        $descripcion = "Una receta sencilla y nutritiva basada en la dieta mediterránea. Perfecta para un almuerzo rápido y ecológico.";
        $ingredientes_data = $ingredientes[array_rand($ingredientes)];
        
        // El score es semi-aleatorio para generar variación
        $score = mt_rand(6, 10); 
        $is_active = (mt_rand(1, 100) > 5) ? 1 : 0; // 5% de chance de ser inactiva

        $sql = "INSERT INTO recetas (titulo, descripcion, ingredientes_data, sostenibilidad_score, is_active) 
                VALUES (:titulo, :descripcion, :ingredientes_data, :score, :is_active)";

        $params = [
            ':titulo' => $titulo,
            ':descripcion' => $descripcion,
            ':ingredientes_data' => $ingredientes_data,
            ':score' => $score,
            ':is_active' => $is_active
        ];

        $db->execute($sql, $params);
        $count++;
    }

    echo "✅ Éxito: Se han insertado {$count} recetas de prueba correctamente.\n";

} catch (\PDOException $e) {
    echo "❌ Error de Conexión o SQL: " . $e->getMessage() . "\n";
    exit(1);
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>