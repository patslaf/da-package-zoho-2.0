<?php

namespace Patslaf\DigitalAcorn\Zoho20\Api;

class ApiConfig extends Base
{
    protected $username;

    protected $clientId;

    protected $secret;

    protected $refreshToken;

    protected $redirectUrl;

    public function __construct(string $username, string $clientId, string $secret, string $refreshToken, string $redirectUrl)
    {
        $this->username = $username;
        $this->clientId = $clientId;
        $this->secret = $secret;
        $this->refreshToken = $refreshToken;
        $this->redirectUrl = $redirectUrl;
    }
}
