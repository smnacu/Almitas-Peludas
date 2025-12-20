<?php
require_once __DIR__ . '/includes/functions.php';

try {
    $db = Database::getConnection();
    // Check if column exists first to avoid error
    $stmt = $db->query("SHOW COLUMNS FROM usuarios LIKE 'secret_2fa'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN secret_2fa VARCHAR(255) NULL");
        echo "Columna 'secret_2fa' agregada exitosamente.";
    } else {
        echo "La columna 'secret_2fa' ya existe.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
