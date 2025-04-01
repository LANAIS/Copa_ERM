<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckColumnType extends Command
{
    protected $signature = 'db:check-column {table} {column}';
    protected $description = 'Check the type of a column in a table';

    public function handle()
    {
        $table = $this->argument('table');
        $column = $this->argument('column');
        
        $result = DB::selectOne("
            SELECT COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_DEFAULT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ?
        ", [$table, $column]);
        
        if ($result) {
            $this->info("Column details for {$table}.{$column}:");
            $this->info("Type: {$result->COLUMN_TYPE}");
            $this->info("Max Length: " . ($result->CHARACTER_MAXIMUM_LENGTH ?? 'N/A'));
            $this->info("Nullable: {$result->IS_NULLABLE}");
            $this->info("Default: " . ($result->COLUMN_DEFAULT ?? 'NULL'));
        } else {
            $this->error("Column {$column} not found in table {$table}");
        }
        
        // Bonus: mostrar todos los valores únicos en esa columna
        $values = DB::table($table)
            ->select($column)
            ->distinct()
            ->pluck($column)
            ->toArray();
            
        $this->info("\nCurrent unique values in {$column}:");
        foreach ($values as $value) {
            $this->info("- " . ($value ?? 'NULL'));
        }
    }
} 