<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\AnafDataStructures\Components\AddressData as AnafAddressData;
use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class AddressData extends AnafAddressData
{
    public static function fromXml(EFacturaXml $xml): self
    {
        return new self(
            city: $xml->get('CityName'),
            county: self::eFacturaXmlCounty($xml->get('CountrySubentity')),
            street: $xml->get('StreetName'),
            streetNumber: null,
            postalCode: $xml->get('PostalZone'),
            country: $xml->get('Country.IdentificationCode'),
            details: null,
        );
    }

    //--- Data adapters -----------------------------------------------------------------------------------------------

    protected static function eFacturaXmlCounty(string|null $countyString): string|null
    {
        return is_string($countyString) && str_starts_with($countyString, 'RO-')
            ? substr($countyString, 3)
            : $countyString;
    }
}
