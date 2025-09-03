<?php

use AntonioPrimera\Efc\Data\EFacturaData;
use AntonioPrimera\Efc\EFacturaXml;
use AntonioPrimera\Efc\Enums\InvoiceType;

it('can parse invoice with n2/n3 namespace prefixes - first invoice', function () {
    $xml = EFacturaXml::fromFile(__DIR__ . '/Context/invoices/n2-5398024485.xml');
    expect($xml)->toBeInstanceOf(EFacturaXml::class);

    // Test that namespace prefixes are detected correctly
    $invoiceData = EFacturaData::fromXml($xml);
    expect($invoiceData)->toBeInstanceOf(EFacturaData::class);

    // Test basic invoice data
    expect($invoiceData->efId)->toBe('511028910')
        ->and($invoiceData->issueDate)->toBe('2025-08-25')
        ->and($invoiceData->dueDate)->toBe('2025-08-25')
        ->and($invoiceData->type)->toBe(InvoiceType::Factura)
        ->and($invoiceData->documentCurrencyCode)->toBe('RON')
        ->and($invoiceData->buyerReference)->toBe('Vanzare');

    // Test order reference parsing with n2 namespace
    expect($invoiceData->purchaseOrderReference)->toBe('511047508');

    // Test vendor data parsing
    expect($invoiceData->vendor)->toBeInstanceOf(\AntonioPrimera\Efc\Data\Components\AccountingPartyData::class)
        ->and($invoiceData->vendor->name)->toBe('S.C. Doka Romania Tehnica Cofrajelor S.R.L.')
        ->and($invoiceData->vendor->cif)->toBe('RO11267586')
        ->and($invoiceData->vendor->regCom)->toBe('J2004001180235')
        ->and($invoiceData->vendor->address)->toContain('Com Floresti', 'Floresti', '407280', 'CJ')
        ->and($invoiceData->vendor->contact->name)->toBe('Roxana Ionescu')
        ->and($invoiceData->vendor->contact->phone)->toBe('+40 724 392 454')
        ->and($invoiceData->vendor->contact->email)->toBe('roxana-gabriela.ionescu@doka.com');

    // Test customer data parsing
    expect($invoiceData->customer)->toBeInstanceOf(\AntonioPrimera\Efc\Data\Components\AccountingPartyData::class)
        ->and($invoiceData->customer->name)->toBe('SC AMBRUS A&B CONSULTING SRL')
        ->and($invoiceData->customer->cif)->toBe('RO34948765');

    ray($invoiceData->delivery->location);
    // Test delivery data parsing
    expect($invoiceData->delivery)->toBeInstanceOf(\AntonioPrimera\Efc\Data\Components\DeliveryData::class)
        ->and($invoiceData->delivery->location)->toBeInstanceOf(\AntonioPrimera\Efc\Data\Components\DeliveryLocationData::class)
        ->and($invoiceData->delivery->location->address)->toBeInstanceOf(\AntonioPrimera\Efc\Data\Components\AddressData::class)
        ->and($invoiceData->delivery->location->address->fullAddress())->toContain('Com. Capleni', 'Capleni', '447080', 'SM');

    // Test payment means parsing
    expect($invoiceData->paymentMeans)->toBeInstanceOf(\AntonioPrimera\Efc\Data\Components\PaymentMeansData::class)
        ->and($invoiceData->paymentMeans->code)->toBe('31')
        ->and($invoiceData->paymentMeans->payeeIban)->toBe('RO74RZBR0000060002405825')
        ->and($invoiceData->paymentMeans->payeeName)->toBe('Raiffeisen Bank');

    // Test tax total parsing
    expect($invoiceData->taxTotal)->toBeInstanceOf(\AntonioPrimera\Efc\Data\Components\TaxTotalData::class)
        ->and($invoiceData->taxTotal->amount)->toBe('12631.50')
        ->and($invoiceData->taxTotal->currency)->toBe('RON')
        ->and($invoiceData->taxTotal->taxSubtotal)->toHaveCount(1)
        ->and($invoiceData->taxTotal->taxSubtotal[0]->taxableAmount)->toBe('60150.00')
        ->and($invoiceData->taxTotal->taxSubtotal[0]->taxAmount)->toBe('12631.50')
        ->and($invoiceData->taxTotal->taxSubtotal[0]->taxCategory->id)->toBe('S')
        ->and($invoiceData->taxTotal->taxSubtotal[0]->taxCategory->percent)->toBe('21.00');

    // Test legal monetary total parsing
    expect($invoiceData->legalMonetaryTotal)->toBeInstanceOf(\AntonioPrimera\Efc\Data\Components\LegalMonetaryTotalData::class)
        ->and($invoiceData->legalMonetaryTotal->lineExtensionAmount)->toBe('60150.00')
        ->and($invoiceData->legalMonetaryTotal->taxExclusiveAmount)->toBe('60150.00')
        ->and($invoiceData->legalMonetaryTotal->taxInclusiveAmount)->toBe('72781.50')
        ->and($invoiceData->legalMonetaryTotal->payableAmount)->toBe('72781.50')
        ->and($invoiceData->legalMonetaryTotal->currency)->toBe('RON');

    // Test invoice lines parsing
    expect($invoiceData->lines)->toBeArray()->toHaveCount(2)
        ->and($invoiceData->lines[0]->id)->toBe('1')
        ->and($invoiceData->lines[0]->quantity)->toBe('500.00')
        ->and($invoiceData->lines[0]->uom)->toBe('H87')
        ->and($invoiceData->lines[0]->amount)->toBe('58750.00')
        ->and($invoiceData->lines[0]->unitPrice)->toBe('117.50')
        ->and($invoiceData->lines[0]->currency)->toBe('RON')
        ->and($invoiceData->lines[0]->item->name)->toBe('Placă 3S basic 27 200/50cm')
        ->and($invoiceData->lines[0]->item->classifiedTaxCategory->id)->toBe('S')
        ->and($invoiceData->lines[0]->item->classifiedTaxCategory->percent)->toBe('21.00')
        ->and($invoiceData->lines[1]->id)->toBe('2')
        ->and($invoiceData->lines[1]->item->name)->toBe('Transport')
        ->and($invoiceData->lines[1]->amount)->toBe('1400.00');
});

