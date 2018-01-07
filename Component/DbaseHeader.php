<?php

namespace Mkk\DbaseBundle\Component;

class DbaseHeader
{

    protected $headers = array();

    /**
     * DbaseHeader constructor.
     * @param array $headerInfo
     */
    public function __construct(array $headerInfo)
    {
        $this->headers = $headerInfo;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->headers;
    }

    /**
     * @param $columnNumber
     * @return array
     */
    public function getColumn($columnNumber)
    {
        return $this->headers[$columnNumber];
    }
}