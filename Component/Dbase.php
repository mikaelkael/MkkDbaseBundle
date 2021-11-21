<?php

namespace Mkk\DbaseBundle\Component;

class Dbase
{
    public const DBASE_MODE_READ = 0;
    public const DBASE_MODE_WRITE = 1; // must not be used, see: https://www.php.net/manual/fr/function.dbase-open.php#refsect1-function.dbase-open-parameters
    public const DBASE_MODE_READ_WRITE = 2;

    protected $header;

    protected $connection;

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @throws DbaseException
     *
     * @return mixed
     */
    public function connect(array $params)
    {
        if (!\array_key_exists('mode', $params)) {
            $params['mode'] = self::DBASE_MODE_READ;
        }
        if (null === $params['path']) {
            throw new DbaseException('The filename cannot be empty');
        }
        if (!file_exists(realpath($params['path']))) {
            throw new DbaseException('Unable to open database '.$params['path']);
        }
        $this->connection = dbase_open($params['path'], $params['mode']);
        if (!$this->connection) {
            throw new DbaseException('Unable to open database '.$params['path']);
        }
        $this->header = new DbaseHeader(dbase_get_header_info($this->connection));

        return $this->connection;
    }

    public function getHeader(): DbaseHeader
    {
        return $this->header;
    }

    /**
     * @throws DbaseException
     *
     * @return mixed
     */
    public function create(array $params)
    {
        if (null === $params['path']) {
            throw new DbaseException('The filename cannot be empty');
        }
        if (file_exists(realpath($params['path']))) {
            throw new DbaseException('Database already exists in '.$params['path']);
        }
        if (!\array_key_exists('type', $params) || null === $params['type']) {
            $params['type'] = DBASE_TYPE_DBASE;
        }
        $this->connection = dbase_create($params['path'], $params['fields'], $params['type']);
        if (!$this->connection) {
            throw new DbaseException('Unable to create database '.$params['path']);
        }
        $this->header = new DbaseHeader(dbase_get_header_info($this->connection));

        return $this->connection;
    }

    public function close(bool $pack = false): void
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
     * @throws DbaseException
     */
    public function getNumRecords(): int
    {
        if (!$this->connection) {
            throw new DbaseException('No open database');
        }

        return dbase_numrecords($this->connection);
    }

    /**
     * @throws DbaseException
     */
    public function find(int $numRecord): DbaseRecord
    {
        if (!$this->connection) {
            throw new DbaseException('No open database');
        }

        return new DbaseRecord($this->header, dbase_get_record($this->connection, $numRecord));
    }

    /**
     * @throws DbaseException
     */
    public function addRecord(array $data): self
    {
        if (!$this->connection) {
            throw new DbaseException('No open database');
        }
        dbase_add_record($this->connection, $data);

        return $this;
    }

    /**
     * @throws DbaseException
     */
    public function deleteRecord(int $numRecord): self
    {
        if (!$this->connection) {
            throw new DbaseException('No open database');
        }
        dbase_delete_record($this->connection, $numRecord);

        return $this;
    }

    /**
     * @throws DbaseException
     *
     * @return DbaseRecord[]
     */
    public function findAll(): array
    {
        $records = [];
        $numRecords = $this->getNumRecords();
        for ($i = 1; $i <= $numRecords; ++$i) {
            $records[$i] = $this->find($i);
        }

        return $records;
    }
}
