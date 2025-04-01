<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Copa de Robótica 2025 - Escuela de Robótica Misiones</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">
                <img src="{{ \App\Models\SiteConfig::getLogo() }}" alt="Logo Copa de Robótica">
            </div>
            <ul class="nav-links">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#categorias">Categorías</a></li>
                <li><a href="#fechas">Fechas</a></li>
                <li><a href="#inscripcion">Inscripción</a></li>
                <li><a href="#contacto">Contacto</a></li>
                @auth
                    <li><a href="{{ route('dashboard') }}">Mi Panel</a></li>
                @else
                    <li><a href="{{ route('login') }}">Iniciar Sesión</a></li>
                @endauth
            </ul>
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </nav>
    </header>

    <main>
        <section id="hero" class="hero">
            <div class="hero-content">
                <h1>Copa de Robótica 2025</h1>
                <p>La competencia más importante de robótica de Misiones</p>
                <div class="countdown-container">
                    <p>Faltan:</p>
                    <div class="countdown" data-target-date="{{ \App\Models\CountdownConfig::getTargetDate() }}"></div>
                </div>
                <a href="{{ route('login') }}" class="cta-button">Inscríbete Ahora</a>
            </div>
        </section>

        <section id="categorias" class="categorias">
            <h2>Categorías en Competencia</h2>
            <div class="categorias-grid">
                @foreach($categorias as $categoria)
                <div class="categoria-card">
                    <i class="{{ $categoria['icono'] }}"></i>
                    <h3>{{ $categoria['nombre'] }}</h3>
                    <p>{{ $categoria['descripcion'] }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <section id="fechas" class="fechas py-20 bg-gray-900">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-white mb-4">Fechas Importantes</h2>
                    <p class="text-lg text-gray-300 max-w-2xl mx-auto">Marca en tu calendario estas fechas y prepárate para participar en la competencia más importante de robótica de Misiones</p>
                </div>
                
                <div class="categorias-grid">
                    @foreach($fechas as $fecha)
                        <x-event-date 
                            numero="{{ $fecha['numero'] }}" 
                            localidad="{{ $fecha['localidad'] }}" 
                            fecha="{{ $fecha['fecha'] }}" 
                            :categorias="$fecha['categorias']"
                        />
                    @endforeach
                </div>
            </div>
        </section>

        <section id="inscripcion" class="seccion-inscripcion py-20 bg-gray-900">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-white mb-4">Inscripción</h2>
                    <p class="text-lg text-gray-300 max-w-2xl mx-auto">Completa el proceso de inscripción siguiendo estos sencillos pasos</p>
                </div>
                
                <div class="categorias-grid">
                    <div class="inscripcion-card fecha-evento">
                        <div class="fecha-header bg-violet-600">
                            <div class="fecha-numero">CÓMO INSCRIBIRTE</div>
                        </div>
                        
                        <div class="fecha-body">
                            <div class="pasos-inscripcion">
                                <div class="paso">
                                    <div class="paso-numero">
                                        <i class="fas fa-user-plus text-violet-600"></i>
                                    </div>
                                    <div class="paso-contenido">
                                        <h4 class="fecha-localidad">Paso 1: Crea una cuenta</h4>
                                        <p>Regístrate en nuestra plataforma para acceder a todas las funcionalidades.</p>
                                        <div class="boton-container">
                                            <a href="{{ route('register') }}" class="fecha-boton">
                                                <i class="fas fa-user-plus"></i>
                                                Crear cuenta
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="paso mt-5">
                                    <div class="paso-numero">
                                        <i class="fas fa-sign-in-alt text-violet-600"></i>
                                    </div>
                                    <div class="paso-contenido">
                                        <h4 class="fecha-localidad">Paso 2: Inicia sesión</h4>
                                        <p>Accede a tu cuenta con tus credenciales.</p>
                                        <div class="boton-container">
                                            <a href="{{ route('login') }}" class="fecha-boton">
                                                <i class="fas fa-sign-in-alt"></i>
                                                Iniciar sesión
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="paso mt-5">
                                    <div class="paso-numero">
                                        <i class="fas fa-clipboard-list text-violet-600"></i>
                                    </div>
                                    <div class="paso-contenido">
                                        <h4 class="fecha-localidad">Paso 3: Completa tu inscripción</h4>
                                        <p>En tu panel de control, llena el formulario con la información de tu proyecto.</p>
                                    </div>
                                </div>
                                
                                <div class="inscripcion-nota mt-5">
                                    <p class="flex items-center">
                                        <i class="fas fa-info-circle text-violet-600 mr-2"></i>
                                        ¿Ya tienes una cuenta? 
                                        <a href="{{ route('login') }}" class="text-violet-600 font-medium ml-1">
                                            Inicia sesión
                                        </a>
                                        para gestionar tus inscripciones.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contacto" class="contacto">
            <h2>Contacto</h2>
            <div class="contacto-container">
                <div class="contacto-info">
                    <h3>Información de Contacto</h3>
                    <p><i class="fas fa-map-marker-alt"></i> {{ config('app.contacto.direccion') }}</p>
                    <p><i class="fas fa-phone"></i> {{ config('app.contacto.telefono') }}</p>
                    <p><i class="fas fa-envelope"></i> {{ config('app.contacto.email') }}</p>
                </div>
                <div class="redes-sociales">
                    <h3>Síguenos</h3>
                    <div class="social-links">
                        @foreach($redesSociales as $red)
                        <a href="{{ $red['url'] }}" target="_blank" aria-label="{{ $red['nombre'] }}">
                            <i class="{{ $red['icono'] }}"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; {{ date('Y') }} Copa de Robótica Misiones. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
