<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\FileSystem\File;
use AntonioPrimera\FileSystem\Folder;
use AntonioPrimera\Efc\EFacturaXml;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class AttachmentData extends Data
{
    public function __construct(
        public string|null $mimeType,
        public string|null $filename,
        public string|null $fileContents,
        public string|null $url,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        $embeddedBinaryObject = $xml->valueNode('EmbeddedDocumentBinaryObject');
        if ($embeddedBinaryObject)
            return new self(
                mimeType: $embeddedBinaryObject->attribute('mimeCode'),
                filename: $embeddedBinaryObject->attribute('filename'),
                fileContents: $embeddedBinaryObject->value(),
                url: null,
            );

        $externalReferenceUrl = $xml->get('ExternalReference.URI');
        if ($externalReferenceUrl) {
            return new self(
                mimeType: null,
                filename: null,
                fileContents: null,
                url: $externalReferenceUrl,
            );
        }

        return AttachmentData::from([]);
    }

    public function extractToFolder(string|Folder $folder): File
    {
        return Folder::instance($folder)->file($this->filename)->putContents(base64_decode($this->fileContents));
    }

    public function extractToFile(string|File $file): File
    {
        return File::instance($file)->putContents(base64_decode($this->fileContents));
    }
}
