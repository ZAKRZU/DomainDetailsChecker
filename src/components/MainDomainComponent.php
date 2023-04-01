<?php
namespace App\Component;

use App\Component\DomainComponent;

class MainDomainComponent extends DomainComponent
{
    private array|bool $nsRecords;

    private DomainComponent|null $subdomain;

    public function __construct(string $domain)
    {
        parent::__construct($domain);
        $this->setSubdomain(null);
        $this->fetchDNS();
        $this->setNsRecords($this->hasNSRecords());
        if ($this->getNsRecords()) {
            $this->setSubdomain(new DomainComponent('www.'.$this->getDomain()));
            $this->getSubdomain()->fetchDNS();
        }
    }

    /**
     * @return array|boolean
     */
    private function hasNSRecords(): array|bool
    {
        if (strlen($this->getDomain()) <= 0)
            return false;
        $nsRecords = dns_get_record($this->getDomain(), DNS_NS);
        if ($nsRecords) {
            if (count($nsRecords) > 0)
                return $nsRecords;
        }
        return false;
    }

    /**
     * @return boolean
     */
    public function exist(): bool {
        return ($this->getNsRecords() || $this->getDns()) ? true : false;
    }

	/**
	 * @return array|bool
	 */
	public function getNsRecords(): array|bool {
		return $this->nsRecords;
	}
	
	/**
	 * @param array|bool $nsRecords 
	 * @return self
	 */
	public function setNsRecords(array|bool $nsRecords): self {
		$this->nsRecords = $nsRecords;
		return $this;
	}


	/**
	 * @return DomainComponent|null
	 */
	public function getSubdomain(): DomainComponent|null {
		return $this->subdomain;
	}
	
	/**
	 * @param DomainComponent|null $subdomain 
	 * @return self
	 */
	public function setSubdomain(DomainComponent|null $subdomain): self {
		$this->subdomain = $subdomain;
		return $this;
	}
}
