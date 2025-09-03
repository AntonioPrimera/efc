# Usage Patterns and Examples

## Overview
This document provides practical examples and common usage patterns for the e-factura package, demonstrating how to effectively use the package for various scenarios.

## Installation and Setup

### Composer Installation
```bash
composer require antonioprimera/efc
```

### Laravel Integration
```bash
# Publish and run migrations
php artisan vendor:publish --tag="efc-migrations"
php artisan migrate
```

## Basic Usage Patterns

### 1. Parse Invoice from XML File
```php
use AntonioPrimera\Efc\Data\EFacturaData;
use AntonioPrimera\Efc\EFacturaXml;

// Direct from XML file
$invoiceData = EFacturaData::fromXmlFile($xmlFile);

// Or step-by-step
$xml = EFacturaXml::fromFile('/path/to/invoice.xml');
$invoiceData = EFacturaData::fromXml($xml);

// Access invoice data
echo "Invoice ID: " . $invoiceData->efId;
echo "Vendor: " . $invoiceData->vendor->name;
echo "Amount: " . $invoiceData->legalMonetaryTotal->payableAmount;
```

### 2. Parse Invoice from ZIP Archive
```php
use AntonioPrimera\Efc\Actions\GetEFacturaFromZipFile;
use AntonioPrimera\FileSystem\File;

$zipFile = File::instance('/path/to/invoice.zip');
$invoiceData = GetEFacturaFromZipFile::run($zipFile);

// ZIP file is automatically extracted and cleaned up
echo "Parsed invoice: " . $invoiceData->efId;
```

### 3. Parse Invoice from XML String
```php
$xmlString = file_get_contents('/path/to/invoice.xml');
$xml = EFacturaXml::fromString($xmlString);
$invoiceData = EFacturaData::fromXml($xml);
```

## Advanced Usage Patterns

### 1. Batch Processing Multiple Invoices
```php
use AntonioPrimera\FileSystem\Folder;
use AntonioPrimera\Efc\Exceptions\InvalidXmlException;

$invoiceFolder = Folder::instance('/path/to/invoices');
$xmlFiles = $invoiceFolder->getFiles('/\.xml$/');

$processedInvoices = [];
$errors = [];

foreach ($xmlFiles as $xmlFile) {
    try {
        $invoice = EFacturaData::fromXmlFile($xmlFile);
        $processedInvoices[] = [
            'file' => $xmlFile->name,
            'invoice_id' => $invoice->efId,
            'vendor' => $invoice->vendor->name,
            'amount' => $invoice->legalMonetaryTotal->payableAmount,
            'currency' => $invoice->documentCurrencyCode,
        ];
    } catch (InvalidXmlException $e) {
        $errors[] = [
            'file' => $xmlFile->name,
            'error' => $e->getMessage()
        ];
    }
}

echo "Processed: " . count($processedInvoices) . " invoices";
echo "Errors: " . count($errors) . " files";
```

### 2. Extract and Process Attachments
```php
use AntonioPrimera\FileSystem\Folder;

$invoiceData = EFacturaData::fromXmlFile($xmlFile);

if ($invoiceData->attachment) {
    $attachment = $invoiceData->attachment;
    
    // For embedded attachments
    if ($attachment->fileContents) {
        $attachmentFolder = Folder::instance('/path/to/attachments');
        $extractedFile = $attachment->extractToFolder($attachmentFolder);
        
        echo "Extracted: " . $extractedFile->name;
        echo "MIME Type: " . $attachment->mimeType;
    }
    
    // For external URL attachments
    if ($attachment->url) {
        echo "External attachment URL: " . $attachment->url;
        // You can download the file using your preferred HTTP client
    }
}
```

