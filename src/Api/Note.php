<?php

namespace Patslaf\DigitalAcorn\Zoho20\Api;

use com\zoho\crm\api\notes\ActionWrapper;
use com\zoho\crm\api\notes\APIException;
use com\zoho\crm\api\notes\BodyWrapper;
use com\zoho\crm\api\notes\NotesOperations;
use com\zoho\crm\api\notes\SuccessResponse;
use com\zoho\crm\api\record\Record;

// SOURCE: https://www.zoho.com/crm/developer/docs/php-sdk/v2/notes-samples.html

class Note extends Base
{
    public function __construct(ApiConfig $apiConfig)
    {
        parent::__construct($apiConfig);
    }

    public static function createNote(string $recordId, string $recordType, string $content, string $title = null)
    {
        //Get instance of NotesOperations Class
        $notesOperations = new NotesOperations();

        //Get instance of BodyWrapper Class that will contain the request body
        $bodyWrapper = new BodyWrapper();

        //List of Note instances
        $notes = [];

        $nodeClass = 'com\zoho\crm\api\notes\Note';
        $note = new $nodeClass();

        if ($title) {
            $note->setNoteTitle($title);
        }

        $note->setNoteContent($content);

        //Get instance of Record Class
        $parentRecord = new Record();
        $parentRecord->setId($recordId);
        $note->setParentId($parentRecord);
        $note->setSeModule($recordType);

        array_push($notes, $note);

        //Set the list to notes in BodyWrapper instance
        $bodyWrapper->setData($notes);

        //Call createNotes method that takes BodyWrapper instance as parameter
        $response = $notesOperations->createNotes($bodyWrapper);

        if ($response != null) {
            //Get the status code from response
            //echo("Status Code: " . $response->getStatusCode() . "\n");

            //Get object from response
            $actionHandler = $response->getObject();

            if ($actionHandler instanceof APIException) {
                $exception = $actionHandler;
                throw new \Exception($exception->getCode()->getValue().' '.$exception->getMessage()->getValue());
            }

            if ($actionHandler instanceof ActionWrapper) {
                //Get the received ActionWrapper instance
                $actionWrapper = $actionHandler;

                //Get the list of obtained ActionResponse instances
                $actionResponses = $actionWrapper->getData();
                $actionResponse = $actionResponses[0];

                //Check if the request is successful
                if ($actionResponse instanceof SuccessResponse) {
                    //Get the received SuccessResponse instance
                    $successResponse = $actionResponse;

                    if ($successResponse->getDetails() != null) {
                        return $successResponse->getDetails();
                    }

                    //Get the Message
                    return $successResponse->getMessage()->getValue();
                }
                //Check if the request returned an exception
                elseif ($actionResponse instanceof APIException) {
                    //Get the received APIException instance
                    $exception = $actionResponse;
                    throw new \Exception($exception->getCode()->getValue().' '.$exception->getMessage()->getValue());
                }
            }

        }
    }
}
