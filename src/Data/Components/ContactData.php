<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ContactData extends Data
{
    public function __construct(
        public string|null $name,
        public string|null $phone,
        public string|null $email,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        return new self(
            name: $xml->get('Name'),
            phone: $xml->get('Telephone'),
            email: $xml->get('ElectronicMail'),
        );
    }
}
