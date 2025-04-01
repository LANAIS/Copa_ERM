<x-filament-panels::page>
    <div class="flex flex-col gap-y-8">
        <x-filament-panels::header
            :actions="$this->getCachedHeaderActions()"
            :heading="$this->getHeading()"
            :subheading="$this->getSubheading()"
        />

        <x-filament::section>
            <div class="flex flex-col items-center justify-center p-4 text-center">
                <h2 class="text-xl font-bold tracking-tight md:text-2xl">
                    Bienvenido al Panel de Jueces
                </h2>
                <p class="mt-2 text-sm text-gray-500 md:text-base">
                    Gestiona homologaciones de robots y resultados de partidos de manera eficiente.
                </p>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-2">
                <!-- Tarjeta de homologaciones -->
                <x-filament::card>
                    <div class="flex items-center">
                        <div class="mr-4 flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100">
                            <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium">Homologaciones</h3>
                            <p class="text-sm text-gray-500">Verifica que los robots cumplan con las especificaciones técnicas.</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="/judge/resources/homologaciones" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Gestionar homologaciones
                            <svg class="ml-2 -mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                </x-filament::card>

                <!-- Tarjeta de enfrentamientos -->
                <x-filament::card>
                    <div class="flex items-center">
                        <div class="mr-4 flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-6 w-6 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium">Enfrentamientos</h3>
                            <p class="text-sm text-gray-500">Gestiona los resultados de los partidos en la competición.</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="/judge/resources/enfrentamientos" class="inline-flex items-center rounded-md border border-transparent bg-amber-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                            Gestionar enfrentamientos
                            <svg class="ml-2 -mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                </x-filament::card>
            </div>
        </x-filament::section>

        <x-filament-widgets::widgets
            :widgets="$this->getWidgets()"
            :columns="$this->getColumns()"
        />
    </div>
</x-filament-panels::page> 