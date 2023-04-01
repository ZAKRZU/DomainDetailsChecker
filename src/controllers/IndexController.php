<?php
namespace App\Controller;

use App\App;
use App\Component\MainDomainComponent;
use App\Component\SSLComponent;
use App\Component\WordpressComponent;
use App\Entity\DomainEntity;
use App\Manager\DomainChecker;
use App\Manager\RedirectManager;

class IndexController
{
    public function index()
    {
        /*
        * This variables are provided to template to render page
        */
        $version = App::VERSION;
        $domainName = $this->parseDomain($_GET['lookup']);
        $mainDomain = new MainDomainComponent($domainName);
        $db = App::$app->getDb();
        if ($mainDomain->exist()) {
            if ($db) {
                $manager = new DomainChecker();
                $dEntity = new DomainEntity($domainName, 'now');
                $counter = $manager->countDomain($domainName);
                if ($counter > 0)
                    $lastTime = $manager->getLastDomain($domainName)->getDate()->format('d F Y');
                else
                    $lastTime = null;

                if (isset($_SESSION['lastDomain'])) {
                    if (strcmp($_SESSION['lastDomain'], $domainName) !== 0) {
                        $manager->add($dEntity);
                    }
                } else {
                    $manager->add($dEntity);
                }
                
                $_SESSION['lastDomain'] = $domainName;
            }

            $subdomain = $mainDomain->getSubdomain();
            $ssl = new SSLComponent($domainName);
            $redirectManager = new RedirectManager($mainDomain);
            $mRedirect = $redirectManager->getMainDomain();
            $sRedirect = $redirectManager->getSubDomain();
            $rRedirect = $redirectManager->getDomainWithPath();

            $wp = new WordpressComponent($redirectManager->getMainDomain(), $domainName);
        }

        include __DIR__."/../../template/body.html";
    }

    public function parseDomain(string $domain): string
    {
        return trim($domain);
    }

    public function hasNSRecords(string $domain): array|bool
    {
        $nsRecords = dns_get_record($domain, DNS_NS);
        if ($nsRecords) {
            if (count($nsRecords) > 0)
                return $nsRecords;
        }
        return false;
    }

    public function form()
    {
        $version = App::VERSION;
        $mainDomain = new MainDomainComponent("");
        include __DIR__."/../../template/body.html";
    }
}
