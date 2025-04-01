@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="{{ route('equipos.index') }}" class="text-blue-500 hover:text-blue-600 mr-4">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Crear Nuevo Equipo</h1>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('equipos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Información del Equipo</h2>
                    
                    <div class="mb-4">
                        <label for="nombre" class="block text-gray-700 font-medium mb-2">Nombre del Equipo *</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('nombre')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="descripcion" class="block text-gray-700 font-medium mb-2">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="4" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Describe tu equipo, su historia, misión, y metas...">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Contacto y Redes Sociales</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="sitio_web" class="block text-gray-700 font-medium mb-2">Sitio Web</label>
                            <div class="flex items-center">
                                <span class="bg-gray-100 px-3 py-2 border border-r-0 border-gray-300 rounded-l-lg">
                                    <i class="fas fa-globe"></i>
                                </span>
                                <input type="url" name="sitio_web" id="sitio_web" value="{{ old('sitio_web') }}"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            @error('sitio_web')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                            <div class="flex items-center">
                                <span class="bg-gray-100 px-3 py-2 border border-r-0 border-gray-300 rounded-l-lg">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="instagram" class="block text-gray-700 font-medium mb-2">Instagram</label>
                            <div class="flex items-center">
                                <span class="bg-gray-100 px-3 py-2 border border-r-0 border-gray-300 rounded-l-lg">
                                    <i class="fab fa-instagram"></i>
                                </span>
                                <input type="text" name="instagram" id="instagram" value="{{ old('instagram') }}"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            @error('instagram')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="facebook" class="block text-gray-700 font-medium mb-2">Facebook</label>
                            <div class="flex items-center">
                                <span class="bg-gray-100 px-3 py-2 border border-r-0 border-gray-300 rounded-l-lg">
                                    <i class="fab fa-facebook"></i>
                                </span>
                                <input type="text" name="facebook" id="facebook" value="{{ old('facebook') }}"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            @error('facebook')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="youtube" class="block text-gray-700 font-medium mb-2">YouTube</label>
                            <div class="flex items-center">
                                <span class="bg-gray-100 px-3 py-2 border border-r-0 border-gray-300 rounded-l-lg">
                                    <i class="fab fa-youtube"></i>
                                </span>
                                <input type="text" name="youtube" id="youtube" value="{{ old('youtube') }}"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            @error('youtube')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="linkedin" class="block text-gray-700 font-medium mb-2">LinkedIn</label>
                            <div class="flex items-center">
                                <span class="bg-gray-100 px-3 py-2 border border-r-0 border-gray-300 rounded-l-lg">
                                    <i class="fab fa-linkedin"></i>
                                </span>
                                <input type="text" name="linkedin" id="linkedin" value="{{ old('linkedin') }}"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            @error('linkedin')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Imágenes</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="logo" class="block text-gray-700 font-medium mb-2">Logo del Equipo</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                <div class="mb-3">
                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl"></i>
                                </div>
                                <p class="text-gray-500 mb-2">Arrastra y suelta o haz clic para seleccionar</p>
                                <p class="text-xs text-gray-400">PNG, JPG, SVG o WEBP (Max. 2MB)</p>
                                <input type="file" name="logo" id="logo" accept="image/*"
                                    class="w-full mt-4 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            @error('logo')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="banner" class="block text-gray-700 font-medium mb-2">Banner del Equipo</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                <div class="mb-3">
                                    <i class="fas fa-image text-gray-400 text-3xl"></i>
                                </div>
                                <p class="text-gray-500 mb-2">Arrastra y suelta o haz clic para seleccionar</p>
                                <p class="text-xs text-gray-400">PNG, JPG o WEBP (Max. 5MB)</p>
                                <p class="text-xs text-gray-400">Dimensión recomendada: 1200x300 pixeles</p>
                                <input type="file" name="banner" id="banner" accept="image/*"
                                    class="w-full mt-4 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            @error('banner')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end mt-8">
                    <a href="{{ route('equipos.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-lg mr-2">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg">
                        Crear Equipo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 