<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Robots del Equipo') }}: {{ $team->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Lista de Robots</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('teams.index') }}" class="px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                        Volver a Equipos
                    </a>
                    <a href="{{ route('teams.robots.create', $team) }}" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                        Nuevo Robot
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (count($robots) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Modelo</th>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Descripción</th>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($robots as $robot)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">{{ $robot->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">{{ $robot->model ?? 'No especificado' }}</td>
                                            <td class="px-6 py-4 border-b border-gray-200">{{ Str::limit($robot->description, 50) ?? 'Sin descripción' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('robots.show', $robot) }}" class="text-blue-600 hover:text-blue-900">Ver</a>
                                                    <a href="{{ route('robots.edit', $robot) }}" class="text-green-600 hover:text-green-900">Editar</a>
                                                    <form action="{{ route('robots.destroy', $robot) }}" method="POST" class="inline-block">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Estás seguro de que deseas eliminar este robot?')">Eliminar</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p class="text-center py-4">No hay robots registrados para este equipo. ¡Registra tu primer robot!</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 