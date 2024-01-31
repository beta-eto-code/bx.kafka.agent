<?php

namespace Bx\Kafka\Agent;

use MessageBroker\MessageInterface;
use SplObserver;
use SplSubject;

class NewMessageSubject implements SplSubject
{
    private TopicSubject $topicSubject;
    private MessageInterface $message;

    public function __construct(TopicSubject $topicSubject, MessageInterface $message)
    {
        $this->topicSubject = $topicSubject;
        $this->message = $message;
    }

    public function getTopicName(): string
    {
        return $this->topicSubject->getTopicName();
    }

    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    public function attach(SplObserver $observer)
    {
        $this->topicSubject->attach($observer);
    }

    public function detach(SplObserver $observer)
    {
        $this->topicSubject->detach($observer);
    }

    public function notify()
    {
    }
}
