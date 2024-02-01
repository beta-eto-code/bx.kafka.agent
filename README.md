# Bitrix Kafaka agent

## Установка 

```shell
composer require beta/bx.kafka.agent
```

Далее устанавливаем модуль (bx.kafka.agent) через админку - /bitrix/admin/partner_modules.php?lang=ru

После установки в корне проекта появится 2 файла:

* kfagent - скрипт для запуска проекта
* kfagent.service - сервис для подсистемы systemd

Далее переносим сервис для запуска:

```shell
mv kfagent.service /etc/systemd/system/
```

Перезапускаем конфигурацию systemd:

```shell
sudo systemctl daemon-reload
```

Активируем сервис:

```shell
sudo systemctl enable kfagent 
```

И запускаем его:

```shell
sudo systemctl start kfagent
```

## Пример регистрации через bitrix обработчики:

```php
use Bx\Kafka\Agent\Manager;

// через init.php
Manager::getInstance()->addEventHandler(
    'employee', // название топика
    'my.module', 
    'SomeNamespace\\MyClass', 
    'someStaticMethod'
);

// через миграцию
Manager::getInstance()->registerEventHandler(
    'employee', // название топика
    'my.module', 
    'SomeNamespace\\MyClass', 
    'someStaticMethod'
);
```

## Пример регистрации через SPL (Наблюдатель):

```php
use Bx\Kafka\Agent\Manager;
use Bx\Kafka\Agent\NewMessageSubject;

class MyNewEmployeeObserver implements SplObserver
{
    public function update(SplSubject $subject): void
    {
        if (!($subject instanceof NewMessageSubject)) {
            return;
        }
        
        $subject->getMessage()->getData(); // получаем данные из брокера
    }
}

Manager::getInstance()->addObserver(
    'employee', // название топика
    new MyNewEmployeeObserver() // экземпляр наблюдателя
);
```