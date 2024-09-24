<?php
namespace AntonioPrimera\Efc\Data;

use AntonioPrimera\Efc\Data\Components\AccountingPartyData;
use AntonioPrimera\Efc\Data\Components\AttachmentData;
use AntonioPrimera\Efc\Data\Components\BillingReferenceData;
use AntonioPrimera\Efc\Data\Components\DeliveryData;
use AntonioPrimera\Efc\Data\Components\InvoiceLineData;
use AntonioPrimera\Efc\Data\Components\LegalMonetaryTotalData;
use AntonioPrimera\Efc\Data\Components\PaymentMeansData;
use AntonioPrimera\Efc\Data\Components\TaxTotalData;
use AntonioPrimera\FileSystem\File;
use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

/**
 * UBL-Invoice Definition:
 * https://docs.peppol.eu/poacc/billing/3.0/2023-Q4/syntax/ubl-invoice/tree/
 */
#[MapName(SnakeCaseMapper::class)]
class EFacturaData extends Data
{
    public function __construct(
        public string|null $efId,
        public string|null $issueDate,
        public string|null $dueDate,
        public string|null $invoiceTypeCode,
        public string|null $note,
        public string|null $documentCurrencyCode,
        public string|null $accountingCost,
        public string|null $buyerReference,
        public string|null $purchaseOrderReference,
        public string|null $salesOrderReference,
        /** @var array<BillingReferenceData> */
        public array $billingReferences,     //todo: this can be an array of references
        public AccountingPartyData|null $vendor,
        public AccountingPartyData|null $customer,
        public DeliveryData|null $delivery,
        public PaymentMeansData|null $paymentMeans,
        public TaxTotalData|null $taxTotal,
        public LegalMonetaryTotalData|null $legalMonetaryTotal,
        /** @var array<InvoiceLineData> */
        public array $lines,
        public AttachmentData|null $attachment,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        return new self(
            efId: $xml->get('ID'),
            issueDate: $xml->get('IssueDate'),
            dueDate: $xml->get('DueDate'),
            invoiceTypeCode: $xml->get('InvoiceTypeCode'),
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

    public static function fromXmlFile(File $file): self
    {
        return self::fromXml(EFacturaXml::fromFile($file));
    }
}
