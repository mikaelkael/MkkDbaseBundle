<?php

namespace Mkk\DbaseBundle\Component;

class DbaseRecord
{
    protected $markAsDeleted = false;
    protected $originalData = array();
    protected $data = array();

    /**
     * DbaseRecord constructor.
     * @param DbaseHeader $header
     * @param array $data
     */
    public function __construct(DbaseHeader $header, array $data)
    {
        $this->markAsDeleted = (bool) $data['deleted'];
        unset($data['deleted']);
        $this->originalData = $data;
        $this->prepareData($header);
    }

    /**
     * @param DbaseHeader $header
     */
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

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
}