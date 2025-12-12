<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TimeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_time', [$this, 'formatTime']),
        ];
    }

    public function formatTime(?int $minutes): string
    {
        if ($minutes === null) {
            return 'Non spécifié';
        }

        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours . 'h';
        }

        return $hours . 'h' . $remainingMinutes;
    }
}

