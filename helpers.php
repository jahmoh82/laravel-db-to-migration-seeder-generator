<?php

class MigrationSeederHelper
{
    public static function generateMigrationContent($tableName, $columns)
    {
        $migrationContent = "<?php\n\nuse Illuminate\Database\Migrations\Migration;\nuse Illuminate\Database\Schema\Blueprint;\nuse Illuminate\Support\Facades\Schema;\n\nclass Create{$tableName}Table extends Migration\n{\n    public function up()\n    {\n        Schema::create('{$tableName}', function (Blueprint \$table) {\n";

        foreach ($columns as $column) {
            $columnName = $column['COLUMN_NAME'];
            $dataType = $column['DATA_TYPE'];
            $isNullable = $column['IS_NULLABLE'];
            $maxLength = $column['CHARACTER_MAXIMUM_LENGTH'];

            $migrationContent .= "            \$table->";

            switch ($dataType) {
                case 'bigint':
                    $migrationContent .= "bigInteger('$columnName')";
                    break;
                case 'int':
                    $migrationContent .= "integer('$columnName')";
                    break;
                case 'smallint':
                    $migrationContent .= "smallInteger('$columnName')";
                    break;
                case 'tinyint':
                    $migrationContent .= "tinyInteger('$columnName')";
                    break;
                case 'bit':
                    $migrationContent .= "boolean('$columnName')";
                    break;
                case 'decimal':
                case 'numeric':
                    $precision = isset($column['NUMERIC_PRECISION']) ? $column['NUMERIC_PRECISION'] : null;
                    $scale = isset($column['NUMERIC_SCALE']) ? $column['NUMERIC_SCALE'] : null;
                    $migrationContent .= "decimal('$columnName', $precision, $scale)";
                    break;
                case 'money':
                    $migrationContent .= "bigInteger('$columnName')";
                    break;
                case 'smallmoney':
                    $migrationContent .= "integer('$columnName')";
                    break;
                case 'float':
                    $mantisBits = isset($column['FLOAT_MANTISSA_BITS']) ? $column['FLOAT_MANTISSA_BITS'] : null;
                    $migrationContent .= "float('$columnName', $mantisBits)";
                    break;
                case 'real':
                    $migrationContent .= "float('$columnName', 24)";
                    break;
                case 'datetime':
                    $migrationContent .= "dateTime('$columnName')";
                    break;
                case 'smalldatetime':
                    $migrationContent .= "dateTime('$columnName')";
                    break;
                case 'char':
                case 'varchar':
                    $migrationContent .= "char('$columnName', $maxLength)";
                    break;
                case 'text':
                    $migrationContent .= "text('$columnName')";
                    break;
                case 'nchar':
                case 'nvarchar':
                    $migrationContent .= "string('$columnName', $maxLength)";
                    break;
                case 'ntext':
                    $migrationContent .= "text('$columnName')";
                    break;
                case 'binary':
                case 'varbinary':
                    $migrationContent .= "binary('$columnName', $maxLength)";
                    break;
                case 'image':
                    $migrationContent .= "binary('$columnName')";
                    break;
                case 'timestamp':
                    $migrationContent .= "timestamp('$columnName')";
                    break;
                case 'uniqueidentifier':
                    $migrationContent .= "uuid('$columnName')";
                    break;
            }

            if ($isNullable === 'NO') {
                $migrationContent .= "->nullable(false)";
            }

            $migrationContent .= ";\n";
        }

        $migrationContent .= "        });\n    }\n\n    public function down()\n    {\n        Schema::dropIfExists('$tableName');\n    }\n}";

        return $migrationContent;
    }

    public static function generateMigration($tableName, $migrationFolder, $migrationName, $migrationContent)
    {
        $migrationPath = $migrationFolder . '/' . $migrationName . '.php';
        file_put_contents($migrationPath, $migrationContent);
    }

    public static function getAllMigrationFileName($conn)
	{
		$query = $conn->prepare("SELECT migration FROM migrations");
		$query->execute();
		$results = $query->fetchAll(PDO::FETCH_ASSOC);
		return ($results !== false) ? $results : [];
	}
	
	public static function findOrCreateTable($array, $tableName)
	{
		foreach ($array as $row) {
			if (isset($row['migration']) && preg_match("/create_{$tableName}_table/i", $row['migration'])) {
				return $row['migration'];
			}
		}
		
		return date('Y_m_d_His') . '_create_' . $tableName . '_table';
	}

    public static function generateSeeder($tableName, $seederFolder, $seederName, $seederContent)
    {
        $seederPath = $seederFolder . '/' . $seederName . '.php';
        file_put_contents($seederPath, $seederContent);
    }
	
    public static function generateMigrationsAndSeeders($database)
    {
        // Database configurations
        $server = $database['server'];
        $databaseName = $database['database'];
        $username = $database['username'];
        $password = $database['password'];
		
        $migrationFolder = 'migration/' . $databaseName;
        $seederFolder = 'seeder/' . $databaseName;

        // Check if migration folder exists, create if not
        if (!is_dir($migrationFolder)) {
            mkdir($migrationFolder, 0755, true);
        }

        // Check if seeder folder exists, create if not
        if (!is_dir($seederFolder)) {
            mkdir($seederFolder, 0755, true);
        }

        // Connection options
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8,
        ];

        // Connect to the database
        $conn = new PDO("sqlsrv:Server=$server;Database=$databaseName", $username, $password, $options);
		
		// Get list of all migratation
		$migrationList = MigrationSeederHelper::getAllMigrationFileName($conn);

        // Get the table names from the database
        $tablesQuery = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
        $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);

        // Generate migrations and seeders for each table
        foreach ($tables as $table) {
            // Generate migration
            $columnsQuery = $conn->query("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table'");
            $columns = $columnsQuery->fetchAll(PDO::FETCH_ASSOC);

            $migrationContent = MigrationSeederHelper::generateMigrationContent($table, $columns);

            // Get migration file name from migration table if it exists
            $migrationName = MigrationSeederHelper::findOrCreateTable($migrationList, $table);
			
            MigrationSeederHelper::generateMigration($table, $migrationFolder, $migrationName, $migrationContent);

            // Generate seeder
            if (isset($database['tables']) && is_array($database['tables'])) {
                foreach ($database['tables'] as $dbTable) {
                    if ($dbTable['name'] === $table && isset($dbTable['seederTable'])) {
                        $seederTable = $dbTable['seederTable'];
                        $seedRows = isset($dbTable['seedRows']) ? intval($dbTable['seedRows']) : 1;

                        $seederName = $table . 'TableSeeder';
                        $seederContent = "<?php\n\nuse Illuminate\Database\Seeder;\n\nclass $seederName extends Seeder\n{\n    public function run()\n    {\n        factory(App\\$seederTable::class, $seedRows)->create();\n    }\n}";
                        MigrationSeederHelper::generateSeeder($table, $seederFolder, $seederName, $seederContent);

                        break;
                    }
                }
            }
        }
    }
}
