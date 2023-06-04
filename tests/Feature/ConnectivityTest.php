<?php

namespace Patslaf\DigitalAcorn\Zoho20\Tests\Feature;

use Patslaf\DigitalAcorn\Core\Models\ConfigZoho;
use Patslaf\DigitalAcorn\Zoho20\Api\ApiConfig;
use Patslaf\DigitalAcorn\Zoho20\Api\Record;
use Tests\TestCase;

class ConnectivityTest extends TestCase
{
    public function __test_all_configs(): void
    {
        // // get all connections
        // $configs = ConfigZoho::where('enabled', true)->get()->toArray();

        // foreach ($configs as $config) {
        //     $configZoho = new ApiConfig($config['username'], $config['client_id'], $config['client_secret'], $config['refresh_token'], "/");
        //     $service = new Record($configZoho);
        //     $recordId = '5658085000001296001';

        //     try {
        //         $response = $service->getRecord('Contacts', $recordId)->getKeyValues();
        //         print "SUCESSSS ";
        //         $this->assertIsArray($response);
        //     }

        //     catch(Exception $e) {
        //         $this->assertTrue(false);
        //     }

        // }
    }

    public function test_connection_to_cfx(): void
    {
        $config = ConfigZoho::where('code', 'credit-x')->first()->toArray();

        $configZoho = new ApiConfig($config['username'], $config['client_id'], $config['client_secret'], $config['refresh_token'], '/');
        $service = new Record($configZoho);
        $recordId = '5658085000001296001';
        $response = $service->getRecord('Contacts', $recordId)->getKeyValues();
        $this->assertIsArray($response);
    }

    public function test_connection_to_fwt(): void
    {
        $config = ConfigZoho::where('code', 'finwell')->first()->toArray();

        $configZoho = new ApiConfig($config['username'], $config['client_id'], $config['client_secret'], $config['refresh_token'], '/');
        $service = new Record($configZoho);
        $recordId = '4220412000103233047';
        $response = $service->getRecord('Leads', $recordId)->getKeyValues();
        $this->assertIsArray($response);
    }

    public function test_connection_to_madison(): void
    {
        $config = ConfigZoho::where('code', 'madison')->first()->toArray();

        $configZoho = new ApiConfig($config['username'], $config['client_id'], $config['client_secret'], $config['refresh_token'], '/');
        $service = new Record($configZoho);
        $recordId = '4997805000056734010';
        $response = $service->getRecord('Contacts', $recordId)->getKeyValues();
        $this->assertIsArray($response);
    }
}
