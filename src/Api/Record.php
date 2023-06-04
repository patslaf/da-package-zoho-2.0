<?php

namespace Patslaf\DigitalAcorn\Zoho20\Api;

use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\APIException;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Contacts;
use com\zoho\crm\api\record\DeleteRecordParam;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\record\Leads;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\ResponseWrapper;
use com\zoho\crm\api\record\SearchRecordsParam;
use com\zoho\crm\api\record\SuccessResponse;
use Exception;
use Patslaf\DigitalAcorn\Zoho20\Exceptions\RecordDuplicateException;

class Record extends Base
{
    public function __construct(ApiConfig $apiConfig)
    {
        parent::__construct($apiConfig);
    }

    public function getRecord(string $moduleAPIName, string $recordId)
    {
        $recordOperations = new RecordOperations();
        $paramInstance = new ParameterMap();
        $headerInstance = new HeaderMap();
        $response = $recordOperations->getRecord($recordId, $moduleAPIName, $paramInstance, $headerInstance);
        if (! $response) {
            throw new \Exception('Unable to retrieve response: '.$recordId);
        }

        if (! $response->isExpected()) {
            throw new \Exception('Not the expected response');
        }

        $responseHandler = $response->getObject();

        if ($responseHandler instanceof APIException) {
            $exception = $responseHandler;
            throw new \Exception($exception->getCode()->getValue().' '.$exception->getMessage()->getValue());
        }

        if (! $responseHandler instanceof ResponseWrapper) {
            throw new \Exception('Not the expected response handler');
        }

        $responseWrapper = $responseHandler;
        $records = $responseWrapper->getData();

        if (! $records) {
            throw new \Exception('Unable to extract record');
        }

        if (count($records) !== 1) {
            throw new \Exception('Several records returned...');
        }

        return $records[0];
    }

    public function searchRecordsByPhone(string $moduleAPIName, string $telephone)
    {
        $paramInstance = new ParameterMap();
        $paramInstance->add(SearchRecordsParam::phone(), $telephone);
        $records = self::searchRecords($moduleAPIName, $paramInstance);

        if (! $records) {
            throw new \Exception(sprintf('Unable to find %s in %s', $moduleAPIName, $telephone));
        }

        return $records;
    }

    public function searchRecords(string $moduleAPIName, ParameterMap $paramInstance = null)
    {
        $recordOperations = new RecordOperations();
        if (! $paramInstance) {
            $paramInstance = new ParameterMap();
        }
        $headerInstance = new HeaderMap();
        $response = $recordOperations->searchRecords($moduleAPIName, $paramInstance, $headerInstance);

        if ($response != null) {
            //Get object from response
            $responseHandler = $response->getObject();

            if ($responseHandler instanceof ResponseWrapper) {
                //Get the received ResponseWrapper instance
                $responseWrapper = $responseHandler;

                //Get the list of obtained Record instances
                $records = $responseWrapper->getData();

                return $records;
            }
        }
    }

