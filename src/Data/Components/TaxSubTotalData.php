<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\Data\Components\InvoiceLineItem\TaxCategoryData;
use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class TaxSubTotalData extends Data
{
    public function __construct(
        public string|null $taxableAmount,
        public string|null $taxAmount,
        public string|null $currency,
        public TaxCategoryData|null $taxCategory,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        $taxableAmount = $xml->getValueNode('TaxableAmount');
        $taxAmount = $xml->getValueNode('TaxAmount');
        return new self(
            taxableAmount: $taxableAmount->value(),
            taxAmount: $taxAmount->value(),
            currency: $taxableAmount->attribute('currencyID') ?: $taxAmount->attribute('currencyID'),
            taxCategory: data($xml->node('TaxCategory'), TaxCategoryData::class),
        );
    }
}
