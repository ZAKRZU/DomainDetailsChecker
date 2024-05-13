<?php

namespace Zakrzu\DDC\Component;

class WordpressComponent
{
    private bool $xRedirectByWordpress = false;

    private bool $linkToWpRest = false;

    private bool $rss = false;

    public function __construct(DomainRedirect $redirection, String $domain)
    {
        foreach ($redirection->getAllHeaders() as $value) {
            if (isset($value->getAdditionalHeaders()['x-redirect-by'])) {
                $xRedirectBy = $value->getAdditionalHeaders()['x-redirect-by'];
                if (strcmp(strtolower($xRedirectBy), 'wordpress') === 0) {
                    $this->xRedirectByWordpress = true;
                }
            }
            if (isset($value->getAdditionalHeaders()['link'])) {
                $link = $value->getAdditionalHeaders()['link'];
                foreach ($link as $linkValue) {
                    if (str_contains($linkValue, 'api.w.org')) {
                        $this->linkToWpRest = true;
                    }
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

    public function lookForRSS(string $domain) // TODO: Move to curl
    {
        set_error_handler(function () { /* ignore errrors by now */
        });
        $fp = fopen('https://' . $domain . '/wp-content/plugins/really-simple-ssl/', 'r');
        restore_error_handler();
        try {
            $headers = stream_get_meta_data($fp)['wrapper_data'];
        } catch (\TypeError $th) {
            if (isset($http_response_header))
                return $http_response_header[0];
            else
                return "";
        }

        foreach ($headers as $value) {

            if (str_starts_with($value, 'HTTP/1.1')) {
                $val = intval(substr($value, 9, 3));
                return $val;
            }
        }

        return 0;
    }

    /**
     * @return bool
     */
    public function getXRedirectByWordpress(): bool
    {
        return $this->xRedirectByWordpress;
    }

    public function hasLinkToWpRest(): bool
    {
        return $this->linkToWpRest;
    }

    /**
     * @return bool
     */
    public function isWordpress(): bool
    {
        if ($this->getXRedirectByWordpress() || $this->hasLinkToWpRest())
            return true;
        return false;
    }
}
