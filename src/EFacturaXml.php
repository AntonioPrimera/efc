<?php
namespace AntonioPrimera\Efc;

use AntonioPrimera\Efc\Dto\Price;
use AntonioPrimera\Efc\Dto\Quantity;
use AntonioPrimera\Efc\Exceptions\InvalidXmlException;
use AntonioPrimera\Efc\Exceptions\XmlParseException;

/**
 * A wrapper of SimpleXMLElement that provides a more convenient API for working with EFactura XMLs
 * It initializes namespaces and provides methods for searching and extracting data from the XML
 * It also provides methods for searching and extracting data from the XML
 *
 * All methods use dot notation for paths, which are then converted to XPath expressions
 * All methods starting with "get" use an exact, absolute path
 * All methods starting with "search" search for the path anywhere in the hierarchy
 * ValueNodes are leaf nodes (cbc:) (containing a string value) and Nodes are branch nodes (cac:)
 *
 * Check the EFacturaXmlParserTest for usage examples
 */
class EFacturaXml extends \SimpleXMLElement
{
    //whether the namespaces have been initialized
    protected bool $namespacesInitialized = false;

    //--- Factories ---------------------------------------------------------------------------------------------------

    public static function fromString(string $xml): static
    {
        $eFacturaXmlInstance = simplexml_load_string($xml, static::class);
        if ($eFacturaXmlInstance === false)
            throw new InvalidXmlException('Could not load XML from string');

        return $eFacturaXmlInstance->initializeNamespaces();
    }

    public static function fromFile(string $path): static
    {
        $eFacturaXmlInstance = simplexml_load_file($path, static::class);

        if (!file_exists($path))
            throw new InvalidXmlException("File [$path] does not exist");

        if ($eFacturaXmlInstance === false)
            throw new InvalidXmlException("Could not load XML from file [$path]");

        return $eFacturaXmlInstance->initializeNamespaces();
    }

    //--- Protected helpers -------------------------------------------------------------------------------------------

    public function initializeNamespaces(): static
    {
        //don't reinitialize the namespaces
        if ($this->namespacesInitialized)
            return $this;

        $defaultNamespaces = [
            "" => "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2", //default namespace must exist
        //    "cbc" => "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
        //    "cac" => "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
        ];

        $namespaces = array_merge($this->getNamespaces(true), $defaultNamespaces);
        //$namespaces = $this->getNamespaces(true);

        foreach ($namespaces as $key => $value) {
            if ($this->registerXPathNamespace($key ?: 'default', $value) === false)
                throw new XmlParseException('Could not initialize namespace: ' . ($key ?: 'default'));

            $this->namespaces[$key ?: 'default'] = (string) $value;
        }

        $errors = $this->xpath('//default:Error');

        if (!empty($errors))
            throw new XmlParseException('Could not initialize namespaces');

        //mark the namespaces as initialized for this instance
        $this->namespacesInitialized = true;

        return $this;
    }

    //--- Public api --------------------------------------------------------------------------------------------------

    /**
     * Define a path to search for in the EFactura XML, using dot notation
     * Path parts are prefixed with cac: and the last part with :cbc if $leaf is true
     * If $exactPath is false, the path will be searched in the entire hierarchy (using '//')
     */
    public function path(string $path, bool $leaf, bool $exactPath): string
    {
        //split the path into parts and prefix each part with cac: or cbc: depending on its position
        $pathParts = collect(explode('.', $path));
        $partCount = $pathParts->count();
        $lastPartPrefix = $leaf ? 'cbc' : 'cac';
        $pathParts->transform(fn($part, $index) => $index < $partCount - 1 ? "cac:$part" : "$lastPartPrefix:$part");

        //join the parts back together and search for the path in the entire hierarchy
        return ($exactPath ? './' : '//') . $pathParts->implode('/');
    }

    /**
     * Get a set of branch nodes (cac:) using an exact path
     */
    public function getNodes(string $path): array|false|null|static
    {
        return $this->expath($this->path($path, leaf: false, exactPath: true));
    }

    /**
     * Get a set of branch nodes (cac:) searching anywhere in the hierarchy
     */
    public function searchNodes(string $path): array|false|null|static
    {
        return $this->expath($this->path(path: $path, leaf: false, exactPath: false));
    }

    /**
     * Search for a specific branch node (cac:) using an exact path and an optional index (default 0)
     */
    public function getNode(string $path, int $index = 0): null|static
    {
        return $this->getNodes(path: $path)[$index] ?? null;
    }

    /**
     * Syntactic sugar for getNode() - gets a branch node (cac:) using an exact path
     * and gets the first node found or null if no nodes are found
     */
    public function node(string $path): null|static
    {
        return $this->getNode(path: $path);
    }