it('can parse invoice with n2/n3 namespace prefixes - second invoice', function () {
    $xml = EFacturaXml::fromFile(__DIR__ . '/Context/invoices/n2-5405218443.xml');
    expect($xml)->toBeInstanceOf(EFacturaXml::class);

    // Test that namespace prefixes are detected correctly
    $invoiceData = EFacturaData::fromXml($xml);
    expect($invoiceData)->toBeInstanceOf(EFacturaData::class);

    // Test basic invoice data
    expect($invoiceData->efId)->toBe('511028952')
        ->and($invoiceData->issueDate)->toBe('2025-08-27')
        ->and($invoiceData->dueDate)->toBe('2025-08-27')
        ->and($invoiceData->type)->toBe(InvoiceType::Factura)
        ->and($invoiceData->documentCurrencyCode)->toBe('RON')
        ->and($invoiceData->buyerReference)->toBe('Vanzare');

    // Test order reference parsing with n2 namespace
    expect($invoiceData->purchaseOrderReference)->toBe('511047590');

    // Test that vendor and customer data are parsed correctly
    expect($invoiceData->vendor)->toBeInstanceOf(\AntonioPrimera\Efc\Data\Components\AccountingPartyData::class)
        ->and($invoiceData->vendor->name)->toBeString()->not->toBeEmpty()
        ->and($invoiceData->vendor->cif)->toBeString()->not->toBeEmpty();

    expect($invoiceData->customer)->toBeInstanceOf(\AntonioPrimera\Efc\Data\Components\AccountingPartyData::class)
        ->and($invoiceData->customer->name)->toBeString()->not->toBeEmpty()
        ->and($invoiceData->customer->cif)->toBeString()->not->toBeEmpty();

    // Test that monetary totals are parsed
    expect($invoiceData->legalMonetaryTotal)->toBeInstanceOf(\AntonioPrimera\Efc\Data\Components\LegalMonetaryTotalData::class)
        ->and($invoiceData->legalMonetaryTotal->payableAmount)->toBeString()->not->toBeEmpty()
        ->and($invoiceData->legalMonetaryTotal->currency)->toBe('RON');

    // Test that invoice lines are parsed
    expect($invoiceData->lines)->toBeArray()->not->toBeEmpty();

    // Test at least one line is properly parsed
    if (count($invoiceData->lines) > 0) {
        expect($invoiceData->lines[0]->id)->toBeString()->not->toBeEmpty()
            ->and($invoiceData->lines[0]->item)->toBeInstanceOf(\AntonioPrimera\Efc\Data\Components\InvoiceLineItemData::class)
            ->and($invoiceData->lines[0]->item->name)->toBeString()->not->toBeEmpty();
    }
});

it('can handle dynamic namespace prefix detection correctly', function () {
    // Test with standard cac/cbc namespaces
    $xmlStandard = EFacturaXml::fromFile(__DIR__ . '/Context/4344790293.xml');
    $pathStandard = $xmlStandard->path('ID', true, true);
    expect($pathStandard)->toContain('cbc:ID');

    // Test with custom namespaces
    $xmlN2N3 = EFacturaXml::fromFile(__DIR__ . '/Context/invoices/n2-5398024485.xml');
    $pathN2N3 = $xmlN2N3->path('ID', true, true);
    expect($pathN2N3)->toContain('zzz:ID');

    $pathN2N3Aggregate = $xmlN2N3->path('OrderReference', false, true);
    expect($pathN2N3Aggregate)->toContain('xxx:OrderReference');
});

it('maintains backwards compatibility with existing invoices', function () {
    // Test that existing functionality still works with standard namespaces
    $xml = EFacturaXml::fromFile(__DIR__ . '/Context/4344790293.xml');
    $invoiceData = EFacturaData::fromXml($xml);

    expect($invoiceData)->toBeInstanceOf(EFacturaData::class)
        ->and($invoiceData->efId)->toBe('PN2044643-')
        ->and($invoiceData->vendor->name)->toBe('PORSCHE INTER AUTO ROMANIA SRL')
        ->and($invoiceData->customer->name)->toBe('AGRORAL SERV SRL')
        ->and($invoiceData->lines)->toHaveCount(14);
});
