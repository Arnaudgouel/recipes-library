<?php

namespace App\Twig;

use Twig\Attribute\AsTwigFunction;
use App\Service\SeasonService;

class SeasonExtension
{

    public function __construct(
        private readonly SeasonService $seasonService
    ) {
    }

    #[AsTwigFunction('current_season')]
    public function getCurrentSeason(): string
    {
        $season = $this->seasonService->getCurrentSeason();
        return match ($season) {
            'printemps' => 'Printemps',
            'ete' => 'Été',
            'automne' => 'Automne',
            'hiver' => 'Hiver',
        };
    }
}
