<?php

namespace App\Component;

use App\Manager\RedirectManager;

class DomainRedirect
{

    private \CurlHandle $curl;

    private ?string $lastLocation;

    private array $redirects;

    public function __construct(private DomainComponent $domain, private string $path = '')
    {
        $this->curl = RedirectManager::getCurlHandle();
        $this->lastLocation = 'http://' . $domain->getDomain() . $path;
        $this->redirects = [];

        while ($this->lastLocation) {
            $redirect = $this->loadRedirect();
            $this->lastLocation = $redirect->getRedirectedTo();
            if (strcmp($this->lastLocation, '') === 0) {
                $this->lastLocation = null;
                break;
            }
            $this->redirects[count($this->redirects)] = $redirect;
        }
    }

    public function hasHTTPS(): bool
    {
        return $this->getLastRedirect()->hasHTTPS();
    }

    public function isAbroad(): bool
    {
        return $this->getLastRedirect()->isAbroad();
    }

    public function getRedirects(): array
    {
        return $this->redirects;
    }

    public function getLastRedirect(): Redirect
    {
        return $this->redirects[count($this->redirects) - 1];
    }

    public function getDomain(): DomainComponent
    {
        return $this->domain;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function loadRedirect(): Redirect
    {
        $redirect = new Redirect($this->lastLocation);
        $headers = array(
            'Accept: text/html',
            'Content-Type: text/html'
        );
        curl_setopt($this->curl, CURLOPT_URL, $this->lastLocation);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_HEADER, 1);
        curl_setopt($this->curl, CURLOPT_USERAGENT, "Mozilla/5.0 (platform; rv:geckoversion) Gecko/geckotrail Firefox/firefoxversion");
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($this->curl);

        // Then, after your curl_exec call:
        $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $nheader = [];

        foreach (explode("\r\n", $header) as $i => $line)
            if ($i === 0)
                $nheader['http_code'] = $line;
            else {
                if (strlen($line) < 1)
                    continue;
                list($key, $value) = explode(': ', $line);

                $nheader[strtolower($key)] = $value;
            }

        $code = str_replace('HTTP/1.0 ', '', $nheader['http_code']);
        $code = str_replace('HTTP/1.1 ', '', $code);
        $code = str_replace('HTTP/2 ', '', $code);
        $code = str_replace('HTTP/3 ', '', $code);
        $code = substr($code, 0, 3);

        if ($redirect->getRequestCode() === 0)
            $redirect->setRequestCode(intval($code));

        if (isset($nheader['location'])) {
            $redirect->setRedirectedTo($nheader['location']);
        }

        if (isset($nheader['x-redirect-by'])) {
            $redirect->addAdditionalHeader('x-redirect-by', $nheader['x-redirect-by']);
        }
        return $redirect;
    }
}
