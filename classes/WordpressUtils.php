<?php

class WordpressUtils
{
    private bool $xRedirectByWordpress = false;

    private bool $rss = false;

    public function __construct(Redirection $redirection, String $domain)
    {
        foreach ($redirection->getAll() as $key => $value) {
            if (isset($value->getAdditional()['x-redirect-by'])) {
                $xRedirectBy = $value->getAdditional()['x-redirect-by'];
                if (strcmp(strtolower($xRedirectBy), 'wordpress') === 0) {
                    $this->xRedirectByWordpress = true;
                }
            }
        }
        switch ($this->lookForRSS($domain)) {
            case 'HTTP/1.1 403 Forbidden':
                $this->rss = true;
                break;
            case '200':
                $this->rss = true;
                break;
            default:
                $this->rss = false;
                break;
        }
    }

    public function hasRSS(): bool
    {
        return $this->rss;
    }

    public function lookForRSS(string $domain)
    {
        $fp = fopen('https://'.$domain.'/wp-content/plugins/really-simple-ssl/', 'r');
        try {
            $headers = stream_get_meta_data($fp)['wrapper_data'];
        } catch (\TypeError $th) {
            return $http_response_header[0];
        }

        foreach ($headers as $key => $value) {

            if (str_starts_with($value, 'HTTP/1.1'))
            {
                $val = intval(substr($value, 9, 3));
                // if ($val)
                return $val;
            }

        }

        return 0;
    }

    public function getCurlHeaders(string $url): bool
    {
        $ch = curl_init();
        $headers = array(
            'Accept: text/html',
            'Content-Type: text/html'
        );
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (platform; rv:geckoversion) Gecko/geckotrail Firefox/firefoxversion");
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);

        // Then, after your curl_exec call:
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
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

        curl_close($ch);

        $code = str_replace('HTTP/1.0 ', '', $nheader['http_code']);
        $code = str_replace('HTTP/1.1 ', '', $code);
        $code = str_replace('HTTP/2 ', '', $code);
        $code = str_replace('HTTP/3 ', '', $code);
        $code = intval(substr($code, 0, 3));
        var_dump($nheader);

        if ($code === 200 || $code === 403) {
            return true;
        }


        return false;
    }

	/**
	 * @return bool
	 */
	public function getXRedirectByWordpress(): bool {
		return $this->xRedirectByWordpress;
	}

    /**
	 * @return bool
	 */
    public function isWordpress(): bool {
        if ($this->getXRedirectByWordpress())
            return true;
        return false;
    }
}
