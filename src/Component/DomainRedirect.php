<?php

namespace Zakrzu\DDC\Component;

use Zakrzu\DDC\Manager\RedirectManager;

class DomainRedirect
{
    const MAX_SAME_REDIRECTS = 9;

    const MAX_REDIRECT_DEPTH = 50;

    private \CurlHandle $curl;

    private ?string $lastLocation;

    private array $redirects;

    private array $allHeaders;

    private int $errorNumber = 0;

    private string $errorString = "";

    public function __construct(private ?DomainInfo $domain, private string $path = '')
    {
        $this->curl = RedirectManager::getCurlHandle();

        if ($domain !== null)
            $this->lastLocation = 'http://' . $domain->getDomainName() . $path;
        else {
            $this->lastLocation = null;
        }

        $this->redirects = [];
        $this->allHeaders = [];

        $antiLoop = 0;
        $redirectsDepth = 0;

        while ($this->lastLocation) {
            if (
                $antiLoop > DomainRedirect::MAX_SAME_REDIRECTS
                || $redirectsDepth > DomainRedirect::MAX_REDIRECT_DEPTH
            ) {
                $redirect = new Redirect($this->lastLocation);
                $redirect->setRedirectedTo("INFINITE REDIRECT LOOP! (or too many redirects)");
                $this->redirects[] = $redirect;
                break;
            }
            $redirectsDepth++;

            $redirect = $this->loadRedirect();
            if (
                strcmp($this->lastLocation, $redirect->getRedirectedTo()) === 0
                || $this->redirectLoopDetection($redirect->getRedirectedTo())
            )
                $antiLoop++;

            if (!parse_url($redirect->getRedirectedTo(), PHP_URL_HOST) && $redirect->getRedirectedTo()) {
                if (str_starts_with($redirect->getRedirectedTo(), "/")) {
                    $scheme = parse_url($redirect->getFrom(), PHP_URL_SCHEME);
                    $host = parse_url($redirect->getFrom(), PHP_URL_HOST);
                    $url = $scheme . "://" . $host . $redirect->getRedirectedTo();
                } else {
                    $url = $redirect->getFrom() . $redirect->getRedirectedTo();
                }
                $redirect->setRedirectedTo($url);
            }

            $this->lastLocation = $redirect->getRedirectedTo();
            array_push($this->allHeaders, $redirect);

            if (strcmp($this->lastLocation, '') === 0) {
                $this->lastLocation = null;
                break;
            }

            $this->redirects[] = $redirect;
        }
    }

    public function redirectLoopDetection(string $url): bool
    {
        foreach ($this->redirects as $redirect) {
            if (strcmp($redirect->getRedirectedTo(), $url) === 0)
                return true;
        }
        return false;
    }

    public function hasHTTPS(): bool
    {
        return $this->getLastRedirect()->hasHTTPS();
    }

    public function isAbroad(): bool
    {
        $pureDomain = str_replace('www.', '', $this->domain->getDomainName());
        $parseRedirected = parse_url($this->getLastRedirect()->getRedirectedTo(), PHP_URL_HOST);
        if ($parseRedirected === null)
            $parseRedirected = $this->getLastRedirect()->getRedirectedTo();

        $pureRedirected = str_replace('www.', '', $parseRedirected);
        if (strcmp($pureDomain, $pureRedirected) === 0)
            return false;
        else
            return true;
    }

    public function getRedirects(): array
    {
        return $this->redirects;
    }

    public function getAllHeaders(): array
    {
        return $this->allHeaders;
    }

    public function getLastRedirect(): Redirect
    {
        return $this->redirects[count($this->redirects) - 1];
    }

    public function getDomain(): DomainInfo
    {
        return $this->domain;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getErrorCode(): int
    {
        return $this->errorNumber;
    }

    public function getErrorMessage(): string
    {
        return $this->errorString;
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
        curl_setopt($this->curl, CURLOPT_NOBODY, 1);
        curl_setopt($this->curl, CURLOPT_HEADER, 1);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:126.0) Gecko/20100101 Firefox/126.0");
        // Curl timeout is now controlled by RedirectManager

        $response = curl_exec($this->curl);

        $errno = curl_errno($this->curl);

        if ($errno > 0) {
            $this->errorNumber = $errno;
            $this->errorString = curl_error($this->curl);
        }

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

                if (strcmp(strtolower($key), 'link') === 0) {
                    if (!isset($nheader['link'])) {
                        $nheader['link'] = [];
                    }
                    array_push($nheader['link'], $value);
                } else {
                    $nheader[strtolower($key)] = $value;
                }
            }

        $code = str_replace('HTTP/1.0 ', '', $nheader['http_code']);
        $code = str_replace('HTTP/1.1 ', '', $code);
        $code = str_replace('HTTP/2 ', '', $code);
        $code = str_replace('HTTP/3 ', '', $code);
        $code = substr($code, 0, 3);

        if ($redirect->getResponseCode() === 0)
            $redirect->setResponseCode(intval($code));

        if (
            isset($nheader['location'])
            && ($code == 301 || $code == 302 || $code == 303 || $code == 307 || $code == 308)
        ) {
            $redirect->setRedirectedTo($nheader['location']);
        }

        if (isset($nheader['x-redirect-by'])) {
            $redirect->addAdditionalHeader('x-redirect-by', $nheader['x-redirect-by']);
        }

        if (isset($nheader['link']) && is_array($nheader['link'])) {
            $redirect->addAdditionalHeaderList('link', $nheader['link']);
        }

        return $redirect;
    }
}
