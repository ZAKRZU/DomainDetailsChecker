<?php

namespace Zakrzu\DDC\Component;

class Redirect
{

    private string $redirectedTo;

    private int $requestCode;

    private array $additionalHeaders;

    public function __construct(private string $from)
    {
        $this->redirectedTo = '';
        $this->requestCode = 0;
        $this->additionalHeaders = [];
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setRequestCode(int $code): void
    {
        $this->requestCode = $code;
    }

    public function getRequestCode(): int
    {
        return $this->requestCode;
    }

    public function setRedirectedTo(string $redirectedTo): void
    {
        $this->redirectedTo = $redirectedTo;
    }

    public function getRedirectedTo(): string
    {
        return $this->redirectedTo;
    }

    public function getAdditionalHeaders(): array
    {
        return $this->additionalHeaders;
    }

    public function addAdditionalHeader(string $name, string $value): void
    {
        $this->additionalHeaders[$name] = $value;
    }

    public function addAdditionalHeaderList(string $name, array $value): void
    {
        $this->additionalHeaders[$name] = $value;
    }

    public function hasHTTPS(): bool
    {
        $parsed = parse_url($this->redirectedTo, PHP_URL_SCHEME);
        if (!$parsed)
            return false;
        if (strcmp($parsed, 'https') === 0)
            return true;
        else
            return false;
    }

    public function isAbroad(): bool
    {
        $parseDomain = parse_url($this->from, PHP_URL_HOST);
        $parseRedirected = parse_url($this->redirectedTo, PHP_URL_HOST);

        if (!$parseDomain)
            $parseDomain = $this->from;

        if (!$parseRedirected)
            $parseRedirected = $this->redirectedTo;

        $pureDomain = str_replace('www.', '', $parseDomain);
        $pureRedirected = str_replace('www.', '', $parseRedirected);

        if (strcmp($pureDomain, $pureRedirected) === 0)
            return false;
        else
            return true;
    }
}
