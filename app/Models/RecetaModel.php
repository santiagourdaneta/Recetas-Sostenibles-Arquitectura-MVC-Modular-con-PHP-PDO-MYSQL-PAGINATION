<?php

namespace App\Models;

use App\Database;

class RecetaModel
{
    private $db;

    // Inyección de dependencia para la conexión a la base de datos
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Obtiene un segmento de recetas activas para paginación.
     *
     * @param int $page La página actual solicitada (mínimo 1).
     * @param int $limit El número máximo de recetas por página.
     * @return array Una matriz de recetas con sus datos.
     */
    public function getRecetasPaginadas(int $page, int $limit): array
    {
        // Aseguramos que la página no sea menor a 1
        $page = max(1, $page);
        
        $offset = ($page - 1) * $limit;
        
        // La cláusula WHERE ahora es fija y solo filtra por el estado activo.
        $whereClause = ' WHERE is_active = 1 ';
        
        // NOTA: Usamos LIMIT y OFFSET para paginación. Los parámetros deben ser 'int'
        // en la consulta, pero PDO maneja los valores de forma segura en 'execute'.
        $sql = "SELECT id, titulo, descripcion, ingredientes_data, sostenibilidad_score 
                FROM recetas 
                {$whereClause} 
                ORDER BY id DESC 
                LIMIT :limit OFFSET :offset";

        $params = [
            ':limit' => $limit,
            ':offset' => $offset
        ];
        
        // Ejecuta la consulta y retorna el array de recetas
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Obtiene el número total de recetas activas para calcular la paginación.
     *
     * @return int
     */
    public function getTotalRecetas(): int
    {
        $sql = "SELECT COUNT(id) FROM recetas WHERE is_active = 1";
        // Usamos el método fetchColumn para obtener el resultado COUNT(*).
        return $this->db->fetchColumn($sql);
    }

}
