<?php
namespace AntonioPrimera\Efc\Data\Parsers;

use AntonioPrimera\Efc\Data\Components\InvoiceLineData;
use AntonioPrimera\Efc\Data\Components\InvoiceLineItemData;
use AntonioPrimera\Efc\EFacturaXml;

class CreditNoteLineParser
{
    public static function parse(EFacturaXml $xml): InvoiceLineData
    {
        $invoicedQuantity = $xml->quantityNode('CreditedQuantity');
        $lineExtensionAmount = $xml->priceNode('LineExtensionAmount');
        $unitPrice = $xml->priceNode('Price.PriceAmount');

        return new InvoiceLineData(
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
