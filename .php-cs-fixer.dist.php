<?php

$finder = PhpCsFixer\Finder::create()
    // Indica a PHP-CS-Fixer dónde buscar los archivos PHP.
    ->in(__DIR__ . '/app')
    ->in(__DIR__ . '/public')
    // Excluye el directorio de dependencias de Composer y otros archivos generados.
    ->exclude('vendor')
    ->exclude('cache');

return (new PhpCsFixer\Config())
    // 1. Define el conjunto de reglas (ruleset) a seguir. 
    ->setRules([
        '@PSR12' => true,
        
        // La regla control_structure_braces ayuda a uniformar los espacios dentro de bloques.
        'blank_line_before_statement' => ['statements' => ['return', 'if', 'try', 'while', 'foreach', 'declare', 'throw']],
        'no_extra_blank_lines' => ['tokens' => ['extra']], // Elimina líneas vacías redundantes
        'blank_line_after_opening_tag' => false, 

        // Soluciona el problema de "mixed line endings"
        'line_ending' => true, // Asegura que solo se use el valor de setLineEnding

        // --- REGLAS DE ESTILO PERSONALIZADO ---
        'array_syntax' => ['syntax' => 'short'], // Usa [] en lugar de array()
        'ordered_imports' => ['sort_algorithm' => 'alpha'], // Ordena las sentencias use
        'strict_comparison' => false, // No forzar comparación estricta (===)
    ])
    // 2. Especifica el Finder para usar los directorios definidos arriba.
    ->setFinder($finder)
    // 3. Define la línea de fin (Crucial para arreglar 'mixed line endings')
    ->setLineEnding("\n") 
    // 4. Indica al Fixer que no debe revisar archivos que no sean PHP.
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');