@extends('layouts.app')

@section('content')
<div class="container mx-auto py-10 px-4">
    <div class="max-w-lg mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-red-500 px-6 py-4">
            <h1 class="text-white text-lg font-semibold">{{ $title ?? 'Error' }}</h1>
        </div>
        <div class="px-6 py-4">
            <div class="text-gray-700 mb-4">
                {{ $message ?? 'Ha ocurrido un error inesperado.' }}
            </div>
            <div class="mt-6 flex justify-center">
                <a href="{{ url()->previous() }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded mr-2">
                    Volver atrás
                </a>
                <a href="{{ route('welcome') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Ir al inicio
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 