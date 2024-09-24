<?php
namespace AntonioPrimera\Efc\Enums;

enum InvoiceType: int
{
    case Factura = 380;
    case FacturaCorectata = 384;
    case AutoFactura = 389;
    case FacturaInformare = 751;
}
