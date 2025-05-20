<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Illuminate\Support\Str;
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
    ) {}

    //public function fullAddress(): string
    //{
    //    return "{$this->street}, {$this->streetNumber}, "
    //        . ($this->details ? "{$this->details}, " : '')
    //        . "{$this->city}, Judet:{$this->county}, CP:{$this->postalCode}, {$this->country}";
    //}

    public function fullAddress(): string
    {
        $judet = $this->county;

        if (str_contains(Str::slug($judet), 'bucuresti') || $judet === 'B')
            $judet = 'București';
        elseif ($judet)
            $judet = "Județ: $judet";

        $parts = [
            $this->street,
            $this->streetNumber ? "nr. $this->streetNumber" : null,
            $this->details,
            $this->city,
            $judet,
            $this->postalCode ? "CP: $this->postalCode" : null,
            $this->country,
        ];

        return implode(', ', array_filter($parts));
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
}
