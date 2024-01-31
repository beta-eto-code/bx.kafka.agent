<?php

namespace Bx\Kafka\Agent;

class TopicConfig
{
    public string $topicName = '';
    public string $eventName = '';

    public function __construct(string $topicName, ?string $eventName = null)
    {
        $this->topicName = $topicName;
        $this->eventName = $eventName;
    }
}
