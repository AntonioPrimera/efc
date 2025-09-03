# E-Factura Package Architecture Overview

## Purpose
This package provides comprehensive parsing and processing capabilities for Romanian UBL-compliant invoices downloaded from the ANAF e-factura system. It transforms XML invoice data into structured Spatie Data objects for easy manipulation and integration.

## Core Architecture

### Main Components
1. **EFacturaXml** - Custom XML parser extending SimpleXMLElement
2. **EFacturaData** - Root data object representing a complete invoice
3. **Component Data Classes** - Individual components (parties, lines, taxes, etc.)
4. **Parsers** - Specialized parsing logic for invoices and credit notes
5. **Actions** - High-level operations like ZIP file processing

### Key Dependencies
- **PHP 8.2+** - Modern PHP features and syntax
- **Spatie Laravel Data** - Type-safe data objects with validation
- **antonioprimera/filesystem** - File system operations
- **antonioprimera/cif** - Romanian VAT number validation
- **ext-simplexml** - XML parsing capabilities

### Package Structure
```
src/
├── Actions/                    # High-level operations
│   └── GetEFacturaFromZipFile.php
├── Data/
│   ├── Components/            # Individual data structures
│   ├── Parsers/               # Document type parsers
│   └── EFacturaData.php       # Root data object
├── Dto/                       # Simple data transfer objects
├── Enums/                     # Enumerations (invoice types)
├── Exceptions/                # Custom exceptions
├── Models/                    # Laravel model
└── helpers.php                # Utility functions
```

## Design Patterns

### 1. Factory Pattern
- `EFacturaXml::fromString()` and `EFacturaXml::fromFile()` for XML creation
- `EFacturaData::fromXml()` for data object creation

### 2. Strategy Pattern
- Separate parsers for different document types (Invoice, Credit Note)
- Dynamic parser selection based on XML root element

### 3. Data Transfer Object Pattern
- Spatie Data classes provide type safety and serialization
- Snake case mapping for Laravel conventions

### 4. Facade Pattern
- `EFacturaXml` provides simplified interface to complex XML operations
- Dot notation path system abstracts XPath complexity

## Key Features

### XML Processing
- Namespace-aware XML parsing with automatic initialization
- Dot notation path system for intuitive XML navigation
- Support for exact and hierarchical searches
- Attribute and value extraction with type safety

### UBL Standard Compliance
- Full support for Romanian UBL invoice structure
- Handles standard invoice elements (parties, lines, totals, etc.)
- Support for attachments (embedded and external URLs)
- Credit note processing

### Data Transformation
- Automatic conversion to Spatie Data objects
- Type-safe properties with validation
- Support for nested data structures
- Serialization to arrays/JSON

### Error Handling
- Comprehensive exception hierarchy
- Validation of XML structure and data integrity
- Graceful handling of missing or malformed data

## Performance Considerations
- Lazy loading of XML namespaces
- Efficient XPath queries with caching
- Minimal memory footprint for large documents
- Streaming-friendly design for batch processing