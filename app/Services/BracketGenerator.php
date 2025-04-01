<?php

namespace App\Services;

use App\Models\Llave;
use App\Models\Enfrentamiento;
use Illuminate\Support\Collection;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para la generación de brackets de torneos
 */
class BracketGenerator
{
    /**
     * Tipos de brackets soportados
     */
    const TIPO_ELIMINACION_DIRECTA = 'eliminacion_directa';
    const TIPO_ELIMINACION_DOBLE = 'eliminacion_doble';
    const TIPO_TODOS_CONTRA_TODOS = 'todos_contra_todos';
    const TIPO_SUIZO = 'suizo';
    const TIPO_GRUPOS = 'grupos';
    const TIPO_FASE_GRUPOS_ELIMINACION = 'fase_grupos_eliminacion';

    /**
     * Fases de un torneo
     */
    const FASE_GRUPOS = 'grupos';
    const FASE_WINNERS = 'winners';
    const FASE_LOSERS = 'losers';
    const FASE_FINAL = 'final';

    /**
     * Modelo de la llave donde se generará el bracket
     */
    protected $llave;

    /**
     * Opciones de generación
     */
    protected $options = [
        'usar_cabezas_serie' => false,
        'rondas_suizo' => 0,
        'num_grupos' => 4,
        'equipos_por_grupo' => 0,
        'equipos_clasificados' => 2,
        'seeding_manual' => [], // Array de IDs de equipos en orden de seeding
        'usar_historial' => false, // Si se debe usar el historial para seeding
        'criterios_desempate' => ['puntos', 'diferencia_goles', 'goles_favor', 'enfrentamiento_directo'], // Orden de criterios de desempate
        'puntos_victoria' => 3,
        'puntos_empate' => 1,
        'puntos_derrota' => 0,
        'clasificacion_automatica' => true, // Si se deben clasificar automáticamente los equipos después de la fase de grupos
        'usar_buchholz' => true, // Si se debe usar el sistema de Buchholz para desempates
        'buchholz_medio' => true, // Si se debe usar Buchholz medio (ignorar peor resultado)
        'emparejamiento_suizo' => 'score', // Tipo de emparejamiento: 'score' (por puntaje) o 'buchholz' (por Buchholz)
    ];

    /**
     * Constructor
     */
    public function __construct(Llave $llave)
    {
        $this->llave = $llave;
    }

