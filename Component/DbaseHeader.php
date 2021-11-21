<?php

namespace Mkk\DbaseBundle\Component;

class DbaseHeader
{
    protected $headers = [];

    public function __construct(array $headerInfo)
    {
        $this->headers = $headerInfo;
    }

    public function toArray(): array
    {
        return $this->headers;
    }

    public function getColumn($columnNumber): array
    {
        return $this->headers[$columnNumber];
    }
}
