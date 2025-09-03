# UBL Standard Mapping

## Overview
This document maps the UBL (Universal Business Language) standard elements to the package's data structures, providing a comprehensive reference for understanding how XML elements are transformed into PHP objects.

## UBL Reference
- **UBL Invoice Definition**: https://docs.peppol.eu/poacc/billing/3.0/2023-Q4/syntax/ubl-invoice/tree/
- **Namespace**: `urn:oasis:names:specification:ubl:schema:xsd:Invoice-2`
- **Common Basic Components**: `cbc:` prefix
- **Common Aggregate Components**: `cac:` prefix

## Root Document Mapping

### Invoice Document
```xml
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">
```
**Maps to**: `EFacturaData` object

### Credit Note Document
```xml
<CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2">
```
**Maps to**: `EFacturaData` object (with type = NotaDeCreditare)

## Header Information Mapping

| UBL Element | Package Property | Data Type | Notes |
|-------------|------------------|-----------|-------|
| `cbc:ID` | `$efId` | string\|null | Invoice identifier |
| `cbc:IssueDate` | `$issueDate` | string\|null | Format: YYYY-MM-DD |
| `cbc:DueDate` | `$dueDate` | string\|null | Format: YYYY-MM-DD |
| `cbc:InvoiceTypeCode` | `$type` | InvoiceType\|null | Enum mapping |
| `cbc:CreditNoteTypeCode` | `$type` | InvoiceType\|null | For credit notes |
| `cbc:Note` | `$note` | string\|null | Multiple notes joined with \n |
| `cbc:DocumentCurrencyCode` | `$documentCurrencyCode` | string\|null | Usually 'RON' |
| `cbc:AccountingCost` | `$accountingCost` | string\|null | Cost center |
| `cbc:BuyerReference` | `$buyerReference` | string\|null | Buyer reference |

### Order References
| UBL Path | Package Property | Notes |
|----------|------------------|-------|
| `cac:OrderReference/cbc:ID` | `$purchaseOrderReference` | Purchase order ID |
| `cac:OrderReference/cbc:SalesOrderID` | `$salesOrderReference` | Sales order ID |

## Party Information Mapping

### Supplier (Vendor) Mapping
**UBL Path**: `cac:AccountingSupplierParty/cac:Party`
**Maps to**: `AccountingPartyData $vendor`

| UBL Element | Property | Resolution Logic |
|-------------|----------|------------------|
| `cac:PartyName/cbc:Name` | `$name` | Primary name source |
| `cac:PartyLegalEntity/cbc:RegistrationName` | `$name` | Fallback name source |
| `cac:PartyTaxScheme/cbc:CompanyID` | `$cif` | Primary CIF source |
| `cac:PartyIdentification/cbc:ID` | `$cif` | Secondary CIF source |
| `cac:PartyLegalEntity/cbc:CompanyID` | `$regCom` | Primary RegCom source |
| `cac:PostalAddress` | `$address` | Converted to string |
| `cac:Contact` | `$contact` | ContactData object |

### Customer Mapping
**UBL Path**: `cac:AccountingCustomerParty/cac:Party`
**Maps to**: `AccountingPartyData $customer`
Uses same mapping logic as supplier.

### Address Mapping
**UBL Path**: `cac:PostalAddress`
**Maps to**: `AddressData` → converted to string

| UBL Element | AddressData Property |
|-------------|---------------------|
| `cbc:StreetName` | `$street` |
| `cbc:CityName` | `$city` |
| `cbc:PostalZone` | `$postalCode` |
| `cbc:CountrySubentity` | `$countrySubentity` |
| `cac:Country/cbc:IdentificationCode` | `$countryCode` |

### Contact Mapping
**UBL Path**: `cac:Contact`
**Maps to**: `ContactData`

| UBL Element | Property |
|-------------|----------|
| `cbc:Name` | `$name` |
| `cbc:Telephone` | `$phone` |
| `cbc:ElectronicMail` | `$email` |

