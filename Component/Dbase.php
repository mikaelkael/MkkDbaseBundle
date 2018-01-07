<?php

namespace Mkk\DbaseBundle\Component;

class Dbase
{

    const DBASE_MODE_READ = 0;
    const DBASE_MODE_WRITE = 1;
    const DBASE_MODE_READ_WRITE = 2;

    protected $header = null;

    protected $connection;

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param array $params
     * @return int
     * @throws DbaseException
     */
    public function connect(array $params)
    {
        if (!array_key_exists('mode', $params)) {
            $params['mode'] = self::DBASE_MODE_READ;
        }
        if ($params['path'] == null) {
            throw new DbaseException('The filename cannot be empty');
        }
        if (!file_exists(realpath($params['path']))) {
            throw new DbaseException('Unable to open database ' . $params['path']);
        }
        $this->connection = dbase_open($params['path'], $params['mode']);
        if (!$this->connection) {
            throw new DbaseException('Unable to open database ' . $params['path']);
        }
        $this->header = new DbaseHeader(dbase_get_header_info($this->connection));
        return $this->connection;
    }

    /**
     * @return DbaseHeader
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param array $params
     * @return int
     * @throws DbaseException
     */
    public function create(array $params)
    {
        if ($params['path'] == null) {
            throw new DbaseException('The filename cannot be empty');
        }
        if (file_exists(realpath($params['path']))) {
            throw new DbaseException('Database already exists in ' . $params['path']);
        }
        $this->connection = dbase_create($params['path'], $params['fields']);
        if (!$this->connection) {
            throw new DbaseException('Unable to create database ' . $params['path']);
        }
        $this->header = new DbaseHeader(dbase_get_header_info($this->connection));
        return $this->connection;
    }

    /**
     * @param bool $pack
     * @return void
     */
    public function close($pack = false)
    {
        if ($this->connection) {
            if ($pack) {
                dbase_pack($this->connection);
            }
            dbase_close($this->connection);
            $this->connection = false;
        }
    }

    /**
     * @return int
     * @throws DbaseException
     */
    public function getNumRecords()
    {
        if (!$this->connection) {
            throw new DbaseException('No open database');
        }
        return dbase_numrecords($this->connection);
    }

    /**
     * @param $numRecord
     * @return DbaseRecord
     * @throws DbaseException
     */
    public function find($numRecord)
    {
        if (!$this->connection) {
            throw new DbaseException('No open database');
        }
        return new DbaseRecord($this->header, dbase_get_record($this->connection, $numRecord));
    }

    /**
     * @param array $data
     * @throws DbaseException
     */
    public function addRecord(array $data)
    {
        if (!$this->connection) {
            throw new DbaseException('No open database');
        }
        dbase_add_record($this->connection, $data);
        return $this;
    }

    /**
     * @param array $data
     * @throws DbaseException
     */
    public function deleteRecord($numRecord)
    {
        if (!$this->connection) {
            throw new DbaseException('No open database');
        }
        dbase_delete_record($this->connection, $numRecord);
        return $this;
    }


    /**
     * @return DbaseRecord[]
     * @throws DbaseException
     */
    public function findAll()
    {
        $records = array();
        $numRecords = $this->getNumRecords();
        for ($i = 1 ; $i <= $numRecords ; $i++) {
            $records[$i] = $this->find($i);
        }
        return $records;
    }
}