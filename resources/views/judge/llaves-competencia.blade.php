<x-judge-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $title ?? 'Brackets de Competencia' }}
        </h2>
        
        @if(isset($breadcrumbs))
            <div class="mt-2">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        @foreach($breadcrumbs as $breadcrumb)
                            <li>
                                @if(isset($breadcrumb['url']))
                                    <a href="{{ $breadcrumb['url'] }}" class="text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        {{ $breadcrumb['title'] }}
                                    </a>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">{{ $breadcrumb['title'] }}</span>
                                @endif
                            </li>
                            @if(!$loop->last)
                                <li>
                                    <span class="text-gray-400 dark:text-gray-600">/</span>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
            </div>
        @endif
    </x-slot>

    <div>
        @livewire('judge.bracket-list-view')
    </div>
</x-judge-layout> 