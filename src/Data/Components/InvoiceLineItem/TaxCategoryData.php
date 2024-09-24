<?php
namespace AntonioPrimera\Efc\Data\Components\InvoiceLineItem;

use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class TaxCategoryData extends Data
{
    public function __construct(
        public string|null $id,
        public string|null $percent,
        public string|null $exemptionReasonCode,
        public string|null $exemptionReason,
        public string|null $taxScheme,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        return new self(
            id: $xml->get('ID'),
            percent: $xml->get('Percent'),
            exemptionReasonCode: $xml->get('TaxExemptionReasonCode'),
            exemptionReason: $xml->get('TaxExemptionReason'),
            taxScheme: $xml->get('TaxScheme.ID'),
        );
    }
}
