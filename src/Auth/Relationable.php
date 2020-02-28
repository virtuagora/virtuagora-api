<?php
declare(strict_types=1);

namespace App\Auth;

interface Relationable
{
    /**
     * @return string[]
     */
    public function getRelationsWith(Actor $agent): array;
}
