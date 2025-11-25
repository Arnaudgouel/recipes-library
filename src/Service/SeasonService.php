<?php

namespace App\Service;

class SeasonService
{
    /**
     * Détermine la saison courante selon la date précise
     * Utilise les dates officielles des équinoxes et solstices
     * 
     * @return string La saison courante : 'printemps', 'ete', 'automne' ou 'hiver'
     */
    public function getCurrentSeason(): string
    {
        $today = new \DateTime();
        $year = (int) $today->format('Y');
        $month = (int) $today->format('n');
        $day = (int) $today->format('j');
        
        // Dates des équinoxes et solstices (approximatives, peuvent varier d'un jour selon l'année)
        // Printemps : équinoxe de printemps (généralement 20-21 mars) jusqu'au solstice d'été
        // Été : solstice d'été (généralement 20-21 juin) jusqu'à l'équinoxe d'automne
        // Automne : équinoxe d'automne (généralement 22-23 septembre) jusqu'au solstice d'hiver
        // Hiver : solstice d'hiver (généralement 21-22 décembre) jusqu'à l'équinoxe de printemps
        
        // Si on est en janvier ou février, on est en hiver (qui a commencé en décembre de l'année précédente)
        if ($month <= 2) {
            return 'hiver';
        }
        
        // Si on est en mars, vérifier si on est avant ou après l'équinoxe de printemps (20 mars)
        if ($month === 3) {
            return $day < 20 ? 'hiver' : 'printemps';
        }
        
        // Si on est en juin, vérifier si on est avant ou après le solstice d'été (21 juin)
        if ($month === 6) {
            return $day < 21 ? 'printemps' : 'ete';
        }
        
        // Si on est en septembre, vérifier si on est avant ou après l'équinoxe d'automne (23 septembre)
        if ($month === 9) {
            return $day < 22 ? 'ete' : 'automne';
        }
        
        // Si on est en décembre, vérifier si on est avant ou après le solstice d'hiver (21 décembre)
        if ($month === 12) {
            return $day < 21 ? 'automne' : 'hiver';
        }
        
        // Pour les autres mois, détermination simple
        if ($month >= 4 && $month <= 5) {
            return 'printemps';
        } elseif ($month >= 7 && $month <= 8) {
            return 'ete';
        } elseif ($month >= 10 && $month <= 11) {
            return 'automne';
        }
        
        // Par défaut (ne devrait jamais arriver)
        return 'hiver';
    }
}

