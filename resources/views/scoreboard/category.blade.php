<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tabla de Posiciones') }}: {{ $category->name }} - {{ $category->type }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-xl font-semibold">{{ $category->name }} - {{ $category->type }}</h3>
                            <p class="text-gray-600 mt-1">{{ $category->description }}</p>
                        </div>
                        <a href="{{ route('scoreboard.index') }}" class="px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                            Volver a Categorías
                        </a>
                    </div>

                    @if(count($scores) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Posición</th>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Equipo</th>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total de Puntos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($scores as $index => $score)
                                        <tr class="{{ $index < 3 ? 'bg-yellow-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">
                                                <span class="flex items-center">
                                                    @if($index === 0)
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 2a1 1 0 0 0-.894.553L6.236 8.414l-6.453.836a1 1 0 0 0-.562 1.705l4.742 4.625-1.12 6.549a1 1 0 0 0 1.456 1.054L10 18.667l5.701 3.016a1 1 0 0 0 1.456-1.054l-1.12-6.55 4.742-4.624a1 1 0 0 0-.562-1.705l-6.453-.836L10.894 2.553A1 1 0 0 0 10 2z" clip-rule="evenodd" />
                                                        </svg>
                                                    @endif
                                                    {{ $index + 1 }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200">
                                                {{ $score->team->name }}
                                                <span class="text-gray-500 text-xs ml-2">{{ $score->team->institution }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap border-b border-gray-200 font-bold">
                                                {{ $score->total_points }} puntos
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <p>Aún no hay puntajes registrados para esta categoría.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-bold text-lg mb-4">Reglas de la Categoría</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p>{{ $category->rules ?? 'No hay reglas específicas definidas para esta categoría.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 