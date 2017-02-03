<?php
namespace Cielo\API30\Ecommerce;

class RecurrentPayment implements \JsonSerializable
{

    const INTERVAL_MONTHLY = 'Monthly';

    const INTERVAL_BIMONTHLY = 'Bimonthly';

    const INTERVAL_QUARTERLY = 'Quarterly';

    const INTERVAL_SEMIANNUAL = 'SemiAnnual';

    const INTERVAL_ANNUAL = 'Annual';

    private $authorizeNow;

    private $startDate;

    private $endDate;

    private $interval;

    public function __construct($authorizeNow)
    {
        $this->setAuthorizeNow($authorizeNow);
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public function populate(\stdClass $data)
    {
        $this->authorizeNow = isset($data->AuthorizeNow)? !!$data->AuthorizeNow: false;
        $this->startDate = isset($data->StartDate)? $data->StartDate: null;
        $this->endDate = isset($data->EndDate)? $data->EndDate: null;
        $this->interval = isset($data->Interval)? !!$data->Interval: null;
    }

    public function getAuthorizeNow()
    {
        return $this->authorizeNow;
    }

    public function setAuthorizeNow($authorizeNow)
    {
        $this->authorizeNow = $authorizeNow;
        return $this;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getInterval()
    {
        return $this->interval;
    }

    public function setInterval($interval)
    {
        $this->interval = $interval;
        return $this;
    }
}