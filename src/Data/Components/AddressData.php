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
    #[Computed]
    public string $fullAddress;

    public function __construct(
        public string|null $street,
        public string|null $city,
        public string|null $postalCode,
        public string|null $country,
        public string|null $county,
    ) {
        $this->county = $this->county($county);
        $this->fullAddress = $this->fullAddress();
    }

    public static function fromXml(EFacturaXml $xml): self
    {
        return new self(
            street: $xml->get('StreetName'),
            city: $xml->get('CityName'),
            postalCode: $xml->get('PostalZone'),
            country: $xml->get('Country.IdentificationCode'),
            county: $xml->get('CountrySubentity'),
        );
    }

    protected function county(string $countyString): string
    {
        return str_starts_with($countyString, 'RO-') ? substr($countyString, 3) : $countyString;
    }

    protected function fullAddress(): string
    {
        return "{$this->street}, {$this->city}, Judet:{$this->county}, CP:{$this->postalCode}, {$this->country}";
    }
}
