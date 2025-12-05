<?php
/**
 * Almitas Peludas - Configuración de Base de Datos
 * 
 * Conexión PDO robusta a MySQL con manejo de errores
 * 
 * @package AlmitasPeludas
 * @version 1.0
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'almitas_db');
define('DB_USER', 'root');        // Cambiar en producción
define('DB_PASS', '');            // Cambiar en producción
define('DB_CHARSET', 'utf8mb4');

/**
 * Clase Database - Singleton para conexión PDO
 */
class Database {
    private static ?PDO $instance = null;

    /**
     * Obtiene la instancia de conexión PDO
     * 
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // En producción, loguear el error sin exponer detalles
                error_log("Error de conexión a BD: " . $e->getMessage());
                throw new PDOException("Error de conexión a la base de datos");
            }
        }

        return self::$instance;
    }

    /**
     * Previene la clonación del objeto
     */
    private function __clone() {}

    /**
     * Previene la deserialización del objeto
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Función helper para respuestas JSON de la API
 * 
 * @param mixed $data
 * @param int $statusCode
 */
function jsonResponse($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Función helper para errores de la API
 * 
 * @param string $message
 * @param int $statusCode
 */
function jsonError(string $message, int $statusCode = 400): void {
    jsonResponse(['error' => true, 'message' => $message], $statusCode);
}

/**
 * Configura headers CORS para React
 */
function setCorsHeaders(): void {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    
    // Manejar preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/**
 * Obtiene el body JSON del request
 * 
 * @return array
 */
function getJsonInput(): array {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonError('JSON inválido en el body del request', 400);
    }
    
    return $data ?? [];
}
