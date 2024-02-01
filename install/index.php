<?php

IncludeModuleLangFile(__FILE__);
use Bitrix\Main\ModuleManager;

class bx_kafka_agent extends CModule
{
    public $MODULE_ID = "bx.kafka.agent";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $errors;

    public function __construct()
    {
        $this->MODULE_VERSION = "1.0.0";
        $this->MODULE_VERSION_DATE = "2024-01-31 07:34:24";
        $this->MODULE_NAME = "Kafka agent";
        $this->MODULE_DESCRIPTION = "";
    }

    public function DoInstall(): bool
    {
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();
        ModuleManager::RegisterModule($this->MODULE_ID);
        return true;
    }

    public function DoUninstall(): bool
    {
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        ModuleManager::UnRegisterModule($this->MODULE_ID);
        return true;
    }

    public function InstallDB(): bool
    {
        return true;
    }

    public function UnInstallDB(): bool
    {
        return true;
    }

    public function InstallEvents(): bool
    {
        return true;
    }

    public function UnInstallEvents(): bool
    {
        return true;
    }

    public function InstallFiles(): bool
    {
        CopyDirFiles(__DIR__ . "/files", $_SERVER["DOCUMENT_ROOT"]);
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $serviceFilePath = $documentRoot . '/kfagent.service';
        $serviceContent = strtr(
            file_get_contents($serviceFilePath),
            ['{$documentRoot}' => $documentRoot]
        );
        file_put_contents($serviceFilePath, $serviceContent);
        return true;
    }

    public function UnInstallFiles(): bool
    {
        DeleteDirFiles(__DIR__ . "/files", $_SERVER["DOCUMENT_ROOT"]);
        return true;
    }
}
