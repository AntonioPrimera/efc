<?php

use AntonioPrimera\Efc\Actions\GetEFacturaFromZipFile;
use AntonioPrimera\Efc\Data\EFacturaData;
use AntonioPrimera\FileSystem\File;

it('can extract an EFacturaData object from a zip file', function () {
    //expect(preg_match('/\.xml$/', '4344790293.xml'))->toBe(1);
    $zipFile = File::instance(__DIR__ . '/Context/4344790293.zip');

    $invoiceData = GetEFacturaFromZipFile::run($zipFile);

    expect($invoiceData)->toBeInstanceOf(EFacturaData::class)
        ->and($invoiceData->efId)->toBe('PN2044643-');
});
