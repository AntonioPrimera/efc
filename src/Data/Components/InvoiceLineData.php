<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class InvoiceLineData extends Data
{
    public function __construct(
        public string|null $id,
        public string|null $quantity,
        public string|null $uom,
        public string|null $amount,
        public string|null $unitPrice,
        public string|null $currency,
        public string|null $note,
        public string|null $orderLineReference,
        public InvoiceLineItemData|null $item,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        $invoicedQuantity = $xml->quantityNode('InvoicedQuantity');
        $lineExtensionAmount = $xml->priceNode('LineExtensionAmount');
        $unitPrice = $xml->priceNode('Price.PriceAmount');
        return new self(
            id: $xml->get('ID'),
            quantity: $invoicedQuantity->quantity,
            uom: $invoicedQuantity->uom,
            amount: $lineExtensionAmount->amount,
            unitPrice: $unitPrice->amount,
            currency: $unitPrice->currency ?: $lineExtensionAmount->currency ?: 'RON',
            note: implode("\n", $xml->getValues('Note')) ?: null,
            orderLineReference: $xml->get('OrderLineReference.LineID'),
            item: data($xml->node('Item'), InvoiceLineItemData::class),
        );
    }
}
