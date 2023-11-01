<?php
namespace App\Controller;

use App\App;
use App\Component\SSLComponent;
use App\Component\WordpressComponent;
use App\Component\DomainInfo;
use App\Entity\DomainEntity;
use App\Manager\DomainChecker;
use App\Manager\RedirectManager;

class IndexController
{
    public function index()
    {
        $version = App::VERSION;
        $db = App::$app->getDb();

        /*
        * This variables are provided for template rendering
        */
        $notSafeDomain = $_GET['lookup'];
        $safeDomainName = $this->parseDomain($notSafeDomain);
        $mainDomain = new DomainInfo($safeDomainName);
        if ($mainDomain->dnsZoneExist()) {
            if ($db) {
                $manager = new DomainChecker();
                $dEntity = new DomainEntity($mainDomain->getDomainName(), 'now');
                $counter = $manager->countDomain($mainDomain->getDomainName());
                if ($counter > 0)
                    $lastTime = $manager->getLastDomain($mainDomain->getDomainName())->getDate()->format('d F Y');
                else
                    $lastTime = null;

                if (isset($_SESSION['lastDomain'])) {
                    if (strcmp($_SESSION['lastDomain'], $mainDomain->getDomainName()) !== 0) {
                        $manager->add($dEntity);
                    }
                } else {
                    $manager->add($dEntity);
                }
                
                $_SESSION['lastDomain'] = $mainDomain->getDomainName();
            }
            $subDomain = new DomainInfo('www.'.$safeDomainName);
            $ssl = new SSLComponent($mainDomain->getDomainName());
            $redirectManager = new RedirectManager($mainDomain, $subDomain);
            $mRedirect = $redirectManager->getMainDomain();
            $sRedirect = $redirectManager->getSubDomain();
            $rRedirect = $redirectManager->getDomainWithPath();

            $wp = new WordpressComponent($redirectManager->getMainDomain(), $mainDomain->getDomainName());
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
        $version = App::VERSION;
        $mainDomain = new DomainInfo("");
        include __DIR__."/../../template/body.html";
    }

}
