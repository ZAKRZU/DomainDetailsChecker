<?php
namespace App\Component;


class DnsComponent
{
    private string $recordA;

    private string $revDns;


    public function __construct()
    {
		$this->setRecordA("Not found");
		$this->setRevDns("");
    }

	/**
	 * @return string
	 */
	public function getRecordA(): string {
		return $this->recordA;
	}
	
	/**
	 * @param string $recordA 
	 * @return self
	 */
	public function setRecordA(string $recordA): self {
		$this->recordA = $recordA;
		return $this;
	}

    /**
	 * @return string
	 */
	public function getRevDns(): string {
		return $this->revDns;
	}
	
	/**
	 * @param string $revDns 
	 * @return self
	 */
	public function setRevDns(string $revDns): self {
		$this->revDns = $revDns;
		return $this;
	}

}
