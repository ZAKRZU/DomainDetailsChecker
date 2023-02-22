<?php

class Redirection
{

    private $ch;

    private array $redirects = [];

    private int $size = 0;

    private bool $redirectAbroad = false;

    private bool $https = false;

    public function __construct(private Domain $domain, string $path = "")
    {
        $this->ch = curl_init();
        $location = 'http://' . $domain->getHostname() . $path;
        while ($location != null) {
            $redirect = $this->getCurlHeaders($location);
            if (strcmp($redirect->getLocation(), '') !== 0) {
                $this->redirects[] = $redirect;
                $this->size++;
            }
            if (strcmp($redirect->getLocation(), '') !== 0)
                $location = $redirect->getLocation();
            else
                $location = null;
        }

        curl_close($this->ch);

        $this->checkHttps();
        $this->checkRedirects($domain->getHostname());
    }

    public function getAll(): array
    {
        return $this->redirects;
    }

    public function getFirst(): Redirect
    {
        return $this->redirects[0];
    }

    public function getLast(): Redirect|null
    {
        if ($this->getSize() === 0)
            return null;
        return $this->redirects[$this->getSize()-1];
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function hasHTTPS(): bool
    {
        return $this->https;
    }

    public function redirectsToAnotherSite(): bool
    {
        return $this->redirectAbroad;
    }

    private function checkHttps(): void
    {
        if (!$this->getLast()) {
            $this->https = false;
            return;
        }
        $this->https = $this->getLast()->getHasHTTPS();
    }

    private function checkRedirects(string $domain): void
    {
        $domain = str_replace('www.', '', $domain);
        foreach ($this->redirects as $value) {
            if ($value->getAbroad())
                $this->redirectAbroad = true;
        }
    }

    public function getCurlHeaders(string $url): Redirect
    {
        $domain = str_replace('www.', '', $this->domain->getHostname());
        $red = new Redirect($domain);
        $red->setFrom($url);
        //$this->ch = curl_init();
        $headers = array(
            'Accept: text/html',
            'Content-Type: text/html'
        );
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (platform; rv:geckoversion) Gecko/geckotrail Firefox/firefoxversion");
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($this->ch);

        // Then, after your curl_exec call:
        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $nheader = [];
        // $body = substr($response, $header_size);

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

        if ($red->getCode() === 0)
            $red->setCode(intval($code));

        if (isset($nheader['location'])) {
            $red->setLocation($nheader['location']);
        }

        if (isset($nheader['x-redirect-by'])) {
            $red->addAdditional('x-redirect-by', $nheader['x-redirect-by']);
        }


        return $red;
    }
}

class Redirect
{
    private string $originalLocation = '';

    private int $code = 0;

    private string $from = '';

    private string $location = '';

    private array $additional = [];

    private bool $abroad = false;

    private bool $hasHTTPS = false;
    public function __construct(string $orgLoc)
    {
        $domain = str_replace('www.', '', $orgLoc);
        $this->originalLocation = str_replace('/random/url', '', $orgLoc);
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code 
     * @return self
     */
    public function setCode(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from 
     * @return self
     */
    public function setFrom(string $from): self
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location 
     * @return self
     */
    public function setLocation(string $location): self
    {
        $this->location = $location;
        if (strcmp(parse_url(str_replace('www.', '', $location), PHP_URL_HOST), $this->originalLocation) === 0)
            $this->setAbroad(false);
        else
            $this->setAbroad(true);

        if (strcmp(parse_url($location, PHP_URL_SCHEME), 'https') === 0)
            $this->setHasHTTPS(true);
        else
            $this->setHasHTTPS(false);
        return $this;
    }

    /**
     * @return array
     */
    public function getAdditional(): array
    {
        return $this->additional;
    }

    /**
     * @param array $additional 
     * @return self
     */
    public function setAdditional(array $additional): self
    {
        $this->additional = $additional;
        return $this;
    }

    /**
     * @param array $additional 
     * @return self
     */
    public function addAdditional(string $name, string $value): self
    {
        $this->additional[$name] = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAbroad(): bool
    {
        return $this->abroad;
    }

    /**
     * @param bool $abroad 
     * @return self
     */
    public function setAbroad(bool $abroad): self
    {
        $this->abroad = $abroad;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHasHTTPS(): bool
    {
        return $this->hasHTTPS;
    }

    /**
     * @param bool $hasHTTPS 
     * @return self
     */
    public function setHasHTTPS(bool $hasHTTPS): self
    {
        $this->hasHTTPS = $hasHTTPS;
        return $this;
    }
}
