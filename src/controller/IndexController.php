<?php
namespace Zakrzu\DDC\Controller;

use Zakrzu\DDC\App;
use Zakrzu\DDC\Component\SSLComponent;
use Zakrzu\DDC\Component\WordpressComponent;
use Zakrzu\DDC\Component\DomainInfo;
use Zakrzu\DDC\Entity\DomainEntity;
use Zakrzu\DDC\Manager\DomainChecker;
use Zakrzu\DDC\Manager\RedirectManager;

use Iodev\Whois\Factory;
use Iodev\Whois\Whois;

class IndexController
{
    private $version = App::VERSION;
    private $db = null;
    private ?DomainInfo $mainDomain = null;
    private string $txtLookup = "";
    private ?Whois $whois = null;
    private bool $domainIsAvailable = true;

    public function __construct()
    {
        $this->db = App::$app->getDb();
        $this->whois = Factory::get()->createWhois();

        if (isset($_GET["txt"])) {
            $this->txtLookup = $_GET["txt"];
        }

        if (isset($_GET["lookup"])) {
            $parsed = $this->parseDomain($_GET["lookup"]);
            $this->mainDomain = new DomainInfo($parsed);
            $this->domainIsAvailable = $this->whois->isDomainAvailable($parsed);
            $this->index();
        } else {
            $this->form();
        }
    }

    public function index()
    {
        /*
        * This variables are provided for template rendering
        */
        if ($this->mainDomain->dnsZoneExist()) {
            if (!$this->domainIsAvailable) {
                $domainWhois = $this->whois->loadDomainInfo($this->mainDomain->getDomainName());
                $domainWhoisRaw = $this->whois->lookupDomain($this->mainDomain->getDomainName())->text;
            }
            if ($this->db) {
                $manager = new DomainChecker();
                $dEntity = new DomainEntity($this->mainDomain->getDomainName(), 'now');
                $counter = $manager->countDomain($this->mainDomain->getDomainName());
                if ($counter > 0)
                    $lastTime = $manager->getLastDomain($this->mainDomain->getDomainName())->getDate()->format('d F Y');
                else
                    $lastTime = null;

                if (isset($_SESSION['lastDomain'])) {
                    if (strcmp($_SESSION['lastDomain'], $this->mainDomain->getDomainName()) !== 0) {
                        $manager->add($dEntity);
                    }
                } else {
                    $manager->add($dEntity);
                }
                
                $_SESSION['lastDomain'] = $this->mainDomain->getDomainName();
            }

            $hasGivenTXT = $this->mainDomain->getDNSZone()->hasTXTRecord($this->txtLookup);
            $subDomain = new DomainInfo('www.'.$this->mainDomain->getDomainName());
            $ssl = new SSLComponent($this->mainDomain->getDomainName());
            $redirectManager = new RedirectManager($this->mainDomain, $subDomain);
            $mRedirect = $redirectManager->getMainDomain();
            $sRedirect = $redirectManager->getSubDomain();
            $rRedirect = $redirectManager->getDomainWithPath();

            $wp = new WordpressComponent($redirectManager->getMainDomain(), $this->mainDomain->getDomainName());
        }

        include __DIR__."/../../template/body.html";
    }

    public function parseDomain(string $domain): string
    {
        $parsedName = trim($domain);
        $parsedName = str_replace('*.', '', $parsedName);
        $parsedName = str_replace('www.', '', $parsedName);
        if (parse_url($parsedName, PHP_URL_HOST) != null) {
            $parsedName = parse_url($parsedName, PHP_URL_HOST);
        }
        if (str_contains($parsedName, '/')) {
            $parsedName = explode('/', $parsedName)[0];
        }
        return trim($parsedName);
    }

    public function form()
    {
        include __DIR__."/../../template/body.html";
    }

}
