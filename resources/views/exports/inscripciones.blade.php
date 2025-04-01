<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
            padding: 8px;
        }
        td {
            padding: 8px;
        }
        h1 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 22px;
        }
        h2 {
            margin-top: 30px;
            font-size: 18px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        h3 {
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 5px;
        }
        .filtros {
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .filtros strong {
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            padding: 10px 0;
        }
        .sin-inscripciones {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }
        .estado-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .estado-pendiente { background-color: #eaeaea; color: #666; }
        .estado-confirmada { background-color: #cfe2ff; color: #084298; }
        .estado-pagada { background-color: #d1e7dd; color: #0a5239; }
        .estado-rechazada { background-color: #f8d7da; color: #842029; }
        .estado-cancelada { background-color: #fff3cd; color: #664d03; }
        .estado-homologada { background-color: #d1e7dd; color: #0a5239; }
        .estado-participando { background-color: #d1e7dd; color: #0a5239; }
        .estado-finalizada { background-color: #d1e7dd; color: #0a5239; }
        .resumen {
            margin-top: 30px;
            page-break-before: always;
        }
        .stats-card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .stats-card h3 {
            margin-top: 0;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .stats-row {
            margin-bottom: 5px;
        }
        .stats-label {
            font-weight: bold;
            display: inline-block;
            width: 140px;
        }
        .stats-value {
            display: inline-block;
        }
        .stats-total {
            margin-top: 20px;
            font-weight: bold;
            font-size: 14px;
            text-align: right;
        }
    </style>
</head>
<body>
    @if($incluirTitulo)
    <h1>{{ $titulo }}</h1>
    @endif
    
    <div class="filtros">
        <strong>Filtros aplicados:</strong><br>
        <strong>Evento:</strong> {{ $filtros['evento'] ?? 'Todos los eventos' }} |
        <strong>Fecha:</strong> {{ $filtros['fecha'] ?? 'Todas las fechas' }} |
        <strong>Categoría:</strong> {{ $filtros['categoria'] ?? 'Todas las categorías' }}
    </div>
    
    @if(count($inscripciones) > 0)
    <table>
        <thead>
            <tr>
                <th>Evento</th>
                <th>Fecha</th>
                <th>Categoría</th>
                <th>Equipo</th>
                <th>Robots</th>
                <th>Estado</th>
                <th>Fecha de inscripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inscripciones as $inscripcion)
                <tr>
                    <td>{{ $inscripcion['evento'] }}</td>
                    <td>{{ $inscripcion['fecha'] }}</td>
                    <td>{{ $inscripcion['categoria'] }}</td>
                    <td>{{ $inscripcion['equipo'] }}</td>
                    <td>{{ $inscripcion['robots'] }}</td>
                    <td>
                        <span class="estado-badge estado-{{ strtolower($inscripcion['estado']) }}">
                            {{ $inscripcion['estado'] }}
                        </span>
                    </td>
                    <td>{{ $inscripcion['fecha_inscripcion'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Sección de Resumen -->
    <div class="resumen">
        <h2>Resumen de Inscripciones</h2>
        
        @if(count($estadisticas) > 0)
            @foreach($estadisticas as $categoria)
                <div class="stats-card">
                    <h3>Categoría: {{ $categoria['nombre'] }}</h3>
                    
                    <div class="stats-row">
                        <span class="stats-label">Equipos inscritos:</span>
                        <span class="stats-value">{{ $categoria['equipos'] }}</span>
                    </div>
                    
                    <div class="stats-row">
                        <span class="stats-label">Robots totales:</span>
                        <span class="stats-value">{{ $categoria['robots'] }}</span>
                    </div>
                    
                    <div class="stats-row">
                        <span class="stats-label">Robots homologados:</span>
                        <span class="stats-value">{{ $categoria['robots_homologados'] }}</span>
                    </div>
                    
                    <div class="stats-row">
                        <span class="stats-label">Robots pendientes:</span>
                        <span class="stats-value">{{ $categoria['robots_pendientes'] }}</span>
                    </div>
                    
                    <div class="stats-row">
                        <span class="stats-label">Estados:</span>
                        <span class="stats-value">
                            @foreach($categoria['estados'] as $estado => $cantidad)
                                @if($cantidad > 0)
                                    <span class="estado-badge estado-{{ $estado }}">{{ $estado }}: {{ $cantidad }}</span>
                                @endif
                            @endforeach
                        </span>
                    </div>
                </div>
            @endforeach
            
            <div class="stats-total">
                Total general: {{ $totalEquipos }} equipos, {{ $totalRobots }} robots
            </div>
        @else
            <div class="sin-inscripciones">
                No hay datos estadísticos disponibles.
            </div>
        @endif
    </div>
    @else
    <div class="sin-inscripciones">
        No se encontraron inscripciones con los filtros seleccionados.
    </div>
    @endif
    
    <div class="footer">
        Reporte generado el {{ $fechaGeneracion }} | Total de inscripciones: {{ count($inscripciones) }}
    </div>
</body>
</html> 