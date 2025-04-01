@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Mis Equipos</h1>
        <a href="{{ route('equipos.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i> Crear Nuevo Equipo
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if($equipos->isEmpty())
    <div class="bg-gray-100 rounded-lg p-8 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-700 mb-2">No tienes equipos registrados</h3>
        <p class="text-gray-500 mb-4">Comienza creando tu primer equipo para la competencia.</p>
        <a href="{{ route('equipos.create') }}" class="inline-flex items-center bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i> Crear Equipo
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($equipos as $equipo)
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <div class="h-32 bg-gray-200 relative">
                @if($equipo->banner)
                <img src="{{ Storage::url($equipo->banner) }}" alt="{{ $equipo->nombre }}" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-r from-blue-400 to-purple-500">
                    <span class="text-white text-xl font-bold">{{ $equipo->nombre }}</span>
                </div>
                @endif
            </div>
            
            <div class="p-6 pt-0 relative">
                <div class="flex justify-center -mt-10 mb-3">
                    <div class="w-20 h-20 rounded-full border-4 border-white bg-gray-100 overflow-hidden">
                        @if($equipo->logo)
                        <img src="{{ Storage::url($equipo->logo) }}" alt="{{ $equipo->nombre }}" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center bg-blue-500 text-white text-xl font-bold">
                            {{ substr($equipo->nombre, 0, 1) }}
                        </div>
                        @endif
                    </div>
                </div>
                
                <h3 class="text-xl font-bold text-center mb-2">{{ $equipo->nombre }}</h3>
                
                <div class="flex justify-center space-x-2 mb-4">
                    @if($equipo->sitio_web)
                    <a href="{{ $equipo->sitio_web }}" target="_blank" class="text-gray-600 hover:text-blue-500">
                        <i class="fas fa-globe"></i>
                    </a>
                    @endif
                    
                    @if($equipo->email)
                    <a href="mailto:{{ $equipo->email }}" class="text-gray-600 hover:text-blue-500">
                        <i class="fas fa-envelope"></i>
                    </a>
                    @endif
                    
                    @if($equipo->instagram)
                    <a href="{{ $equipo->instagram }}" target="_blank" class="text-gray-600 hover:text-blue-500">
                        <i class="fab fa-instagram"></i>
                    </a>
                    @endif
                    
                    @if($equipo->facebook)
                    <a href="{{ $equipo->facebook }}" target="_blank" class="text-gray-600 hover:text-blue-500">
                        <i class="fab fa-facebook"></i>
                    </a>
                    @endif
                    
                    @if($equipo->youtube)
                    <a href="{{ $equipo->youtube }}" target="_blank" class="text-gray-600 hover:text-blue-500">
                        <i class="fab fa-youtube"></i>
                    </a>
                    @endif
                    
                    @if($equipo->linkedin)
                    <a href="{{ $equipo->linkedin }}" target="_blank" class="text-gray-600 hover:text-blue-500">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    @endif
                </div>
                
                <div class="flex justify-between mt-4">
                    <a href="{{ route('equipos.show', $equipo) }}" class="text-blue-500 hover:text-blue-600">
                        <i class="fas fa-eye mr-1"></i> Ver
                    </a>
                    <a href="{{ route('equipos.edit', $equipo) }}" class="text-yellow-500 hover:text-yellow-600">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </a>
                    <form action="{{ route('equipos.destroy', $equipo) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este equipo?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-600 bg-transparent border-0 p-0">
                            <i class="fas fa-trash mr-1"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection 