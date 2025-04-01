<?php

namespace App\Filament\Competitor\Resources\YesResource\Pages;

use App\Filament\Competitor\Resources\YesResource;
use Filament\Resources\Pages\Page;

class Dashboard extends Page
{
    protected static string $resource = YesResource::class;

    protected static string $view = 'filament.competitor.resources.yes-resource.pages.dashboard';
}
