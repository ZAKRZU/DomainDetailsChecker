<?php

namespace App\Entity;

class DomainEntity
{
    private int $id;

    private string $domain;

    private \DateTime $date;

    public function __construct(string $domain, string $time)
    {
        $this->setDomain($domain);
        $this->setDate(new \DateTime($time));
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
	 * @return \DateTime
	 */
	public function getDate(): \DateTime {
		return $this->date;
	}
	
	/**
	 * @param \DateTime $date 
	 * @return self
	 */
	public function setDate(\DateTime $date): self {
		$this->date = $date;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}
	
	/**
	 * @param int $id 
	 * @return self
	 */
	public function setId(int $id): self {
		$this->id = $id;
		return $this;
	}
}
