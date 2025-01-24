<?php

namespace Bx\Kafka\Agent;

class ExtOptions
{
    protected static ?ExtOptions $instance = null;
    private array $options = [];

    public static function getInstance(): ExtOptions
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public function getOptions(): array
    {
        return array_values($this->options);
    }

    public function registerObserver(string $observerClass): void
    {
        if (!is_a($observerClass, BaseObserver::class, true)) {
            return;
        }

        $tabName = $observerClass::getName();
        foreach ($observerClass::getOptions() as $name => $optionData) {
            $this->addOption($tabName, $name, $optionData);
        }
    }

    public function addStringOption(
        string $tabName,
        string $name,
        ?string $description = null,
        bool $isMultipleValue = false
    ): void {
        $this->addOption($tabName, $name, [
            'type' => 'string',
            'name' => $name,
            'label' => $description ?? $name,
            'multiple' => $isMultipleValue,
        ]);
    }

    public function addCheckOption(string $tabName, string $name, ?string $description = null): void
    {
        $this->addOption($tabName, $name, [
            'type' => 'checkbox',
            'name' => $name,
            'label' => $description ?? $name,
        ]);
    }

    public function addSelectOption(
        string $tabName,
        string $name,
        array $selectValues,
        ?string $description = null,
        bool $isMultipleValue = false
    ): void {
        $this->addOption($tabName, $name, [
            'type' => 'select',
            'name' => $name,
            'label' => $description ?? $name,
            'values' => $selectValues,
            'multiple' => $isMultipleValue,
        ]);
    }

    private function addOption(string $tabName, string $optionName, array $optionData): void
    {
        $tabId = $this->getIdByName($tabName);
        if (!array_key_exists($tabId, $this->options)) {
            $this->options[$tabId]['tab'] = $tabName;
        }
        $this->options[$tabId]['options'][$optionName] = $optionData;
    }

    private function getIdByName(string $tabName): string
    {
        return md5($tabName);
    }
}
