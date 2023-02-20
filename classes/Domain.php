<?php

require_once('Redirection.php');

class Domain
{
    private string $hostname = '';

    private string $ip = '';

    private string $revDNS = '';

    private bool $valid = true;

    private Redirection|null $redirects = null;

    private Redirection|null $redirectsWithUrl = null;

    public function __construct(string $domain)
    {
        $this->hostname = $domain;

        if (dns_get_record($domain, DNS_A)) {
            $this->ip = dns_get_record($domain, DNS_A)[0]['ip'];
            $this->revDNS = gethostbyaddr($this->ip);
            $this->redirects = new Redirection($this);
            $this->redirectsWithUrl = new Redirection($this, "/random/url");
        } else {
            $this->ip = 'Not found';
            $this->valid = false;
        }
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function getIP(): string
    {
        return $this->ip;
    }

    public function getReverseDNS(): string
    {
        return $this->revDNS;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getRedirects(): Redirection|null
    {
        return $this->redirects;
    }
    public function getRedirectsWithUrl(): Redirection|null
    {
        return $this->redirectsWithUrl;
    }
}
