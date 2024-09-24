<?php
namespace AntonioPrimera\Efc\Data\Components;

use AntonioPrimera\Efc\EFacturaXml;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class PaymentMeansData extends Data
{
    public function __construct(
        public string|null $code,
        public string|null $payeeIban,
        public string|null $payeeName,
    ) {}

    public static function fromXml(EFacturaXml $xml): self
    {
        return new self(
            code: $xml->get('PaymentMeansCode'),
            payeeIban: $xml->get('PayeeFinancialAccount.ID'),
            payeeName: $xml->get('PayeeFinancialAccount.Name'),
        );
    }
}
