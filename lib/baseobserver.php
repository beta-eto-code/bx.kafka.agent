<?php

namespace Bx\Kafka\Agent;
use SplObserver;
use SplSubject;

abstract class BaseObserver implements SplObserver
{
    abstract public static function getName(): string;

    /**
     * @return Option[]
     */
    abstract protected static function getInternalOptions(): array;
    abstract function processMessage(NewMessageSubject $message);

    final public static function getOptionValue(string $optionName, $defaultValue = null): mixed
    {
        return \Bitrix\Main\Config\Option::get('bx.kafka.agent', static::class . '_' . $optionName, $defaultValue);
    }

    /**
     * @return array<string, array>
     */
    final public static function getOptions(): array
    {
        $options = [];
        foreach (static::getInternalOptions() as $option) {
            if ($option instanceof Option) {
                $name = static::class . '_' . $option->getName();
                $options[$name] = $option->jsonSerialize();
                $options[$name]['name'] = $name;
            }
        }

        return $options;
    }

    final public function update(SplSubject $subject): void
    {
        if (!($subject instanceof NewMessageSubject)) {
            return;
        }
        $this->processMessage($subject);
    }
}
