<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 */


namespace Ls\Omni\Client\Ecommerce\Entity;

class ReplUnitOfMeasureResponse
{

    /**
     * @property string $LastKey
     */
    protected $LastKey = null;

    /**
     * @property string $MaxKey
     */
    protected $MaxKey = null;

    /**
     * @property int $RecordsRemaining
     */
    protected $RecordsRemaining = null;

    /**
     * @property ArrayOfUnitOfMeasure $UnitOfMeasures
     */
    protected $UnitOfMeasures = null;

    /**
     * @param string $LastKey
     * @return $this
     */
    public function setLastKey($LastKey)
    {
        $this->LastKey = $LastKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastKey()
    {
        return $this->LastKey;
    }

    /**
     * @param string $MaxKey
     * @return $this
     */
    public function setMaxKey($MaxKey)
    {
        $this->MaxKey = $MaxKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getMaxKey()
    {
        return $this->MaxKey;
    }

    /**
     * @param int $RecordsRemaining
     * @return $this
     */
    public function setRecordsRemaining($RecordsRemaining)
    {
        $this->RecordsRemaining = $RecordsRemaining;
        return $this;
    }

    /**
     * @return int
     */
    public function getRecordsRemaining()
    {
        return $this->RecordsRemaining;
    }

    /**
     * @param ArrayOfUnitOfMeasure $UnitOfMeasures
     * @return $this
     */
    public function setUnitOfMeasures($UnitOfMeasures)
    {
        $this->UnitOfMeasures = $UnitOfMeasures;
        return $this;
    }

    /**
     * @return ArrayOfUnitOfMeasure
     */
    public function getUnitOfMeasures()
    {
        return $this->UnitOfMeasures;
    }


}
