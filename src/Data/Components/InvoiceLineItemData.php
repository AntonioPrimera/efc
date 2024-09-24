<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\Data\Components\InvoiceLineItem\TaxCategoryData;
use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class InvoiceLineItemData extends Data
{
    public function __construct(
        public string|null $name,
        public string|null $description,
        public string|null $sellersItemIdentification,
        public string|null $buyersItemIdentification,
        public string|null $standardItemIdentification,
        public string|null $originCountry,
        public string|null $commodityClassification,
        public TaxCategoryData|null $classifiedTaxCategory,
        public array|null $additionalItemProperties,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        $commodityClassification = $xml->valueNode('CommodityClassification.ItemClassificationCode');
        return new self(
            name: $xml->get('Name'),
            description: $xml->get('Description'),
            sellersItemIdentification: $xml->get('SellersItemIdentification.ID'),
            buyersItemIdentification: $xml->get('BuyersItemIdentification.ID'),
            standardItemIdentification: $xml->get('StandardItemIdentification.ID'),
            originCountry: $xml->get('OriginCountry.IdentificationCode'),
            commodityClassification: $commodityClassification
                ? ($commodityClassification->attribute('listID') . ':' . $commodityClassification->value())
                : null,
            classifiedTaxCategory: data($xml->node('ClassifiedTaxCategory'), TaxCategoryData::class),
            additionalItemProperties: collect($xml->nodes('AdditionalItemProperty'))
                ->mapWithKeys(fn($node) => [$node->get('Name') => $node->get('Value')])
                ->toArray(),
        );
    }
}
