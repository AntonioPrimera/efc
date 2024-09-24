<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class DeliveryData extends Data
{
    public function __construct(
        public string|null $date,
        public DeliveryLocationData|null $location,
        public string|null $party,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        return new self(
            date: $xml->get('ActualDeliveryDate'),
            location: data($xml->node('DeliveryLocation'), DeliveryLocationData::class),
            party: $xml->get('DeliveryParty.PartyName.Name'),
        );
    }
}
