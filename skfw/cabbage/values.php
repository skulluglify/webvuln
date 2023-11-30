<?php
namespace Skfw\Cabbage;

use Override;
use Skfw\Interfaces\Cabbage\IValues;
use Stringable;

readonly class Values implements Stringable, IValues {

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
        return $this->shift() ?? '';
    }

    #[Override]
    public function name(): string
    {
        return $this->_name;
    }

    #[Override]
    public function values(): array
    {
        return $this->_values;
    }

    #[Override]
    public function shift(): ?string
    {
        $length = count($this->_values);
        if ($length > 0) return $this->_values[0];
        return null;
    }
}
