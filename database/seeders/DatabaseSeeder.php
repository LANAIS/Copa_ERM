<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Inscripcion;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CategorySeeder::class,
            CompetitionSeeder::class,
            RegistrationSeeder::class,
            EventDateSeeder::class,
            SiteConfigSeeder::class,
        ]);
        
        // Crear categorías
        $categorias = [
            [
                'nombre' => 'Fútbol RC',
                'descripcion' => 'Competencia de fútbol con robots controlados',
                'icono' => 'robot',
                'activo' => true,
                'orden' => 1,
            ],
            [
                'nombre' => 'Carrera',
                'descripcion' => 'Competencia de velocidad en pista',
                'icono' => 'flag-checkered',
                'activo' => true,
                'orden' => 2,
            ],
            [
                'nombre' => 'Sumo Autónomo',
                'descripcion' => 'Competencia de sumo con robots autónomos',
                'icono' => 'robot',
                'activo' => true,
                'orden' => 3,
            ],
            [
                'nombre' => 'MiniSumo',
                'descripcion' => 'Competencia de sumo con robots miniatura',
                'icono' => 'robot',
                'activo' => true,
                'orden' => 4,
            ],
            [
                'nombre' => 'Laberinto',
                'descripcion' => 'Competencia de resolución de laberintos',
                'icono' => 'maze',
                'activo' => true,
                'orden' => 5,
            ],
            [
                'nombre' => 'Innovación',
                'descripcion' => 'Presentación de proyectos innovadores',
                'icono' => 'lightbulb',
                'activo' => true,
                'orden' => 6,
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }

        // Ejecutar los seeders individuales
        $this->call([
            MiembroEquipoSeeder::class,
            EventoSeeder::class,
            FechaEventoSeeder::class,
            CategoriaEventoSeeder::class,
        ]);

        // Crear inscripciones de ejemplo
        for ($i = 0; $i < 20; $i++) {
            Inscripcion::create([
                'nombre' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'categoria_id' => rand(1, count($categorias)),
                'institucion' => fake()->company(),
                'telefono' => fake()->phoneNumber(),
                'notas' => fake()->paragraph(),
                'estado' => collect(['pendiente', 'aprobada', 'rechazada'])->random(),
            ]);
        }
    }
}
