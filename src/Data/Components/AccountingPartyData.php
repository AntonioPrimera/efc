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
        public string|null $address,        //changed from AddressData to string
        public ContactData|null $contact,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        $xml = $xml->node('Party');
        $regCom = static::determineRegCom($xml);
        $cif = static::determineCif($xml);
        $name = $xml->get('PartyName.Name') ?? $xml->get('PartyLegalEntity.RegistrationName');

        //parse the address data, but only the full address is used as a string
        /* @var AddressData $address */
        $address = data($xml->node('PostalAddress'), AddressData::class);

        //for personal invoices, the CNP is used as a CIF (if no valid cnp is found, a hash of the name is used)
        if (!$cif)
            $cif = static::determineCNP($xml) ?? static::pfUid($name, $address);

        return new self(
            name: $name,
            cif: $cif,
            regCom: $regCom,
            address: $address->fullAddress(),   //this is a string, not an AddressData object
            contact: data($xml->node('Contact'), ContactData::class),
        );
    }

    //--- Protected helpers -------------------------------------------------------------------------------------------

    protected static function determineCif(EFacturaXml $xml): string|null
    {
        $cif = $xml->get('PartyTaxScheme.CompanyID');
        if ($cif && cif($cif)->isValid())
            return $cif;

        $cif = $xml->get('PartyIdentification.ID');
        if ($cif && cif($cif)->isValid())
            return $cif;

        //this is usually regCom, but sometimes it's CIF (used as a last resort)
        $cif = $xml->get('PartyLegalEntity.CompanyID');
        if ($cif && cif($cif)->isValid())
            return $cif;

        return null;
    }

    protected static function determineRegCom(EFacturaXml $xml): string|null
    {
        $regCom = $xml->get('PartyLegalEntity.CompanyID');
        if ($regCom && isRegCom($regCom))
            return $regCom;

        //this is usually CIF, but sometimes it's regCom (used as a last resort)
        $regCom = $xml->get('PartyIdentification.ID');
        if ($regCom && isRegCom($regCom))
            return $regCom;

        return null;
    }

    protected static function determineCNP(EFacturaXml $xml): string|null
    {
        $cnp = $xml->get('PartyTaxScheme.CompanyID');
        if ($cnp && static::isValidCnp($cnp))
            return $cnp;

        $cnp = $xml->get('PartyIdentification.ID');
        if ($cnp && static::isValidCnp($cnp))
            return $cnp;

        $cnp = $xml->get('PartyLegalEntity.CompanyID');
        if ($cnp && static::isValidCnp($cnp))
            return $cnp;

        return null;
    }

    /**
     * Generates a unique identifier based on the provided name and address data.
     * This should uniquely identify a person, even if no CNP is provided.
     */
    protected static function pfUid(string $name, AddressData|null $address): string
    {
        $city = $address?->city ?? '';
        $street = $address?->street
            ? str_replace(['strada', 'str', 'bulevard', 'bd', 'blvd', 'bld', 'intrarea', 'numar', 'nr' ], '', strtolower($address->street))
            : '';

        return hash('sha256', Str::slug("$name-$city-$street"));
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