    /**
     * Syntactic sugar for getNodes() - gets an array of branch nodes (cac:) using an exact path
     */
    public function nodes(string $path): array|false|null|static
    {
        return $this->getNodes(path: $path);
    }

    /**
     * Syntactic sugar for getValueNode() - gets the first leaf node (cbc:) matching the exact path
     */
    public function valueNode(string $path): null|static
    {
        return $this->getValueNode(path: $path);
    }

    public function searchNode(string $path, int $index = 0): null|static
    {
        return $this->searchNodes(path: $path)[$index] ?? null;
    }

    /**
     * Get a set of leaf nodes (cbc:) using an exact path
     */
    public function getValueNodes(string $path): array|false|null|static
    {
        return $this->expath($this->path(path: $path, leaf: true, exactPath: true));
    }

    /**
     * Get a set of leaf nodes (cbc:) searching anywhere in the hierarchy
     */
    public function searchValueNodes(string $path): array|false|null|static
    {
        return $this->expath($this->path(path: $path, leaf: true, exactPath: false));
    }

    /**
     * Get a specific leaf node (cbc:) using an exact path and an optional index (default 0)
     */
    public function getValueNode(string $path, int $index = 0): null|static
    {
        return $this->getValueNodes(path: $path)[$index] ?? null;
    }

    /**
     * Get a specific leaf node (cbc:) searching anywhere in the hierarchy and an optional index (default 0)
     */
    public function searchValueNode(string $path, int $index = 0): null|static
    {
        return $this->searchValueNodes(path: $path)[$index] ?? null;
    }

    /**
     * Get a specific leaf node (cbc:) using an exact path and an optional index (default 0)
     * By default, it gets the value of the first node found or null if no nodes are found
     */
    public function getValue(string $path, int $index = 0, string|null $default = null): null|string
    {
        $node = $this->getValueNodes(path: $path)[$index] ?? null;
        return $node ? (string) $node : $default;
    }

    /**
     * Get a specific leaf node (cbc:) searching anywhere in the hierarchy and an optional index (default 0)
     * By default, it gets the value of the first node found or null if no nodes are found
     */
    public function searchValue(string $path, int $index = 0, string|null $default = null): null|string
    {
        $node = $this->searchValueNodes(path: $path)[$index] ?? null;
        return $node ? (string) $node : $default;
    }

    /**
     * Syntactic sugar for getValue()
     */
    public function get(string $path, string|null $default = null): null|string
    {
        return $this->getValueNode(path: $path)?->value($default);
    }

    /**
     * Syntactic sugar for searchValue()
     */
    public function search(string $path, string|null $default = null): null|string
    {
        return $this->searchValueNode(path: $path)?->value($default);
    }

    /**
     * Get a list of all values of matching leaf nodes (cbc:) using an exact path
     */
    public function getValues(string $path): array
    {
        $nodes = $this->getValueNodes(path: $path);
        return collect($nodes)->map(fn($node) => (string) $node)->all();
    }

    /**
     * Get a list of all values of matching leaf nodes (cbc:) searching anywhere in the hierarchy
     */
    public function searchValues(string $path): array
    {
        $nodes = $this->searchValueNodes(path: $path);
        return collect($nodes)->map(fn($node) => (string) $node)->all();
    }

    /**
     * Get the value of an attribute from the current node
     * If the attribute is not found, return the default value
     */
    public function attribute(string $attribute, string|null $default = null): null|string
    {
        $attributeValue = $this->attributes()[$attribute] ?? null;
        return $attributeValue ? (string) $attributeValue : $default;
    }

    /**
     * Get the string value of the current node, trimmed
     * If the value is empty, return the default value
     */
    public function value(string|null $default = null): string|null
    {
        $trimmedValue = trim((string) $this);
        return $trimmedValue ?: $default;
    }

    /**
     * Get the quantity and unit of measure of the current node, holding a quantity
     */
    public function quantity(): Quantity
    {
        return new Quantity(
            quantity: $this->value() ?? 0,
            uom: $this->attribute('unitCode') ?? '',
        );
    }

    /**
     * Get the price and currency of the current node, holding a monetary value
     */
    public function price(): Price
    {
        return new Price(
            amount: $this->value() ?? 0,
            currency: $this->attribute('currencyID') ?? 'RON',
        );
    }

    public function priceNode(string $path): Price
    {
        return $this->valueNode(path: $path)?->price() ?? Price::empty();
    }

    public function quantityNode(string $path): Quantity
    {
        return $this->valueNode(path: $path)?->quantity() ?? Quantity::empty();
    }

    //--- Protected helpers -------------------------------------------------------------------------------------------

    /**
     * Wrapper of the parent xpath() method that initializes
     * namespaces before executing the expression
     *
     * @throws XmlParseException
     */
    protected function expath(string $expression): array|false|null
    {
        return $this->initializeNamespaces()->xpath($expression);
    }
}
