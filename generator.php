<?php

require_once 'helpers.php';

try {

    // Read the JSON file containing the database configurations
    $databaseConfigFile = 'database_config.json';
    $databaseConfig = json_decode(file_get_contents($databaseConfigFile), true);

    // Generate migrations and seeders for each database
    foreach ($databaseConfig['databases'] as $database) {
        MigrationSeederHelper::generateMigrationsAndSeeders($database);
    }

    echo "Migration and seeder generation completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
