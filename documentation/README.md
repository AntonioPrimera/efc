# E-Factura Package Documentation

## Overview
Comprehensive documentation for the Romanian UBL invoice parsing package that transforms ANAF e-factura XML files into structured Spatie Data objects.

## Documentation Structure

### 1. [Architecture Overview](01-architecture-overview.md)
- **Purpose**: Core system design and architectural patterns
- **Key Topics**:
  - Main components and dependencies
  - Package structure and organization
  - Design patterns used (Factory, Strategy, DTO, Facade)
  - Performance considerations

### 2. [UBL Parsing Flow](02-ubl-parsing-flow.md)
- **Purpose**: Detailed parsing process from XML to data objects
- **Key Topics**:
  - Complete parsing workflow
  - XML preprocessing and namespace handling
  - Parser selection strategy (Invoice vs Credit Note)
  - Component parsing flow with complex business logic
  - Error handling and validation
  - Special processing cases (GLN, attachments, multi-language)

### 3. [Class Hierarchy](03-class-hierarchy.md)
- **Purpose**: Complete class structure and relationships
- **Key Topics**:
  - Core classes (EFacturaXml, EFacturaData)
  - Component data classes with properties
  - Parser classes and their responsibilities
  - DTO classes (Price, Quantity)
  - Exception hierarchy
  - Class relationship diagrams

### 4. [Usage Patterns and Examples](04-usage-patterns-and-examples.md)
- **Purpose**: Practical implementation examples and best practices
- **Key Topics**:
  - Basic usage patterns (file, string, ZIP parsing)
  - Advanced scenarios (batch processing, attachments, error handling)
  - Data export patterns (array, JSON, CSV, database)
  - Performance optimization techniques
  - Testing patterns

### 5. [UBL Standard Mapping](05-ubl-standard-mapping.md)
- **Purpose**: Complete mapping between UBL XML and package structures
- **Key Topics**:
  - Root document mapping
  - Header information mapping
  - Party information with Romanian-specific logic
  - Tax and monetary totals mapping
  - Invoice line details mapping
  - Attachment and billing reference handling
  - Path translation examples (dot notation → XPath)

### 6. [Troubleshooting Guide](06-troubleshooting-guide.md)
- **Purpose**: Common issues, debugging techniques, and solutions
- **Key Topics**:
  - Exception handling (InvalidXmlException, XmlParseException)
  - Data parsing issues and debugging
  - Performance problems and solutions
  - ZIP file processing issues
  - Validation problems
  - Laravel integration issues
  - Advanced debugging techniques

## Quick Reference

### Essential Classes
- **EFacturaXml**: Enhanced XML parser with UBL support
- **EFacturaData**: Root data object representing complete invoice
- **AccountingPartyData**: Vendor/customer information
- **InvoiceLineData**: Individual line items
- **LegalMonetaryTotalData**: Financial totals

### Key Entry Points
```php
// From XML file
$invoice = EFacturaData::fromXmlFile($file);

// From ZIP archive
$invoice = GetEFacturaFromZipFile::run($zipFile);

// From XML string
$invoice = EFacturaData::fromXml(EFacturaXml::fromString($xml));
```

### Common Operations
```php
// Access structured data
echo $invoice->vendor->name;
echo $invoice->legalMonetaryTotal->payableAmount;

// Process lines
foreach ($invoice->lines as $line) {
    echo $line->item->name . ': ' . $line->amount;
}

// Handle attachments
if ($invoice->attachment) {
    $file = $invoice->attachment->extractToFolder($folder);
}
```

## Package Features

### ✅ Supported Features
- **UBL Standard Compliance**: Full Romanian UBL invoice support
- **Document Types**: Invoices and Credit Notes
- **Party Resolution**: Complex CIF/RegCom identification logic
- **Tax Calculations**: VAT and other tax category processing
- **Attachments**: Both embedded and external URL attachments
- **ZIP Processing**: Automatic extraction and cleanup
- **Type Safety**: Spatie Data integration with validation
- **Error Handling**: Comprehensive exception hierarchy
- **Performance**: Memory-efficient processing for large batches

### 🔧 Technical Requirements
- **PHP**: 8.2+
- **Extensions**: ext-simplexml
- **Framework**: Laravel compatible (optional)
- **Dependencies**: Spatie Data, antonioprimera/filesystem, antonioprimera/cif

## Getting Started
1. Read [Architecture Overview](01-architecture-overview.md) for system understanding
2. Follow [Usage Patterns](04-usage-patterns-and-examples.md) for implementation
3. Reference [UBL Mapping](05-ubl-standard-mapping.md) for XML structure
4. Use [Troubleshooting](06-troubleshooting-guide.md) when issues arise

## Contributing
When contributing to the package, ensure you understand:
- The UBL standard mapping
- Romanian business identification logic
- Spatie Data integration patterns
- Performance implications for large-scale processing