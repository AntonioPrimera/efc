<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class LegalMonetaryTotalData extends Data
{
    //note: each value should have a currency attribute, but for now it should be enough to have one for all amounts
    public function __construct(
        public string|null $lineExtensionAmount,
        public string|null $taxExclusiveAmount,
        public string|null $taxInclusiveAmount,
        public string|null $allowanceTotalAmount,
        public string|null $chargeTotalAmount,
        public string|null $prepaidAmount,
        public string|null $payableAmount,
        public string|null $currency,   //in later versions, maybe each amount should have its own currency
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        return new self(
            lineExtensionAmount: $xml->get('LineExtensionAmount'),
            taxExclusiveAmount: $xml->get('TaxExclusiveAmount'),
            taxInclusiveAmount: $xml->get('TaxInclusiveAmount'),
            allowanceTotalAmount: $xml->get('AllowanceTotalAmount'),
            chargeTotalAmount: $xml->get('ChargeTotalAmount'),
            prepaidAmount: $xml->get('PrepaidAmount'),
            payableAmount: $xml->get('PayableAmount'),
            currency: $xml->getValueNode('PayableAmount')?->attribute('currencyID', 'RON'),
        );
    }
}
