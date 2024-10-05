<?php

use AntonioPrimera\Efc\Data\EFacturaData;
use AntonioPrimera\Efc\EFacturaXml;
use AntonioPrimera\FileSystem\Folder;

beforeEach(function () {
    $this->eFacturaFolder = Folder::instance(__DIR__ . '/Context');
});

it('can extract accounting party data from PartyLegalEntity data', function () {
    $f = EFacturaData::from(EFacturaXml::fromFile($this->eFacturaFolder->file('invoices/4407242534.xml')));

    expect($f->efId)->toBe('259106355338')
        ->and($f->vendor->name)->toBe('DANTE INTERNATIONAL SA')
        ->and($f->vendor->cif)->toBe('RO14399840')
        ->and($f->vendor->regCom)->toBe('J40/372/2002')
        ->and($f->vendor->address->street)->toBe('Sos. Virtutii nr. 148, spatiul E47')
        ->and($f->vendor->address->streetNumber)->toBeNull()
        ->and($f->vendor->address->city)->toBe('SECTOR6')
        ->and($f->vendor->address->postalCode)->toBe('060787')
        ->and($f->vendor->address->country)->toBe('RO')
        ->and($f->vendor->address->county)->toBe('B')
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
        ->and($f->vendor->address->street)->toBe('ALEXEI TOLSTOI 8')
        ->and($f->vendor->address->streetNumber)->toBeNull()
        ->and($f->vendor->address->city)->toBe('Bacau')
        ->and($f->vendor->address->postalCode)->toBe('600093')
        ->and($f->vendor->address->country)->toBe('RO')
        ->and($f->vendor->address->county)->toBe('BC')
        ->and($f->vendor->contact->name)->toBeNull()
        ->and($f->vendor->contact->phone)->toBe('0234525525')
        ->and($f->vendor->contact->email)->toBe('suportclienti@dedeman.ro')

        ->and($f->customer->name)->toBe('ENJOY BSM CONSULTING SRL')
        ->and($f->customer->cif)->toBe('42009129')
        ->and($f->customer->regCom)->toBeNull()
        ->and($f->customer->address->street)->toBe('BD FERDINAND I 118')
        ->and($f->customer->address->streetNumber)->toBeNull()
        ->and($f->customer->address->city)->toBe('SECTOR2')
        ->and($f->customer->address->postalCode)->toBeNull()
        ->and($f->customer->address->country)->toBe('RO')
        ->and($f->customer->address->county)->toBe('B')
        ->and($f->customer->contact)->toBeNull();
});
