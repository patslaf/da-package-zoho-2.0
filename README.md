# Digital Acorn: Zoho (SDK 2.0)

## Install 

### Set your repository

#### From Localhost
```
    "repositories": [
        {
            "type": "path",
            "url": "../da-package-zoho-2.0"
        }
    ],
```

#### From Github
```
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/patslaf/da-package-zoho-2.0"
        }
    ],
```

### Install package

#### Pull package
```
composer require patslaf/da-package-zoho-2.0
```

#### Run migrations
```
php artisan migrate
```

## Testing
Add this to your Laravel application (phpunit.xml)
```
         <testsuite name="Feature_da-package-zoho-2.0">
            <directory suffix="Test.php">./vendor/patslaf/da-package-zoho-2.0/tests/Feature</directory>
        </testsuite>
```

Then run:
```
php artisan test --filter 'Patslaf\\DigitalAcorn\\Zoho20'
```

## Examples

## Record
```
$config = \Patslaf\DigitalAcorn\Core\Models\ConfigZoho::where('code', 'credit-x')->first()->toArray();
$apiConfig = new \Patslaf\DigitalAcorn\Zoho20\Api\ApiConfig($config['username'], $config['client_id'], $config['client_secret'], $config['refresh_token'], '/');
$service = new \Patslaf\DigitalAcorn\Zoho20\Api\Record($apiConfig);
$recordId = '5658085000001296001';
$response = $service->getRecord('Contacts', $recordId)->getKeyValues();
```

## Note
```
$config = \Patslaf\DigitalAcorn\Core\Models\ConfigZoho::where('code', 'credit-x')->first()->toArray();
$apiConfig = new \Patslaf\DigitalAcorn\Zoho20\Api\ApiConfig($config['username'], $config['client_id'], $config['client_secret'], $config['refresh_token'], '/');
$zohoNoteService = new \Patslaf\DigitalAcorn\Zoho20\Note($apiConfig);
$note = $zohoNoteService->createNote('4220412000066580179', 'Leads', 'CONTENT', 'TITLE');
```