<?php

namespace App\Component;

class DnsZone
{

    /**
     * Domain name for which DNS zone will be checked
     *
     * @var string
     */
    private string $domainName;

    /**
     * Contains records received from the domain's DNS zone
     *
     * @var array
     */
    private array $recordsList;

    /**
     * Indicates whether there are records in the DNS zone
     *
     * @var boolean
     */
    private bool $haveAnyDNSRecords;

    /**
     * Creates a DNS zone checker for a given domain
     *
     * @param string $domainName
     */
    public function __construct(string $domainName)
    {
        $this->domainName = $domainName;
        $this->haveAnyDNSRecords = false;
        $this->recordsList = [
            'A' => [],
            'NS' => [],
            'TXT' => [],
        ];
        if (strlen($this->domainName) <= 0)
            return;
        if (checkdnsrr($this->domainName, "A")) {
            $this->haveAnyDNSRecords = true;
            $this->fetchZone();
        }
    }

    /**
     * Gets the DNS zone if it exist and formats it for easier access
     *
     * @return void
     */
    public function fetchZone()
    {
        if (!$this->haveAnyDNSRecords())
            return;

        $recordsTable = dns_get_record($this->domainName, DNS_ALL);

        foreach ($recordsTable as $key => $record) {
            switch ($record['type']) {
                // can be missleading when subdomain have cname record that points to main domain (because subdomain does not have an A record 
                // but the website shows it as an A record)
                // TODO: add check to indicate that problem
                case 'A':
                    $pushRecord = [
                        'ip' => $record['ip'],
                        'reverse' => gethostbyaddr($record['ip'])
                    ];
                    array_push($this->recordsList['A'], $pushRecord);
                    break;

                case 'NS':
                    $pushRecord = [
                        'host' => $record['target'],
                        'ip' => gethostbyname($record['target'])
                    ];
                    array_push($this->recordsList['NS'], $pushRecord);
                    break;

                case 'TXT':
                    $pushRecord = $record['entries'][0];
                    array_push($this->recordsList['TXT'], $pushRecord);
                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Returns information whether the domain has a valid DNS zone
     *
     * @return boolean
     */
    public function haveAnyDNSRecords(): bool
    {
        return $this->haveAnyDNSRecords;
    }

    /**
     * Returns the first A record if it exist, otherwise it returns null
     *
     * @return array|null
     */
    public function getARecord(): ?array
    {
        if ($this->recordsList['A'])
            return $this->recordsList['A'][0];

        return null;
    }

    /**
     * Returns all A records. If no records were found, it returns an empty array
     *
     * @return array
     */
    public function getARecords(): array
    {
        return $this->recordsList['A'];
    }

    /**
     * Returns the number of A records found
     *
     * @return integer
     */
    public function countARecords(): int
    {
        return count($this->recordsList['A']);
    }

    /**
     * Returns all NS records. If no records were found, it returns an empty array
     *
     * @return array
     */
    public function getNSRecords(): array
    {
        return $this->recordsList['NS'];
    }

    /**
     * Returns the number of NS records found
     *
     * @return integer
     */
    public function countNSRecords(): int
    {
        return count($this->recordsList['NS']);
    }

    /**
     * Returns all TXT records. If no records were found, it returns an empty array
     *
     * @return array
     */
    public function getTXTRecords(): array
    {
        return $this->recordsList['TXT'];
    }

    /**
     * Returns TRUE when a TXT record with the given value has been found
     * otherwise returns FALSE
     *
     * @param string $lookingFor
     * @return boolean
     */
    public function hasTXTRecord(string $lookingFor): bool
    {
        if ($this->countTXTRecords() > 0) {
            $allTxts = $this->getTXTRecords();
            if (array_search($lookingFor, $allTxts) != false)
                return true;
        }
        return false;
    }

    /**
     * Returns the number of TXT records found
     *
     * @return integer
     */
    public function countTXTRecords(): int
    {
        return count($this->recordsList['TXT']);
    }
}
