<?php

namespace App\Service;

class NormalizationService
{
    /**
     * Normalise les accents d'une chaîne (slugifier)
     * Remplace les caractères accentués par leurs équivalents sans accent
     * Utilise iconv() qui est plus rapide que strtr() selon les benchmarks
     */
    public static function normalizeAccents(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return null;
        }

        // Utiliser iconv() si disponible (plus rapide)
        $normalized = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        // Si iconv échoue, retourner le texte original (ou utiliser un fallback)
        if ($normalized !== false && $normalized !== '') {
            return strtolower($normalized);
        }

        // Fallback : tableau de correspondance (moins rapide mais plus fiable)
        $accents = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ç' => 'c', 'ñ' => 'n',
            'À' => 'a', 'Á' => 'a', 'Â' => 'a', 'Ã' => 'a', 'Ä' => 'a', 'Å' => 'a',
            'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e',
            'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i',
            'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
            'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u',
            'Ý' => 'y', 'Ÿ' => 'y',
            'Ç' => 'c', 'Ñ' => 'n',
        ];

        return strtolower(strtr($text, $accents));
    }
}

