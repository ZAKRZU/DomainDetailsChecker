<?php
define("VERSION", "V0.1.1");
include_once('Configuration.php');

require_once('Domain.php');
require_once('Database.php');
require_once('SSLUtil.php');
require_once('WordpressUtils.php');


class Manager
{
    private array $dns = [];

    private array $ns = [];

    private SSLUtil|null $ssl = null;

    private WordpressUtils|null $wp = null;

    private Database|null $db = null;

    private int $domainCheckCount = 0;

    private string $domainLastCheck = '';

    public function __construct(string $domain)
    {
        $this->db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        $checker = parse_url($domain, PHP_URL_HOST);
        if ($checker)
            $domain = $checker;

        $this->dns[''] = new Domain($domain);
        $this->dns['www'] = new Domain('www.' . $domain);

        if (!$this->isDomainValid())
            return;
        
        $this->domainCheckCount = $this->db->getDomainCheckCount($this->getMainDomain()->getHostname());

        if ($_SESSION['lastDomainChecked']) { // we dont count refreshed pages
            if (strcmp($_SESSION['lastDomainChecked'], $this->getMainDomain()->getHostname()) !== 0) {
                $this->db->addDomainCheck($this->getMainDomain()->getHostname());
            }
        } else {
            $this->db->addDomainCheck($this->getMainDomain()->getHostname());
        }

        $_SESSION['lastDomainChecked'] = $this->getMainDomain()->getHostname();
        
        $this->domainLastCheck = $this->db->getLastDomainCheck($this->getMainDomain()->getHostname());

        if ($this->getMainDomain()->isValid()) {
            $this->ns = dns_get_record($this->getMainDomain()->getHostname(), DNS_NS);
            $this->ssl = new SSLUtil($this->getMainDomain());
        } else if ($this->getSubdomain()->isValid()) {
            $this->ns = dns_get_record($this->getSubdomain()->getHostname(), DNS_NS);
            $this->ssl = new SSLUtil($this->getSubdomain());
        } else
            $this->ns = [];

        $this->wp = new WordpressUtils($this->getMainDomain()->getRedirects(), $this->getMainDomain()->getHostname());
    }

    public function getMainDomain(): Domain
    {
        return $this->dns[''];
    }

    public function getSubdomain(): Domain
    {
        return $this->dns['www'];
    }

    public function getNS(): array
    {
        return $this->ns;
    }

    public function getSSL(): SSLUtil|null
    {
        return $this->ssl;
    }

    public function getWordpressUtils(): WordpressUtils
    {
        return $this->wp;
    }

    public function isDomainValid(): bool
    {
        return $this->getMainDomain()->isValid() || $this->getSubdomain()->isValid();
    }

    public function getDomainCheckCount(): int
    {
        return $this->domainCheckCount;
    }

    public function getDomainLastCheckDate(): string
    {
        return $this->domainLastCheck;
    }

    public function isDBConnected(): bool
    {
        return $this->db->isConnected();
    }
}
