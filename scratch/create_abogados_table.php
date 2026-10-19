<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nesistema', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `abogados` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `nombres` varchar(255) NOT NULL,
            `apellidos` varchar(255) NOT NULL,
            `especialidad` varchar(255) NOT NULL,
            `colegio_abogados` varchar(255) DEFAULT NULL,
            `matricula_profesional` varchar(255) DEFAULT NULL,
            `ciudad` varchar(255) NOT NULL,
            `pais` varchar(255) NOT NULL DEFAULT 'Ecuador / USA',
            `experiencia_anios` int(11) NOT NULL DEFAULT 5,
            `idiomas` varchar(255) NOT NULL DEFAULT 'Español, Inglés',
            `biografia_corta` text DEFAULT NULL,
            `foto` varchar(255) DEFAULT NULL,
            `telefono_privado` varchar(255) DEFAULT NULL,
            `email_privado` varchar(255) DEFAULT NULL,
            `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
            `destacado` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `abogados_estado_ciudad_index` (`estado`,`ciudad`),
            KEY `abogados_estado_especialidad_index` (`estado`,`especialidad`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Register in migrations table if exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'nesistema' AND table_name = 'migrations'");
    if ($stmt->fetchColumn() > 0) {
        $mName = '2026_09_22_180000_create_abogados_table';
        $check = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
        $check->execute([$mName]);
        if ($check->fetchColumn() == 0) {
            $batchStmt = $pdo->query("SELECT MAX(batch) FROM migrations");
            $maxBatch = (int)$batchStmt->fetchColumn() + 1;
            $ins = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $ins->execute([$mName, $maxBatch]);
        }
    }
    
    echo "Abogados table created and registered successfully in MySQL nesistema!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
