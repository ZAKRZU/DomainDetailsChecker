<?php

namespace Zakrzu\DDC\Controller;

use Iodev\Whois\Exceptions\ServerMismatchException;
use Zakrzu\DDC\App;

use Zakrzu\DDC\Component\SSLComponent;
use Zakrzu\DDC\Component\WordpressComponent;
use Zakrzu\DDC\Component\DomainInfo;

use Zakrzu\DDC\Entity\DomainEntity;

use Zakrzu\DDC\Manager\DomainChecker;
use Zakrzu\DDC\Manager\RedirectManager;

use Zakrzu\DDC\Modules\Template\TemplateView;
use Zakrzu\DDC\Modules\WhoisExt\WhoisExt;

class IndexController
{
    private $db = null;
    private ?WhoisExt $whoisExt = null;

    public function __construct()
    {
        $this->whoisExt = new WhoisExt();
        $this->db = App::$app->getDb();
    }

    public function getView(): ?TemplateView
    {
        if (isset($_GET["lookup"]) && strlen($_GET["lookup"]) > 0) {
            return $this->index();
        } else {
            return $this->form();
        }
    }

    public function index(): TemplateView
    {
        $activeDomain = null;
        $txtLookup = "";
        $hasTXT = false;
        $db = null;
        $dns = null;
        $ssl = null;
        $redirectManager = null;
        $mRedirect = null;
        $sRedirect = null;
        $rRedirect = null;
        $redList = [];
        $wp = null;
        $whoisInfo = null;
        $whoisRaw = null;

        $domainName = $this->parseDomain($_GET["lookup"]);
        $mainDomain = new DomainInfo($domainName);
        $subDomain = new DomainInfo("www." . $domainName);

        if ($mainDomain->getDns())
            $activeDomain = $mainDomain;
        else if ($subDomain->getDns())
            $activeDomain = $subDomain;

        try {
            $whoisInfo = $this->whoisExt->loadDomainInfo($domainName);
            $whoisRaw = $this->whoisExt->lookupDomain($domainName)->text ?? null;
        } catch (ServerMismatchException $e) {
            $whoisRaw = $e->getMessage();
        }

        if ($activeDomain) {
            if (isset($_GET["txt"])) {
                $txtLookup = $_GET["txt"];
                $hasTXT = $activeDomain->getDns()->hasTXT($txtLookup);
            }

            $db = $this->getDbVars($mainDomain);

            $dns = [
                "txt_count" => count($activeDomain->getDns()->TXT),
                "ns_count" => count($activeDomain->getDns()->NS),
                "caa_count" => count($activeDomain->getDns()->CAA),
            ];

            $ssl = new SSLComponent($activeDomain->getDomainName());

            $redirectManager = new RedirectManager($mainDomain, $subDomain);
            $mRedirect = $redirectManager->getMainDomain();
            $sRedirect = $redirectManager->getSubDomain();
            $rRedirect = $redirectManager->getDomainWithPath();
            $redList[] = $mRedirect;
            $redList[] = $sRedirect;
            $redList[] = $rRedirect;

            $wp = new WordpressComponent($redirectManager->getMainDomain(), $activeDomain->getDomainName());
        }

        return new TemplateView("body.html", [
            "domain_name" => $domainName,
            "active_domain" => $activeDomain,
            "main_domain" => $mainDomain,
            "sub_domain" => $subDomain,
            "db" => $db,
            "dns" => $dns,
            "txt_lookup" => $txtLookup,
            "has_txt" => $hasTXT,
            "ssl" => $ssl,
            "whois_info" => $whoisInfo,
            "whois_raw" => $whoisRaw,
            "red_list" => $redList,
            "wp" => $wp,
        ]);
    }

    public function getDbVars(DomainInfo $domain): array
    {
        $ret = [];
        if ($this->db && $domain) {
            $manager = new DomainChecker();
            $dEntity = new DomainEntity($domain->getDomainName(), 'now');
            $ret["counter"] = $manager->countDomain($domain->getDomainName());
            if ($ret["counter"] > 0)
                $ret["lastTime"] = $manager->getLastDomain($domain->getDomainName())->getDate()->format('d F Y');
            else
                $ret["lastTime"] = null;

            if (isset($_SESSION['lastDomain'])) {
                if (strcmp($_SESSION['lastDomain'], $domain->getDomainName()) !== 0) {
                    $manager->add($dEntity);
                }
            } else {
                $manager->add($dEntity);
            }

            $_SESSION['lastDomain'] = $domain->getDomainName();
        }
        return $ret;
    }

    public function parseDomain(string $domain): string
    {
        $parsedName = trim($domain);
        if (parse_url($parsedName, PHP_URL_HOST) != null) {
            $parsedName = parse_url($parsedName, PHP_URL_HOST);
        }
        if (str_contains($parsedName, '/')) {
            $parsedName = explode('/', $parsedName)[0];
        }
        if (str_starts_with($parsedName, '*.')) {
            $parsedName = substr($parsedName, 2);
        }
        if (str_starts_with($parsedName, 'www.')) {
            $parsedName = substr($parsedName, 4);
        }
        return trim($parsedName);
    }

    public function form(): TemplateView
    {
        return new TemplateView('body.html');
    }
}
