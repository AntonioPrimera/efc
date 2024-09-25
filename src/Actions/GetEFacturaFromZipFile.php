<?php
namespace AntonioPrimera\Efc\Actions;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\Efc\Data\EFacturaData;
use AntonioPrimera\Efc\Exceptions\InvalidXmlException;

/**
 * Unzips a ZIP file containing an EFactura XML file and returns the EFactura data
 */
class GetEFacturaFromZipFile
{

    public static function run(File|string $zipFile): EFacturaData
    {
        //unzip the archive to a temporary folder, in the same location as the zip file
        $unzipFolder = $zipFile->parentFolder->subFolder($zipFile->nameWithoutExtension . '-unzipped');
        $zipFile->unzipTo($unzipFolder);

        //get the first XML file from the unzipped folder (should be the only one)
        $xmlFile = array_values($unzipFolder->getFiles('/\.xml$/'))[0] ?? null;
        if (!$xmlFile)
            throw new InvalidXmlException("No XML file found in the ZIP archive [$zipFile->name].");

        //parse the XML file into an EFacturaData object and delete the temporary folder with the unzipped files
        $invoiceData = EFacturaData::fromXmlFile($xmlFile);
        $unzipFolder->delete(true);

        return $invoiceData;
    }
}
