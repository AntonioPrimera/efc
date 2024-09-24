<?php
namespace AntonioPrimera\Efc\Dto;

class Price
{
    public function __construct(
        public string|int|float $amount,
        public string $currency
    )
    {}

    public static function empty(): self
    {
        return new self(0, 'RON');
    }
}
