<?php

namespace Mkk\DbaseBundle\Component;

class DbaseRecord implements \ArrayAccess
{
    protected $markAsDeleted = false;
    protected $originalData = [];
    protected $data = [];

    public function __construct(DbaseHeader $header, array $data)
    {
        $this->markAsDeleted = (bool) $data['deleted'];
        unset($data['deleted']);
        $this->originalData = $data;
        $this->prepareData($header);
    }

    protected function prepareData(DbaseHeader $header)
    {
        foreach ($this->originalData as $columnNumber => $data) {
            $headerInfo = $header->getColumn($columnNumber);

            switch ($headerInfo['type']) {
                case 'character':
                    $this->data[strtolower($headerInfo['name'])] = trim($data);

                    break;

                case 'date':
                    $this->data[strtolower($headerInfo['name'])] = new \DateTime($data);

                    break;

                case 'boolean':
                    $this->data[strtolower($headerInfo['name'])] = (bool) $data;

                    break;

                default:
                    $this->data[strtolower($headerInfo['name'])] = $data;
            }
        }
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
