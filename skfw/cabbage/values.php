<?php
namespace Skfw\Cabbage;

use Override;
use Skfw\Interfaces\Cabbage\IValues;

readonly class Values implements IValues {

    private string $_name;
    private array $_values;

    public function __construct(string $name, array $values = []) {

        $this->_name = $name;
        $this->_values = $values;
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
}
