# UBL Parsing Flow Documentation

## Overview
This document outlines the complete parsing flow from UBL XML to structured data objects, including all transformation steps and decision points.

## Main Parsing Flow

### 1. Entry Points
```php
// From XML string
$data = EFacturaData::fromXml(EFacturaXml::fromString($xmlString));

// From XML file
$data = EFacturaData::fromXmlFile($file);

// From ZIP file (common ANAF format)
$data = GetEFacturaFromZipFile::run($zipFile);
```

### 2. XML Preprocessing
1. **Namespace Initialization** (src/EFacturaXml.php:52-83)
   - Auto-registers UBL namespaces
   - Sets default namespace: `urn:oasis:names:specification:ubl:schema:xsd:Invoice-2`
   - Validates XML structure integrity

2. **Document Type Detection** (src/Data/EFacturaData.php:58-63)
   ```php
   if ($xml->getName() === 'CreditNote')
       return CreditNoteParser::parse($xml);
   
   return InvoiceParser::parse($xml);
   ```

### 3. Parser Selection Strategy

#### Invoice Parser (src/Data/Parsers/InvoiceParser.php)
Handles standard invoices (InvoiceTypeCode: 380, 384, 389, 751):
- **Header Information**: ID, dates, currency, references
- **Party Data**: Supplier and customer details
- **Delivery Information**: Date and location data
- **Payment Terms**: Payment means and schedules
- **Line Items**: Individual invoice lines with items
- **Tax Calculations**: VAT and other tax totals
- **Monetary Totals**: All financial summaries
- **Attachments**: Embedded or referenced documents

#### Credit Note Parser (src/Data/Parsers/CreditNoteParser.php)
Handles credit notes (InvoiceTypeCode: 381):
- **Key Differences**:
  - Uses `CreditNoteTypeCode` instead of `InvoiceTypeCode`
  - No due date processing
  - Uses `CreditNoteLine` instead of `InvoiceLine`
  - No delivery information
  - Defaults to `NotaDeCreditare` type

### 4. Component Parsing Flow

#### Party Data Parsing (AccountingPartyData)
**Complex CIF/RegCom Resolution Logic** (src/Data/Components/AccountingPartyData.php:50-80):

1. **CIF Determination Priority**:
   - `PartyTaxScheme.CompanyID` (primary)
   - `PartyIdentification.ID` (secondary)
   - `PartyLegalEntity.CompanyID` (fallback)

2. **RegCom Determination Priority**:
   - `PartyLegalEntity.CompanyID` (primary)
   - `PartyIdentification.ID` (fallback)

3. **Special Cases**:
   - **Personal Invoices**: Uses CNP validation or generates hash-based UID
   - **Data Inversion**: Sometimes CIF/RegCom positions are swapped in XML

#### Line Item Parsing
**Dynamic Parser Selection** (src/Data/Components/InvoiceLineData.php:26-31):
```php
if ($xml->getName() === 'CreditNoteLine')
    return CreditNoteLineParser::parse($xml);

return InvoiceLineParser::parse($xml);
```

**Price and Quantity Extraction**:
- Uses specialized `Price` and `Quantity` DTOs
- Automatic currency detection with RON default
- Unit of measure parsing from quantity nodes

### 5. XML Path Navigation System

#### Dot Notation to XPath Translation (src/EFacturaXml.php:92-102)
```php
// Input: "Party.PartyName.Name"
// Output: ".//cac:Party/cac:PartyName/cbc:Name" (for leaf nodes)
// Output: ".//cac:Party/cac:PartyName/cac:Name" (for branch nodes)
```

#### Search Types:
- **Exact Path** (`getNodes`): Uses `./` prefix for absolute paths
- **Hierarchical Search** (`searchNodes`): Uses `//` prefix for anywhere in document
- **Value Extraction**: Automatic string conversion with trimming

### 6. Error Handling and Validation

#### XML Validation Points:
1. **File Existence**: Before XML parsing
2. **XML Well-formedness**: During SimpleXML loading
3. **Namespace Registration**: During initialization
4. **Data Integrity**: During component creation

#### Data Validation:
- **CIF Validation**: Using `antonioprimera/cif` package
- **CNP Validation**: Custom algorithm with checksum
- **RegCom Format**: Regex validation for Romanian format

### 7. Special Processing Cases

#### GLN Delivery Locations
- Handles Global Location Numbers for delivery addresses
- Address may be null when only GLN is provided

#### Attachment Processing
**Two Types Supported**:
1. **Embedded Attachments**: Base64-encoded file content
2. **External Attachments**: URL references to external documents

#### Multi-language Support
- Handles Romanian-specific business rules
- Address parsing for Romanian format
- Tax code validation per Romanian standards

### 8. Performance Optimizations

#### Lazy Loading
- Namespaces initialized only when needed
- XPath expressions cached per instance

#### Memory Efficiency
- Streaming-friendly design
- Minimal object creation during parsing
- Efficient array operations using Laravel collections

## Data Flow Diagram

```
ZIP File → Extract → XML File → EFacturaXml → Parser Selection
                                      ↓
                            Document Type Detection
                                      ↓
                          ┌─────────────────┬─────────────────┐
                          ↓                 ↓                 ↓
                  InvoiceParser    CreditNoteParser    [Future Parsers]
                          ↓                 ↓                 ↓
                    Component Parsing → Data Validation → EFacturaData
                          ↓
                  Spatie Data Objects with Type Safety
```

## Usage Examples

### Basic Parsing
```php
$xml = EFacturaXml::fromFile('invoice.xml');
$invoice = EFacturaData::fromXml($xml);

// Access structured data
echo $invoice->vendor->name;
echo $invoice->legalMonetaryTotal->payableAmount;
```

### Batch Processing
```php
foreach ($zipFiles as $zipFile) {
    try {
        $invoice = GetEFacturaFromZipFile::run($zipFile);
        // Process invoice data
    } catch (InvalidXmlException $e) {
        // Handle parsing errors
    }
}
```