<?php

namespace Zakrzu\DDC\Component;

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
    private ?DnsZone $dnsZone;

    /**
     * Creates new domain information instance
     *
     * @param string $domainName
     */
    public function __construct(string $domainName)
    {
        $this->domainName = $domainName;
        $this->dnsZone = new DnsZone($this->getDomainName());
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

    /**
     * Returns DNS zone
     *
     * @return DnsZone|null
     */
    public function getDNSZone(): ?DnsZone
    {
        return $this->dnsZone;
    }

    /**
     * Returns whether the DNS zone exist
     *
     * @return boolean
     */
    public function dnsZoneExist(): bool
    {
        return $this->getDNSZone()->haveAnyDNSRecords();
    }
}
