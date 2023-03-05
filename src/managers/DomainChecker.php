<?php
namespace App\Manager;

use App\App;
use App\Component\Database;
use App\Entity\DomainEntity;

class DomainChecker
{
    private Database $db;

    public function __construct() {
        $this->db = App::$app->getDb();
    }

    public function add(DomainEntity $domain): bool
    {
        $result = false;
        $sql = "INSERT INTO domain_checker (domain) VALUES (?)";
        $domainName = $domain->getDomain();

        $stmt = $this->db->getConn()->prepare($sql);
        $stmt->bind_param('s', $domainName);

        $result = $stmt->execute();

        return $result;
    }

    public function getAll(): array {
        $resultArray = [];
        $sql = "SELECT * FROM domain_checker";
        $stmt = $this->db->getConn()->prepare($sql);
        $result = $stmt->execute();
        if ($result) {
            $stmt->bind_result($id, $domain, $date);
            while ($stmt->fetch()) {
                $domain = new DomainEntity($domain, $date);
                $domain->setId($id);
                array_push($resultArray, $domain);
            }
        }
        $stmt->close();

        return $resultArray;
    }

    public function getAllByDomain(string $domain): array {
        $resultArray = [];
        $sql = "SELECT * FROM domain_checker WHERE domain like (?)";
        $stmt = $this->db->getConn()->prepare($sql);
        $stmt->bind_param('s', $domain);
        $stmt->bind_result($id, $domain, $date);
        $result = $stmt->execute();
        if ($result) {
            while ($stmt->fetch()) {
                $domain = new DomainEntity($domain, $date);
                $domain->setId($id);
                array_push($resultArray, $domain);
            }
        }
        $stmt->close();

        return $resultArray;
    }

    public function countDomain(string $domain): int|bool {
        $sql = "SELECT count(*) FROM domain_checker WHERE domain like (?)";
        $stmt = $this->db->getConn()->prepare($sql);
        $stmt->bind_param('s', $domain);
        $result = $stmt->execute();
        if ($result) {
            $stmt->bind_result($result);
            $stmt->fetch();
        }
        $stmt->close();

        return intval($result);
    }

    public function getLastDomain(string $domain): DomainEntity|bool {
        $sql = "SELECT * FROM domain_checker WHERE domain like (?) order by date desc LIMIT 1";
        $stmt = $this->db->getConn()->prepare($sql);
        $stmt->bind_param('s', $domain);
        $stmt->bind_result($id, $domain, $date);
        $result = $stmt->execute();
        $domain = false;
        if ($result) {
            if ($stmt->fetch()) {
                $domain = new DomainEntity($domain, $date);
                $domain->setId($id);
            }
        }
        $stmt->close();

        return $domain;
    }

    public function getById(int $id): DomainEntity|bool {
        $sql = "SELECT * FROM domain_checker WHERE id = ?";
        $stmt = $this->db->getConn()->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->bind_result($id, $domain, $date);
        $result = $stmt->execute();
        $domain = false;
        if ($result) {
            if ($stmt->fetch()) {
                $domain = new DomainEntity($domain, $date);
                $domain->setId($id);
            }
        }
        $stmt->close();

        return $domain;
    }
}