<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class DeliveryLocationData extends Data
{
    public function __construct(
        public string|null $id,
        public AddressData|null $address,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        $addressNode = $xml->node('Address');
        return new self(
            id: $xml->get('ID'),
            address: is_object($addressNode) ? AddressData::fromXml($addressNode) : null,
        );
    }
}
