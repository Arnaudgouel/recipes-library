<?php

namespace App\DataTable\Exception;

/**
 * Exception levée lorsqu'un filtre entity avec autocomplete n'a pas de route définie.
 */
class MissingAutocompleteRouteException extends \RuntimeException
{
    public function __construct(string $filterName, string $entityClass)
    {
        $message = sprintf(
            'Le filtre "%s" de type entity avec autocomplete activé nécessite une route ou un alias défini dans les options. ' .
            'Entité cible: %s. ' .
            'Ajoutez l\'option "route" ou "alias" dans la configuration du filtre.',
            $filterName,
            $entityClass
        );
        
        parent::__construct($message);
    }
}