## Delivery Information Mapping

**UBL Path**: `cac:Delivery`
**Maps to**: `DeliveryData`

| UBL Element | Property | Notes |
|-------------|----------|-------|
| `cbc:ActualDeliveryDate` | `$date` | Delivery date |
| `cac:DeliveryLocation` | `$location` | DeliveryLocationData |
| `cac:DeliveryParty` | `$party` | AccountingPartyData |

### Delivery Location
**UBL Path**: `cac:Delivery/cac:DeliveryLocation`
**Maps to**: `DeliveryLocationData`

| UBL Element | Property | Notes |
|-------------|----------|-------|
| `cbc:ID` | `$id` | GLN number or location ID |
| `cac:Address` | `$address` | AddressData object |

## Payment Information Mapping

**UBL Path**: `cac:PaymentMeans`
**Maps to**: `PaymentMeansData`

| UBL Element | Property | Notes |
|-------------|----------|-------|
| `cbc:PaymentMeansCode` | `$code` | Payment method code |
| `cac:PayeeFinancialAccount/cbc:ID` | `$payeeIban` | Bank account IBAN |
| `cac:PayeeFinancialAccount/cbc:Name` | `$payeeName` | Account holder name |

## Tax Information Mapping

### Tax Total
**UBL Path**: `cac:TaxTotal`
**Maps to**: `TaxTotalData`

| UBL Element | Property |
|-------------|----------|
| `cbc:TaxAmount` | `$amount` |
| `@currencyID` | `$currency` |
| `cac:TaxSubtotal[]` | `$taxSubtotal` |

### Tax Subtotal
**UBL Path**: `cac:TaxTotal/cac:TaxSubtotal`
**Maps to**: `TaxSubTotalData[]`

| UBL Element | Property |
|-------------|----------|
| `cbc:TaxableAmount` | `$taxableAmount` |
| `cbc:TaxAmount` | `$taxAmount` |
| `@currencyID` | `$currency` |
| `cac:TaxCategory` | `$taxCategory` |

### Tax Category
**UBL Path**: `cac:TaxCategory`
**Maps to**: `TaxCategoryData`

| UBL Element | Property |
|-------------|----------|
| `cbc:ID` | `$id` |
| `cbc:Percent` | `$percent` |
| `cbc:TaxExemptionReasonCode` | `$exemptionReasonCode` |
| `cbc:TaxExemptionReason` | `$exemptionReason` |
| `cac:TaxScheme/cbc:ID` | `$taxScheme` |

## Monetary Totals Mapping

**UBL Path**: `cac:LegalMonetaryTotal`
**Maps to**: `LegalMonetaryTotalData`

| UBL Element | Property |
|-------------|----------|
| `cbc:LineExtensionAmount` | `$lineExtensionAmount` |
| `cbc:TaxExclusiveAmount` | `$taxExclusiveAmount` |
| `cbc:TaxInclusiveAmount` | `$taxInclusiveAmount` |
| `cbc:AllowanceTotalAmount` | `$allowanceTotalAmount` |
| `cbc:ChargeTotalAmount` | `$chargeTotalAmount` |
| `cbc:PrepaidAmount` | `$prepaidAmount` |
| `cbc:PayableAmount` | `$payableAmount` |
| `@currencyID` | `$currency` |

## Invoice Line Mapping

### Invoice Lines
**UBL Path**: `cac:InvoiceLine` or `cac:CreditNoteLine`
**Maps to**: `InvoiceLineData[]`

| UBL Element | Property |
|-------------|----------|
| `cbc:ID` | `$id` |
| `cbc:InvoicedQuantity` | `$quantity` + `$uom` |
| `cbc:LineExtensionAmount` | `$amount` |
| `cac:Price/cbc:PriceAmount` | `$unitPrice` |
| `@currencyID` | `$currency` |
| `cbc:Note` | `$note` |
| `cac:OrderLineReference/cbc:LineID` | `$orderLineReference` |
| `cac:Item` | `$item` |

