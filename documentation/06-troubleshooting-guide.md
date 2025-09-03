# Troubleshooting Guide

## Overview
This guide addresses common issues, error scenarios, and debugging techniques when working with the e-factura package.

## Common Exceptions and Solutions

### InvalidXmlException
**File**: `src/Exceptions/InvalidXmlException.php`

#### Symptoms
- Exception thrown during XML loading
- Message: "Could not load XML from string" or "Could not load XML from file"

#### Common Causes
1. **File Not Found**
   ```
   File [/path/to/invoice.xml] does not exist
   ```
   **Solution**: Verify file path and permissions

2. **Malformed XML**
   ```
   Could not load XML from string
   ```
   **Solution**: Validate XML structure using XML validator

3. **Encoding Issues**
   ```
   Could not load XML from file [/path/to/invoice.xml]
   ```
   **Solution**: Check file encoding (should be UTF-8)

#### Debug Steps
```php
// Check if file exists
if (!file_exists($xmlPath)) {
    echo "File does not exist: $xmlPath";
}

// Check file permissions
if (!is_readable($xmlPath)) {
    echo "File is not readable: $xmlPath";
}

// Validate XML manually
$xmlContent = file_get_contents($xmlPath);
$dom = new DOMDocument();
if (!$dom->loadXML($xmlContent)) {
    echo "Invalid XML structure";
}
```

### XmlParseException
**File**: `src/Exceptions/XmlParseException.php`

#### Symptoms
- Exception during namespace initialization
- Message: "Could not initialize namespace" or "Could not initialize namespaces"

#### Common Causes
1. **Missing Namespaces**
   ```
   Could not initialize namespace: default
   ```
   **Solution**: Verify XML has proper UBL namespace declarations

2. **Invalid Namespace URIs**
   ```
   Could not initialize namespaces
   ```
   **Solution**: Check namespace URIs match UBL standard

#### Debug Steps
```php
try {
    $xml = EFacturaXml::fromFile($xmlPath);
} catch (XmlParseException $e) {
    // Check namespaces manually
    $dom = new DOMDocument();
    $dom->load($xmlPath);
    
    $namespaces = [];
    $xpath = new DOMXPath($dom);
    foreach ($xpath->query('namespace::*', $dom->documentElement) as $node) {
        $namespaces[$node->nodeName] = $node->nodeValue;
    }
    
    print_r($namespaces);
}
```

## Data Parsing Issues

### Missing or Null Values

#### Symptoms
- Expected data is null or empty
- Invoice components not populated

#### Common Causes
1. **Incorrect XML Paths**
   ```php
   $vendor = $invoiceData->vendor; // null
   ```

#### Debug Steps
```php
$xml = EFacturaXml::fromFile($xmlPath);

// Check if basic elements exist
echo "Invoice ID: " . ($xml->get('ID') ?? 'MISSING') . "\n";
echo "Vendor exists: " . ($xml->node('AccountingSupplierParty') ? 'YES' : 'NO') . "\n";

// Debug specific paths
$supplierParty = $xml->node('AccountingSupplierParty.Party');
if ($supplierParty) {
    echo "Party Name: " . ($supplierParty->get('PartyName.Name') ?? 'MISSING') . "\n";
    echo "Party CIF: " . ($supplierParty->get('PartyTaxScheme.CompanyID') ?? 'MISSING') . "\n";
}

// Check available nodes at a level
$allInvoiceChildren = $xml->xpath('.//*[local-name()]');
foreach ($allInvoiceChildren as $child) {
    if ($child->parentNode === $xml[0]) {
        echo "Root child: " . $child->nodeName . "\n";
    }
}
```

### CIF/RegCom Resolution Issues

#### Symptoms
- CIF or RegCom fields are null when they should have values
- Wrong identification numbers assigned

#### Debug CIF Resolution
```php
$xml = EFacturaXml::fromFile($xmlPath);
$partyNode = $xml->node('AccountingSupplierParty.Party');

if ($partyNode) {
    // Check all possible CIF sources
    $cifSources = [
        'PartyTaxScheme.CompanyID' => $partyNode->get('PartyTaxScheme.CompanyID'),
        'PartyIdentification.ID' => $partyNode->get('PartyIdentification.ID'),
        'PartyLegalEntity.CompanyID' => $partyNode->get('PartyLegalEntity.CompanyID'),
    ];
    
    foreach ($cifSources as $source => $value) {
        echo "$source: " . ($value ?? 'NULL') . "\n";
        if ($value) {
            // Test CIF validation
            try {
                $isValid = cif($value)->isValid();
                echo "  Valid CIF: " . ($isValid ? 'YES' : 'NO') . "\n";
            } catch (Exception $e) {
                echo "  CIF validation error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Check RegCom validation
    foreach ($cifSources as $source => $value) {
        if ($value && isRegCom($value)) {
            echo "RegCom found in $source: $value\n";
        }
    }
}
```

