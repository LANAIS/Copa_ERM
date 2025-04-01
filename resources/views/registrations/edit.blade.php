<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Inscripción') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-200 rounded">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium">Detalles de la competencia</h3>
                            <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pendiente de aprobación</span>
                        </div>
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                            <p class="font-bold text-xl">{{ $event->competition->name }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                    {{ $event->category->name }}
                                </span>
                                <span class="text-sm text-gray-600">
                                    <span class="font-medium">Fecha:</span> {{ date('d/m/Y', strtotime($event->event_date)) }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ $event->description ?? 'Sin descripción disponible.' }}
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('registrations.update', $registration) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-4">Datos del participante</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="team_name" :value="__('Equipo')" />
                                    <div class="p-2 border border-gray-300 rounded-md shadow-sm mt-1 bg-gray-50">
                                        {{ $team->name }} ({{ $team->institution }})
                                    </div>
                                    <input type="hidden" name="team_id" value="{{ $team->id }}">
                                </div>

                                <div>
                                    <x-input-label for="robot_id" :value="__('Robot')" />
                                    <select id="robot_id" name="robot_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                        @foreach ($robots as $robot)
                                            <option value="{{ $robot->id }}" {{ old('robot_id', $registration->robot_id) == $robot->id ? 'selected' : '' }}>
                                                {{ $robot->name }} ({{ $robot->model }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('robot_id')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <x-input-label for="notes" :value="__('Notas adicionales (opcional)')" />
                            <textarea id="notes" name="notes" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">{{ old('notes', $registration->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('registrations.show', $registration) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Actualizar Inscripción') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 