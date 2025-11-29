<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private $pdo;

    public function __construct(string $host, string $dbname, string $user, string $password)
    {
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Ejecuta una consulta SQL y retorna el valor de la primera columna
     * de la primera fila. Ideal para consultas COUNT, SUM, etc.
     *
     * @param string $sql La consulta SQL a ejecutar.
     * @param array $params Parámetros para la consulta preparada.
     * @return mixed El valor de la columna, o 0 si no hay resultados.
     */
    public function fetchColumn(string $sql, array $params = []): mixed
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            // Usamos fetchColumn() de PDO para obtener el valor de la primera columna (índice 0)
            $result = $stmt->fetchColumn(); 
            
            // Retornamos 0 si la consulta falló en algo o si PDO devolvió false
            return $result !== false ? $result : 0;
            
        } catch (\PDOException $e) {
            // En un entorno de producción, registraríamos el error.
            // Por ahora, lanzamos una excepción o devolvemos 0.
            error_log("Database Error: " . $e->getMessage());
            return 0; 
        }
    }

    /**
     * Ejecuta una consulta (INSERT, UPDATE, DELETE).
     * @param string $sql Consulta SQL.
     * @param array $params Parámetros para la consulta preparada.
     * @return int Número de filas afectadas.
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * Ejecuta una consulta de lectura (SELECT).
     * @param string $sql Consulta SQL.
     * @param array $params Parámetros para la consulta preparada.
     * @return object Retorna el objeto Statement para fetch.
     */
    public function query(string $sql, array $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }
}
