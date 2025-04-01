@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Configuración del Countdown</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.countdown-config.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="target_date">Fecha Objetivo</label>
                            <input type="datetime-local" class="form-control @error('target_date') is-invalid @enderror" 
                                   id="target_date" name="target_date" value="{{ $targetDate }}" required>
                            @error('target_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary">
                                Actualizar Fecha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 