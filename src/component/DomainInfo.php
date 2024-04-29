<?php

namespace Zakrzu\DDC\Component;

use Zakrzu\DDC\App;

use Zakrzu\DDC\Exceptions\DnsException;
use Zakrzu\DDC\Modules\Dns\DnsZone;

class DomainInfo
{

    /**
     * Domain name
     *
     * @var string
     */
    private string $domainName;

    /**
     * Domain DNS Zone
     *
     * @var DnsZone|null
     */
    private ?DnsZone $dns = null;

    private string $lastError = "";

    /**
     * Creates new domain information instance
     *
     * @param string $domainName
     */
    public function __construct(string $domainName)
    {
        $this->domainName = $domainName;
        try {
            $this->dns = App::$app->getDnsModule()->dig($this->getDomainName());
        } catch (DnsException $e) {
            $this->lastError = $e->getMessage();
        }
    }

    /**
     * Returns domain name
     *
     * @return string
     */
    public function getDomainName(): string
    {
        return $this->domainName;
    }

    public function getDns(): ?DnsZone
    {
        return $this->dns;
    }

    public function getLastErrorMessage(): string
    {
        return $this->lastError;
    }
}
