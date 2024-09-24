<?php
namespace AntonioPrimera\Efc\Dto;

class Quantity
{
    public function __construct(
        public string|int|float $quantity,
        public string $uom
    )
    {}

    public static function empty(): self
    {
        return new self(0, '');
    }
}
