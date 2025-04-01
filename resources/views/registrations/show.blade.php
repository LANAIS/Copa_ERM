<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalles de Inscripción') }}
            </h2>
            <div class="flex space-x-3">
                @if($registration->status === 'pending')
                    <a href="{{ route('registrations.edit', $registration) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        {{ __('Editar') }}
                    </a>
                @endif
                <a href="{{ route('registrations.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                    {{ __('Volver a Inscripciones') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-200 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-200 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Detalles de la Competencia -->
                <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium">Detalles de la Competencia</h3>
                            <span class="px-3 py-1 text-xs rounded-full 
                                @if($registration->status === 'approved') bg-green-100 text-green-800
                                @elseif($registration->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($registration->status) }}
                            </span>
                        </div>
                        <div class="mt-4">
                            <p class="font-bold text-xl">{{ $registration->competitionEvent->competition->name }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                    {{ $registration->competitionEvent->category->name }}
                                </span>
                                <span class="text-sm text-gray-600">
                                    <span class="font-medium">Fecha:</span> {{ date('d/m/Y', strtotime($registration->competitionEvent->event_date)) }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ $registration->competitionEvent->description ?? 'Sin descripción disponible.' }}
                            </p>
                        </div>

                        <div class="mt-6">
                            <h4 class="text-md font-medium mb-2">Participante</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <div class="text-sm font-medium text-gray-500">Equipo</div>
                                    <div class="font-medium">{{ $registration->team->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $registration->team->institution }}</div>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <div class="text-sm font-medium text-gray-500">Robot</div>
                                    <div class="font-medium">{{ $registration->robot->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $registration->robot->model }}</div>
                                </div>
                            </div>
                        </div>

                        @if($registration->notes)
                            <div class="mt-6">
                                <h4 class="text-md font-medium mb-2">Notas adicionales</h4>
                                <div class="p-3 bg-gray-50 rounded-lg text-sm">
                                    {{ $registration->notes }}
                                </div>
                            </div>
                        @endif

                        <div class="mt-6">
                            <h4 class="text-md font-medium mb-2">Detalles de la inscripción</h4>
                            <div class="p-3 bg-gray-50 rounded-lg grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="font-medium text-gray-500">Fecha de inscripción:</span> 
                                    {{ date('d/m/Y H:i', strtotime($registration->registration_date)) }}
                                </div>
                                @if($registration->status === 'approved')
                                <div>
                                    <span class="font-medium text-gray-500">Fecha de aprobación:</span> 
                                    {{ $registration->approval_date ? date('d/m/Y H:i', strtotime($registration->approval_date)) : 'N/A' }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones y Puntajes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium mb-4">Puntajes</h3>

                        @if($registration->scores->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($registration->scores as $score)
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <div class="flex justify-between">
                                            <span class="font-medium">{{ $score->criteria }}</span>
                                            <span class="font-bold text-lg">{{ $score->points }}</span>
                                        </div>
                                        @if($score->comments)
                                            <div class="mt-1 text-sm text-gray-600">
                                                {{ $score->comments }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                                <div class="p-3 bg-blue-50 rounded-lg mt-3">
                                    <div class="flex justify-between">
                                        <span class="font-bold">Total</span>
                                        <span class="font-bold text-lg">{{ $registration->scores->sum('points') }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center p-4 text-gray-500">
                                @if($registration->status === 'approved')
                                    <p>No hay puntajes registrados todavía.</p>
                                @else
                                    <p>Los puntajes estarán disponibles después de que la inscripción sea aprobada.</p>
                                @endif
                            </div>
                        @endif

                        <div class="mt-6 border-t pt-4">
                            <h3 class="text-lg font-medium mb-3">Acciones</h3>

                            @if($registration->status === 'pending')
                                <form method="POST" action="{{ route('registrations.destroy', $registration) }}" class="mt-4">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none" onclick="return confirm('¿Estás seguro de que deseas cancelar esta inscripción?')">
                                        {{ __('Cancelar Inscripción') }}
                                    </button>
                                </form>
                            @endif

                            @if(auth()->user()->isAdmin() && $registration->status === 'pending')
                                <div class="mt-3 flex space-x-3">
                                    <form method="POST" action="{{ route('admin.registrations.approve', $registration) }}" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none">
                                            {{ __('Aprobar') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.registrations.reject', $registration) }}" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none">
                                            {{ __('Rechazar') }}
                                        </button>
                                    </form>
                                </div>
                            @endif

                            @if(auth()->user()->isAdmin() && $registration->status === 'approved')
                                <a href="{{ route('admin.scores.create', $registration) }}" class="mt-3 w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                                    {{ __('Asignar Puntaje') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 