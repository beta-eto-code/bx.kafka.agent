<?php

namespace Bx\Kafka\Agent;

use SplObserver;

interface ManagerInterface
{
    public function addTopic(TopicConfig $topicConfig): void;
    /**
     * @return string[]
     */
    public function getTopicNameList(): array;
    /**
     * @param string $topicName
     * @return TopicConfig[]
     */
    public function getTopicConfigListByTopicName(string $topicName): array;
    public function getTopicSubject(string $topic): ?TopicSubject;
    public function addObserver(string $topic, SplObserver $observer): void;
    public function addEventHandler(string $topic, string $module, string $class, string $method): void;
    public function registerEventHandler(string $topic, string $module, string $class, string $method): void;
}
