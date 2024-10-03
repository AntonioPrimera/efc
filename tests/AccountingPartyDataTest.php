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
