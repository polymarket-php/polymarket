<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Enums;

enum RfqState: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case UNKNOWN = 'unknown';
}
