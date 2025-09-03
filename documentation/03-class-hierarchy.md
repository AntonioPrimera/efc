# Class Hierarchy and Structure

## Overview
This document provides a comprehensive overview of all classes, their relationships, and responsibilities within the e-factura package.

## Core Classes

### EFacturaXml (extends SimpleXMLElement)
**File**: `src/EFacturaXml.php`
**Purpose**: Enhanced XML parser with UBL-specific functionality

#### Key Methods:
- **Factory Methods**:
  - `fromString(string $xml)`: Create from XML string
  - `fromFile(string $path)`: Create from file path

- **Navigation Methods**:
  - `path(string $path, bool $leaf, bool $exactPath)`: Convert dot notation to XPath
  - `getNodes(string $path)`: Get branch nodes using exact path
  - `searchNodes(string $path)`: Search branch nodes hierarchically
  - `getValueNodes(string $path)`: Get leaf nodes using exact path
  - `searchValueNodes(string $path)`: Search leaf nodes hierarchically

- **Convenience Methods**:
  - `get(string $path)`: Get single value with exact path
  - `search(string $path)`: Search for single value hierarchically
  - `attribute(string $attribute)`: Get attribute value
  - `price()`: Convert to Price DTO
  - `quantity()`: Convert to Quantity DTO

### EFacturaData (extends Spatie\LaravelData\Data)
**File**: `src/Data/EFacturaData.php`
**Purpose**: Root data object representing complete invoice structure

#### Properties:
```php
public function __construct(
    public string|null $efId,                          // Invoice ID
    public string|null $issueDate,                     // Issue date (YYYY-MM-DD)
    public string|null $dueDate,                       // Due date (YYYY-MM-DD)
    public InvoiceType|null $type,                     // Invoice type enum
    public string|null $note,                          // Combined notes
    public string|null $documentCurrencyCode,          // Currency (usually RON)
    public string|null $accountingCost,                // Accounting cost center
    public string|null $buyerReference,                // Buyer reference
    public string|null $purchaseOrderReference,        // PO reference
    public string|null $salesOrderReference,           // SO reference
    public array $billingReferences,                   // Billing references array
    public AccountingPartyData|null $vendor,           // Supplier data
    public AccountingPartyData|null $customer,         // Customer data
    public DeliveryData|null $delivery,                // Delivery information
    public PaymentMeansData|null $paymentMeans,        // Payment information
    public TaxTotalData|null $taxTotal,                // Tax calculations
    public LegalMonetaryTotalData|null $legalMonetaryTotal, // Financial totals
    public array $lines,                               // Invoice lines array
    public AttachmentData|null $attachment = null,     // Attachments
)
```

#### Factory Methods:
- `fromXml(EFacturaXml $xml)`: Parse from XML
- `fromXmlFile(File $file)`: Parse from file
- `fromModel(Invoice $invoice)`: Convert from Laravel model

## Component Data Classes

### AccountingPartyData
**File**: `src/Data/Components/AccountingPartyData.php`
**Purpose**: Represents vendor/customer information

#### Properties:
```php
public string|null $name,           // Party name
public string|null $cif,            // Romanian VAT number
public string|null $regCom,         // Romanian registration number
public string|null $address,        // Full address string
public ContactData|null $contact,   // Contact information
```

#### Complex Logic:
- **CIF/RegCom Resolution**: Handles Romanian-specific business identification
- **CNP Processing**: Personal identification for individual invoices
- **Address Parsing**: Converts structured address to string format

### InvoiceLineData
**File**: `src/Data/Components/InvoiceLineData.php`
**Purpose**: Individual line item on invoice

#### Properties:
```php
public string|null $id,                         // Line ID
public string|null $quantity,                  // Quantity value
public string|null $uom,                       // Unit of measure
public string|null $amount,                    // Line total amount
public string|null $unitPrice,                 // Unit price
public string|null $currency,                  // Currency code
public string|null $note,                      // Line notes
public string|null $orderLineReference,        // Order line reference
public InvoiceLineItemData|null $item,         // Item details
```

### InvoiceLineItemData
**File**: `src/Data/Components/InvoiceLineItemData.php`
**Purpose**: Detailed item information within invoice lines

#### Properties:
```php
public string|null $name,                           // Item name
public string|null $description,                   // Item description
public string|null $sellersItemIdentification,     // Seller's item ID
public string|null $buyersItemIdentification,      // Buyer's item ID
public string|null $standardItemIdentification,    // Standard item ID
public string|null $originCountry,                 // Country of origin
public string|null $commodityClassification,       // Commodity code
public TaxCategoryData|null $classifiedTaxCategory, // Tax category
public array $additionalItemProperties,            // Extra properties
```

### TaxTotalData
**File**: `src/Data/Components/TaxTotalData.php`
**Purpose**: Tax calculation summary

#### Properties:
```php
public string|null $amount,                    // Total tax amount
public string|null $currency,                 // Tax currency
public array $taxSubtotal,                    // Tax subtotals array
```

