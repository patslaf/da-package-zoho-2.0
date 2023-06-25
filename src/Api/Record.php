<?php

namespace Patslaf\DigitalAcorn\Zoho20\Api;

use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\APIException;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\CarryOverTags;
use com\zoho\crm\api\record\Contacts;
use com\zoho\crm\api\record\ConvertActionHandler;
use com\zoho\crm\api\record\ConvertActionWrapper;
use com\zoho\crm\api\record\ConvertBodyWrapper;
use com\zoho\crm\api\record\DeleteRecordParam;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\record\LeadConverter;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\ResponseWrapper;
use com\zoho\crm\api\record\SearchRecordsParam;
use com\zoho\crm\api\record\SuccessfulConvert;
use com\zoho\crm\api\record\SuccessResponse;
use com\zoho\crm\api\util\CommonAPIHandler;
use com\zoho\crm\api\util\Constants;
use Patslaf\DigitalAcorn\Zoho20\Exceptions\NoApiResponseException;
use Patslaf\DigitalAcorn\Zoho20\Exceptions\NotApiExpectedResponseException;
use Patslaf\DigitalAcorn\Zoho20\Exceptions\ParsedApiException;
use Patslaf\DigitalAcorn\Zoho20\Exceptions\RecordDuplicateException;
use Patslaf\DigitalAcorn\Zoho20\Exceptions\RecordNotFoundException;

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
            throw new NoApiResponseException('Unable to retrieve response: '.$recordId);
        }

        if (! $response->isExpected()) {
            throw new RecordNotFoundException();
        }

        $responseHandler = $response->getObject();

        if ($responseHandler instanceof APIException) {
            throw new ParsedApiException($responseHandler);
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

    public function searchRecordsByEmail(string $moduleAPIName, string $email)
    {
        $paramInstance = new ParameterMap();
        $paramInstance->add(SearchRecordsParam::email(), $email);
        $records = self::searchRecords($moduleAPIName, $paramInstance);

        return $records;
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

            if ($responseHandler instanceof APIException) {
                throw new ParsedApiException($responseHandler);
            }

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

        if ($moduleAPIName === 'Contacts') {
            $email = $recordData[Contacts::Email()->getApiName()] ?? null;

            $matchingLeads = $this->searchRecordsByEmail('Leads', $email);
            if ($matchingLeads) {
                $recordId = $matchingLeads[0]->getId();
                $contactId = $this->convertLead($recordId);

                // update contact
                $this->updateRecord('Contacts', $contactId, $recordData);

                return $contactId;
            }
        }

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
            throw new NoApiResponseException('Unable to retrieve response');
        }

        if (! $response->isExpected()) {
            throw new NotApiExpectedResponseException();
        }

        //Get object from response
        $actionHandler = $response->getObject();

        //Check if the request returned an exception
        if ($actionHandler instanceof APIException) {
            throw new ParsedApiException($actionHandler);
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
                    $code = $actionResponse->getCode()->getValue();
                    $details = $actionResponse->getDetails();

                    if ($code === 'DUPLICATE_DATA') {
                        throw new RecordDuplicateException($details['id']);
                    }

                    throw new ParsedApiException($actionResponse);
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
            throw new NoApiResponseException('Unable to retrieve response: '.$recordId);
        }

        if (! $response->isExpected()) {
            throw new NotApiExpectedResponseException();
        }

        //Get object from response
        $actionHandler = $response->getObject();

        //Check if the request returned an exception
        if ($actionHandler instanceof APIException) {
            throw new ParsedApiException($actionHandler);
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
                    throw new ParsedApiException($actionResponse);
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
            throw new NoApiResponseException('Unable to retrieve response: '.$recordId);
        }

        if (! $response->isExpected()) {
            throw new NotApiExpectedResponseException();
        }

        //Get object from response
        $actionHandler = $response->getObject();

        //Check if the request returned an exception
        if ($actionHandler instanceof APIException) {
            throw new ParsedApiException($actionHandler);
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
                    throw new ParsedApiException($actionResponse);
                }
            }
        }
    }

    public function convertLead(string $recordId, string $convertTo = 'Contacts')
    {
        //Get instance of RecordOperations Class
        $recordOperations = new RecordOperations();
        //Get instance of ConvertBodyWrapper Class that will contain the request body
        $request = new ConvertBodyWrapper();
        //List of LeadConverter instances
        $data = [];
        //Get instance of LeadConverter Class
        $record1 = new LeadConverter();
        $record1->setOverwrite(true);

        // get tags
        $lead = $this->getRecord("Leads", $recordId);
        $tagsRaw = $lead->getKeyValues()['Tag'];
        $tags = [];
        foreach($tagsRaw as $tag) {
            $tags[] = $tag->getName();
        }

        // set carryover tags
        $carryOverTags = new CarryOverTags();
        //$carryOverTags->setAccounts(["Test"]);
        $carryOverTags->setContacts($tags);
        //$carryOverTags->setDeals(["Test"]);
        $record1->setCarryOverTags($carryOverTags);

        //Add Record instance to the list
        array_push($data, $record1);
        //Set the list to Records in BodyWrapper instance
        $request->setData($data);
        //Call updateRecord method that takes BodyWrapper instance, ModuleAPIName and recordId as parameter.

        $handlerInstance = new CommonAPIHandler();
        $apiPath = '';
        $apiPath = $apiPath.('/crm/v2/Leads/');
        $apiPath = $apiPath.(strval($recordId));
        $apiPath = $apiPath.('/actions/convert');
        $handlerInstance->setAPIPath($apiPath);
        $handlerInstance->setHttpMethod(Constants::REQUEST_METHOD_POST);
        $handlerInstance->setCategoryMethod(Constants::REQUEST_CATEGORY_CREATE);
        $handlerInstance->setContentType('application/json');
        $handlerInstance->setRequest($request);
        $handlerInstance->setMandatoryChecker(true);
        //Utility::getFields("Deals", $handlerInstance);
        $response = $handlerInstance->apiCall(ConvertActionHandler::class, 'application/json');

        if (! $response) {
            throw new NoApiResponseException('Unable to retrieve response: '.$recordId);
        }

        if (! $response->isExpected()) {
            throw new NotApiExpectedResponseException();
        }

        $actionHandler = $response->getObject();
        if ($actionHandler instanceof APIException) {
            throw new ParsedApiException($actionHandler);
        }

        if ($actionHandler instanceof ConvertActionWrapper) {
            //Get the received ActionWrapper instance
            $actionWrapper = $actionHandler;
            //Get the list of obtained ActionResponse instances
            $actionResponses = $actionWrapper->getData();
            foreach ($actionResponses as $actionResponse) {
                //Check if the request is successful
                if ($actionResponse instanceof SuccessfulConvert) {
                    $successResponse = $actionResponse;
                    $contactId = $successResponse->getContacts();

                    return $contactId;
                }
                //Check if the request returned an exception
                elseif ($actionResponse instanceof APIException) {
                    $code = $actionResponse->getCode()->getValue();
                    $details = $actionResponse->getDetails();

                    if ($code === 'DUPLICATE_DATA') {
                        throw new RecordDuplicateException($details['id']);
                    }

                    throw new ParsedApiException($actionResponse);
                }
            }
        }

        throw $actionHandler;
    }
}
