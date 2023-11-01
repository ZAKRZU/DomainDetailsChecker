<?php

namespace App\Manager;

use App\Component\DomainRedirect;
use App\Component\DomainInfo;

class RedirectManager
{

    public static \CurlHandle|null $curl = null;

    private DomainRedirect $mainDomain;

    private DomainRedirect $subDomain;

    private DomainRedirect $domainWithPath;

    public function __construct(DomainInfo $domain, DomainInfo $subDomain)
    {
        $this->mainDomain = new DomainRedirect($domain);

        $this->subDomain = new DomainRedirect($subDomain);

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
