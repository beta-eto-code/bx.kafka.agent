<?php

namespace Bx\Kafka\Agent;

use Bitrix\Main\Config\Option;
use Exception;
use KafkaClient\Client;
use MessageBroker\ClientInterface;
use RdKafka\Conf;
use RdKafka\Consumer;
use RdKafka\Producer;
use RdKafka\TopicConf;

class Factory
{
    /**
     * @throws Exception
     */
    public static function createConsumerClient(): ClientInterface
    {
        $moduleId = 'bx.kafka.agent';
        $kafkaConfig = static::createKafkaConfig($moduleId);
        $groupId = Option::get($moduleId, 'groupId');
        if (empty($groupId)) {
            throw new Exception('Не указан идентификатор группы (consumer group)');
        }

        $kafkaConfig->set('group.id', $groupId);
        $kafkaConfig->set('enable.partition.eof', 'true');
        $consumer = new Consumer($kafkaConfig);
        $servers = Option::get($moduleId, 'servers');
        if (empty($servers)) {
            throw new Exception('Не указаны адреса серверов');
        }
        $consumer->addBrokers($servers);

        $topicConfig = new TopicConf();
        $topicConfig->set('auto.commit.interval.ms', 100);
        $topicConfig->set('offset.store.method', 'broker');
        $topicConfig->set('auto.offset.reset', 'earliest');

        return Client::initAsConsumer($consumer, $topicConfig);
    }

    /**
     * @throws Exception
     */
    public static function createProducerClient(): ClientInterface
    {
        $moduleId = 'bx.kafka.agent';
        $producer = new Producer(static::createKafkaConfig($moduleId));
        $servers = Option::get($moduleId, 'servers');
        if (empty($servers)) {
            throw new Exception('Не указаны адреса серверов');
        }
        $producer->addBrokers($servers);

        $topicConfig = new TopicConf();
        $topicConfig->set('auto.commit.interval.ms', 100);
        $topicConfig->set('offset.store.method', 'broker');
        $topicConfig->set('auto.offset.reset', 'earliest');

        return Client::initAsProducer($producer, $topicConfig);
    }

    /**
     * @throws Exception
     */
    private static function createKafkaConfig(string $moduleId): Conf
    {
        $kafkaConfig = new Conf();
        $login = Option::get($moduleId, 'login');
        $password = Option::get($moduleId, 'password');
        if (!empty($login)) {
            $kafkaConfig->set('sasl.username', $login);
            $kafkaConfig->set('sasl.password', $password);
            $kafkaConfig->set('security.protocol', 'sasl_ssl');
            $kafkaConfig->set('sasl.mechanism', 'PLAIN');
        }
        return $kafkaConfig;
    }
}
