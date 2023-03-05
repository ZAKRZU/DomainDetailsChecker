<?php
namespace App\Component;

use App\Component\DnsComponent;

class DomainComponent
{

    private string $domain;

    private DnsComponent|null $dns;

    public function __construct(string $domain)
    {
        $this->setDomain($domain);
		$this->setDns(null);
    }
	
	public function fetchDNS(): void
	{
		$records = dns_get_record($this->getDomain(), DNS_A);
		if ($records) {
			$dns = new DnsComponent();
			$dns->setRecordA($records[0]['ip']);
			$dns->setRevDns(gethostbyaddr($dns->getRecordA()));
			$this->setDns($dns);
        }
	}

	/**
	 * @return string
	 */
	public function getDomain(): string {
		return $this->domain;
	}
	
	/**
	 * @param string $domain 
	 * @return self
	 */
	public function setDomain(string $domain): self {
		$this->domain = $domain;
		return $this;
	}

	/**
	 * @return DnsComponent
	 */
	public function getDns(): DnsComponent|null {
		return $this->dns;
	}
	
	/**
	 * @param DnsComponent $dns 
	 * @return self
	 */
	public function setDns(DnsComponent|null $dns): self {
		$this->dns = $dns;
		return $this;
	}

}
