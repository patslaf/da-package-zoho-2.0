<?php

namespace Patslaf\DigitalAcorn\Zoho20\Api;

use com\zoho\api\authenticator\OAuthBuilder;
use com\zoho\api\authenticator\store\DBBuilder;
use com\zoho\api\logger\Levels;
use com\zoho\api\logger\LogBuilder;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\crm\api\InitializeBuilder;
use com\zoho\crm\api\UserSignature;
use Patslaf\DigitalAcorn\Zoho20\Models\ZohoOauthtoken;

class Base
{
    private ApiConfig $apiConfig;

    public function __construct(ApiConfig $apiConfig)
    {
        $this->apiConfig = $apiConfig;

        $this->initialize();
    }

    public function initialize()
    {

        $user = new UserSignature($this->apiConfig->username);
        $environment = USDataCenter::PRODUCTION();

        $logger = (new LogBuilder())
            ->level(Levels::ERROR)
            ->filePath(storage_path('logs/zoho.logs'))
            ->build();

        $configDatabase = (new ZohoOauthtoken())->getConnection()->getConfig();
        $configTablename = (new ZohoOauthtoken())->getTable();

        $tokenstore = (new DBBuilder())
            ->host($configDatabase['host'])
            ->databaseName($configDatabase['database'])
            ->userName($configDatabase['username'])
            ->password($configDatabase['password'])
            ->portNumber($configDatabase['port'])
            ->tableName($configTablename)
            ->build();

        $token = (new OAuthBuilder())
            ->clientId($this->apiConfig->clientId)
            ->clientSecret($this->apiConfig->secret)
            ->refreshToken($this->apiConfig->refreshToken)
            ->redirectURL($this->apiConfig->redirectUrl)
            ->build();

        (new InitializeBuilder())
            ->logger($logger)
            ->user($user)
            ->environment($environment)
            ->token($token)
            ->store($tokenstore)
            ->initialize();
    }
}
