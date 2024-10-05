<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\AnafDataStructures\Components\ContactData as AnafContactData;
use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ContactData extends AnafContactData
{
    public static function fromXml(EFacturaXml $xml): self
    {
        return new self(
            name: $xml->get('Name'),
            phone: $xml->get('Telephone'),
            email: $xml->get('ElectronicMail'),
        );
    }
}
