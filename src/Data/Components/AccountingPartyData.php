<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class AccountingPartyData extends Data
{
    public function __construct(
        #[MapInputName('nume')]
        public string|null $name,
        #[MapInputName('fullCif')]
        public string|null $cif,
        public string|null $regCom,
        #[MapInputName('adresa')]
        public AddressData|null $address,
        public ContactData|null $contact,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        $xml = $xml->node('Party');
        $regCom = $xml->get('PartyLegalEntity.CompanyID');
        return new self(
            name: $xml->get('PartyName.Name') ?? $xml->get('PartyLegalEntity.RegistrationName'),
            cif: $xml->get('PartyTaxScheme.CompanyID')
                ?? $xml->get('PartyIdentification.ID')
                ?? $xml->get('PartyLegalEntity.CompanyID'), //this is usually regCom, but sometimes it's CIF (used as a last resort)
            regCom: isRegCom($regCom) ? $regCom : null,
            address: data($xml->node('PostalAddress'), AddressData::class),
            contact: data($xml->node('Contact'), ContactData::class),
        );
    }
}
