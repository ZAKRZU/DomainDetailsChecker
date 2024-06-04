<?php

namespace Zakrzu\DDC;

use Zakrzu\DDC\Component\Database;

use Zakrzu\DDC\Controller\IndexController;

use Zakrzu\DDC\Modules\Module;
use Zakrzu\DDC\Modules\ModuleType;

use Zakrzu\DDC\Modules\Dns\DnsModule;

use Zakrzu\DDC\Modules\Template\TemplateModule;

use Zakrzu\DDC\Exceptions\ModuleException;

class App
{

    const VERSION = "0.6.0";

    public static $app;

    private ?Database $db;

    private array $modules = [];

    public function __construct()
    {
        App::$app = $this;
        $this->preInit();
        $index = new IndexController();
        $this->getTemplateModule()->display($index->getView());
    }

    public function preInit(): void
    {
        session_start();
        $this->loadConfiguration();
        $this->initModules();
        $this->loadExtensions();
        if (!(DB_NAME && DB_PASS && DB_NAME)) {
            $this->db = null;
            return;
        }
        $connectionDb = new Database();
        if ($connectionDb->isAlive())
            $this->db = $connectionDb;
        else
            $this->db = null;
    }

    public function initModules(): void
    {
        try {
            $this->modules["dns"] = new DnsModule();
            $this->modules["template"] = new TemplateModule();
        } catch (ModuleException $e) {
            echo $e->getMessage();
        }
    }

    public function getModuleByName(string $name): ?Module
    {
        return $this->modules[$name] ?? null;
    }

    public function getDnsModule(): ?DnsModule
    {
        return $this->getModuleByName(ModuleType::DNS) ?? null;
    }

    public function getTemplateModule(): ?TemplateModule
    {
        return $this->getModuleByName(ModuleType::TEMPLATE) ?? null;
    }

    /**
     * @return Database|null
     */
    public function getDb(): Database|null
    {
        return $this->db;
    }

    private function loadConfiguration(): void
    {
        if (file_exists('src/configuration.php')) {
            include_once('src/configuration.php');
        } else {
            copy('src/configuration.default.php', 'src/configuration.php');
            include_once('src/configuration.php');
        }
        if (!defined("APP_ENV"))
            define("APP_ENV", "PROD");
    }

    public function loadExtensions(): void
    {
        foreach (glob("extensions/*") as $folder) {
            foreach (glob($folder . '/*.php') as $script) {
                include $script;
            }
        }
    }
}
