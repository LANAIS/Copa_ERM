@extends('layouts.filament-wrapper')

@section('title', 'Configuración del Sitio')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Configuración del Sitio</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form action="{{ route('admin.site-config.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Logo del Sitio</h2>
                
                <div class="mt-2">
                    <div class="flex items-center space-x-6">
                        <div class="shrink-0">
                            <img id="logo-preview" class="h-24 w-auto object-contain" src="{{ \App\Models\SiteConfig::getLogo() }}" alt="Logo actual">
                        </div>
                        <label class="block">
                            <span class="sr-only">Seleccionar logo</span>
                            <input type="file" name="logo" id="logo" class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100"
                                onchange="previewLogo()"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">PNG, JPG, GIF hasta 2MB</p>
                        </label>
                    </div>
                    @error('logo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewLogo() {
        const file = document.getElementById('logo').files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logo-preview').src = e.target.result;
        }
        if (file) {
            reader.readAsDataURL(file);
        }
    }
</script>
@endsection 