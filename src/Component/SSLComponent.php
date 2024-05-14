<?php

namespace Zakrzu\DDC\Component;

class SSLComponent
{
    private string $cn;
    private string $issuer;
    private string $validFrom;
    private string $validTo;
    private array $subjectAltNames = [];

    public function __construct(private string $domain)
    {
        // EXPERIMENTAL
        $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE, "verify_peer" => FALSE, "verify_peer_name" => FALSE)));
        // ORIGINAL
        // $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        if (APP_ENV === "DEV") {
            $read = stream_socket_client("ssl://" . $domain . ":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
        } else {
            $read = @stream_socket_client("ssl://" . $domain . ":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
        }

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
        $subjectAltNameReplaced = str_replace('DNS:', "", $certinfo['extensions']['subjectAltName']);
        $this->subjectAltNames = explode(', ', $subjectAltNameReplaced);
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
    // EXPERIMENTAL
    public function hasValidCN(): bool
    {
        foreach ($this->subjectAltNames as $altName) {
            if (str_contains($altName, $this->domain))
                return true;
        }
        return false;
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
