<?php
declare(strict_types=1);

namespace Plugins\MagixFeaturedProduct;

use App\Component\Hook\HookManager;

class Boot
{
    public function register(): void
    {
        // On passe bien les 3 arguments attendus par votre HookManager !
        /*HookManager::register(
            'displayHomeBottom',
            'MagixFeaturedProduct',
            [\Plugins\MagixFeaturedProduct\src\FrontendController::class, 'renderWidget']
        );*/
    }
}