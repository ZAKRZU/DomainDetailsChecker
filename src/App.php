<?php 
namespace App;

use App\Component\Database;
use App\Controller\IndexController;

class App
{

    const VERSION = "0.4.0_DEV";

    public static $app;

    private ?Database $db;

    public function __construct()
    {
        App::$app = $this;
        $this->preInit();
        $index = new IndexController();
        if (isset($_GET['lookup'])) {
            $index->index();
        } else {
            $index->form();
        }
    }

    public function preInit(): void
    {
        session_start();
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
}