### 3. Working with Invoice Lines
```php
$invoiceData = EFacturaData::fromXmlFile($xmlFile);

foreach ($invoiceData->lines as $index => $line) {
    echo "Line " . ($index + 1) . ":\n";
    echo "  ID: " . $line->id . "\n";
    echo "  Quantity: " . $line->quantity . " " . $line->uom . "\n";
    echo "  Unit Price: " . $line->unitPrice . " " . $line->currency . "\n";
    echo "  Total: " . $line->amount . " " . $line->currency . "\n";
    
    // Item details
    if ($line->item) {
        echo "  Item: " . $line->item->name . "\n";
        echo "  Seller ID: " . $line->item->sellersItemIdentification . "\n";
        
        // Additional properties (vehicle info, etc.)
        if ($line->item->additionalItemProperties) {
            foreach ($line->item->additionalItemProperties as $key => $value) {
                echo "    $key: $value\n";
            }
        }
    }
    echo "\n";
}
```

### 4. Tax Analysis
```php
$invoiceData = EFacturaData::fromXmlFile($xmlFile);
$taxTotal = $invoiceData->taxTotal;

if ($taxTotal) {
    echo "Total Tax Amount: " . $taxTotal->amount . " " . $taxTotal->currency . "\n";
    
    foreach ($taxTotal->taxSubtotal as $subtotal) {
        echo "Tax Category: " . $subtotal->taxCategory->id . "\n";
        echo "Tax Rate: " . $subtotal->taxCategory->percent . "%\n";
        echo "Taxable Amount: " . $subtotal->taxableAmount . "\n";
        echo "Tax Amount: " . $subtotal->taxAmount . "\n";
        
        if ($subtotal->taxCategory->exemptionReason) {
            echo "Exemption: " . $subtotal->taxCategory->exemptionReason . "\n";
        }
        echo "\n";
    }
}
```

### 5. Credit Note Processing
```php
use AntonioPrimera\Efc\Enums\InvoiceType;

$invoiceData = EFacturaData::fromXmlFile($xmlFile);

if ($invoiceData->type === InvoiceType::NotaDeCreditare) {
    echo "Processing Credit Note: " . $invoiceData->efId . "\n";
    echo "Issue Date: " . $invoiceData->issueDate . "\n";
    echo "Credit Amount: " . $invoiceData->legalMonetaryTotal->payableAmount . "\n";
    
    // Credit notes don't have due dates or delivery information
    echo "Due Date: " . ($invoiceData->dueDate ?? 'N/A') . "\n";
    echo "Delivery: " . ($invoiceData->delivery ? 'Yes' : 'No') . "\n";
}
```

## Advanced XML Navigation

### 1. Custom XML Queries
```php
$xml = EFacturaXml::fromFile('/path/to/invoice.xml');

// Direct value extraction
$invoiceId = $xml->get('ID');
$currency = $xml->get('DocumentCurrencyCode');

// Search anywhere in hierarchy
$partyNames = $xml->searchValues('PartyName.Name');
// Returns array: ['Vendor Name', 'Customer Name']

// Get specific nodes for further processing
$supplierNode = $xml->node('AccountingSupplierParty');
$supplierCif = $supplierNode->get('Party.PartyTaxScheme.CompanyID');

// Working with attributes
$payableAmountNode = $xml->valueNode('LegalMonetaryTotal.PayableAmount');
$currency = $payableAmountNode->attribute('currencyID');

// Price and quantity extraction
$unitPriceNode = $xml->valueNode('InvoiceLine.Price.PriceAmount');
$price = $unitPriceNode->price(); // Returns Price DTO

$quantityNode = $xml->valueNode('InvoiceLine.InvoicedQuantity');
$quantity = $quantityNode->quantity(); // Returns Quantity DTO
```

### 2. Error Handling Patterns
```php
use AntonioPrimera\Efc\Exceptions\InvalidXmlException;
use AntonioPrimera\Efc\Exceptions\XmlParseException;

try {
    $invoiceData = EFacturaData::fromXmlFile($xmlFile);
    
    // Process invoice data
    
} catch (InvalidXmlException $e) {
    // Handle invalid XML format
    echo "Invalid XML: " . $e->getMessage();
    
} catch (XmlParseException $e) {
    // Handle parsing errors (namespace issues, etc.)
    echo "Parse error: " . $e->getMessage();
    
} catch (\Exception $e) {
    // Handle other errors
    echo "Unexpected error: " . $e->getMessage();
}
```

