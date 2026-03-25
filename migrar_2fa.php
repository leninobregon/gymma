<?php
/**
 * MIGRACIÓN: Agregar columnas 2FA a tabla usuarios
 * Ejecutar una sola vez: php migrar_2fa.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "config/Database.php";

echo "Iniciando migración 2FA...\n";

try {
    $db = (new Database())->getConnection();
    
    // Verificar si las columnas ya existen
    $stmt = $db->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('two_factor_secret', $columns)) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN two_factor_secret VARCHAR(64) DEFAULT NULL AFTER password");
        echo "✓ Columna two_factor_secret agregada\n";
    }
    
    if (!in_array('two_factor_enabled', $columns)) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0 AFTER two_factor_secret");
        echo "✓ Columna two_factor_enabled agregada\n";
    }
    
    if (!in_array('two_factor_code', $columns)) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN two_factor_code VARCHAR(10) DEFAULT NULL AFTER two_factor_enabled");
        echo "✓ Columna two_factor_code agregada\n";
    }
    
    if (!in_array('two_factor_expires', $columns)) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN two_factor_expires DATETIME DEFAULT NULL AFTER two_factor_code");
        echo "✓ Columna two_factor_expires agregada\n";
    }
    
    echo "\n✅ Migración completada exitosamente!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}