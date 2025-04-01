<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles del Robot') }}: {{ $robot->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">{{ $robot->name }}</h3>
                            <p class="text-gray-600">Equipo: <a href="{{ route('teams.show', $robot->team) }}" class="text-blue-600 hover:underline">{{ $robot->team->name }}</a></p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('teams.robots.index', $robot->team) }}" class="px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                                Volver a Robots
                            </a>
                            <a href="{{ route('robots.edit', $robot) }}" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                                Editar Robot
                            </a>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="text-lg font-semibold mb-2">Información Básica</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Nombre:</p>
                                <p class="font-medium">{{ $robot->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Modelo:</p>
                                <p class="font-medium">{{ $robot->model ?? 'No especificado' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-lg font-semibold mb-2">Descripción</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p>{{ $robot->description ?? 'Sin descripción disponible.' }}</p>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h4 class="text-lg font-semibold mb-2">Inscripciones a Competencias</h4>
                        @if($robot->registrations && $robot->registrations->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Evento</th>
                                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Categoría</th>
                                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                                            <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($robot->registrations as $registration)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">
                                                    {{ $registration->competitionEvent->competition->name }} - {{ $registration->competitionEvent->location }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">
                                                    {{ $registration->competitionEvent->category->name }} ({{ $registration->competitionEvent->category->type }})
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">
                                                    {{ $registration->competitionEvent->event_date->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">
                                                    @if($registration->status === 'approved')
                                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Aprobada</span>
                                                    @elseif($registration->status === 'pending')
                                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pendiente</span>
                                                    @else
                                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Rechazada</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-600">Este robot aún no está inscrito en ninguna competencia.</p>
                            <a href="{{ route('registrations.create') }}" class="mt-2 inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">
                                Inscribir en Competencia
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <form action="{{ route('robots.destroy', $robot) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500" onclick="return confirm('¿Estás seguro de que deseas eliminar este robot? Esta acción no se puede deshacer.')">
                        Eliminar Robot
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout> 