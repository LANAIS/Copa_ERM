<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="fi-section-card rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <div class="p-2 bg-primary-100 rounded-lg dark:bg-primary-500/20">
                        <x-heroicon-o-cog class="w-6 h-6 text-primary-500 dark:text-primary-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            Mis Robots
                        </h3>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ \App\Models\Robot::where(function($query) {
                                $user = auth()->user();
                                $equiposIds = \App\Models\Equipo::where('user_id', $user->id)->pluck('id');
                                $query->where('user_id', $user->id)
                                    ->orWhereIn('equipo_id', $equiposIds);
                            })->count() }} en total
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('filament.competitor.resources.mis-robots.index') }}" class="text-sm text-primary-600 font-medium hover:underline dark:text-primary-400">
                        Ver todos mis robots →
                    </a>
                </div>
            </div>
            
            <div class="fi-section-card rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <div class="p-2 bg-success-100 rounded-lg dark:bg-success-500/20">
                        <x-heroicon-o-user-group class="w-6 h-6 text-success-500 dark:text-success-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            Mis Equipos
                        </h3>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ \App\Models\Equipo::where('user_id', auth()->id())->count() }} en total
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('filament.competitor.resources.mis-equipos.index') }}" class="text-sm text-success-600 font-medium hover:underline dark:text-success-400">
                        Ver todos mis equipos →
                    </a>
                </div>
            </div>
            
            <div class="fi-section-card rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <div class="p-2 bg-warning-100 rounded-lg dark:bg-warning-500/20">
                        <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-warning-500 dark:text-warning-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            Mis Inscripciones
                        </h3>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ \App\Models\Registration::where('user_id', auth()->id())->count() }} en total
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('filament.competitor.resources.mis-inscripciones.index') }}" class="text-sm text-warning-600 font-medium hover:underline dark:text-warning-400">
                        Ver todas mis inscripciones →
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Acciones rápidas -->
        <div class="fi-section-card rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="text-lg font-semibold mb-4 text-gray-950 dark:text-white">Acciones Rápidas</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('filament.competitor.resources.mis-robots.create') }}" class="flex items-center p-3 bg-primary-50 rounded-lg hover:bg-primary-100 transition dark:bg-primary-500/10 dark:hover:bg-primary-500/20">
                    <div class="p-2 rounded-full bg-primary-500 text-white mr-3">
                        <x-heroicon-s-plus class="h-4 w-4" />
                    </div>
                    <span class="text-gray-700 dark:text-gray-300">Nuevo Robot</span>
                </a>
                
                <a href="{{ route('filament.competitor.resources.mis-equipos.create') }}" class="flex items-center p-3 bg-success-50 rounded-lg hover:bg-success-100 transition dark:bg-success-500/10 dark:hover:bg-success-500/20">
                    <div class="p-2 rounded-full bg-success-500 text-white mr-3">
                        <x-heroicon-s-plus class="h-4 w-4" />
                    </div>
                    <span class="text-gray-700 dark:text-gray-300">Nuevo Equipo</span>
                </a>
                
                <a href="{{ route('filament.competitor.resources.mis-inscripciones.create') }}" class="flex items-center p-3 bg-warning-50 rounded-lg hover:bg-warning-100 transition dark:bg-warning-500/10 dark:hover:bg-warning-500/20">
                    <div class="p-2 rounded-full bg-warning-500 text-white mr-3">
                        <x-heroicon-s-plus class="h-4 w-4" />
                    </div>
                    <span class="text-gray-700 dark:text-gray-300">Nueva Inscripción</span>
                </a>
            </div>
        </div>
        
        <!-- Información adicional en 2 columnas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Competencias Activas -->
            <div class="fi-section-card rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h2 class="text-lg font-semibold mb-4 text-gray-950 dark:text-white">Competencias Activas</h2>
                
                @php
                $competencias = \App\Models\Competition::where('active', true)->take(5)->get();
                @endphp
                
                @if($competencias->count() > 0)
                    <div class="space-y-4">
                        @foreach($competencias as $competencia)
                            <div class="border-l-4 border-primary-500 pl-3 py-1">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $competencia->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $competencia->year }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                        <p>No hay competencias activas en este momento</p>
                    </div>
                @endif
            </div>
            
            <!-- Mis inscripciones recientes -->
            <div class="fi-section-card rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h2 class="text-lg font-semibold mb-4 text-gray-950 dark:text-white">Mis Inscripciones Recientes</h2>
                
                @php
                $inscripciones = \App\Models\Registration::where('user_id', auth()->id())
                    ->with(['competition', 'robot', 'equipo'])
                    ->latest()
                    ->take(5)
                    ->get();
                @endphp
                
                @if($inscripciones->count() > 0)
                    <div class="space-y-3">
                        @foreach($inscripciones as $inscripcion)
                            <div class="flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $inscripcion->competition->name ?? 'Competencia no disponible' }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Robot: {{ $inscripcion->robot->nombre ?? 'No disponible' }} | 
                                        Equipo: {{ $inscripcion->equipo->nombre ?? 'No disponible' }}
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($inscripcion->status === 'approved') bg-success-100 text-success-800 dark:bg-success-500/20 dark:text-success-400
                                    @elseif($inscripcion->status === 'rejected') bg-danger-100 text-danger-800 dark:bg-danger-500/20 dark:text-danger-400
                                    @else bg-warning-100 text-warning-800 dark:bg-warning-500/20 dark:text-warning-400
                                    @endif">
                                    {{ ucfirst($inscripcion->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                        <p>No tienes inscripciones todavía</p>
                        <a href="{{ route('filament.competitor.resources.mis-inscripciones.create') }}" class="inline-block mt-2 text-sm text-primary-600 hover:underline dark:text-primary-400">
                            Inscribirse a una competencia
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page> 