    public function createRecord(string $moduleAPIName, $recordData)
    {
        $recordOperations = new RecordOperations();
        $bodyWrapper = new BodyWrapper();
        $records = [];
        $recordClass = 'com\zoho\crm\api\record\Record';
        $record1 = new $recordClass();

        foreach ($recordData as $key => $value) {
            $record1->addFieldValue(new Field($key), $value);
        }

        array_push($records, $record1);
        $bodyWrapper->setData($records);
        $headerInstance = new HeaderMap();

        $response = $recordOperations->createRecords($moduleAPIName, $bodyWrapper, $headerInstance);
        if (! $response) {
            throw new \Exception('Unable to retrieve response');
        }

        if (! $response->isExpected()) {
            throw new \Exception('Not the expected response');
        }

        //Get object from response
        $actionHandler = $response->getObject();

        //Check if the request returned an exception
        if ($actionHandler instanceof APIException) {
            //Get the received APIException instance
            $exception = $actionHandler;
            //$status =  $exception->getStatus()->getValue();
            $code = $exception->getCode()->getValue();
            $details = $exception->getDetails();
            $message = $exception->getMessage()->getValue();

            throw new \Exception(sprintf('Code: %s Message: %s', $code, $message));
        }

        if ($actionHandler instanceof ActionWrapper) {
            //Get the received ActionWrapper instance
            $actionWrapper = $actionHandler;
            //Get the list of obtained ActionResponse instances
            $actionResponses = $actionWrapper->getData();
            foreach ($actionResponses as $actionResponse) {
                //Check if the request is successful
                if ($actionResponse instanceof SuccessResponse) {
                    $successResponse = $actionResponse;
                    // $status =  $successResponse->getStatus()->getValue();
                    // $code = $successResponse->getCode()->getValue();
                    $details = $successResponse->getDetails();
                    // $message = $successResponse->getMessage()->getValue();
                    return $details['id'];
                }
                //Check if the request returned an exception
                elseif ($actionResponse instanceof APIException) {
                    //Get the received APIException instance
                    $exception = $actionResponse;
                    //$status =  $exception->getStatus()->getValue();
                    $code = $exception->getCode()->getValue();
                    $details = $exception->getDetails();
                    $message = $exception->getMessage()->getValue();

                    if ($code === 'DUPLICATE_DATA') {
                        throw new RecordDuplicateException($details['id']);
                    }

                    throw new \Exception(sprintf('Code: %s Message: %s', $code, $message));
                }
            }
        }
    }

    public function updateRecord(string $moduleAPIName, $recordId, $recordData)
    {
        $recordOperations = new RecordOperations();
        $bodyWrapper = new BodyWrapper();
        $records = [];
        $recordClass = 'com\zoho\crm\api\record\Record';
        $record1 = new $recordClass();
        $record1->setId($recordId);

        foreach ($recordData as $key => $value) {
            $record1->addFieldValue(new Field($key), $value);
        }

        array_push($records, $record1);
        $bodyWrapper->setData($records);
        $headerInstance = new HeaderMap();

        $response = $recordOperations->updateRecords($moduleAPIName, $bodyWrapper, $headerInstance);
        if (! $response) {
            throw new \Exception('Unable to retrieve response');
        }

        if (! $response->isExpected()) {
            throw new \Exception('Not the expected response');
        }

        //Get object from response
        $actionHandler = $response->getObject();

        //Check if the request returned an exception
        if ($actionHandler instanceof APIException) {
            //Get the received APIException instance
            $exception = $actionHandler;
            //$status =  $exception->getStatus()->getValue();
            $code = $exception->getCode()->getValue();
            $details = $exception->getDetails();
            $message = $exception->getMessage()->getValue();

            throw new \Exception(sprintf('Code: %s Message: %s', $code, $message));
        }

        if ($actionHandler instanceof ActionWrapper) {
            //Get the received ActionWrapper instance
            $actionWrapper = $actionHandler;
            //Get the list of obtained ActionResponse instances
            $actionResponses = $actionWrapper->getData();
            foreach ($actionResponses as $actionResponse) {
                //Check if the request is successful
                if ($actionResponse instanceof SuccessResponse) {
                    $successResponse = $actionResponse;
                    // $status =  $successResponse->getStatus()->getValue();
                    // $code = $successResponse->getCode()->getValue();
                    $details = $successResponse->getDetails();
                    // $message = $successResponse->getMessage()->getValue();
                    return $details['id'];
                }
                //Check if the request returned an exception
                elseif ($actionResponse instanceof APIException) {
                    //Get the received APIException instance
                    $exception = $actionResponse;
                    //$status =  $exception->getStatus()->getValue();
                    $code = $exception->getCode()->getValue();
                    $details = $exception->getDetails();
                    $message = $exception->getMessage()->getValue();

                    if ($code === 'DUPLICATE_DATA') {
                        return $details['id'];
                    }

                    throw new \Exception(sprintf('Code: %s Message: %s', $code, $message));
                }
            }
        }
    }

