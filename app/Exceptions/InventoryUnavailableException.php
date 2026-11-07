<?php

namespace App\Exceptions;

use RuntimeException;

class InventoryUnavailableException extends RuntimeException
{
    public function __construct(
        public readonly string $productName,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct("Only {$available} item(s) remain for {$productName}; {$requested} requested.");
    }
}
