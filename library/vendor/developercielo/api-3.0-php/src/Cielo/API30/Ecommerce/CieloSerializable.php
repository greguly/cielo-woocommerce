<?php
namespace Cielo\API30\Ecommerce;

interface CieloSerializable extends \JsonSerializable
{
    public function populate(\stdClass $data);
}