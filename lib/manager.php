<?php

namespace Bx\Kafka\Agent;

use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Exception;
use SplObserver;
use SplSubject;

class Manager implements ManagerInterface
{
    private const MODULE_ID = 'bx.kafka.agent';

    private static ?Manager $instance = null;
    /**
     * @var array<string, TopicConfig[]>
     */
    private array $topicConfigList = [];
    /**
     * @var SplSubject[]
     */
    private array $subjectList = [];

    public static function getInstance(): Manager
    {
        if (static::$instance instanceof Manager) {
            return static::$instance;
        }

        return static::$instance = new Manager(...static::createTopicConfigListFromModuleOptions());
    }

    /**
     * @return TopicConfig[]
     */
    private static function createTopicConfigListFromModuleOptions(): array
    {
        $moduleId = static::MODULE_ID;
        $topicEventMap = (array) (json_decode(Option::get($moduleId, 'topicEventMap', '[]'), true) ?: []);
        $result = [];
        foreach ($topicEventMap as $item) {
            $topicName = $item['key'] ?: '';
            $eventName = $item['value'] ?: '';
            if (!empty($topicName) && !empty($eventName)) {
                $result[] = new TopicConfig($topicName, $eventName);
            }
        }
        return $result;
    }

    private function __construct(TopicConfig ...$topicConfigList)
    {
        foreach ($topicConfigList as $topicConfig) {
            $this->addTopic($topicConfig);
        }
    }

    public function addTopic(TopicConfig $topicConfig): void
    {
        $this->topicConfigList[$topicConfig->topicName][] = $topicConfig;
    }

    public function addObserver(string $topic, SplObserver $observer): void
    {
        if ($observer instanceof BaseObserver) {
            ExtOptions::getInstance()->registerObserver(get_class($observer));
        }

        $this->getOrCreateTopicSubject($topic)->attach($observer);
    }

    private function getOrCreateTopicSubject(string $topic): TopicSubject
    {
        $subject = $this->getTopicSubject($topic);
        if ($subject instanceof TopicSubject) {
            return $subject;
        }

        return $this->subjectList[$topic] = new TopicSubject($topic);
    }

    public function getTopicSubject(string $topic): ?TopicSubject
    {
        $subject = $this->subjectList[$topic] ?: null;
        if ($subject instanceof TopicSubject) {
            return $subject;
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function addEventHandler(string $topic, string $module, string $class, string $method): void
    {
        EventManager::getInstance()->addEventHandler(
            static::MODULE_ID,
            $this->getEventNameByTopic($topic),
            $module,
            [$class, $method]
        );
    }

    /**
     * @throws Exception
     */
    public function registerEventHandler(string $topic, string $module, string $class, string $method): void
    {
        EventManager::getInstance()->registerEventHandler(
            static::MODULE_ID,
            $this->getEventNameByTopic($topic),
            $module,
            $class,
            $method
        );
    }

    /**
     * @throws Exception
     */
    private function getEventNameByTopic(string $topic): string
    {
        $configList = $this->getTopicConfigListByTopicName($topic);
        if (empty($configList)) {
            throw new Exception("Не найдено зарегистрированного события для топика $topic");
        }

        return current($configList)->eventName;
    }

    public function getTopicNameList(): array
    {
        $resultList = array_merge(
            array_keys($this->topicConfigList),
            array_keys($this->subjectList)
        );
        return array_unique($resultList);
    }

    public function getTopicConfigListByTopicName(string $topicName): array
    {
        return $this->topicConfigList[$topicName] ?: [];
    }
}
