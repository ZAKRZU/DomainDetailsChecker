<?php

namespace App\Manager;

use App\Component\DomainComponent;
use App\Component\DomainRedirect;

class RedirectManager
{

    public static \CurlHandle|null $curl = null;

    private DomainRedirect $mainDomain;

    private DomainRedirect $subDomain;

    private DomainRedirect $domainWithPath;

    public function __construct(DomainComponent $domain)
    {
        $this->mainDomain = new DomainRedirect($domain);

        $subdomain = new DomainComponent('www.'.$domain->getDomain());
        $this->subDomain = new DomainRedirect($subdomain);

        $this->domainWithPath = new DomainRedirect($domain, '/random/url/');
    }

    public function getMainDomain(): DomainRedirect
    {
        return $this->mainDomain;
    }

    public function getSubDomain(): DomainRedirect
    {
        return $this->subDomain;
    }

    public function getDomainWithPath(): DomainRedirect
    {
        return $this->domainWithPath;
    }

    public function __destruct()
    {
        if (RedirectManager::getCurlHandle() !== null)
            curl_close(RedirectManager::getCurlHandle());
    }

    public static function getCurlHandle()
    {
        if (RedirectManager::$curl === null)
            RedirectManager::$curl = curl_init();
        return RedirectManager::$curl;
    }
}
