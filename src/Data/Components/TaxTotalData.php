<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class TaxTotalData extends Data
{
    public function __construct(
        public string|null $amount,
        public string|null $currency,
        /** @var array<TaxSubTotalData> */
        public array $taxSubtotal,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        $taxAmount = $xml->priceNode('TaxAmount');
        return new self(
            amount: $taxAmount->amount,
            currency: $taxAmount->currency,
            taxSubtotal: array_map(
                fn(EFacturaXml $xml) => TaxSubTotalData::fromXml($xml),
                $xml->getNodes('TaxSubtotal')
            ),
        );
    }
}
