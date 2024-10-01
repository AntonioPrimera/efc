<?php

use AntonioPrimera\Efc\Enums\InvoiceType;
use AntonioPrimera\FileSystem\Folder;
use AntonioPrimera\Efc\Data\Components\AccountingPartyData;
use AntonioPrimera\Efc\Data\Components\AddressData;
use AntonioPrimera\Efc\Data\Components\AttachmentData;
use AntonioPrimera\Efc\Data\Components\ContactData;
use AntonioPrimera\Efc\Data\Components\DeliveryData;
use AntonioPrimera\Efc\Data\Components\InvoiceLineItem\TaxCategoryData;
use AntonioPrimera\Efc\Data\Components\InvoiceLineData;
use AntonioPrimera\Efc\Data\Components\InvoiceLineItemData;
use AntonioPrimera\Efc\Data\Components\LegalMonetaryTotalData;
use AntonioPrimera\Efc\Data\Components\PaymentMeansData;
use AntonioPrimera\Efc\Data\Components\TaxSubTotalData;
use AntonioPrimera\Efc\Data\Components\TaxTotalData;
use AntonioPrimera\Efc\Data\EfacturaData;
use AntonioPrimera\Efc\EFacturaXml;

beforeEach(function () {
    $this->eFacturaFolder = Folder::instance(__DIR__ . '/Context');
    $this->xml = EFacturaXml::fromFile(__DIR__ . '/Context/4344790293.xml');
    expect($this->xml)->toBeInstanceOf(EFacturaXml::class);
    config([
        'data.features.cast_and_transform_iterables' => true,
        'data.features.ignore_exception_when_trying_to_set_computed_property_value' => true,
    ]);
});

