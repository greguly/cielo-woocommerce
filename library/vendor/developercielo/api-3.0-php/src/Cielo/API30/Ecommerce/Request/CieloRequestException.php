<?php
namespace Cielo\API30\Ecommerce\Request;

class CieloRequestException extends \Exception
{

    private $cieloError;

    public function __construct($message, $code, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getCieloError()
    {
        return $this->cieloError;
    }

    public function setCieloError(CieloError $cieloError)
    {
        $this->cieloError = $cieloError;
        return $this;
    }
}