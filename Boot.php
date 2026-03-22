<?php
declare(strict_types=1);

namespace Plugins\MagixFeatured;

use App\Component\Hook\HookManager;

class Boot
{
    public function register(): void
    {
        // On passe bien les 3 arguments attendus par votre HookManager !
        HookManager::register(
            'displayHomeBottom',
            'MagixFeatured',
            [\Plugins\MagixFeatured\src\FrontendController::class, 'renderWidget']
        );
    }
}