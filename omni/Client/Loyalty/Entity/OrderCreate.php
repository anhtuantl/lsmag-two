<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 */


namespace Ls\Omni\Client\Loyalty\Entity;

use Ls\Omni\Client\IRequest;

class OrderCreate implements IRequest
{

    /**
     * @property Order $request
     */
    protected $request = null;

    /**
     * @param Order $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Order
     */
    public function getRequest()
    {
        return $this->request;
    }


}
