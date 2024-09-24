<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class BillingReferenceData extends Data
{
    public function __construct(
        public string|null $invoiceId,
        public string|null $issueDate,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        return new self(
            invoiceId: $xml->get('InvoiceDocumentReference.ID'),
            issueDate: $xml->get('InvoiceDocumentReference.IssueDate'),
        );
    }
}
