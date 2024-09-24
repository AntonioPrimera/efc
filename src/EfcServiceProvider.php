<?php
namespace AntonioPrimera\Efc;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EfcServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('efc')
            ->hasMigration('create_invoices_table');
    }
}
