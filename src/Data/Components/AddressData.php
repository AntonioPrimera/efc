<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class AddressData extends Data
{
    public function __construct(
        public string|null $city,
        public string|null $county,
        public string|null $street,
        public string|null $streetNumber,
        public string|null $postalCode,
        public string|null $country,
        public string|null $details,
    ) {
    }

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

    //protected function fullAddress(): string
    //{
    //    return "{$this->street}, {$this->city}, Judet:{$this->county}, CP:{$this->postalCode}, {$this->country}";
    //}
}
