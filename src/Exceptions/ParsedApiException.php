<?php

namespace Patslaf\DigitalAcorn\Zoho20\Exceptions;

use com\zoho\crm\api\record\APIException;
use Exception;

class ParsedApiException extends Exception
{
    protected $zohoCode;

    protected $details;

    protected $exception;

    protected $message;

    public function __construct(APIException $exception)
    {
        $this->zohoCode = $exception->getCode()->getValue();
        $this->details = $exception->getDetails();
        $this->exception = $exception;
        $this->message = $exception->getMessage()->getValue();

        return parent::__construct($this->message);
    }
}
