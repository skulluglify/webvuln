<?php
namespace Skfw\Cabbage;

use Override;
use Skfw\Interfaces\Cabbage\IValues;
use Stringable;

readonly class Values implements IValues, Stringable {

    private string $_name;
    private array $_values;

    public function __construct(string $name, array $values = [])
    {

        $this->_name = $name;
        $this->_values = $values;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->first() ?? "";
    }

    #[Override]
    public function getName(): string
    {
        return $this->_name;
    }

    #[Override]
    public function getValues(): array
    {
        return $this->_values;
    }

    #[Override]
    public function first(): ?string
    {
        $length = count($this->_values);
        if ($length > 0) return $this->_values[0];
        return null;
    }
}
