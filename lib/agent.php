<?php

namespace Bx\Kafka\Agent;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Exception;
use KafkaClient\Client;
use MessageBroker\ClientInterface;
use MessageBroker\MessageInterface;
use RdKafka\Conf;
use RdKafka\Consumer;
use RdKafka\TopicConf;

class Agent
{
    private const MODULE_ID = 'bx.kafka.agent';

    private ClientInterface $client;
    private EventManager $eventManager;
    private ManagerInterface $manager;

    /**
     * @throws Exception
     */
    public static function initFromModuleOptions(): Agent
    {
        $eventManager = EventManager::getInstance();
        $client = static::createKafkaClientFromModuleOptions();
        return new Agent($eventManager, $client, Manager::getInstance());
    }

    /**
     * @throws Exception
     */
    private static function createKafkaClientFromModuleOptions(): ClientInterface
    {
        return Factory::createConsumerClient();
    }

    public function __construct(EventManager $eventManager, ClientInterface $client, ManagerInterface $manager)
    {
        $this->eventManager = $eventManager;
        $this->client = $client;
        $this->manager = $manager;
    }

    public function execute(array $options = []): void
    {
        foreach ($this->manager->getTopicNameList() as $topicName) {
            $this->executeTopic($topicName, $options);
        }
    }

    /**
     * @param string $topicName
     * @param array $options
     * @return void
     */
    private function executeTopic(string $topicName, array $options = []): void
    {
        $configList = $this->manager->getTopicConfigListByTopicName($topicName);
        $subject = $this->manager->getTopicSubject($topicName);
        foreach ($this->client->getMessageIterator($topicName, $options) as $message) {
            $this->sendMessageToEvents($message, $configList);

            if ($subject instanceof TopicSubject) {
                $subject->sendMessage($message);
            }
        }
    }

    /**
     * @param MessageInterface $message
     * @param TopicConfig[] $configList
     * @return void
     */
    private function sendMessageToEvents(MessageInterface $message, array $configList): void
    {
        foreach ($configList as $config) {
            $this->eventManager->send(new Event(
                static::MODULE_ID,
                $config->eventName,
                ['message' => $message]
            ));
        }
    }
}
