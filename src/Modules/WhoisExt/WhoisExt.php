<?php

namespace Zakrzu\DDC\Modules\WhoisExt;

use Zakrzu\DDC\Modules\Module;
use Zakrzu\DDC\Modules\ModuleType;

use Iodev\Whois\Factory;
use Iodev\Whois\Whois;
use Iodev\Whois\Modules\Tld\TldInfo;
use Iodev\Whois\Modules\Tld\TldResponse;
use Zakrzu\DDC\Modules\WhoisExt\Parsers\TldPlParser;

class WhoisExt extends Module
{

    private string $path = __DIR__ . "/timeouts/";

    private int $timeout = 3600; // in seconds

    private ?Whois $whois = null;

    private ?TldResponse $response = null;

    private ?TldInfo $info = null;

    private bool $isDomainLoaded = false;

    public function __construct()
    {
        parent::__construct(ModuleType::WHOIS_EXT);
        $this->whois = Factory::get()->createWhois();
        if (!file_exists($this->path)) {
            mkdir($this->path);
        }
    }

    public function getWhois(): ?Whois
    {
        return $this->whois;
    }

    public function loadDomainInfo(string $domainName): ?TldInfo
    {
        if (!$this->isDomainLoaded) {
            $this->loadDomain($domainName);
        }

        return $this->info;
    }

    public function lookupDomain(string $domainName): TldResponse
    {
        if (!$this->isDomainLoaded) {
            $this->loadDomain($domainName);
        }

        return $this->response;
    }

    public function loadDomain(string $domainName): void
    {
        $this->isDomainLoaded = true;
        $zone = null;
        $servers = $this->whois->getTldModule()->matchServers($domainName);
        foreach ($servers as $server) {
            if (str_ends_with($domainName, $server->getZone())) {
                if (!$zone) {
                    $zone = $server->getZone();
                } else {
                    if (!str_contains($zone, $server->getZone())) {
                        $zone = $server->getZone();
                    }
                }
            }
        }

        if (!$this->isTimeoutExpired($zone)) {
            $this->info = null;
            $this->response = new TldResponse([
                'domain' => $domainName,
                'host' => '',
                'query' => '',
                'text' => 'Request limit exceeded. Whois queries will be blocked until ' . $this->getTimeoutDate($zone)
            ]);
            return;
        }

        list($response, $info) = $this->whois->getTldModule()->loadDomainData($domainName, $servers);
        $this->response = $response;
        if (strcmp($zone, ".pl") === 0 && $info) {
            $parser = new TldPlParser();
            $this->info = $parser->parseInfo($info);
        } else {
            $this->info = $info;
        }
        if (str_contains($this->response->text, "request limit exceeded")) {
            $this->lockTldQuery($zone);
        }
    }

    public function isTimeoutExpired(string $zone): bool
    {
        $filePath = $this->path . $zone . ".timeout";
        if (!file_exists($filePath))
            return true;

        $timeout = file_get_contents($filePath);

        if ($timeout + $this->timeout < time()) {
            unlink($filePath);
            return true;
        }

        return false;
    }

    public function lockTldQuery(string $zone): void
    {
        if ($this->isTimeoutExpired($zone))
            file_put_contents($this->path . $zone . ".timeout", time());
    }

    public function getTimeoutDate(string $zone): string
    {
        $filePath = $this->path . $zone . ".timeout";
        if (!file_exists($filePath))
            return "";
        $timeout = file_get_contents($filePath);
        $date = date("Y-m-d H:i:s\Z", $timeout + $this->timeout);
        return $date;
    }
}