### TaxSubTotalData
**File**: `src/Data/Components/TaxSubTotalData.php`
**Purpose**: Individual tax category calculations

### LegalMonetaryTotalData
**File**: `src/Data/Components/LegalMonetaryTotalData.php`
**Purpose**: Financial totals and calculations

#### Properties:
```php
public string|null $lineExtensionAmount,       // Net amount
public string|null $taxExclusiveAmount,        // Amount before tax
public string|null $taxInclusiveAmount,        // Amount including tax
public string|null $allowanceTotalAmount,      // Total allowances
public string|null $chargeTotalAmount,         // Total charges
public string|null $prepaidAmount,             // Prepaid amount
public string|null $payableAmount,             // Final payable amount
public string|null $currency,                  // Currency code
```

### DeliveryData
**File**: `src/Data/Components/DeliveryData.php`
**Purpose**: Delivery information and location

### PaymentMeansData
**File**: `src/Data/Components/PaymentMeansData.php`
**Purpose**: Payment method and banking details

### AttachmentData
**File**: `src/Data/Components/AttachmentData.php`
**Purpose**: File attachments (embedded or external)

#### Key Features:
- Base64 content decoding
- External URL handling
- File extraction capabilities

## Parser Classes

### InvoiceParser
**File**: `src/Data/Parsers/InvoiceParser.php`
**Purpose**: Parse standard invoices (types 380, 384, 389, 751)

#### Method:
```php
public static function parse(EFacturaXml $xml): EFacturaData
```

### CreditNoteParser
**File**: `src/Data/Parsers/CreditNoteParser.php`
**Purpose**: Parse credit notes (type 381)

#### Key Differences from Invoice:
- Uses `CreditNoteTypeCode` instead of `InvoiceTypeCode`
- No due date processing
- Uses `CreditNoteLine` instead of `InvoiceLine`
- No delivery information

### InvoiceLineParser
**File**: `src/Data/Parsers/InvoiceLineParser.php`
**Purpose**: Parse individual invoice lines

### CreditNoteLineParser
**File**: `src/Data/Parsers/CreditNoteLineParser.php`
**Purpose**: Parse credit note lines

## Action Classes

### GetEFacturaFromZipFile
**File**: `src/Actions/GetEFacturaFromZipFile.php`
**Purpose**: Extract and parse invoice from ZIP archive

#### Process:
1. Extract ZIP to temporary folder
2. Find numeric XML file (invoice data)
3. Parse to EFacturaData object
4. Clean up temporary files

## DTO Classes

### Price
**File**: `src/Dto/Price.php`
**Purpose**: Monetary value with currency

#### Properties:
```php
public string|float $amount,
public string $currency,
```

### Quantity
**File**: `src/Dto/Quantity.php`
**Purpose**: Quantity with unit of measure

#### Properties:
```php
public string|float $quantity,
public string $uom,
```

## Enum Classes

### InvoiceType
**File**: `src/Enums/InvoiceType.php`
**Purpose**: Romanian invoice type definitions

#### Values:
```php
case Factura = 380;              // Standard invoice
case NotaDeCreditare = 381;      // Credit note
case FacturaCorectata = 384;     // Corrected invoice
case AutoFactura = 389;          // Self-billing invoice
case FacturaInformare = 751;     // Information invoice
```

## Exception Classes

### XmlException
**File**: `src/Exceptions/XmlException.php`
**Purpose**: Base XML processing exception

### InvalidXmlException
**File**: `src/Exceptions/InvalidXmlException.php`
**Purpose**: Invalid XML format or structure

### XmlParseException
**File**: `src/Exceptions/XmlParseException.php`
**Purpose**: XML parsing errors

## Model Classes

### Invoice
**File**: `src/Models/Invoice.php`
**Purpose**: Laravel Eloquent model for database persistence

## Service Provider

### EfcServiceProvider
**File**: `src/EfcServiceProvider.php`
**Purpose**: Laravel package service provider
- Registers package services
- Publishes migrations
- Configures package settings

## Class Relationships Diagram

```
EFacturaXml (SimpleXMLElement)
    ↓
EFacturaData (Spatie Data)
    ├── AccountingPartyData (vendor/customer)
    │   ├── ContactData
    │   └── AddressData
    ├── InvoiceLineData[]
    │   └── InvoiceLineItemData
    │       └── TaxCategoryData
    ├── TaxTotalData
    │   └── TaxSubTotalData[]
    │       └── TaxCategoryData
    ├── LegalMonetaryTotalData
    ├── DeliveryData
    │   └── DeliveryLocationData
    ├── PaymentMeansData
    ├── BillingReferenceData[]
    └── AttachmentData

Parsers:
├── InvoiceParser → EFacturaData
├── CreditNoteParser → EFacturaData
├── InvoiceLineParser → InvoiceLineData
└── CreditNoteLineParser → InvoiceLineData

Actions:
└── GetEFacturaFromZipFile → EFacturaData
```