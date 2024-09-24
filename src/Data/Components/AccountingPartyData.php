<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class AccountingPartyData extends Data
{
    public function __construct(
        public string|null $name,
        public string|null $cif,
        public string|null $regCom,
        public AddressData|null $address,
        public ContactData|null $contact,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        $xml = $xml->node('Party');
        return new self(
            name: $xml->get('PartyName.Name'),
            cif: $xml->get('PartyTaxScheme.CompanyID'),
            regCom: $xml->get('PartyLegalEntity.CompanyID'),
            address: data($xml->node('PostalAddress'), AddressData::class),
            contact: data($xml->node('Contact'), ContactData::class),
        );
    }
}
