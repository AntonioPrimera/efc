<?php

use AntonioPrimera\Efc\Data\EFacturaData;
use AntonioPrimera\Efc\EFacturaXml;
use AntonioPrimera\FileSystem\Folder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->eFacturaFolder = Folder::instance(__DIR__ . '/Context');
});

it('can extract accounting party data from PartyLegalEntity data', function () {
    $f = EFacturaData::from(EFacturaXml::fromFile($this->eFacturaFolder->file('invoices/4407242534.xml')));

    expect($f->efId)->toBe('259106355338')
        ->and($f->vendor->name)->toBe('DANTE INTERNATIONAL SA')
        ->and($f->vendor->cif)->toBe('RO14399840')
        ->and($f->vendor->regCom)->toBe('J40/372/2002')
        ->and($f->vendor->address)->toContain('Sos. Virtutii nr. 148, spatiul E47', 'SECTOR6', '060787', 'RO')
        ->and($f->vendor->contact->name)->toBeNull()
        ->and($f->vendor->contact->phone)->toBe('+40212005200')
        ->and($f->vendor->contact->email)->toBeNull();
});

it('can extract cusomer accounting party data from PartyLegalEntity data', function () {
    $f = EFacturaData::from(EFacturaXml::fromFile($this->eFacturaFolder->file('invoices/4420596047.xml')));

    expect($f->efId)->toBe('8600951016')
        ->and($f->vendor->name)->toBe('DEDEMAN SRL')
        ->and($f->vendor->cif)->toBe('RO2816464')
        ->and($f->vendor->regCom)->toBe('J04/2621/1992')
        ->and($f->vendor->address)->toContain('ALEXEI TOLSTOI 8', 'Bacau', '600093', 'RO', 'BC')
        ->and($f->vendor->contact->name)->toBeNull()
        ->and($f->vendor->contact->phone)->toBe('0234525525')
        ->and($f->vendor->contact->email)->toBe('suportclienti@dedeman.ro')

        ->and($f->customer->name)->toBe('ENJOY BSM CONSULTING SRL')
        ->and($f->customer->cif)->toBe('42009129')
        ->and($f->customer->regCom)->toBeNull()
        ->and($f->customer->address)->toContain('BD FERDINAND I 118', 'SECTOR2', 'RO')
        ->and($f->customer->contact)->toBeNull();
});

it ('can extract accounting party data from person data', function() {
    $f = EFacturaData::from(EFacturaXml::fromFile($this->eFacturaFolder->file('invoices/4861499211-pf.xml')));

    expect($f->efId)->toBe('PAZF25004')
        ->and($f->customer->name)->toBe('BALABAN GEORGE')
        ->and($f->customer->cif)->toBe(hash('sha256', Str::slug('BALABAN GEORGE-Giurgiu-FREZIEI, 11')))    //Str::slug("$name|$city|$street")
        ->and($f->customer->regCom)->toBeNull()
        ->and($f->customer->address)->toContain('STR FREZIEI, NR. 11', 'Giurgiu', 'RO', 'GR');
});
