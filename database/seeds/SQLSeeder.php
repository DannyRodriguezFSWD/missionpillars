<?php

use Illuminate\Database\Seeder;

class SQLSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // PDO Credentials
        $db = [
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'host' => env('DB_HOST'),
            'database' => env('DB_DATABASE')
        ];
        $mysqlpath = env('MYSQL_PATH') ?: exec('which mysql');
        
        foreach (['charts','settings','statement_templates','widget-types'] as $filename) {
            $sql = base_path("database/seeds/sql/$filename.sql");
            echo "running $sql\n";
            exec("$mysqlpath --user={$db['username']} --password={$db['password']} --host={$db['host']} --database {$db['database']} < $sql");
            // DB::unprepared(file_get_contents($sql)); // unprepared doesn't work for some sql files
        }
    }
}