## Performance Issues

### Memory Usage Problems

#### Symptoms
- PHP Fatal error: Allowed memory size exhausted
- Very slow processing of large files

#### Solutions
1. **Increase Memory Limit**
   ```php
   ini_set('memory_limit', '256M');
   ```

2. **Process Files Individually**
   ```php
   foreach ($xmlFiles as $file) {
       $invoice = EFacturaData::fromXmlFile($file);
       processInvoice($invoice);
       unset($invoice); // Free memory
       
       if (memory_get_usage() > 200 * 1024 * 1024) {
           gc_collect_cycles();
       }
   }
   ```

3. **Use Streaming for Large Batches**
   ```php
   function processInvoicesStream($xmlFiles) {
       foreach ($xmlFiles as $file) {
           try {
               yield EFacturaData::fromXmlFile($file);
           } catch (Exception $e) {
               error_log("Failed to process: " . $file->path . " - " . $e->getMessage());
           }
       }
   }
   
   foreach (processInvoicesStream($xmlFiles) as $invoice) {
       // Process one at a time
       saveInvoice($invoice);
   }
   ```

### Slow Parsing Performance

#### Symptoms
- Processing takes longer than expected
- High CPU usage during parsing

#### Debug Performance
```php
$startTime = microtime(true);
$startMemory = memory_get_usage();

$invoice = EFacturaData::fromXmlFile($xmlFile);

$endTime = microtime(true);
$endMemory = memory_get_usage();

echo "Processing time: " . round(($endTime - $startTime) * 1000, 2) . " ms\n";
echo "Memory used: " . round(($endMemory - $startMemory) / 1024 / 1024, 2) . " MB\n";
echo "Peak memory: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";
```

#### Performance Optimizations
1. **Avoid Unnecessary Parsing**
   ```php
   // Quick validation before full parsing
   $xml = EFacturaXml::fromFile($xmlFile);
   $invoiceId = $xml->get('ID');
   
   if (!$invoiceId || $invoiceId === 'SKIP') {
       continue; // Skip this file
   }
   
   $invoice = EFacturaData::fromXml($xml);
   ```

2. **Cache Parsed Results**
   ```php
   $cacheKey = md5_file($xmlFile->path);
   
   if ($cached = Cache::get($cacheKey)) {
       $invoice = unserialize($cached);
   } else {
       $invoice = EFacturaData::fromXmlFile($xmlFile);
       Cache::put($cacheKey, serialize($invoice), 3600);
   }
   ```

## ZIP File Processing Issues

### GetEFacturaFromZipFile Problems

#### Symptoms
- Exception: "No XML file found in the ZIP archive"
- Temporary files not cleaned up

#### Debug ZIP Contents
```php
use AntonioPrimera\FileSystem\File;

$zipFile = File::instance('/path/to/invoice.zip');

// Check ZIP contents before processing
$zip = new ZipArchive();
if ($zip->open($zipFile->path) === TRUE) {
    echo "ZIP contains " . $zip->numFiles . " files:\n";
    
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $fileInfo = $zip->statIndex($i);
        echo "  " . $fileInfo['name'] . " (" . $fileInfo['size'] . " bytes)\n";
        
        // Check if it matches expected pattern
        if (preg_match('/^\d+\.xml$/', $fileInfo['name'])) {
            echo "    → This matches invoice XML pattern\n";
        }
    }
    $zip->close();
}

// Now try the action
try {
    $invoice = GetEFacturaFromZipFile::run($zipFile);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

#### Manual ZIP Processing
```php
use AntonioPrimera\FileSystem\File;

$zipFile = File::instance('/path/to/invoice.zip');
$extractDir = sys_get_temp_dir() . '/efactura_debug_' . uniqid();