    /**
     * Configurar opciones
     */
    public function withOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * Generar bracket usando una estrategia según el tipo
     * 
     * @param array $equipos IDs de los equipos
     * @param string $tipo Tipo de bracket
     * @return bool
     * @throws Exception
     */
    public function generate(array $equipos, string $tipo): bool
    {
        try {
            // Verificar que la llave tenga evento, fecha y categoría asignados
            if (!$this->llave->evento_id || !$this->llave->fecha_id || !$this->llave->categoria_evento_id) {
                throw new Exception('La llave debe tener evento, fecha y categoría asignados.');
            }
            
            // Verificar que la fecha esté activa
            if (!$this->llave->fecha->estaActiva()) {
                throw new Exception('La fecha debe estar activa para generar el bracket.');
            }
            
            // Obtener solo los equipos homologados y confirmados
            $equiposHomologados = $this->llave->obtenerEquiposDisponibles();
            
            // Verificar que haya suficientes equipos homologados
            if (count($equiposHomologados) < 2) {
                throw new Exception('Se necesitan al menos 2 equipos homologados para generar el bracket.');
            }
            
            // Filtrar los equipos para incluir solo los homologados
            $equipos = array_intersect($equipos, $equiposHomologados);
            
            // Verificar que todos los equipos estén homologados
            foreach ($equipos as $equipoId) {
                if (!$this->llave->equipoHomologado($equipoId)) {
                    throw new Exception("El equipo {$equipoId} no está homologado para esta llave.");
                }
            }
            
            // Generar el bracket según el tipo
            switch ($tipo) {
                case self::TIPO_ELIMINACION_DIRECTA:
                    $this->generarEliminacionDirecta($equipos);
                    break;
                case self::TIPO_ELIMINACION_DOBLE:
                    $this->generarEliminacionDoble($equipos);
                    break;
                case self::TIPO_TODOS_CONTRA_TODOS:
                    $this->generarTodosContraTodos($equipos);
                    break;
                case self::TIPO_SUIZO:
                    $this->generarSuizo($equipos, $this->options['rondas_suizo']);
                    break;
                case self::TIPO_GRUPOS:
                    $this->generarGrupos($equipos, $this->options['num_grupos'], false);
                    break;
                case self::TIPO_FASE_GRUPOS_ELIMINACION:
                    $this->generarGrupos($equipos, $this->options['num_grupos'], true);
                    break;
                default:
                    throw new Exception("Tipo de bracket no soportado: {$tipo}");
            }

            // Actualizar el estado de la fecha si es necesario
            if ($this->llave->fecha->estaPendiente()) {
                $this->llave->fecha->update(['estado' => 'activa']);
            }

            return true;
        } catch (Exception $e) {
            Log::error("Error generando bracket: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generar bracket de eliminación directa
     */
    protected function generarEliminacionDirecta(array $equipos): void
    {
        $totalEquipos = count($equipos);
        
        // Calcular número de rondas y enfrentamientos
        $potencia = ceil(log($totalEquipos, 2));
        $tamanoLlave = pow(2, $potencia);
        
        // Aplicar seeding si está activado
        if ($this->options['usar_cabezas_serie']) {
            $equipos = $this->aplicarSeeding($equipos, $tamanoLlave);
        } else {
            // Mezclar los equipos aleatoriamente
            shuffle($equipos);
            
            // Rellenar con nulos si hay menos equipos que plazas
            while (count($equipos) < $tamanoLlave) {
                $equipos[] = null;
            }
        }
        
        // Crear estructura de enfrentamientos para primera ronda
        $numeroJuego = 1;
        
        // Crear los enfrentamientos de la primera ronda
        for ($i = 0; $i < $tamanoLlave / 2; $i++) {
            $equipo1Idx = $i;
            $equipo2Idx = $tamanoLlave - 1 - $i;
            
            $equipo1 = isset($equipos[$equipo1Idx]) ? $equipos[$equipo1Idx] : null;
            $equipo2 = isset($equipos[$equipo2Idx]) ? $equipos[$equipo2Idx] : null;
            
            // Si hay un solo equipo, pasa automáticamente
            $ganadorId = null;
            if ($equipo1 && !$equipo2) {
                $ganadorId = $equipo1;
            } elseif (!$equipo1 && $equipo2) {
                $ganadorId = $equipo2;
            }
            
            $this->llave->enfrentamientos()->create([
                'ronda' => 1,
                'posicion' => $i + 1,
                'numero_juego' => $numeroJuego++,
                'equipo1_id' => $equipo1,
                'equipo2_id' => $equipo2,
                'ganador_id' => $ganadorId,
                'fase' => self::FASE_WINNERS
            ]);
        }
        
        // Crear enfrentamientos para las siguientes rondas
        for ($ronda = 2; $ronda <= $potencia; $ronda++) {
            $enfrentamientosPorRonda = $tamanoLlave / pow(2, $ronda);
            
            for ($i = 0; $i < $enfrentamientosPorRonda; $i++) {
                $this->llave->enfrentamientos()->create([
                    'ronda' => $ronda,
                    'posicion' => $i + 1,
                    'numero_juego' => $numeroJuego++,
                    'equipo1_id' => null,
                    'equipo2_id' => null,
                    'fase' => $ronda == $potencia ? self::FASE_FINAL : self::FASE_WINNERS
                ]);
            }
        }
        
        // Auto-avanzar los equipos que pasaron sin jugar
        $this->avanzarByesIniciales();
        
        // Actualizar estructura
        $this->llave->update([
            'tipo_fixture' => self::TIPO_ELIMINACION_DIRECTA,
            'estructura' => [
                'total_equipos' => $totalEquipos,
                'total_rondas' => $potencia,
                'tamano_llave' => $tamanoLlave,
                'total_enfrentamientos' => $numeroJuego - 1,
            ],
            'finalizado' => false,
            'estado_torneo' => Llave::ESTADO_PENDIENTE
        ]);
    }
    
    /**
     * Generar bracket de grupos con o sin fase de eliminación
     */
    protected function generarGrupos(array $equipos, int $numGrupos = 4, bool $conEliminacion = true): void
    {
        $totalEquipos = count($equipos);
        
        // Verificar que haya suficientes equipos
        if ($totalEquipos < $numGrupos) {
            $numGrupos = $totalEquipos; // Ajustar número de grupos
        }
        
        // Mezclar equipos aleatoriamente si no se usan cabezas de serie
        if (!$this->options['usar_cabezas_serie']) {
            shuffle($equipos);
        }
        
        // Distribuir equipos en grupos
        $grupos = [];
        $letrasGrupos = range('A', 'Z');
        
        // Calcular equipos por grupo (dividir de forma equitativa)
        $equiposPorGrupo = ceil($totalEquipos / $numGrupos);
        
        // Crear grupos
        for ($i = 0; $i < $numGrupos; $i++) {
            $grupos[$letrasGrupos[$i]] = [];
        }
        
        // Distribuir equipos en grupos (usando seeding para distribuir cabezas de serie)
        $grupoActual = 0;
        foreach ($equipos as $equipo) {
            $letraGrupo = $letrasGrupos[$grupoActual % $numGrupos];
            $grupos[$letraGrupo][] = $equipo;
            $grupoActual++;
        }
        
        // Crear enfrentamientos para cada grupo
        $numeroJuego = 1;
        $enfrentamientosGrupos = [];
        
        // Round Robin dentro de cada grupo
        foreach ($grupos as $grupo => $equiposGrupo) {
            $totalEquiposGrupo = count($equiposGrupo);
            
            // Generar todos los emparejamientos posibles
            for ($i = 0; $i < $totalEquiposGrupo; $i++) {
                for ($j = $i + 1; $j < $totalEquiposGrupo; $j++) {
                    $enfrentamiento = $this->llave->enfrentamientos()->create([
                        'ronda' => 1,
                        'posicion' => $numeroJuego,
                        'numero_juego' => $numeroJuego++,
                        'equipo1_id' => $equiposGrupo[$i],
                        'equipo2_id' => $equiposGrupo[$j],
                        'fase' => self::FASE_GRUPOS,
                        'grupo' => $grupo
                    ]);
                    
                    $enfrentamientosGrupos[$grupo][] = $enfrentamiento;
                }
            }
        }
        
        // Si se requiere fase de eliminación después de los grupos
        if ($conEliminacion) {
            // Preparar fase de eliminación según la cantidad de clasificados por grupo
            $clasificadosPorGrupo = $this->options['equipos_clasificados'] ?: 2;
            $totalClasificados = $numGrupos * $clasificadosPorGrupo;
            
            // Ajustar al número potencia de 2 más cercano
            $potencia = ceil(log($totalClasificados, 2));
            $tamanoLlave = pow(2, $potencia);
            
            // Crear estructura para la fase eliminatoria (sin asignar equipos aún)
            $rondaEliminatoria = 1;
            $primeraRondaEliminatoria = $numeroJuego;
            $enfrentamientosEliminatorios = [];
            
            for ($i = 0; $i < $tamanoLlave / 2; $i++) {
                $enfrentamiento = $this->llave->enfrentamientos()->create([
                    'ronda' => $rondaEliminatoria,
                    'posicion' => $i + 1,
                    'numero_juego' => $numeroJuego++,
                    'equipo1_id' => null,
                    'equipo2_id' => null,
                    'fase' => self::FASE_WINNERS
                ]);
                
                $enfrentamientosEliminatorios[$rondaEliminatoria][$i + 1] = $enfrentamiento;
            }
            
            // Rondas adicionales de eliminación
            for ($ronda = 2; $ronda <= $potencia; $ronda++) {
                $enfrentamientosPorRonda = $tamanoLlave / pow(2, $ronda);
                
                for ($i = 0; $i < $enfrentamientosPorRonda; $i++) {
                    $enfrentamiento = $this->llave->enfrentamientos()->create([
                        'ronda' => $ronda,
                        'posicion' => $i + 1,
                        'numero_juego' => $numeroJuego++,
                        'equipo1_id' => null,
                        'equipo2_id' => null,
                        'fase' => $ronda == $potencia ? self::FASE_FINAL : self::FASE_WINNERS
                    ]);
                    
                    $enfrentamientosEliminatorios[$ronda][$i + 1] = $enfrentamiento;
                }
            }
            
            // Si se requiere clasificación automática
            if ($this->options['clasificacion_automatica']) {
                $this->clasificarEquiposGrupos($enfrentamientosGrupos, $enfrentamientosEliminatorios, $clasificadosPorGrupo);
            }
        }
        
        // Actualizar estructura
        $this->llave->update([
            'tipo_fixture' => $conEliminacion ? self::TIPO_FASE_GRUPOS_ELIMINACION : self::TIPO_GRUPOS,
            'estructura' => [
                'total_equipos' => $totalEquipos,
                'num_grupos' => $numGrupos,
                'equipos_por_grupo' => $equiposPorGrupo,
                'grupos' => $grupos,
                'total_enfrentamientos' => $numeroJuego - 1,
                'clasificados_por_grupo' => $conEliminacion ? $clasificadosPorGrupo : 0,
                'primera_ronda_eliminatoria' => $conEliminacion ? $primeraRondaEliminatoria : null,
                'criterios_desempate' => $this->options['criterios_desempate'],
                'puntos' => [
                    'victoria' => $this->options['puntos_victoria'],
                    'empate' => $this->options['puntos_empate'],
                    'derrota' => $this->options['puntos_derrota']
                ]
            ],
            'finalizado' => false,
            'estado_torneo' => Llave::ESTADO_PENDIENTE
        ]);
    }
    
    /**
     * Generar bracket Round Robin (todos contra todos)
     */
    protected function generarTodosContraTodos(array $equipos): void
    {
        $totalEquipos = count($equipos);
        
        // Mezclar aleatoriamente
        shuffle($equipos);
        
        $numeroJuego = 1;
        
        // Generar todos los enfrentamientos
        for ($i = 0; $i < $totalEquipos; $i++) {
            for ($j = $i + 1; $j < $totalEquipos; $j++) {
                $this->llave->enfrentamientos()->create([
                    'ronda' => 1,
                    'posicion' => $numeroJuego,
                    'numero_juego' => $numeroJuego++,
                    'equipo1_id' => $equipos[$i],
                    'equipo2_id' => $equipos[$j],
                    'fase' => self::FASE_WINNERS
                ]);
            }
        }
        
        // Actualizar estructura
        $this->llave->update([
            'tipo_fixture' => self::TIPO_TODOS_CONTRA_TODOS,
            'estructura' => [
                'total_equipos' => $totalEquipos,
                'total_enfrentamientos' => $numeroJuego - 1,
            ],
            'finalizado' => false,
            'estado_torneo' => Llave::ESTADO_PENDIENTE
        ]);
    }
    
    /**
     * Generar sistema suizo
     */
    protected function generarSuizo(array $equipos, int $rondas = 0): void
    {
        $totalEquipos = count($equipos);
        
        // Determinar número de rondas si no se especificó
        if ($rondas <= 0) {
            // En sistema suizo, el número recomendado de rondas es log2(N)
            $rondas = ceil(log($totalEquipos, 2));
        }
        
        // Mezclar equipos y generar primera ronda
        shuffle($equipos);
        
        $numeroJuego = 1;
        $enfrentamientosRonda = [];
        
        // Crear enfrentamientos para la primera ronda
        for ($i = 0; $i < floor($totalEquipos / 2); $i++) {
            $equipo1 = $equipos[$i * 2];
            $equipo2 = isset($equipos[$i * 2 + 1]) ? $equipos[$i * 2 + 1] : null;
            
            // Si hay número impar, el último queda libre (bye)
            if ($equipo2 === null) {
                continue;
            }
            
            $enfrentamiento = $this->llave->enfrentamientos()->create([
                'ronda' => 1,
                'posicion' => $i + 1,
                'numero_juego' => $numeroJuego++,
                'equipo1_id' => $equipo1,
                'equipo2_id' => $equipo2,
                'fase' => self::FASE_WINNERS
            ]);
            
            $enfrentamientosRonda[1][] = $enfrentamiento;
        }
        
        // Generar enfrentamientos para las rondas restantes
        for ($ronda = 2; $ronda <= $rondas; $ronda++) {
            // Obtener resultados y estadísticas de la ronda anterior
            $resultados = $this->calcularResultadosSuizo($enfrentamientosRonda[$ronda - 1]);
            
            // Ordenar equipos por puntaje y Buchholz si está activado
            $equiposOrdenados = $this->ordenarEquiposSuizo($resultados);
            
            // Emparejar equipos
            $enfrentamientosRonda[$ronda] = $this->emparejarEquiposSuizo($equiposOrdenados, $ronda);
        }
        
        // Actualizar estructura
        $this->llave->update([
            'tipo_fixture' => self::TIPO_SUIZO,
            'estructura' => [
                'total_equipos' => $totalEquipos,
                'total_rondas' => $rondas,
                'ronda_actual' => 1,
                'total_enfrentamientos' => $numeroJuego - 1,
                'configuracion' => [
                    'usar_buchholz' => $this->options['usar_buchholz'],
                    'buchholz_medio' => $this->options['buchholz_medio'],
                    'emparejamiento' => $this->options['emparejamiento_suizo']
                ]
            ],
            'finalizado' => false,
            'estado_torneo' => Llave::ESTADO_PENDIENTE
        ]);
    }

    /**
     * Calcular resultados del sistema suizo
     */
    protected function calcularResultadosSuizo(array $enfrentamientos): array
    {
        $resultados = [];
        
        foreach ($enfrentamientos as $enfrentamiento) {
            // Procesar equipo 1
            if (!isset($resultados[$enfrentamiento->equipo1_id])) {
                $resultados[$enfrentamiento->equipo1_id] = [
                    'equipo_id' => $enfrentamiento->equipo1_id,
                    'puntos' => 0,
                    'partidos_jugados' => 0,
                    'victorias' => 0,
                    'empates' => 0,
                    'derrotas' => 0,
                    'goles_favor' => 0,
                    'goles_contra' => 0,
                    'diferencia_goles' => 0,
                    'oponentes' => [],
                    'buchholz' => 0,
                    'buchholz_medio' => 0
                ];
            }
            
            // Procesar equipo 2
            if (!isset($resultados[$enfrentamiento->equipo2_id])) {
                $resultados[$enfrentamiento->equipo2_id] = [
                    'equipo_id' => $enfrentamiento->equipo2_id,
                    'puntos' => 0,
                    'partidos_jugados' => 0,
                    'victorias' => 0,
                    'empates' => 0,
                    'derrotas' => 0,
                    'goles_favor' => 0,
                    'goles_contra' => 0,
                    'diferencia_goles' => 0,
                    'oponentes' => [],
                    'buchholz' => 0,
                    'buchholz_medio' => 0
                ];
            }
            
            // Actualizar estadísticas
            $resultados[$enfrentamiento->equipo1_id]['partidos_jugados']++;
            $resultados[$enfrentamiento->equipo2_id]['partidos_jugados']++;
            
            if ($enfrentamiento->ganador_id) {
                if ($enfrentamiento->ganador_id == $enfrentamiento->equipo1_id) {
                    $resultados[$enfrentamiento->equipo1_id]['victorias']++;
                    $resultados[$enfrentamiento->equipo1_id]['puntos'] += $this->options['puntos_victoria'];
                    $resultados[$enfrentamiento->equipo2_id]['derrotas']++;
                    $resultados[$enfrentamiento->equipo2_id]['puntos'] += $this->options['puntos_derrota'];
                } else {
                    $resultados[$enfrentamiento->equipo2_id]['victorias']++;
                    $resultados[$enfrentamiento->equipo2_id]['puntos'] += $this->options['puntos_victoria'];
                    $resultados[$enfrentamiento->equipo1_id]['derrotas']++;
                    $resultados[$enfrentamiento->equipo1_id]['puntos'] += $this->options['puntos_derrota'];
                }
            } else {
                $resultados[$enfrentamiento->equipo1_id]['empates']++;
                $resultados[$enfrentamiento->equipo2_id]['empates']++;
                $resultados[$enfrentamiento->equipo1_id]['puntos'] += $this->options['puntos_empate'];
                $resultados[$enfrentamiento->equipo2_id]['puntos'] += $this->options['puntos_empate'];
            }
            
            // Actualizar goles
            $resultados[$enfrentamiento->equipo1_id]['goles_favor'] += $enfrentamiento->puntaje_equipo1 ?? 0;
            $resultados[$enfrentamiento->equipo1_id]['goles_contra'] += $enfrentamiento->puntaje_equipo2 ?? 0;
            $resultados[$enfrentamiento->equipo2_id]['goles_favor'] += $enfrentamiento->puntaje_equipo2 ?? 0;
            $resultados[$enfrentamiento->equipo2_id]['goles_contra'] += $enfrentamiento->puntaje_equipo1 ?? 0;
            
            // Actualizar diferencia de goles
            $resultados[$enfrentamiento->equipo1_id]['diferencia_goles'] = 
                $resultados[$enfrentamiento->equipo1_id]['goles_favor'] - 
                $resultados[$enfrentamiento->equipo1_id]['goles_contra'];
            $resultados[$enfrentamiento->equipo2_id]['diferencia_goles'] = 
                $resultados[$enfrentamiento->equipo2_id]['goles_favor'] - 
                $resultados[$enfrentamiento->equipo2_id]['goles_contra'];
            
            // Registrar oponentes para Buchholz
            $resultados[$enfrentamiento->equipo1_id]['oponentes'][] = [
                'equipo_id' => $enfrentamiento->equipo2_id,
                'puntos' => $resultados[$enfrentamiento->equipo2_id]['puntos']
            ];
            $resultados[$enfrentamiento->equipo2_id]['oponentes'][] = [
                'equipo_id' => $enfrentamiento->equipo1_id,
                'puntos' => $resultados[$enfrentamiento->equipo1_id]['puntos']
            ];
        }
        
        // Calcular Buchholz
        foreach ($resultados as &$resultado) {
            // Buchholz total
            $resultado['buchholz'] = array_sum(array_column($resultado['oponentes'], 'puntos'));
            
            // Buchholz medio (ignorar peor resultado)
            if ($this->options['buchholz_medio'] && count($resultado['oponentes']) > 1) {
                $puntos = array_column($resultado['oponentes'], 'puntos');
                sort($puntos);
                array_shift($puntos); // Eliminar peor resultado
                $resultado['buchholz_medio'] = array_sum($puntos);
            } else {
                $resultado['buchholz_medio'] = $resultado['buchholz'];
            }
        }
        
        return array_values($resultados);
    }

    /**
     * Ordenar equipos en sistema suizo
     */
    protected function ordenarEquiposSuizo(array $resultados): array
    {
        usort($resultados, function($a, $b) {
            // Primero por puntos
            if ($b['puntos'] !== $a['puntos']) {
                return $b['puntos'] <=> $a['puntos'];
            }
            
            // Si hay empate y Buchholz está activado
            if ($this->options['usar_buchholz']) {
                if ($this->options['buchholz_medio']) {
                    if ($b['buchholz_medio'] !== $a['buchholz_medio']) {
                        return $b['buchholz_medio'] <=> $a['buchholz_medio'];
                    }
                } else {
                    if ($b['buchholz'] !== $a['buchholz']) {
                        return $b['buchholz'] <=> $a['buchholz'];
                    }
                }
            }
            
            // Si sigue el empate, usar diferencia de goles
            return $b['diferencia_goles'] <=> $a['diferencia_goles'];
        });
        
        return $resultados;
    }

    /**
     * Emparejar equipos en sistema suizo
     */
    protected function emparejarEquiposSuizo(array $equipos, int $ronda): array
    {
        $enfrentamientos = [];
        $numeroJuego = $this->llave->enfrentamientos()->count() + 1;
        $emparejados = [];
        
        // Si hay número impar de equipos, el último queda libre
        $totalEquipos = count($equipos);
        $emparejarHasta = $totalEquipos - ($totalEquipos % 2);
        
        for ($i = 0; $i < $emparejarHasta; $i++) {
            if (isset($emparejados[$i])) continue;
            
            // Buscar el mejor oponente disponible
            $mejorOponente = null;
            $mejorDiferencia = PHP_INT_MAX;
            
            for ($j = $i + 1; $j < $totalEquipos; $j++) {
                if (isset($emparejados[$j])) continue;
                
                // Verificar si ya se enfrentaron
                if ($this->yaSeEnfrentaron($equipos[$i]['equipo_id'], $equipos[$j]['equipo_id'])) {
                    continue;
                }
                
                // Calcular diferencia de puntos
                $diferencia = abs($equipos[$i]['puntos'] - $equipos[$j]['puntos']);
                
                if ($diferencia < $mejorDiferencia) {
                    $mejorDiferencia = $diferencia;
                    $mejorOponente = $j;
                }
            }
            
            // Si se encontró un oponente
            if ($mejorOponente !== null) {
                $enfrentamiento = $this->llave->enfrentamientos()->create([
                    'ronda' => $ronda,
                    'posicion' => count($enfrentamientos) + 1,
                    'numero_juego' => $numeroJuego++,
                    'equipo1_id' => $equipos[$i]['equipo_id'],
                    'equipo2_id' => $equipos[$mejorOponente]['equipo_id'],
                    'fase' => self::FASE_WINNERS
                ]);
                
                $enfrentamientos[] = $enfrentamiento;
                $emparejados[$i] = true;
                $emparejados[$mejorOponente] = true;
            }
        }
        
        return $enfrentamientos;
    }

    /**
     * Verificar si dos equipos ya se enfrentaron
     */
    protected function yaSeEnfrentaron(int $equipo1Id, int $equipo2Id): bool
    {
        return $this->llave->enfrentamientos()
            ->where(function($query) use ($equipo1Id, $equipo2Id) {
                $query->where(function($q) use ($equipo1Id, $equipo2Id) {
                    $q->where('equipo1_id', $equipo1Id)
                      ->where('equipo2_id', $equipo2Id);
                })->orWhere(function($q) use ($equipo1Id, $equipo2Id) {
                    $q->where('equipo1_id', $equipo2Id)
                      ->where('equipo2_id', $equipo1Id);
                });
            })
            ->exists();
    }
    
    /**
     * Generar bracket de doble eliminación
     */
    protected function generarEliminacionDoble(array $equipos): void
    {
        $totalEquipos = count($equipos);
        
        // Calcular número de rondas y enfrentamientos
        $potencia = ceil(log($totalEquipos, 2));
        $tamanoLlave = pow(2, $potencia);
        
        // Aplicar seeding si está activado
        if ($this->options['usar_cabezas_serie']) {
            $equipos = $this->aplicarSeeding($equipos, $tamanoLlave);
        } else {
            // Mezclar los equipos aleatoriamente
            shuffle($equipos);
            
            // Rellenar con nulos si hay menos equipos que plazas
            while (count($equipos) < $tamanoLlave) {
                $equipos[] = null;
            }
        }
        
        $numeroJuego = 1;
        $enfrentamientosWinners = [];
        $enfrentamientosLosers = [];
        
        // Fase de ganadores (winners bracket)
        // Primera ronda
        for ($i = 0; $i < $tamanoLlave / 2; $i++) {
            $equipo1Idx = $i;
            $equipo2Idx = $tamanoLlave - 1 - $i;
            
            $equipo1 = isset($equipos[$equipo1Idx]) ? $equipos[$equipo1Idx] : null;
            $equipo2 = isset($equipos[$equipo2Idx]) ? $equipos[$equipo2Idx] : null;
            
            // Si hay un solo equipo, pasa automáticamente
            $ganadorId = null;
            if ($equipo1 && !$equipo2) {
                $ganadorId = $equipo1;
            } elseif (!$equipo1 && $equipo2) {
                $ganadorId = $equipo2;
            }
            
            $enfrentamiento = $this->llave->enfrentamientos()->create([
                'ronda' => 1,
                'posicion' => $i + 1,
                'numero_juego' => $numeroJuego++,
                'equipo1_id' => $equipo1,
                'equipo2_id' => $equipo2,
                'ganador_id' => $ganadorId,
                'fase' => self::FASE_WINNERS
            ]);
            
            $enfrentamientosWinners[1][$i + 1] = $enfrentamiento;
        }
        
        // Rondas adicionales winners bracket
        for ($ronda = 2; $ronda <= $potencia; $ronda++) {
            $enfrentamientosPorRonda = $tamanoLlave / pow(2, $ronda);
            
            for ($i = 0; $i < $enfrentamientosPorRonda; $i++) {
                $enfrentamiento = $this->llave->enfrentamientos()->create([
                    'ronda' => $ronda,
                    'posicion' => $i + 1,
                    'numero_juego' => $numeroJuego++,
                    'equipo1_id' => null,
                    'equipo2_id' => null,
                    'fase' => self::FASE_WINNERS
                ]);
                
                $enfrentamientosWinners[$ronda][$i + 1] = $enfrentamiento;
            }
        }
        
        // Fase de perdedores (losers bracket)
        $maxRondasPerdedores = 2 * $potencia - 1;
        
        // Crear estructura para brackets de perdedores
        for ($ronda = 1; $ronda <= $maxRondasPerdedores; $ronda++) {
            $enfrentamientosPorRonda = $this->calcularEnfrentamientosRondaPerdedores($ronda, $potencia);
            
            for ($i = 0; $i < $enfrentamientosPorRonda; $i++) {
                $enfrentamiento = $this->llave->enfrentamientos()->create([
                    'ronda' => $ronda,
                    'posicion' => $i + 1,
                    'numero_juego' => $numeroJuego++,
                    'equipo1_id' => null,
                    'equipo2_id' => null,
                    'fase' => self::FASE_LOSERS
                ]);
                
                $enfrentamientosLosers[$ronda][$i + 1] = $enfrentamiento;
            }
        }
        
        // Gran Final (mejor de 2)
        $final1 = $this->llave->enfrentamientos()->create([
            'ronda' => 1,
            'posicion' => 1,
            'numero_juego' => $numeroJuego++,
            'equipo1_id' => null, // Ganador de winners
            'equipo2_id' => null, // Ganador de losers
            'fase' => self::FASE_FINAL
        ]);
        
        // Segunda final (solo si es necesaria)
        $final2 = $this->llave->enfrentamientos()->create([
            'ronda' => 2,
            'posicion' => 1,
            'numero_juego' => $numeroJuego++,
            'equipo1_id' => null,
            'equipo2_id' => null,
            'fase' => self::FASE_FINAL
        ]);
        
        // Configurar conexiones entre brackets
        $this->configurarConexionesBrackets($enfrentamientosWinners, $enfrentamientosLosers, $final1, $final2);
        
        // Auto-avanzar los equipos que pasaron sin jugar
        $this->avanzarByesIniciales();
        
        // Actualizar estructura
        $this->llave->update([
            'tipo_fixture' => self::TIPO_ELIMINACION_DOBLE,
            'estructura' => [
                'total_equipos' => $totalEquipos,
                'total_rondas_winners' => $potencia,
                'total_rondas_losers' => $maxRondasPerdedores,
                'tamano_llave' => $tamanoLlave,
                'total_enfrentamientos' => $numeroJuego - 1,
                'conexiones' => [
                    'winners_to_losers' => $this->generarMapaConexionesWinnersToLosers($potencia),
                    'losers_to_final' => $this->generarMapaConexionesLosersToFinal($potencia)
                ]
            ],
            'finalizado' => false,
            'estado_torneo' => Llave::ESTADO_PENDIENTE
        ]);
    }

    /**
     * Configurar conexiones entre brackets de ganadores y perdedores
     */
    protected function configurarConexionesBrackets(array $winners, array $losers, Enfrentamiento $final1, Enfrentamiento $final2): void
    {
        $potencia = count($winners);
        
        // Conexiones de winners a losers
        foreach ($winners as $ronda => $enfrentamientos) {
            foreach ($enfrentamientos as $posicion => $enfrentamiento) {
                // Los perdedores de winners van a diferentes rondas de losers
                $rondaLosers = $this->calcularRondaLosers($ronda, $potencia);
                $posicionLosers = $this->calcularPosicionLosers($ronda, $posicion, $potencia);
                
                if (isset($losers[$rondaLosers][$posicionLosers])) {
                    $enfrentamiento->enfrentamiento_perdedor_id = $losers[$rondaLosers][$posicionLosers]->id;
                    $enfrentamiento->save();
                }
            }
        }
        
        // Conexiones de losers a final
        $ultimaRondaLosers = max(array_keys($losers));
        $ultimoEnfrentamientoLosers = end($losers[$ultimaRondaLosers]);
        
        if ($ultimoEnfrentamientoLosers) {
            $ultimoEnfrentamientoLosers->enfrentamiento_siguiente_id = $final1->id;
            $ultimoEnfrentamientoLosers->save();
        }
        
        // Conexión entre finales
        $final1->enfrentamiento_siguiente_id = $final2->id;
        $final1->save();
    }

    /**
     * Calcular a qué ronda de losers va un perdedor de winners
     */
    protected function calcularRondaLosers(int $rondaWinners, int $potencia): int
    {
        if ($rondaWinners == 1) {
            return 1; // Primera ronda losers
        }
        
        // Los perdedores de ronda N de winners van a ronda 2N-1 de losers
        return 2 * $rondaWinners - 1;
    }

    /**
     * Calcular la posición en la ronda de losers
     */
    protected function calcularPosicionLosers(int $rondaWinners, int $posicionWinners, int $potencia): int
    {
        if ($rondaWinners == 1) {
            return ceil($posicionWinners / 2); // Primera ronda losers
        }
        
        // La posición en losers depende de la posición en winners
        return ceil($posicionWinners / 2);
    }

    /**
     * Generar mapa de conexiones de winners a losers
     */
    protected function generarMapaConexionesWinnersToLosers(int $potencia): array
    {
        $mapa = [];
        
        for ($ronda = 1; $ronda <= $potencia; $ronda++) {
            $rondaLosers = $this->calcularRondaLosers($ronda, $potencia);
            $mapa[$ronda] = $rondaLosers;
        }
        
        return $mapa;
    }

    /**
     * Generar mapa de conexiones de losers a final
     */
    protected function generarMapaConexionesLosersToFinal(int $potencia): array
    {
        $maxRondasPerdedores = 2 * $potencia - 1;
        return [
            'ronda_final' => $maxRondasPerdedores,
            'posicion_final' => 1
        ];
    }

    /**
     * Calcular el número de enfrentamientos para cada ronda de perdedores en doble eliminación
     */
    protected function calcularEnfrentamientosRondaPerdedores(int $ronda, int $potencia): int
    {
        if ($ronda == 1) {
            return pow(2, $potencia - 2); // Primera ronda losers
        } elseif ($ronda <= $potencia) {
            return pow(2, $potencia - ceil($ronda / 2));
        } else {
            $maxRondasPerdedores = 2 * $potencia - 1;
            return pow(2, ceil(($maxRondasPerdedores - $ronda + 1) / 2));
        }
    }
    
    /**
     * Aplicar seeding a los equipos (ordenamiento estratégico)
     */
    protected function aplicarSeeding(array $equipos, int $tamanoLlave): array
    {
        // Si hay seeding manual definido, usarlo
        if (!empty($this->options['seeding_manual'])) {
            return $this->aplicarSeedingManual($equipos, $tamanoLlave);
        }
        
        // Si se debe usar historial, ordenar por historial
        if ($this->options['usar_historial']) {
            $equipos = $this->ordenarPorHistorial($equipos);
        }
        
        // Obtener posiciones de seeding
        $posiciones = $this->calcularPosicionesSeeding($tamanoLlave);
        
        // Crear array con las posiciones
        $result = array_fill(0, $tamanoLlave, null);
        
        // Colocar equipos según seeding
        for ($i = 0; $i < min(count($equipos), $tamanoLlave); $i++) {
            $result[$posiciones[$i] - 1] = $equipos[$i];
        }
        
        return $result;
    }

    /**
     * Aplicar seeding manual definido por el usuario
     */
    protected function aplicarSeedingManual(array $equipos, int $tamanoLlave): array
    {
        $result = array_fill(0, $tamanoLlave, null);
        $posiciones = $this->calcularPosicionesSeeding($tamanoLlave);
        
        // Crear un mapeo de ID de equipo a posición en el array original
        $equiposMap = array_flip($equipos);
        
        // Colocar equipos según el orden manual definido
        foreach ($this->options['seeding_manual'] as $index => $equipoId) {
            if (isset($equiposMap[$equipoId])) {
                $posicionOriginal = $equiposMap[$equipoId];
                $result[$posiciones[$index] - 1] = $equipos[$posicionOriginal];
            }
        }
        
        // Rellenar las posiciones restantes con los equipos no asignados
        $equiposRestantes = array_values(array_filter($equipos, function($equipoId) {
            return !in_array($equipoId, $this->options['seeding_manual']);
        }));
        
        $indexRestantes = 0;
        for ($i = 0; $i < $tamanoLlave; $i++) {
            if ($result[$i] === null && isset($equiposRestantes[$indexRestantes])) {
                $result[$i] = $equiposRestantes[$indexRestantes++];
            }
        }
        
        return $result;
    }

    /**
     * Ordenar equipos por historial de rendimiento
     */
    protected function ordenarPorHistorial(array $equipos): array
    {
        $equiposConHistorial = [];
        
        foreach ($equipos as $equipoId) {
            $equipo = \App\Models\Equipo::find($equipoId);
            if (!$equipo) continue;
            
            // Calcular puntaje basado en historial
            $puntaje = $this->calcularPuntajeHistorial($equipo);
            
            $equiposConHistorial[] = [
                'id' => $equipoId,
                'puntaje' => $puntaje
            ];
        }
        
        // Ordenar por puntaje de mayor a menor
        usort($equiposConHistorial, function($a, $b) {
            return $b['puntaje'] <=> $a['puntaje'];
        });
        
        // Retornar solo los IDs en orden
        return array_column($equiposConHistorial, 'id');
    }

    /**
     * Calcular puntaje de historial para un equipo
     */
    protected function calcularPuntajeHistorial(\App\Models\Equipo $equipo): float
    {
        $puntaje = 0;
        
        // Obtener últimos 5 torneos del equipo
        $ultimosTorneos = $equipo->llaves()
            ->where('finalizado', true)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        foreach ($ultimosTorneos as $torneo) {
            // Puntos por posición final
            $posicion = $this->obtenerPosicionFinal($equipo, $torneo);
            $puntaje += $this->calcularPuntosPorPosicion($posicion);
            
            // Puntos por victorias
            $victorias = $torneo->enfrentamientos()
                ->where('ganador_id', $equipo->id)
                ->count();
            $puntaje += $victorias * 0.5;
            
            // Puntos por enfrentamientos jugados
            $totalEnfrentamientos = $torneo->enfrentamientos()
                ->where(function($query) use ($equipo) {
                    $query->where('equipo1_id', $equipo->id)
                          ->orWhere('equipo2_id', $equipo->id);
                })
                ->count();
            $puntaje += $totalEnfrentamientos * 0.2;
        }
        
        return $puntaje;
    }

    /**
     * Obtener posición final de un equipo en un torneo
     */
    protected function obtenerPosicionFinal(\App\Models\Equipo $equipo, \App\Models\Llave $torneo): ?int
    {
        // Buscar el último enfrentamiento del equipo
        $ultimoEnfrentamiento = $torneo->enfrentamientos()
            ->where(function($query) use ($equipo) {
                $query->where('equipo1_id', $equipo->id)
                      ->orWhere('equipo2_id', $equipo->id);
            })
            ->orderBy('ronda', 'desc')
            ->orderBy('posicion', 'desc')
            ->first();
        
        if (!$ultimoEnfrentamiento) return null;
        
        // Si es el ganador del último enfrentamiento, es el campeón
        if ($ultimoEnfrentamiento->ganador_id === $equipo->id) {
            return 1;
        }
        
        // Si es el perdedor del último enfrentamiento, es subcampeón
        if ($ultimoEnfrentamiento->fase === \App\Models\Llave::FASE_FINAL) {
            return 2;
        }
        
        // Para otras posiciones, usar la ronda y posición
        return $ultimoEnfrentamiento->ronda * 100 + $ultimoEnfrentamiento->posicion;
    }

    /**
     * Calcular puntos por posición final
     */
    protected function calcularPuntosPorPosicion(?int $posicion): float
    {
        if (!$posicion) return 0;
        
        $puntos = [
            1 => 10, // Campeón
            2 => 8,  // Subcampeón
            3 => 6,  // Tercero
            4 => 4,  // Cuarto
            5 => 2,  // Quinto
            6 => 1,  // Sexto
            7 => 0.5, // Séptimo
            8 => 0.25 // Octavo
        ];
        
        return $puntos[$posicion] ?? 0;
    }
    
    /**
     * Calcular posiciones de seeding para brackets
     */
    protected function calcularPosicionesSeeding(int $tamanoLlave): array
    {
        $posiciones = [];
        
        for ($i = 1; $i <= $tamanoLlave; $i++) {
            $posiciones[] = $this->calcularPosicionSeeding($i, $tamanoLlave);
        }
        
        return $posiciones;
    }
    
    /**
     * Calcular la posición específica de un seed
     */
    protected function calcularPosicionSeeding(int $seed, int $tamanoLlave): int
    {
        if ($tamanoLlave == 1) {
            return 1;
        }
        
        $mitad = $tamanoLlave / 2;
        
        if ($seed <= $mitad) {
            return 2 * $this->calcularPosicionSeeding($seed, $mitad) - 1;
        } else {
            return 2 * $this->calcularPosicionSeeding($seed - $mitad, $mitad);
        }
    }
    
    /**
     * Avanzar automáticamente equipos que obtuvieron "bye" en primera ronda
     */
    protected function avanzarByesIniciales(): void
    {
        // Obtener enfrentamientos de primera ronda que tienen ganador automático
        $enfrentamientos = $this->llave->enfrentamientos()
            ->where('ronda', 1)
            ->where('fase', self::FASE_WINNERS)
            ->whereNotNull('ganador_id')
            ->get();
        
        foreach ($enfrentamientos as $enfrentamiento) {
            $this->llave->avanzarGanador($enfrentamiento);
        }
    }

    /**
     * Clasificar equipos de grupos a fase eliminatoria
     */
    protected function clasificarEquiposGrupos(array $enfrentamientosGrupos, array $enfrentamientosEliminatorios, int $clasificadosPorGrupo): void
    {
        $resultadosGrupos = [];
        
        // Calcular resultados de cada grupo
        foreach ($enfrentamientosGrupos as $grupo => $enfrentamientos) {
            $resultadosGrupos[$grupo] = $this->calcularResultadosGrupo($enfrentamientos);
        }
        
        // Ordenar equipos por criterios de desempate
        foreach ($resultadosGrupos as $grupo => $resultados) {
            $resultadosGrupos[$grupo] = $this->ordenarPorCriteriosDesempate($resultados);
        }
        
        // Asignar equipos clasificados a enfrentamientos eliminatorios
        $enfrentamientoActual = 1;
        foreach ($resultadosGrupos as $grupo => $resultados) {
            // Tomar los primeros N equipos de cada grupo
            for ($i = 0; $i < $clasificadosPorGrupo && $i < count($resultados); $i++) {
                $equipo = $resultados[$i]['equipo_id'];
                
                // Asignar equipo al enfrentamiento correspondiente
                if (isset($enfrentamientosEliminatorios[1][$enfrentamientoActual])) {
                    $enfrentamiento = $enfrentamientosEliminatorios[1][$enfrentamientoActual];
                    
                    // Alternar entre equipo1 y equipo2
                    if ($enfrentamiento->equipo1_id === null) {
                        $enfrentamiento->equipo1_id = $equipo;
                    } else {
                        $enfrentamiento->equipo2_id = $equipo;
                    }
                    
                    $enfrentamiento->save();
                    
                    // Si ambos equipos están asignados, pasar al siguiente enfrentamiento
                    if ($enfrentamiento->equipo1_id !== null && $enfrentamiento->equipo2_id !== null) {
                        $enfrentamientoActual++;
                    }
                }
            }
        }
    }

    /**
     * Calcular resultados de un grupo
     */
    protected function calcularResultadosGrupo(array $enfrentamientos): array
    {
        $resultados = [];
        
        foreach ($enfrentamientos as $enfrentamiento) {
            // Procesar equipo 1
            if (!isset($resultados[$enfrentamiento->equipo1_id])) {
                $resultados[$enfrentamiento->equipo1_id] = [
                    'equipo_id' => $enfrentamiento->equipo1_id,
                    'puntos' => 0,
                    'partidos_jugados' => 0,
                    'victorias' => 0,
                    'empates' => 0,
                    'derrotas' => 0,
                    'goles_favor' => 0,
                    'goles_contra' => 0,
                    'diferencia_goles' => 0,
                    'enfrentamientos_directos' => []
                ];
            }
            
            // Procesar equipo 2
            if (!isset($resultados[$enfrentamiento->equipo2_id])) {
                $resultados[$enfrentamiento->equipo2_id] = [
                    'equipo_id' => $enfrentamiento->equipo2_id,
                    'puntos' => 0,
                    'partidos_jugados' => 0,
                    'victorias' => 0,
                    'empates' => 0,
                    'derrotas' => 0,
                    'goles_favor' => 0,
                    'goles_contra' => 0,
                    'diferencia_goles' => 0,
                    'enfrentamientos_directos' => []
                ];
            }
            
            // Actualizar estadísticas
            $resultados[$enfrentamiento->equipo1_id]['partidos_jugados']++;
            $resultados[$enfrentamiento->equipo2_id]['partidos_jugados']++;
            
            if ($enfrentamiento->ganador_id) {
                if ($enfrentamiento->ganador_id == $enfrentamiento->equipo1_id) {
                    $resultados[$enfrentamiento->equipo1_id]['victorias']++;
                    $resultados[$enfrentamiento->equipo1_id]['puntos'] += $this->options['puntos_victoria'];
                    $resultados[$enfrentamiento->equipo2_id]['derrotas']++;
                    $resultados[$enfrentamiento->equipo2_id]['puntos'] += $this->options['puntos_derrota'];
                } else {
                    $resultados[$enfrentamiento->equipo2_id]['victorias']++;
                    $resultados[$enfrentamiento->equipo2_id]['puntos'] += $this->options['puntos_victoria'];
                    $resultados[$enfrentamiento->equipo1_id]['derrotas']++;
                    $resultados[$enfrentamiento->equipo1_id]['puntos'] += $this->options['puntos_derrota'];
                }
            } else {
                $resultados[$enfrentamiento->equipo1_id]['empates']++;
                $resultados[$enfrentamiento->equipo2_id]['empates']++;
                $resultados[$enfrentamiento->equipo1_id]['puntos'] += $this->options['puntos_empate'];
                $resultados[$enfrentamiento->equipo2_id]['puntos'] += $this->options['puntos_empate'];
            }
            
            // Actualizar goles
            $resultados[$enfrentamiento->equipo1_id]['goles_favor'] += $enfrentamiento->puntaje_equipo1 ?? 0;
            $resultados[$enfrentamiento->equipo1_id]['goles_contra'] += $enfrentamiento->puntaje_equipo2 ?? 0;
            $resultados[$enfrentamiento->equipo2_id]['goles_favor'] += $enfrentamiento->puntaje_equipo2 ?? 0;
            $resultados[$enfrentamiento->equipo2_id]['goles_contra'] += $enfrentamiento->puntaje_equipo1 ?? 0;
            
            // Actualizar diferencia de goles
            $resultados[$enfrentamiento->equipo1_id]['diferencia_goles'] = 
                $resultados[$enfrentamiento->equipo1_id]['goles_favor'] - 
                $resultados[$enfrentamiento->equipo1_id]['goles_contra'];
            $resultados[$enfrentamiento->equipo2_id]['diferencia_goles'] = 
                $resultados[$enfrentamiento->equipo2_id]['goles_favor'] - 
                $resultados[$enfrentamiento->equipo2_id]['goles_contra'];
            
            // Registrar enfrentamiento directo
            $resultados[$enfrentamiento->equipo1_id]['enfrentamientos_directos'][$enfrentamiento->equipo2_id] = [
                'ganado' => $enfrentamiento->ganador_id == $enfrentamiento->equipo1_id,
                'empatado' => !$enfrentamiento->ganador_id,
                'goles_favor' => $enfrentamiento->puntaje_equipo1 ?? 0,
                'goles_contra' => $enfrentamiento->puntaje_equipo2 ?? 0
            ];
            $resultados[$enfrentamiento->equipo2_id]['enfrentamientos_directos'][$enfrentamiento->equipo1_id] = [
                'ganado' => $enfrentamiento->ganador_id == $enfrentamiento->equipo2_id,
                'empatado' => !$enfrentamiento->ganador_id,
                'goles_favor' => $enfrentamiento->puntaje_equipo2 ?? 0,
                'goles_contra' => $enfrentamiento->puntaje_equipo1 ?? 0
            ];
        }
        
        return array_values($resultados);
    }

    /**
     * Ordenar equipos por criterios de desempate
     */
    protected function ordenarPorCriteriosDesempate(array $resultados): array
    {
        usort($resultados, function($a, $b) {
            foreach ($this->options['criterios_desempate'] as $criterio) {
                $comparacion = 0;
                
                switch ($criterio) {
                    case 'puntos':
                        $comparacion = $b['puntos'] <=> $a['puntos'];
                        break;
                    case 'diferencia_goles':
                        $comparacion = $b['diferencia_goles'] <=> $a['diferencia_goles'];
                        break;
                    case 'goles_favor':
                        $comparacion = $b['goles_favor'] <=> $a['goles_favor'];
                        break;
                    case 'enfrentamiento_directo':
                        if (isset($a['enfrentamientos_directos'][$b['equipo_id']])) {
                            $enfrentamiento = $a['enfrentamientos_directos'][$b['equipo_id']];
                            if ($enfrentamiento['ganado']) $comparacion = 1;
                            elseif ($enfrentamiento['empatado']) $comparacion = 0;
                            else $comparacion = -1;
                        }
                        break;
                }
                
                if ($comparacion !== 0) {
                    return $comparacion;
                }
            }
            
            return 0;
        });
        
        return $resultados;
    }
} 