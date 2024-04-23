<?php
namespace Zakrzu\DDC\Component;

class SSLComponent
{
    private string $cn;
    private string $issuer;
    private string $validFrom;
    private string $validTo;

    public function __construct(string $domain)
    {
        //$orignal_parse = parse_url('https://' . $domain, PHP_URL_HOST);
        $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        $read = stream_socket_client("ssl://" . $domain . ":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);

        if ($read == false) {
            $this->cn = "SSL NOT FOUND OR NOT INVALID";
            $this->issuer = "SSL NOT FOUND OR NOT INVALID";
            $this->validFrom = "";
            $this->validTo = "";
            return;
        }
        $cert = stream_context_get_params($read);
        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

        $this->cn = $certinfo['subject']['CN'];
        $this->issuer = $certinfo['issuer']['C'] . ' ' . $certinfo['issuer']['O'] . ' ' . $certinfo['issuer']['CN'];
        $this->validFrom = gmdate("Y-m-d\TH:i:s\Z", intval($certinfo['validFrom_time_t']));
        $this->validTo = gmdate("Y-m-d\TH:i:s\Z", intval($certinfo['validTo_time_t']));
    }

    public function getCN(): string
    {
        return $this->cn;
    }

    public function getIssuer(): string
    {
        return $this->issuer;
    }

    public function getValidFrom(): string
    {
        return $this->validFrom;
    }

    public function getValidTo(): string
    {
        return $this->validTo;
    }

    public function getDays(): string
    {
        $to = new \DateTime($this->getValidTo());
        $today = new \DateTime();
        $interval = $today->diff($to);
        return $interval->format('%R%a days');
    }

    public function getDaysNumber(): int
    {
        $to = new \DateTime($this->getValidTo());
        $today = new \DateTime();
        $interval = $today->diff($to);
        return intval($interval->format('%R%a'));
    }

    public function issuedByLetsEncrypt(): bool
    {
        if (str_contains($this->issuer, "Let' Encrypt"))
            return true;
        if (str_contains($this->issuer, "US Let's Encrypt R3"))
            return true;
        return false;
    }
}
