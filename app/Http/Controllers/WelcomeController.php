<?php

namespace App\Http\Controllers;

use App\Models\EventDate;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        $categorias = [
            [
                'id' => 1,
                'nombre' => 'Fútbol RC',
                'descripcion' => 'Amateur y Pro',
                'icono' => 'fas fa-futbol'
            ],
            [
                'id' => 2,
                'nombre' => 'Carrera',
                'descripcion' => 'Amateur y Pro',
                'icono' => 'fas fa-flag-checkered'
            ],
            [
                'id' => 3,
                'nombre' => 'Sumo Autónomo',
                'descripcion' => 'Amateur y Pro',
                'icono' => 'fas fa-robot'
            ],
            [
                'id' => 4,
                'nombre' => 'MiniSumo',
                'descripcion' => 'Amateur y Pro',
                'icono' => 'fas fa-microchip'
            ],
            [
                'id' => 5,
                'nombre' => 'Laberinto',
                'descripcion' => '',
                'icono' => 'fas fa-route'
            ],
            [
                'id' => 6,
                'nombre' => 'Innovación',
                'descripcion' => '',
                'icono' => 'fas fa-lightbulb'
            ]
        ];

        // Obtener fechas de eventos desde la base de datos
        $fechas = EventDate::getActive()->map(function($eventDate) {
            return [
                'numero' => $eventDate->is_final ? 'Final' : $eventDate->name,
                'localidad' => $eventDate->location,
                'fecha' => $eventDate->formatted_date,
                'categorias' => $eventDate->categories ?? ['Todas las categorías'],
                'id' => $eventDate->id, // Añadimos el ID para referencias
            ];
        })->toArray();

        // Si no hay fechas en la base de datos, usar fechas predeterminadas
        if (empty($fechas)) {
            $fechas = [
                [
                    'numero' => '1',
                    'localidad' => 'Comandante Andresito',
                    'fecha' => '13/09/2025',
                    'categorias' => ['Sumo', 'Seguidor de Línea', 'Desafío Creativo']
                ],
                [
                    'numero' => '2',
                    'localidad' => 'Posadas',
                    'fecha' => '11/10/2025',
                    'categorias' => ['Sumo', 'Seguidor de Línea', 'Programación']
                ],
                [
                    'numero' => '3',
                    'localidad' => 'Eldorado',
                    'fecha' => '15/11/2025',
                    'categorias' => ['Sumo', 'Seguidor de Línea', 'Innovación']
                ],
                [
                    'numero' => 'Final',
                    'localidad' => 'Oberá',
                    'fecha' => '13/12/2025',
                    'categorias' => ['Todas las categorías']
                ]
            ];
        }

        $redesSociales = [
            [
                'nombre' => 'Facebook',
                'url' => 'https://facebook.com/coparoboticamisiones',
                'icono' => 'fab fa-facebook'
            ],
            [
                'nombre' => 'Instagram',
                'url' => 'https://instagram.com/coparoboticamisiones',
                'icono' => 'fab fa-instagram'
            ],
            [
                'nombre' => 'Twitter',
                'url' => 'https://twitter.com/coparoboticamisiones',
                'icono' => 'fab fa-twitter'
            ],
            [
                'nombre' => 'YouTube',
                'url' => 'https://youtube.com/coparoboticamisiones',
                'icono' => 'fab fa-youtube'
            ]
        ];

        return view('welcome', compact('categorias', 'fechas', 'redesSociales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'categoria' => 'required|exists:categorias,id'
        ]);

        // Aquí iría la lógica para guardar la inscripción
        // Por ejemplo, crear un registro en la base de datos

        return redirect()->back()->with('success', '¡Gracias por tu inscripción! Nos pondremos en contacto contigo pronto.');
    }
} 