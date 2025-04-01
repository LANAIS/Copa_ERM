<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ShowTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'show:table {table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the structure of a specific table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tableName = $this->argument('table');
        
        if (!Schema::hasTable($tableName)) {
            $this->error("Table '{$tableName}' does not exist.");
            return 1;
        }
        
        // Obtener las columnas de la tabla
        $columns = Schema::getColumnListing($tableName);
        
        $this->info("Table: {$tableName}");
        $this->info("Columns:");
        
        foreach ($columns as $column) {
            $this->line("  - {$column}");
        }
        
        // Mostrar los índices de la tabla usando SQL nativo
        try {
            $indexes = DB::select("SHOW INDEX FROM {$tableName}");
            
            if (empty($indexes)) {
                $this->info("No indexes found for table '{$tableName}'.");
                return 0;
            }
            
            $this->info("Indexes:");
            
            $currentIndex = null;
            $columns = [];
            
            foreach ($indexes as $index) {
                if ($currentIndex !== $index->Key_name) {
                    if ($currentIndex !== null) {
                        $this->line("  - {$currentIndex}: " . implode(', ', $columns));
                        $columns = [];
                    }
                    
                    $currentIndex = $index->Key_name;
                }
                
                $columns[] = $index->Column_name;
            }
            
            if (!empty($columns)) {
                $this->line("  - {$currentIndex}: " . implode(', ', $columns));
            }
        } catch (\Exception $e) {
            $this->error("Could not get indexes: " . $e->getMessage());
        }
        
        return 0;
    }
}
