<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 */


namespace Ls\Omni\Client\Ecommerce\Entity;

use IteratorAggregate;
use ArrayIterator;

class ArrayOfCurrency implements IteratorAggregate
{

    /**
     * @property Currency[] $Currency
     */
    protected $Currency = array(
        
    );

    /**
     * @param Currency[] $Currency
     * @return $this
     */
    public function setCurrency($Currency)
    {
        $this->Currency = $Currency;
        return $this;
    }

    /**
     * @return Currency[]
     */
    public function getIterator()
    {
        return new ArrayIterator( $this->Currency );
    }

    /**
     * @return Currency[]
     */
    public function getCurrency()
    {
        return $this->Currency;
    }


}
