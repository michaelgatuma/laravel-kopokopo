<?php

namespace Michaelgatuma\Kopokopo\Requests;

class StatusRequest extends BaseRequest
{
    public function getLocation()
    {
        return $this->getRequestData('location');
    }
}
