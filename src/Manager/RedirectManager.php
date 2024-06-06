<?php

namespace Zakrzu\DDC\Manager;

use Zakrzu\DDC\Component\DomainRedirect;
use Zakrzu\DDC\Component\DomainInfo;

class RedirectManager
{

    public static \CurlHandle|null $curl = null;

    private DomainRedirect $mainDomain;

    private DomainRedirect $subDomain;

    private DomainRedirect $domainWithPath;

    private int $failedCount = 0;

    public function __construct(DomainInfo $domain, DomainInfo $subDomain)
    {
        $curl = RedirectManager::getCurlHandle();
        $curlTimeout = 15;
        curl_setopt($curl, CURLOPT_TIMEOUT, $curlTimeout);

        $this->mainDomain = new DomainRedirect($domain);

        if ($this->mainDomain->getErrorCode() > 0) {
            $this->failedCount++;
            $curlTimeout = 5;
            curl_setopt($curl, CURLOPT_TIMEOUT, $curlTimeout);
        }

        $this->subDomain = new DomainRedirect($subDomain);

        if ($this->subDomain->getErrorCode() > 0)
            $this->failedCount++;

        $this->domainWithPath = new DomainRedirect($domain, '/random/url/');

        if ($this->domainWithPath->getErrorCode() > 0)
            $this->failedCount++;
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

    public function getFailedCount(): int
    {
        return $this->failedCount;
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
