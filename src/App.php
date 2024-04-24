<?php 
namespace Zakrzu\DDC;

use Zakrzu\DDC\Component\Database;
use Zakrzu\DDC\Controller\IndexController;

class App
{

    const VERSION = "0.5.x-dev";

    public static $app;

    private ?Database $db;

    private array $overrides = [];

    private array $hooks = [];

    public function __construct()
    {
        App::$app = $this;
        $this->preInit();
        $index = new IndexController($this->overrides, $this->hooks);
    }

    public function preInit(): void
    {
        session_start();
        $this->loadConfiguration();
        $this->loadExtensions();
        if (!(DB_NAME && DB_PASS && DB_NAME))
        {
            $this->db = null;
            return;
        }
        $connectionDb = new Database();
        if ($connectionDb->isAlive())
            $this->db = $connectionDb;
        else
            $this->db = null;
    }

    public static function Log($something) {
        print_r("<pre>");
        print_r($something);
        print_r("</pre>");
    }

	/**
	 * @return Database|null
	 */
	public function getDb(): Database|null {
		return $this->db;
	}

    private function loadConfiguration() {
        if (file_exists('src/configuration.php')) {
            include_once('src/configuration.php');
        } else {
            copy('src/configuration.default.php', 'src/configuration.php');
            include_once('src/configuration.php');
        }
    }

    public function addOverride(string $name, string $value) {
        $this->overrides[$name] = $value;
    }

    public function addHook(string $name, string $value) {
        $this->hooks[$name] = $value;
    }

    public function loadExtensions() {
        foreach(glob("extensions/*") as $folder) {
            foreach(glob($folder.'/*.php') as $script) {
                include $script;
            }
        } 
    }

}