## Data Export Patterns

### 1. Convert to Array/JSON
```php
$invoiceData = EFacturaData::fromXmlFile($xmlFile);

// Convert to array (Spatie Data feature)
$array = $invoiceData->toArray();

// Convert to JSON
$json = $invoiceData->toJson();

// Pretty JSON
$prettyJson = json_encode($invoiceData->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
```

### 2. Database Storage
```php
use AntonioPrimera\Efc\Models\Invoice;

$invoiceData = EFacturaData::fromXmlFile($xmlFile);

// Convert to Laravel model
$invoice = new Invoice();
$invoice->ef_id = $invoiceData->efId;
$invoice->issue_date = $invoiceData->issueDate;
$invoice->due_date = $invoiceData->dueDate;
$invoice->type = $invoiceData->type;
$invoice->vendor = $invoiceData->vendor;
$invoice->customer = $invoiceData->customer;
$invoice->legal_monetary_total = $invoiceData->legalMonetaryTotal;
// ... other properties

$invoice->save();

// Convert back from model
$reconstructedData = EFacturaData::fromModel($invoice);
```

### 3. CSV Export
```php
$invoices = []; // Array of EFacturaData objects

$csvData = [];
$csvData[] = ['ID', 'Date', 'Vendor', 'Customer', 'Amount', 'Currency']; // Headers

foreach ($invoices as $invoice) {
    $csvData[] = [
        $invoice->efId,
        $invoice->issueDate,
        $invoice->vendor->name,
        $invoice->customer->name,
        $invoice->legalMonetaryTotal->payableAmount,
        $invoice->documentCurrencyCode,
    ];
}

// Write to file
$fp = fopen('/path/to/export.csv', 'w');
foreach ($csvData as $row) {
    fputcsv($fp, $row);
}
fclose($fp);
```

## Performance Considerations

### 1. Memory-Efficient Batch Processing
```php
// For large batches, process one at a time to avoid memory issues
$xmlFiles = $folder->getFiles('/\.xml$/');

foreach ($xmlFiles as $xmlFile) {
    $invoice = EFacturaData::fromXmlFile($xmlFile);
    
    // Process immediately (save to DB, export, etc.)
    processInvoice($invoice);
    
    // Free memory
    unset($invoice);
    
    // Optional: garbage collection for very large batches
    if (memory_get_usage() > 100 * 1024 * 1024) { // 100MB
        gc_collect_cycles();
    }
}
```

### 2. Validation Shortcuts
```php
// Quick validation without full parsing
try {
    $xml = EFacturaXml::fromFile($xmlFile);
    $invoiceId = $xml->get('ID');
    
    if (!$invoiceId) {
        throw new InvalidXmlException('Missing invoice ID');
    }
    
    // Proceed with full parsing only if basic validation passes
    $invoice = EFacturaData::fromXml($xml);
    
} catch (Exception $e) {
    // Handle validation errors
}
```

## Testing Patterns

### 1. Test Data Creation
```php
use AntonioPrimera\Efc\EFacturaXml;

// Load test XML
$xml = EFacturaXml::fromFile(__DIR__ . '/fixtures/test-invoice.xml');

// Verify parsing
$invoice = EFacturaData::fromXml($xml);
$this->assertInstanceOf(EFacturaData::class, $invoice);
$this->assertEquals('TEST123', $invoice->efId);
```

### 2. Mock External Dependencies
```php
// When testing ZIP processing
$mockFile = \Mockery::mock(File::class);
$mockFile->shouldReceive('unzipTo')->andReturn(true);

$result = GetEFacturaFromZipFile::run($mockFile);
```

This documentation covers the most common usage patterns and provides a foundation for working effectively with the e-factura package.