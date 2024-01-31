<?php

namespace Bx\Kafka\Agent;

use MessageBroker\MessageInterface;
use SplObjectStorage;
use SplObserver;
use SplSubject;

class TopicSubject implements SplSubject
{
    private SplObjectStorage $observerStorage;
    private string $topicName;
    private ?MessageInterface $message = null;

    public function __construct(string $topicName)
    {
        $this->topicName = $topicName;
        $this->observerStorage = new SplObjectStorage();
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function attach(SplObserver $observer)
    {
        if (!$this->observerStorage->contains($observer)) {
            $this->observerStorage->attach($observer);
        }
    }

    public function detach(SplObserver $observer)
    {
        $this->observerStorage->detach($observer);
    }

    public function sendMessage(MessageInterface $message): void
    {
        $this->message = $message;
        $this->notify();
    }

    public function notify(): void
    {
        if (empty($this->message)) {
            return;
        }

        $messageSubject = new NewMessageSubject($this, $this->message);
        foreach ($this->observerStorage as $observer) {
            if ($observer instanceof SplObserver) {
                $observer->update($messageSubject);
            }
        }
        $this->message = null;
    }
}
