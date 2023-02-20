<?php

class Database
{
    private $conn;
    public function __construct(string $servername, string $username, string $password, string $dbname)
    {
        try {
            $this->conn = new mysqli($servername, $username, $password, $dbname);
        } catch (\Throwable $th) {
            if (mysqli_connect_error()) {
                // die("Connection failed: " . mysqli_connect_error());
                return;
            }
        }

        $sql = "CREATE TABLE if not exists Domain_Checker(
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            domain VARCHAR(120) NOT NULL,
            date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        if ($this->conn->query($sql) !== TRUE) {
            die('Error creating table:' . $this->conn->error);
        }
    }

    public function isDBSupported(): bool
    {
        return $this->conn ? true : false;
    }

    public function addDomainCheck(string $domain)
    {
        if (!$this->conn)
            return;

        $stmt = $this->conn->prepare("INSERT INTO Domain_Checker (domain) VALUES (?)");
        $stmt->bind_param("s", $domain);
        $stmt->execute();

        $stmt->close();
    }

    public function getDomainCheckCount(string $domain): int
    {
        if (!$this->conn)
            return -1;

        $stmt = $this->conn->prepare("SELECT count(distinct DATE(date)) FROM Domain_Checker WHERE domain like (?)");
        $stmt->bind_param("s", $domain);
        $stmt->execute();

        $stmt->bind_result($value);
        $stmt->fetch();

        $stmt->close();
        return $value;
    }

    public function getLastDomainCheck(string $domain): string
    {
        if (!$this->conn)
            return '';

        $stmt = $this->conn->prepare("SELECT * FROM Domain_Checker WHERE domain like (?) ORDER BY id desc LIMIT 1");
        $stmt->bind_param("s", $domain);
        $stmt->execute();

        $stmt->bind_result($id, $domain, $date);
        while ($stmt->fetch()) {
            // echo "<br>";
        }

        $date = date_create($date);

        $stmt->close();
        return $date->format('d F o');
    }

    public function __destruct()
    {
        if ($this->conn)
            $this->conn->close();
    }
}