// Manual extraction for debugging
$zip = new ZipArchive();
if ($zip->open($zipFile->path) === TRUE) {
    $zip->extractTo($extractDir);
    $zip->close();
    
    // List extracted files
    $files = scandir($extractDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "Extracted: $file\n";
            
            if (preg_match('/^\d+\.xml$/', $file)) {
                // Try to parse this file
                $xmlPath = $extractDir . '/' . $file;
                try {
                    $invoice = EFacturaData::fromXmlFile(File::instance($xmlPath));
                    echo "Successfully parsed: " . $invoice->efId . "\n";
                } catch (Exception $e) {
                    echo "Parse error: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    // Clean up
    array_map('unlink', glob("$extractDir/*"));
    rmdir($extractDir);
}
```

## Validation Issues

### Data Validation Problems

#### Debug Data Completeness
```php
$invoice = EFacturaData::fromXmlFile($xmlFile);

// Check completeness
$issues = [];

if (!$invoice->efId) $issues[] = "Missing invoice ID";
if (!$invoice->vendor) $issues[] = "Missing vendor data";
if (!$invoice->customer) $issues[] = "Missing customer data";
if (!$invoice->legalMonetaryTotal) $issues[] = "Missing monetary totals";
if (empty($invoice->lines)) $issues[] = "No invoice lines";

if ($invoice->vendor && !$invoice->vendor->cif) {
    $issues[] = "Vendor missing CIF";
}

if ($invoice->customer && !$invoice->customer->cif) {
    $issues[] = "Customer missing CIF";
}

if (!empty($issues)) {
    echo "Data issues found:\n";
    foreach ($issues as $issue) {
        echo "  - $issue\n";
    }
}
```

#### Validate Against Test Data
```php
// Load known good test file
$testInvoice = EFacturaData::fromXmlFile(File::instance(__DIR__ . '/tests/Context/4344790293.xml'));

// Compare structure
$actualInvoice = EFacturaData::fromXmlFile($yourFile);

if (count($testInvoice->lines) > 0 && count($actualInvoice->lines) === 0) {
    echo "Warning: Test file has lines but actual file doesn't\n";
}

if ($testInvoice->vendor && !$actualInvoice->vendor) {
    echo "Warning: Test file has vendor but actual file doesn't\n";
}
```

## Laravel Integration Issues

### Service Provider Problems

#### Symptoms
- Migration not found
- Package not auto-discovered

#### Debug Steps
```bash
# Check if package is discovered
php artisan package:discover

# List available migrations
php artisan migrate:status

# Publish migrations manually
php artisan vendor:publish --tag="efc-migrations" --force
```

### Database Issues

#### Migration Problems
```php
// Check if table exists
if (!Schema::hasTable('invoices')) {
    echo "Invoices table not found. Run migrations.\n";
}

// Check table structure
$columns = Schema::getColumnListing('invoices');
print_r($columns);
```

## Advanced Debugging

### XML Structure Analysis
```php
function analyzeXmlStructure($xmlFile) {
    $xml = EFacturaXml::fromFile($xmlFile);
    
    echo "Root element: " . $xml->getName() . "\n";
    echo "Namespaces:\n";
    foreach ($xml->getNamespaces(true) as $prefix => $uri) {
        echo "  $prefix: $uri\n";
    }
    
    // Find all unique element names
    $elements = $xml->xpath('//*');
    $elementNames = [];
    foreach ($elements as $element) {
        $elementNames[$element->getName()]++;
    }
    
    echo "\nElement counts:\n";
    arsort($elementNames);
    foreach ($elementNames as $name => $count) {
        echo "  $name: $count\n";
    }
}

analyzeXmlStructure($xmlFile);
```

### Enable Detailed Error Reporting
```php
// Enable all PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add custom error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (strpos($message, 'XML') !== false) {
        echo "XML Error in $file:$line - $message\n";
    }
});

// Enable libxml errors
libxml_use_internal_errors(true);

try {
    $invoice = EFacturaData::fromXmlFile($xmlFile);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    
    // Show libxml errors
    foreach (libxml_get_errors() as $error) {
        echo "LibXML Error: " . $error->message;
    }
}
```

## Getting Help

### Enable Debug Mode
```php
// Add to your debug code
define('EFACTURA_DEBUG', true);

if (defined('EFACTURA_DEBUG')) {
    // Add detailed logging
    echo "Processing file: " . $xmlFile->name . "\n";
    echo "File size: " . $xmlFile->size . " bytes\n";
}
```

### Create Minimal Reproduction Case
```php
// Create a minimal test case
$minimalXml = '<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">
    <cbc:ID>TEST123</cbc:ID>
    <cbc:IssueDate>2024-01-01</cbc:IssueDate>
</Invoice>';

try {
    $xml = EFacturaXml::fromString($minimalXml);
    $invoice = EFacturaData::fromXml($xml);
    echo "Minimal case works: " . $invoice->efId . "\n";
} catch (Exception $e) {
    echo "Minimal case fails: " . $e->getMessage() . "\n";
}
```

### Collect Debug Information
```php
function collectDebugInfo($xmlFile) {
    return [
        'php_version' => PHP_VERSION,
        'simplexml_loaded' => extension_loaded('simplexml'),
        'file_exists' => file_exists($xmlFile),
        'file_readable' => is_readable($xmlFile),
        'file_size' => filesize($xmlFile),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
    ];
}

print_r(collectDebugInfo($xmlFile->path));
```

This troubleshooting guide should help identify and resolve most common issues when working with the e-factura package.