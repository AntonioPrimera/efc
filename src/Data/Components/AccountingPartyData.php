<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Illuminate\Support\Str;
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
        $cif = $xml->get('PartyTaxScheme.CompanyID')
            ?? $xml->get('PartyIdentification.ID')
            ?? $xml->get('PartyLegalEntity.CompanyID'); //this is usually regCom, but sometimes it's CIF (used as a last resort)
        $name = $xml->get('PartyName.Name') ?? $xml->get('PartyLegalEntity.RegistrationName');
        $address = data($xml->node('PostalAddress'), AddressData::class);

        //for personal invoices, the CNP is used as a CIF (if no valid cnp is found, a hash of the name is used)
        if (!static::isCompany($cif))
            $cif = static::pfUid($cif, $name, $address);

        return new self(
            name: $name,
            cif: $cif,
            regCom: isRegCom($regCom) ? $regCom : null,
            address: $address,
            contact: data($xml->node('Contact'), ContactData::class),
        );
    }

    //--- Protected helpers -------------------------------------------------------------------------------------------

    protected static function pfUid(string $cif, string $name, AddressData|null $address): string
    {
        //if a valid CNP is found, use it as the CIF
        if (static::isValidCnp($cif))
            return $cif;

        $city = $address?->city ?? '';
        $street = $address?->street
            ? str_replace(['strada', 'str', 'bulevard', 'bd', 'blvd', 'bld', 'intrarea', 'numar', 'nr' ], '', strtolower($address->street))
            : '';

        return hash('sha256', Str::slug("$name-$city-$street"));
    }

    protected static function isCompany(string|null $cif): bool
    {
        return cif($cif)->isValid();
    }

    protected static function isValidCnp(string|null $cnp): bool
    {
        // Ensure the CNP is 13 digits
        if (strlen($cnp) !== 13 || !is_numeric($cnp) || $cnp[0] === '0')
            return false;

        // Define the constant array for multiplication
        $constant = [2, 7, 9, 1, 4, 6, 3, 5, 8, 2, 7, 9];
        $sum = 0;

        // Loop through the first 12 digits and multiply by the constant
        for ($i = 0; $i < 12; $i++)
            $sum += (int) $cnp[$i] * $constant[$i];

        // Calculate the control digit
        $controlDigit = $sum % 11;

        if ($controlDigit == 10)
            $controlDigit = 1;

        // Compare the control digit with the 13th digit of the CNP
        return $controlDigit == (int) $cnp[12];
    }
}
