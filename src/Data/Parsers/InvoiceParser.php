<?php
namespace AntonioPrimera\Efc\Data\Parsers;

use AntonioPrimera\Efc\Data\Components\AccountingPartyData;
use AntonioPrimera\Efc\Data\Components\AttachmentData;
use AntonioPrimera\Efc\Data\Components\BillingReferenceData;
use AntonioPrimera\Efc\Data\Components\DeliveryData;
use AntonioPrimera\Efc\Data\Components\InvoiceLineData;
use AntonioPrimera\Efc\Data\Components\LegalMonetaryTotalData;
use AntonioPrimera\Efc\Data\Components\PaymentMeansData;
use AntonioPrimera\Efc\Data\Components\TaxTotalData;
use AntonioPrimera\Efc\Data\EFacturaData;
use AntonioPrimera\Efc\EFacturaXml;
use AntonioPrimera\Efc\Enums\InvoiceType;

class InvoiceParser
{

    public static function parse(EFacturaXml $xml): EFacturaData
    {
        return new EFacturaData(
            efId: $xml->get('ID'),
            issueDate: $xml->get('IssueDate'),
            dueDate: $xml->get('DueDate'),
            type: InvoiceType::tryFrom($xml->get('InvoiceTypeCode')),
            note: implode("\n", $xml->getValues('Note')),
            documentCurrencyCode: $xml->get('DocumentCurrencyCode'),
            accountingCost: $xml->get('AccountingCost'),
            buyerReference: $xml->get('BuyerReference'),
            purchaseOrderReference: $xml->get('OrderReference.ID'),     //this is not straightforward, but it's in the definition
            salesOrderReference: $xml->get('OrderReference.SalesOrderID'),
            billingReferences: array_map(fn($node) => BillingReferenceData::fromXml($node), $xml->nodes('BillingReference')),
            vendor: data($xml->node('AccountingSupplierParty'), AccountingPartyData::class),
            customer: data($xml->node('AccountingCustomerParty'), AccountingPartyData::class),
            delivery: data($xml->node('Delivery'), DeliveryData::class),
            paymentMeans: data($xml->node('PaymentMeans'), PaymentMeansData::class),
            taxTotal: data($xml->node('TaxTotal'), TaxTotalData::class),
            legalMonetaryTotal: data($xml->node('LegalMonetaryTotal'), LegalMonetaryTotalData::class),
            lines: array_map(fn($node) => InvoiceLineData::fromXml($node), $xml->nodes('InvoiceLine')),
            attachment: data($xml->node('AdditionalDocumentReference.Attachment'), AttachmentData::class),
        );
    }
}
