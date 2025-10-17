<?php

namespace App\Twig;

use Twig\Attribute\AsTwigFilter;

class AppExtension
{

    #[AsTwigFilter('duration')]
    public function formatDuration(?int $minutes): string
    {
        if ($minutes === null || $minutes < 60) {
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }

        $hours = intval($minutes / 60);
        $remainingMinutes = $minutes % 60;

        $result = $hours . ' heure' . ($hours > 1 ? 's' : '');
        
        if ($remainingMinutes > 0) {
            $result .= ' et ' . $remainingMinutes . ' minute' . ($remainingMinutes > 1 ? 's' : '');
        }

        return $result;
    }
}
