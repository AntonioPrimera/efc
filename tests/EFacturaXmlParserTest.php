<?php
use AntonioPrimera\Efc\EFacturaXml;

beforeEach(function () {
    $this->xml = EFacturaXml::fromFile(__DIR__ . '/Context/4344790293.xml');
    expect($this->xml)->toBeInstanceOf(EFacturaXml::class);
});

it('can find a specific node value', function () {
    //test path()
    expect($this->xml->path('ID', true, true))->toBe('./cbc:ID')
        ->and($this->xml->path('ID', false, true))->toBe('./cac:ID')
        ->and($this->xml->path('ID', true, false))->toBe('//cbc:ID')
        ->and($this->xml->path('ID', false, false))->toBe('//cac:ID')
        ->and($this->xml->path('Party.PartyName.Name', true, true))->toBe('./cac:Party/cac:PartyName/cbc:Name')
        ->and($this->xml->path('Party.PartyName.Name', false, true))->toBe('./cac:Party/cac:PartyName/cac:Name')
        ->and($this->xml->path('Party.PartyName.Name', true, false))->toBe('//cac:Party/cac:PartyName/cbc:Name')
        ->and($this->xml->path('Party.PartyName.Name', false, false))->toBe('//cac:Party/cac:PartyName/cac:Name')

        //test getNodes()
        ->and($this->xml->getNodes('InvoiceLine.Item'))->toBeArray()->toHaveCount(14)
        ->and($this->xml->getNodes('TaxTotal.TaxSubtotal.TaxCategory.TaxScheme'))->toBeArray()->toHaveCount(2)
        ->and($this->xml->getNodes('TaxCategory.TaxScheme'))->toBeArray()->toHaveCount(0)

        //test searchNodes()
        ->and($this->xml->searchNodes('TaxCategory.TaxScheme'))->toBeArray()->toHaveCount(2)
        ->and($this->xml->searchNodes('Party.PartyName'))->toBeArray()->toHaveCount(2)
        ->and($this->xml->searchNodes('non-existing-key'))->toBeArray()->toHaveCount(0)

        //test getNode()
        ->and($this->xml->getNode('InvoiceLine.Item'))->toBeInstanceOf(SimpleXMLElement::class)
        ->and($this->xml->getNode('TaxTotal.TaxSubtotal.TaxCategory.TaxScheme', 1))->toBeInstanceOf(SimpleXMLElement::class)
        ->and($this->xml->getNode('TaxCategory.TaxScheme'))->toBeNull()

        //test searchNode()
        ->and($this->xml->searchNode('TaxCategory.TaxScheme'))->toBeInstanceOf(SimpleXMLElement::class)
        ->and($this->xml->searchNode('Party.PartyName', 1))->toBeInstanceOf(SimpleXMLElement::class)
        ->and($this->xml->searchNode('non-existing-key'))->toBeNull()

        //test getValueNodes()
        ->and($this->xml->getValueNodes('ID'))->toBeArray()->toHaveCount(1)        //valueNodes
        ->and((string) $this->xml->getValueNodes('ID')[0])->toBe('PN2044643-')
        ->and($this->xml->getValueNodes('Note'))->toBeArray()->toHaveCount(14)
        ->and($this->xml->getValueNodes('AccountingSupplierParty.Party.PartyName.Name'))->toBeArray()->toHaveCount(1)
        ->and($this->xml->getValueNodes('non-existing-key'))->toBeArray()->toHaveCount(0)

        //test searchValueNodes()
        ->and($this->xml->searchValueNodes('Note'))->toBeArray()->toHaveCount(14)
        ->and($this->xml->searchValueNodes('PartyName.Name'))->toBeArray()->toHaveCount(2)
        ->and($this->xml->searchValueNodes('non-existing-key'))->toBeArray()->toHaveCount(0)

        //test getValueNode()
        ->and($this->xml->getValueNode('ID'))->toBeInstanceOf(SimpleXMLElement::class)
        ->and((string) $this->xml->getValueNode('ID'))->toBe('PN2044643-')
        ->and($this->xml->getValueNode('Note', 12))->toBeInstanceOf(SimpleXMLElement::class)
        ->and($this->xml->getValueNode('Note', 20))->toBeNull()
        ->and($this->xml->getValueNode('non-existing-key'))->toBeNull()

        //test searchValueNode()
        ->and($this->xml->searchValueNode('PartyName.Name'))->toBeInstanceOf(EFacturaXml::class)
        ->and((string) $this->xml->searchValueNode('PartyName.Name'))->toBe('PORSCHE INTER AUTO ROMANIA SRL')
        ->and($this->xml->searchValueNode('PartyName.Name', 1))->toBeInstanceOf(SimpleXMLElement::class)
        ->and((string) $this->xml->searchValueNode('PartyName.Name', 1))->toBe('AGRORAL SERV SRL')
        ->and($this->xml->searchValueNode('non-existing-key'))->toBeNull()

        //test getValue()
        ->and($this->xml->getValue('ID'))->toBe('PN2044643-')
        ->and($this->xml->getValue('AccountingCustomerParty.Party.PartyName.Name'))->toBe('AGRORAL SERV SRL')
        ->and($this->xml->getValue('Note', 0))->toBe('Vehicle Identification Code: TMBGK7NW2M3100992')
        ->and($this->xml->getValue('Note', 1))->toBe('Vehicle Milage: 72572 km')
        ->and($this->xml->getValue('non-existing-key'))->toBeNull()

        //test searchValue()
        ->and($this->xml->searchValue('PartyName.Name'))->toBe('PORSCHE INTER AUTO ROMANIA SRL')
        ->and($this->xml->searchValue('PartyName.Name', 0))->toBe('PORSCHE INTER AUTO ROMANIA SRL')
        ->and($this->xml->searchValue('PartyName.Name', 1))->toBe('AGRORAL SERV SRL')
        ->and($this->xml->searchValue('non-existing-key'))->toBeNull()

        //test get()
        ->and($this->xml->get('ID'))->toBe('PN2044643-')
        ->and($this->xml->get('AccountingCustomerParty.Party.PartyName.Name'))->toBe('AGRORAL SERV SRL')
        ->and($this->xml->get('non-existing-key'))->toBeNull()

        //test search()
        ->and($this->xml->search('PartyName.Name'))->toBe('PORSCHE INTER AUTO ROMANIA SRL')

        //test getValues()
        ->and($this->xml->getValues('Note'))->toBeArray()->toHaveCount(14)
        ->and($this->xml->getValues('Note')[0])->toBe('Vehicle Identification Code: TMBGK7NW2M3100992')
        ->and($this->xml->getValues('non-existing-key'))->toBeArray()->toHaveCount(0)

        //test searchValues()
        ->and($this->xml->searchValues('PartyName.Name'))->toBeArray()->toHaveCount(2)
        ->and($this->xml->searchValues('PartyName.Name')[0])->toBe('PORSCHE INTER AUTO ROMANIA SRL')
        ->and($this->xml->searchValues('non-existing-key'))->toBeArray()->toHaveCount(0)

        //test getting an attribute from a node
        ->and($this->xml->getValueNode('LegalMonetaryTotal.PayableAmount'))->toBeInstanceOf(EFacturaXml::class)
        ->and($this->xml->getValueNode('LegalMonetaryTotal.PayableAmount')->attribute( 'currencyID'))->toBe('RON')
        ->and($this->xml->getValueNode('LegalMonetaryTotal.PayableAmount')->attribute( 'someAttribute', 'USD'))->toBe('USD')

        //value
        ->and($this->xml->searchValueNode('PartyName.Name', 1)->value())->toBe('AGRORAL SERV SRL')
        ->and($this->xml->searchNode('PartyName', 1)->value())->toBeNull()
        ->and($this->xml->searchNode('PartyName', 1)->value('abc'))->toBe('abc')

        //node
        ->and($this->xml->node('OrderReference')->get('ID'))->toBe('2024019803')

        //querying child nodes
        ->and($this->xml->searchNode('PartyName')->getValueNode('Name')->value())->toBe('PORSCHE INTER AUTO ROMANIA SRL')
        ->and($this->xml->getNode('AccountingSupplierParty.Party')->getNode('PostalAddress')->getValue('Country.IdentificationCode'))->toBe('RO')
        ;
});
