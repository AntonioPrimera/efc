<?php
namespace AntonioPrimera\Efc\Models;

use AntonioPrimera\Efc\Data\Components\BillingReferenceData;
use AntonioPrimera\Efc\Data\Components\DeliveryData;
use AntonioPrimera\Efc\Data\Components\InvoiceLineData;
use AntonioPrimera\Efc\Data\Components\LegalMonetaryTotalData;
use AntonioPrimera\Efc\Data\Components\PaymentMeansData;
use AntonioPrimera\Efc\Data\Components\TaxTotalData;
use AntonioPrimera\Efc\Enums\InvoiceType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\DataCollection;

/**
 * @property int $id
 *
 * @property int|null $vendor_id
 * @property int|null $customer_id
 *
 * @property InvoiceType|null $type
 * @property string|null $ef_id
 * @property Carbon|null $issue_date
 * @property Carbon|null $due_date
 * @property string|null $note
 * @property string|null $document_currency_code
 * @property string|null $accounting_cost
 * @property string|null $buyer_reference
 * @property string|null $so_reference
 * @property string|null $po_reference
 * @property DataCollection|null $billing_references
 * @property DeliveryData|null $delivery
 * @property PaymentMeansData|null $payment_means
 * @property TaxTotalData|null $tax_total
 * @property LegalMonetaryTotalData|null $legal_monetary_total
 * @property DataCollection|null $lines
 */
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

/**
 * Schema::create('invoices', function (Blueprint $table) {
 * $table->id();
 *
 * $table->foreignId('vendor_id')->nullable()->constrained('bps')->nullOnDelete();
 * $table->foreignId('customer_id')->nullable()->constrained('bps')->nullOnDelete();
 *
 * $table->integer('type')->nullable();                        //380 / 384 / 389 / 751
 * $table->string('message_id')->nullable();                   //anaf message id
 *
 * $table->string('ef_id')->index()->nullable();
 * $table->date('issue_date')->nullable();
 * $table->date('due_date')->nullable();
 * $table->text('note')->nullable();
 * $table->string('document_currency_code')->nullable();
 * $table->string('accounting_cost')->nullable();
 * $table->string('buyer_reference')->nullable();
 * $table->string('so_reference')->nullable();                 //sales order reference
 * $table->string('po_reference')->nullable();                 //purchase order reference
 * $table->json('billing_references')->nullable();
 * $table->json('delivery')->nullable();
 * $table->json('payment_means')->nullable();
 * $table->json('tax_total')->nullable();
 * $table->json('legal_monetary_total')->nullable();
 * $table->json('lines')->nullable();
 *
 * $table->dateTime('uploaded_at')->nullable();
 * $table->dateTime('sent_to_webhook_at')->nullable();
 * $table->softDeletes();
 * $table->timestamps();
 * });
 */
