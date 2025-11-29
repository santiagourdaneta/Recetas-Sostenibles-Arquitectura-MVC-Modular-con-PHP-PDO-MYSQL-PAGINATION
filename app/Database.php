<?php

namespace App;

/**
 * Clase Wrapper para la conexión a la base de datos (PDO)
 * Asegura el uso de prepared statements para prevenir SQL Injection.
 */
class Database
{
    private \PDO $pdo;

    public function __construct(string $host, string $dbname, string $user, string $pass)
    {
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $options = [
            // Activa el modo de errores para lanzar excepciones en caso de fallo
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            // Desactiva la emulación de prepared statements para seguridad
            \PDO::ATTR_EMULATE_PREPARES   => false,
            // Establece el tipo de fetch predeterminado a un array asociativo
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ];

        try {
            $this->pdo = new \PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // Error de conexión - No se revela información sensible en el mensaje.
            // La información detallada (user/pass) solo debe estar en logs internos.
            error_log("DB Connection Error: " . $e->getMessage());
            throw new \Exception("Error al conectar con la base de datos.");
        }
    }

    /**
     * Ejecuta una consulta SELECT y retorna los resultados.
     * Utiliza Prepared Statements.
     *
     * @param string $sql La consulta SQL.
     * @param array $params Parámetros para ligar.
     * @return \PDOStatement El objeto de la sentencia PDO.
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Ejecuta una consulta INSERT, UPDATE o DELETE.
     *
     * @param string $sql La consulta SQL.
     * @param array $params Parámetros para ligar.
     * @return int El número de filas afectadas.
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
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
            // PDO fetchColumn es seguro si los parámetros están ligados.
            $result = $stmt->fetchColumn(); 
            
            return $result !== false ? $result : 0;
            
        } catch (\PDOException $e) {
            // Registrar el error sin exponer el SQL o la data sensible al usuario
            error_log("Database Query Error: " . $e->getMessage());
            return 0; 
        }
    }
}