### Line Item Details
**UBL Path**: `cac:InvoiceLine/cac:Item`
**Maps to**: `InvoiceLineItemData`

| UBL Element | Property |
|-------------|----------|
| `cbc:Name` | `$name` |
| `cbc:Description` | `$description` |
| `cac:SellersItemIdentification/cbc:ID` | `$sellersItemIdentification` |
| `cac:BuyersItemIdentification/cbc:ID` | `$buyersItemIdentification` |
| `cac:StandardItemIdentification/cbc:ID` | `$standardItemIdentification` |
| `cac:OriginCountry/cbc:IdentificationCode` | `$originCountry` |
| `cac:CommodityClassification/cbc:ItemClassificationCode` | `$commodityClassification` |
| `cac:ClassifiedTaxCategory` | `$classifiedTaxCategory` |
| `cac:AdditionalItemProperty` | `$additionalItemProperties` |

### Additional Item Properties
**UBL Path**: `cac:Item/cac:AdditionalItemProperty`
**Maps to**: `array $additionalItemProperties`

| UBL Element | Array Key | Array Value |
|-------------|-----------|-------------|
| `cbc:Name` | Key | Property name |
| `cbc:Value` | Value | Property value |

## Attachment Mapping

**UBL Path**: `cac:AdditionalDocumentReference/cac:Attachment`
**Maps to**: `AttachmentData`

| UBL Element | Property | Notes |
|-------------|----------|-------|
| `cbc:EmbeddedDocumentBinaryObject` | `$fileContents` | Base64 encoded |
| `@mimeCode` | `$mimeType` | MIME type |
| `@filename` | `$filename` | Original filename |
| `cac:ExternalReference/cbc:URI` | `$url` | External URL |

## Billing References

**UBL Path**: `cac:BillingReference`
**Maps to**: `BillingReferenceData[]`

| UBL Element | Property |
|-------------|----------|
| `cac:InvoiceDocumentReference/cbc:ID` | `$invoiceDocumentReference` |
| `cac:CreditNoteDocumentReference/cbc:ID` | `$creditNoteDocumentReference` |

## Romanian-Specific Extensions

### CIF/VAT Number Resolution
The package implements complex logic to handle Romanian business identification:

1. **CIF (Cod de Identificare Fiscală)** - VAT Number
   - Primary source: `PartyTaxScheme/CompanyID`
   - Validated using `antonioprimera/cif` package
   - Fallback sources with validation

2. **RegCom (Numărul de înregistrare)** - Registration Number
   - Format: `J##/####/####` or similar
   - Validated using regex pattern
   - Handles data position swapping

3. **CNP (Cod Numeric Personal)** - Personal Identification
   - For individual invoices
   - 13-digit validation with checksum
   - Fallback to hash-based UID

### Invoice Type Enum Mapping
| UBL Code | Romanian Name | Enum Value |
|----------|---------------|------------|
| 380 | Factură | `InvoiceType::Factura` |
| 381 | Notă de creditare | `InvoiceType::NotaDeCreditare` |
| 384 | Factură corectată | `InvoiceType::FacturaCorectata` |
| 389 | Auto-factură | `InvoiceType::AutoFactura` |
| 751 | Factură de informare | `InvoiceType::FacturaInformare` |

## XML Namespace Handling

The package automatically registers these namespaces:
- Default: `urn:oasis:names:specification:ubl:schema:xsd:Invoice-2`
- cbc: `urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2`
- cac: `urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2`

## Path Translation Examples

### Dot Notation → XPath
```php
// Input: "AccountingSupplierParty.Party.PartyName.Name"
// Output: "./cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name"

// Input: "InvoiceLine.Item.Name" (exact search)
// Output: "./cac:InvoiceLine/cac:Item/cbc:Name"

// Input: "PartyName.Name" (hierarchical search)
// Output: "//cac:PartyName/cbc:Name"
```

This mapping provides a complete reference for understanding how UBL elements are transformed into the package's data structures.