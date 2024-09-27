<?php
namespace AntonioPrimera\Efc\Models;

use AntonioPrimera\Efc\Data\Components\BillingReferenceData;
use AntonioPrimera\Efc\Data\Components\DeliveryData;
use AntonioPrimera\Efc\Data\Components\InvoiceLineData;
use AntonioPrimera\Efc\Data\Components\LegalMonetaryTotalData;
use AntonioPrimera\Efc\Data\Components\PaymentMeansData;
use AntonioPrimera\Efc\Data\Components\TaxTotalData;
use AntonioPrimera\Efc\Enums\InvoiceType;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\DataCollection;

abstract class Invoice extends Model
{
    protected static array $invoiceDataCasts = [
        'type' => InvoiceType::class,
        'issue_date' => 'date',
        'due_date' => 'date',
        'billing_references' => DataCollection::class.':'.BillingReferenceData::class,
        'delivery' => DeliveryData::class,
        'payment_means' => PaymentMeansData::class,
        'tax_total' => TaxTotalData::class,
        'legal_monetary_total' => LegalMonetaryTotalData::class,
        'lines' => DataCollection::class.':'.InvoiceLineData::class,
    ];
}
