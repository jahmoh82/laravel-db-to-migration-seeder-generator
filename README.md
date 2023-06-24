# laravel-db-to-migration-seeder-generator
This code is a PHP script that generates Laravel migrations and seeders for a database based on the provided configurations. The script reads the database configurations from a JSON file (database_config.json) and performs the following tasks for each database specified:

- Establishes a connection to the database using the provided server, database name, username, and password.
- Creates migration and seeder folders if they don't already exist.
- Retrieves a list of all existing migrations from the database.
- Retrieves the table names from the database.
- Generates migrations and seeders for each table.
- For each table, it generates a migration file that contains information about the table's columns and their - - - properties.
- Optionally generates a seeder file for the table if specified in the database configurations.
- Once all migrations and seeders are generated, the script prints a success message.

How to Use
Create a JSON file named database_config.json with the following structure:

```json
{
  "databases": [
    {
      "server": "database_server",
      "database": "database_name",
      "username": "database_username",
      "password": "database_password",
      "tables": [
        {
          "name": "table_name",
          "seederTable": "seeder_table_name",
          "seedRows": 10 // number of rows to fetch (0 = All)
        }
      ]
    }
  ]
}
```

Replace database_server, database_name, database_username, and database_password with the actual values for your database server.

Place the database_config.json file in the same directory as the PHP script.

Include the helpers.php file in the same directory as the PHP script. This file should contain helper functions used by the script.

Run the PHP script using a PHP server or the command line.

```bash
php generator.php
```

The script will generate migrations and seeders based on the provided configurations and print a success message upon completion.

Note: Make sure you have appropriate permissions to create directories and write files in the script's execution environment.
Dependencies

This script requires PHP and the PDO extension to connect to the database. Please ensure that PHP and the PDO extension are installed and enabled on your system.
Troubleshooting

If you encounter any errors during script execution, an error message will be displayed along with the corresponding error details. Please review the error message for troubleshooting purposes.

Ensure that the database_config.json file is properly formatted and contains valid JSON syntax.
Double-check the database configurations in the database_config.json file to ensure they are accurate and match your database setup.
