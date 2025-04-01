<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tabla de Posiciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h2 class="text-xl font-semibold mb-4">Copa Robótica Misiones 2025</h2>
                    <p class="mb-2">Selecciona una categoría para ver la tabla de posiciones específica.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($categories as $category)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-bold text-lg mb-2">{{ $category->name }} - {{ $category->type }}</h3>
                        <p class="text-sm text-gray-600 mb-4">{{ $category->description }}</p>
                        <a href="{{ route('scoreboard.category', $category) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                            Ver Posiciones
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-bold text-lg mb-4">Próximas Competencias</h3>
                    
                    @if ($competitions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Competencia</th>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Año</th>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($competitions as $competition)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">{{ $competition->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">{{ $competition->year }}</td>
                                            <td class="px-6 py-4 border-b border-gray-200">{{ $competition->description }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center py-4">No hay competencias activas en este momento.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 