it ('can parse a complete efactura from an xml file', function () {
    /* @var EFacturaData $f */
    $f = EFacturaData::fromXml($this->xml);

    expect($f->efId)->toBe('PN2044643-')
        ->and($f->issueDate)->toBe('2024-06-28')
        ->and($f->dueDate)->toBe('2024-06-28')
        ->and($f->type)->toBe(InvoiceType::Factura)
        ->and($f->note)->toStartWith("Vehicle Identification Code: TMBGK7NW2M3100992\nVehicle Milage: 72572 km\n")
        ->and($f->documentCurrencyCode)->toBe('RON')
        ->and($f->accountingCost)->toBeNull()
        ->and($f->buyerReference)->toBe('2024019803')

        //OrderReferenceData
        ->and($f->purchaseOrderReference)->toBe('2024019803')
        ->and($f->salesOrderReference)->toBe('2024019803')

        //BillingReferenceData
        ->and($f->billingReferences)->toBeArray()->toBeEmpty()

        //Vendor (AccountingPartyData)
        ->and($f->vendor)->toBeInstanceOf(AccountingPartyData::class)
        ->and($f->vendor->name)->toBe('PORSCHE INTER AUTO ROMANIA SRL')
        ->and($f->vendor->cif)->toBe('RO22188461')
        ->and($f->vendor->regCom)->toBe('J23/2067/2007')
        ->and($f->vendor->address)->toBeInstanceOf(AddressData::class)
        ->and($f->vendor->address->street)->toBe('Bd Pipera, NR. 2')
        ->and($f->vendor->address->city)->toBe('Voluntari Jud Ilfov')
        ->and($f->vendor->address->postalCode)->toBe('077190')
        ->and($f->vendor->address->country)->toBe('RO')
        ->and($f->vendor->address->county)->toBe('IF')
        ->and($f->vendor->contact)->toBeInstanceOf(ContactData::class)
        ->and($f->vendor->contact->name)->toBe('PORSCHE INTER AUTO ROMANIA SRL')
        ->and($f->vendor->contact->phone)->toBe('0040212083610')
        ->and($f->vendor->contact->email)->toBe('conta.pia@porsche.ro')

        //Customer (AccountingPartyData)
        ->and($f->customer)->toBeInstanceOf(AccountingPartyData::class)
        ->and($f->customer->name)->toBe('AGRORAL SERV SRL')

        //Delivery
        ->and($f->delivery)->toBeInstanceOf(DeliveryData::class)
        ->and($f->delivery->date)->toBe('2024-06-28')
        ->and($f->delivery->location)->toBeNull()
        ->and($f->delivery->party)->toBeNull()

        //PaymentMeans
        ->and($f->paymentMeans)->toBeInstanceOf(PaymentMeansData::class)
        ->and($f->paymentMeans->code)->toBe('42')
        ->and($f->paymentMeans->payeeIban)->toBe('RO70RZBR0000060009434129')
        ->and($f->paymentMeans->payeeName)->toBe('PORSCHE INTER AUTO ROMANIA SRL')

        //TaxTotal
        ->and($f->taxTotal)->toBeInstanceOf(TaxTotalData::class)
        ->and($f->taxTotal->amount)->toBe('291.08')
        ->and($f->taxTotal->currency)->toBe('RON')
        ->and($f->taxTotal->taxSubtotal)->toHaveCount(2)
        ->and($f->taxTotal->taxSubtotal[0])->toBeInstanceOf(TaxSubTotalData::class)
        ->and($f->taxTotal->taxSubtotal[0]->taxableAmount)->toBe('1532.02')
        ->and($f->taxTotal->taxSubtotal[0]->taxAmount)->toBe('291.08')
        ->and($f->taxTotal->taxSubtotal[0]->currency)->toBe('RON')
        ->and($f->taxTotal->taxSubtotal[0]->taxCategory)->toBeInstanceOf(TaxCategoryData::class)
        ->and($f->taxTotal->taxSubtotal[0]->taxCategory->id)->toBe('S')
        ->and($f->taxTotal->taxSubtotal[0]->taxCategory->percent)->toBe('19.00')
        ->and($f->taxTotal->taxSubtotal[0]->taxCategory->taxScheme)->toBe('VAT')
        ->and($f->taxTotal->taxSubtotal[1])->toBeInstanceOf(TaxSubTotalData::class)
        ->and($f->taxTotal->taxSubtotal[1]->taxableAmount)->toBe('0.00')
        ->and($f->taxTotal->taxSubtotal[1]->taxCategory->exemptionReasonCode)->toBeNull()
        ->and($f->taxTotal->taxSubtotal[1]->taxCategory->exemptionReason)->toBe('Excempted from VAT')

        //LegalMonetaryTotal
        ->and($f->legalMonetaryTotal)->toBeInstanceOf(LegalMonetaryTotalData::class)
        ->and($f->legalMonetaryTotal->lineExtensionAmount)->toBe('1532.02')
        ->and($f->legalMonetaryTotal->taxExclusiveAmount)->toBe('1532.02')
        ->and($f->legalMonetaryTotal->taxInclusiveAmount)->toBe('1823.10')
        ->and($f->legalMonetaryTotal->allowanceTotalAmount)->toBe('0.00')
        ->and($f->legalMonetaryTotal->chargeTotalAmount)->toBe('0.00')
        ->and($f->legalMonetaryTotal->prepaidAmount)->toBe('0.00')
        ->and($f->legalMonetaryTotal->payableAmount)->toBe('1823.10')
        ->and($f->legalMonetaryTotal->currency)->toBe('RON')

        //Attachment
        ->and($f->attachment)->toBeNull()

        //InvoiceLines (just the count and the first line)
        ->and($f->lines)->toBeArray()->toHaveCount(14)
        ->and($f->lines[0])->toBeInstanceOf(InvoiceLineData::class)
        ->and($f->lines[0]->id)->toBe('2')
        ->and($f->lines[0]->quantity)->toBe('1.00')
        ->and($f->lines[0]->uom)->toBe('H87')
        ->and($f->lines[0]->amount)->toBe('99.76')
        ->and($f->lines[0]->unitPrice)->toBe('99.76')
        ->and($f->lines[0]->currency)->toBe('RON')
        ->and($f->lines[0]->note)->toBeNull()
        ->and($f->lines[0]->orderLineReference)->toBeNull()
        ->and($f->lines[0]->item)->toBeInstanceOf(InvoiceLineItemData::class)
        ->and($f->lines[0]->item->name)->toBe('FILTRU ULE Se utilizeaza 04E115561T')
        ->and($f->lines[0]->item->description)->toBeNull()
        ->and($f->lines[0]->item->sellersItemIdentification)->toBe('04E115561AC')
        ->and($f->lines[0]->item->buyersItemIdentification)->toBeNull()
        ->and($f->lines[0]->item->standardItemIdentification)->toBeNull()
        ->and($f->lines[0]->item->originCountry)->toBeNull()
        ->and($f->lines[0]->item->commodityClassification)->toBe('HS:8421230090')
        ->and($f->lines[0]->item->classifiedTaxCategory)->toBeInstanceOf(TaxCategoryData::class)
        ->and($f->lines[0]->item->classifiedTaxCategory->id)->toBe('S')
        ->and($f->lines[0]->item->classifiedTaxCategory->percent)->toBe('19.00')
        ->and($f->lines[0]->item->classifiedTaxCategory->taxScheme)->toBe('VAT')
        ->and($f->lines[0]->item->additionalItemProperties)->toBeArray()
        ->and($f->lines[0]->item->additionalItemProperties)->toHaveCount(10)
        ->and($f->lines[0]->item->additionalItemProperties['Vehicle Identification Code'])->toBe('TMBGK7NW2M3100992')
        ->and($f->lines[0]->item->additionalItemProperties['Vehicle Milage'])->toBe('72572 km')

        //and the last line
        ->and($f->lines[13])->toBeInstanceOf(InvoiceLineData::class)
        ->and($f->lines[13]->id)->toBe('24')
        ->and($f->lines[13]->unitPrice)->toBe('0.00')
    ;
});

it ('can parse the other invoices successfully', function () {
    $invoices = $this->eFacturaFolder->subFolder('invoices')->getFiles('/\.xml$/');
    $invoiceCount = count($invoices);

    $timer = microtime(true);
    foreach ($invoices as $invoice)
        expect(EFacturaData::from(EFacturaXml::fromFile($invoice)))->toBeInstanceOf(EFacturaData::class);

    $time = intval((microtime(true) - $timer) * 1000);
    $average = number_format($time / $invoiceCount, 2);
    $perSecond = intval($invoiceCount / ($time / 1000));
    ray("Time to parse $invoiceCount invoices: $time (ms). Average: $average (ms / invoice). Speed: $perSecond (invoices / second)");
});

it('can extract a pdf attachment from an efactura xml', function () {
    //set up the attachments folder and delete it and its contents if it already exists
    $attachmentsFolder = $this->eFacturaFolder->subFolder('attachments');
    $attachmentsFolder->delete(true);

    $f = EFacturaData::from(EFacturaXml::fromFile($this->eFacturaFolder->file('4298367633.xml')));
    expect($f->attachment)->toBeInstanceOf(AttachmentData::class)
        ->and($f->attachment->filename)->toBe('24MI06909718.pdf')
        ->and($f->attachment->mimeType)->toBe('application/pdf')
        ->and($f->attachment->fileContents)->toBeString()->toStartWith('JVBERi0')
        ->and($f->attachment->extractToFolder($attachmentsFolder))
            ->toBeInstanceOf(\AntonioPrimera\FileSystem\File::class)
            ->exists->toBeTrue()
            ->name->toBe('24MI06909718.pdf');
});
