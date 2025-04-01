<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inscripción a Competencia') }}
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

                    <form method="POST" action="{{ route('registrations.store') }}">
                        @csrf

                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-4">Selecciona la competencia</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($events as $event)
                                    <div class="p-4 border rounded-lg hover:bg-gray-50 cursor-pointer event-option" 
                                         data-event-id="{{ $event->id }}">
                                        <input type="radio" name="competition_event_id" id="event_{{ $event->id }}" 
                                               value="{{ $event->id }}" class="hidden" 
                                               {{ old('competition_event_id') == $event->id ? 'checked' : '' }}>
                                        <div class="flex items-start mb-2">
                                            <div class="flex-grow">
                                                <label for="event_{{ $event->id }}" class="font-bold text-lg">
                                                    {{ $event->competition->name }}
                                                </label>
                                                <span class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                                    {{ $event->category->name }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-gray-700">Fecha:</span> 
                                            <span class="font-medium">{{ date('d/m/Y', strtotime($event->event_date)) }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-gray-700">Inscripción hasta:</span> 
                                            <span class="font-medium">{{ date('d/m/Y', strtotime($event->registration_end)) }}</span>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ $event->description ?? 'Sin descripción disponible.' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-4">Datos del participante</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="team_id" :value="__('Equipo')" />
                                    <select id="team_id" name="team_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                        <option value="">Selecciona un equipo</option>
                                        @foreach ($teams as $team)
                                            <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                                {{ $team->name }} ({{ $team->institution }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('team_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="robot_id" :value="__('Robot')" />
                                    <select id="robot_id" name="robot_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                        <option value="">Selecciona un robot</option>
                                        @foreach ($robots as $robot)
                                            <option value="{{ $robot->id }}" data-team-id="{{ $robot->team_id }}" class="robot-option {{ old('team_id') == $robot->team_id ? '' : 'hidden' }}" {{ old('robot_id') == $robot->id ? 'selected' : '' }}>
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
                            <textarea id="notes" name="notes" rows="3" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('registrations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Inscribir') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Selección de la competencia
            const eventOptions = document.querySelectorAll('.event-option');
            eventOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Desmarcar todos
                    eventOptions.forEach(opt => {
                        opt.classList.remove('bg-blue-50', 'border-blue-200');
                        const radio = opt.querySelector('input[type="radio"]');
                        radio.checked = false;
                    });
                    
                    // Marcar el seleccionado
                    this.classList.add('bg-blue-50', 'border-blue-200');
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                });
            });

            // Filtrar robots por equipo
            const teamSelect = document.getElementById('team_id');
            const robotSelect = document.getElementById('robot_id');
            const robotOptions = document.querySelectorAll('.robot-option');

            teamSelect.addEventListener('change', function() {
                const selectedTeamId = this.value;

                // Ocultar todos los robots
                robotOptions.forEach(option => {
                    option.classList.add('hidden');
                });

                // Mostrar solo los robots del equipo seleccionado
                if (selectedTeamId) {
                    document.querySelectorAll(`.robot-option[data-team-id="${selectedTeamId}"]`).forEach(option => {
                        option.classList.remove('hidden');
                    });
                }

                // Resetear la selección de robot
                robotSelect.value = '';
            });

            // Si hay una competencia seleccionada previamente (por ejemplo, en caso de error de validación)
            const selectedEventId = document.querySelector('input[name="competition_event_id"]:checked');
            if (selectedEventId) {
                const selectedEvent = document.querySelector(`.event-option[data-event-id="${selectedEventId.value}"]`);
                if (selectedEvent) {
                    selectedEvent.classList.add('bg-blue-50', 'border-blue-200');
                }
            }
        });
    </script>
    @endpush
</x-app-layout> 