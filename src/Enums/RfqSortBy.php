<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Enums;

enum RfqSortBy: string
{
    case PRICE = 'price';
    case EXPIRY = 'expiry';
    case SIZE = 'size';
    case CREATED = 'created';
}
