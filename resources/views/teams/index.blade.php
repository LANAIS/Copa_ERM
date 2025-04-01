<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Equipos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Lista de Equipos</h2>
                <a href="{{ route('teams.create') }}" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                    Nuevo Equipo
                </a>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (count($teams) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Institución</th>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ciudad</th>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Robots</th>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($teams as $team)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">{{ $team->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">{{ $team->institution ?? 'No especificada' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">{{ $team->city ?? 'No especificada' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">{{ $team->robots->count() }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('teams.show', $team) }}" class="text-blue-600 hover:text-blue-900">Ver</a>
                                                    <a href="{{ route('teams.edit', $team) }}" class="text-green-600 hover:text-green-900">Editar</a>
                                                    <a href="{{ route('teams.robots.index', $team) }}" class="text-purple-600 hover:text-purple-900">Robots</a>
                                                    <form action="{{ route('teams.destroy', $team) }}" method="POST" class="inline-block">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Estás seguro de que deseas eliminar este equipo?')">Eliminar</button>
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
                        <p class="text-center py-4">No tienes equipos registrados. ¡Crea tu primer equipo!</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 