    public function deleteRecord(string $moduleAPIName, $recordId)
    {
        //Get instance of RecordOperations Class
        $recordOperations = new RecordOperations();
        //Get instance of ParameterMap Class
        $paramInstance = new ParameterMap();
        $paramInstance->add(DeleteRecordParam::wfTrigger(), false);
        $headerInstance = new HeaderMap();
        //Call deleteRecord method that takes paramInstance, ModuleAPIName and recordId as parameter.
        $response = $recordOperations->deleteRecord($recordId, $moduleAPIName, $paramInstance, $headerInstance);

        if (! $response) {
            throw new \Exception('Unable to retrieve response');
        }

        if (! $response->isExpected()) {
            throw new \Exception('Not the expected response');
        }

        //Get object from response
        $actionHandler = $response->getObject();

        //Check if the request returned an exception
        if ($actionHandler instanceof APIException) {
            //Get the received APIException instance
            $exception = $actionHandler;
            //$status =  $exception->getStatus()->getValue();
            $code = $exception->getCode()->getValue();
            $details = $exception->getDetails();
            $message = $exception->getMessage()->getValue();

            throw new \Exception(sprintf('Code: %s Message: %s', $code, $message));
        }

        if ($actionHandler instanceof ActionWrapper) {
            //Get the received ActionWrapper instance
            $actionWrapper = $actionHandler;
            //Get the list of obtained ActionResponse instances
            $actionResponses = $actionWrapper->getData();
            foreach ($actionResponses as $actionResponse) {
                //Check if the request is successful
                if ($actionResponse instanceof SuccessResponse) {
                    $successResponse = $actionResponse;
                    // $status =  $successResponse->getStatus()->getValue();
                    // $code = $successResponse->getCode()->getValue();
                    $details = $successResponse->getDetails();
                    // $message = $successResponse->getMessage()->getValue();
                    return true;
                }
                //Check if the request returned an exception
                elseif ($actionResponse instanceof APIException) {
                    //Get the received APIException instance
                    $exception = $actionResponse;
                    //$status =  $exception->getStatus()->getValue();
                    $code = $exception->getCode()->getValue();
                    $details = $exception->getDetails();
                    $message = $exception->getMessage()->getValue();

                    throw new \Exception(sprintf('Code: %s Message: %s', $code, $message));
                }
            }
        }
    }

    public function convertLead(string $recordId, string $convertTo = 'Contacts')
    {
        // get lead
        $lead = $this->getRecord('Leads', $recordId);
        if (! $lead) {
            throw new Exception('Unable to find a lead for #'.$recordId);
        }

        // save to contact
        $data = [
            Contacts::FirstName()->getApiName() => $lead->getKeyValue(Leads::FirstName()->getApiName()),
            Contacts::LastName()->getApiName() => $lead->getKeyValue(Leads::LastName()->getApiName()),
            Contacts::Phone()->getApiName() => $lead->getKeyValue(Leads::Phone()->getApiName()),
            Contacts::Email()->getApiName() => $lead->getKeyValue(Leads::Email()->getApiName()),
            Contacts::MailingStreet()->getApiName() => $lead->getKeyValue(Leads::Street()->getApiName()),
            Contacts::MailingCity()->getApiName() => $lead->getKeyValue(Leads::City()->getApiName()),
            Contacts::MailingState()->getApiName() => $lead->getKeyValue(Leads::State()->getApiName()),
            Contacts::MailingZip()->getApiName() => $lead->getKeyValue(Leads::ZipCode()->getApiName()),
        ];
        $zohoId = $this->createRecord('Contacts', $data);

        // delete contact
        return $this->deleteRecord('Leads', $recordId);
    }
}
