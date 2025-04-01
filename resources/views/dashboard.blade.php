<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Control') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (Auth::user()->isAdmin())
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 rounded" role="alert">
                    <p class="font-bold">Acceso de Administrador</p>
                    <p>Tienes acceso a todas las funciones administrativas. <a href="{{ route('admin.dashboard') }}" class="underline">Ir al panel de administración</a>.</p>
                </div>
            @endif

            <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-bold text-lg mb-3">Mis Equipos</h3>
                        <p class="mb-3">Gestiona tus equipos de competición.</p>
                        <a href="{{ route('teams.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Ver equipos</a>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-bold text-lg mb-3">Mis Inscripciones</h3>
                        <p class="mb-3">Revisa y gestiona tus inscripciones a competencias.</p>
                        <a href="{{ route('registrations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Ver inscripciones</a>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-bold text-lg mb-3">Tabla de Posiciones</h3>
                        <p class="mb-3">Consulta las posiciones y puntajes de las competencias.</p>
                        <a href="{{ route('scoreboard.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Ver tabla de posiciones</a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-bold text-lg mb-3">Próximos Eventos</h3>
                    <p class="text-gray-700 mb-4">Estos son los próximos eventos de la Copa Robótica Misiones 2025.</p>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Evento</th>
                                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Lugar</th>
                                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Inscripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Ejemplo de eventos, esto se debe reemplazar con datos reales -->
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">Fecha 1</td>
                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">Posadas</td>
                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">15 de Febrero, 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">1/02 al 10/02</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">Fecha 2</td>
                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">San Ignacio</td>
                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">20 de Marzo, 2025</td>
                                    <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">1/03 al 15/03</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
