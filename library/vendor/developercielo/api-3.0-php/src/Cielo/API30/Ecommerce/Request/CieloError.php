<?php
namespace Cielo\API30\Ecommerce\Request;

class CieloError
{

    private $code;

    private $message;

    public function __construct($message, $code)
    {
        $this->setMessage($message);
        $this->setCode($code);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
}