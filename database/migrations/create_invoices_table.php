<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vendor_id')->nullable()->constrained('bps')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('bps')->nullOnDelete();

            $table->integer('type')->nullable();                        //380 / 384 / 389 / 751
            $table->string('message_id')->nullable();                   //anaf message id

            $table->string('ef_id')->index()->nullable();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->text('note')->nullable();
            $table->string('document_currency_code')->nullable();
            $table->string('accounting_cost')->nullable();
            $table->string('buyer_reference')->nullable();
            $table->string('so_reference')->nullable();                 //sales order reference
            $table->string('po_reference')->nullable();                 //purchase order reference
            $table->json('billing_references')->nullable();
            $table->json('delivery')->nullable();
            $table->json('payment_means')->nullable();
            $table->json('tax_total')->nullable();
            $table->json('legal_monetary_total')->nullable();
            $table->json('lines')->nullable();

            $table->dateTime('uploaded_at')->nullable();
            $table->dateTime('sent_to_webhook_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
