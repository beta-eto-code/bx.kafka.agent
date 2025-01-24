<?php

namespace Bx\Kafka\Agent;

use JsonSerializable;

class Option implements JsonSerializable
{
    private array $data;

    public static function createString(
        string $name,
        ?string $description = null,
        bool $isMultipleValue = false
    ): Option {
        return new self([
            'type' => 'string',
            'name' => $name,
            'label' => $description ?? $name,
            'multiple' => $isMultipleValue,
        ]);
    }

    public static function createCheckbox(
        string $name,
        ?string $description = null
    ): Option {
        return new self([
            'type' => 'string',
            'name' => $name,
            'label' => $description ?? $name
        ]);
    }

    public static function createSelect(
        string $name,
        array $selectValues,
        ?string $description = null,
        bool $isMultipleValue = false
    ): Option {
        return new self([
            'type' => 'string',
            'name' => $name,
            'label' => $description ?? $name,
            'values' => $selectValues,
            'multiple' => $isMultipleValue,
        ]);
    }

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getName(): string
    {
        return (string) ($this->data['name'] ?? '');
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
