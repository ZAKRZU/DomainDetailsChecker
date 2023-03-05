<?php
namespace App\Component;

class Database
{

    private \mysqli $conn;

    private bool $isAlive;

    public function __construct()
    {
        try {
            $this->conn = new \mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        } catch (\mysqli_sql_exception $th) {
            $this->setIsAlive(false);
            if (mysqli_connect_error()) {
                // die("Connection failed: " . mysqli_connect_error());
                return;
            }
        }
        $this->setIsAlive(true);
    }

	/**
	 * @return bool
	 */
	public function isAlive(): bool {
		return $this->isAlive;
	}
	
	/**
	 * @param bool $isAlive 
	 * @return self
	 */
	public function setIsAlive(bool $isAlive): self {
		$this->isAlive = $isAlive;
		return $this;
	}

	/**
	 * @return \mysqli
	 */
	public function getConn(): \mysqli {
		return $this->conn;
	}
	
	/**
	 * @param \mysqli $conn 
	 * @return self
	 */
	private function setConn(\mysqli $conn): self {
		$this->conn = $conn;
		return $this;
	}
}