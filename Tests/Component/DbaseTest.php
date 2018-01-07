<?php

namespace Mkk\DbaseBundle\Tests;

use Mkk\DbaseBundle\Component\Dbase;
use PHPUnit\Framework\TestCase;

class DbaseTest extends TestCase
{

    public function setUp()
    {
        if (file_exists(__DIR__ . '/../Fixtures/test.dbf')) {
            unlink(__DIR__ . '/../Fixtures/test.dbf');
        }
        parent::setUp();
    }

    public function testConnect()
    {
        $dbase      = new Dbase();
        $connection = $dbase->connect(array('path' => __DIR__ . '/../Fixtures/dbase.dbf'));
        $this->assertNotFalse($connection);
    }

    /**
     * @expectedException \Mkk\DbaseBundle\Component\DbaseException
     */
    public function testConnectNotExists()
    {
        $dbase      = new Dbase();
        $dbase->connect(array('path' => __DIR__ . '/../Fixtures/notexists.dbf'));
    }

    /**
     * @expectedException \Mkk\DbaseBundle\Component\DbaseException
     */
    public function testConnectNoPath()
    {
        $dbase      = new Dbase();
        $dbase->connect(array('path' => null));
    }

    public function testNumRecords()
    {
        $dbase = new Dbase();
        $dbase->connect(array('path' => __DIR__ . '/../Fixtures/dbase.dbf'));
        $this->assertEquals(200, $dbase->getNumRecords());
    }

    public function testGetRecord()
    {
        $dbase = new Dbase();
        $dbase->connect(array('path' => __DIR__ . '/../Fixtures/dbase.dbf'));
        $data = $dbase->find(5)->toArray();
        $this->assertCount(3, $data);
        $this->assertEquals(5, $data['id']);
        $this->assertEquals('foo5', $data['name']);
        $this->assertEquals('20100105', $data['date']->format('Ymd'));
    }

    public function testGetAllRecords()
    {
        $dbase = new Dbase();
        $dbase->connect(array('path' => __DIR__ . '/../Fixtures/dbase.dbf'));
        $data = $dbase->findAll();
        $this->assertCount(200, $data);
        $this->assertEquals(1, $data[1]->toArray()['id']);
        $this->assertEquals('foo1', $data[1]->toArray()['name']);
        $this->assertEquals('20100101', $data[1]->toArray()['date']->format('Ymd'));
        $this->assertEquals(200, $data[200]->toArray()['id']);
        $this->assertEquals('foo200', $data[200]->toArray()['name']);
        $this->assertEquals('20100719', $data[200]->toArray()['date']->format('Ymd'));
    }

    public function testGetHeader()
    {
        $dbase = new Dbase();
        $dbase->connect(array('path' => __DIR__ . '/../Fixtures/dbase.dbf'));
        $this->assertSame(array(array(
            'name'      => 'ID',
            'type'      => 'number',
            'length'    => 11,
            'precision' => 0,
            'format'    => '%11s',
            'offset'    => 1,
        ),
            array(
                'name'      => 'NAME',
                'type'      => 'character',
                'length'    => 11,
                'precision' => 0,
                'format'    => '%-11s',
                'offset'    => 12,
            ),
            array(
                'name'      => 'DATE',
                'type'      => 'date',
                'length'    => 8,
                'precision' => 0,
                'format'    => '%8s',
                'offset'    => 23,
            )), $dbase->getHeader()->toArray());
    }

    public function testCreate()
    {
        $dbase = new Dbase();
        $dbase->create(array('path' => __DIR__ . '/../Fixtures/test.dbf', 'fields' => array(
            array('ID', 'N', 11, 0),
            array('BOOL', 'L'),
            array('DATE', 'D')
        )));
        $dbase->addRecord(array(123, 'Y', date('Ymd')));
        $dbase->close();

        $dbase->connect(array('path' => __DIR__ . '/../Fixtures/test.dbf'));
        $this->assertSame(array(
            array(
                'name'      => 'ID',
                'type'      => 'number',
                'length'    => 11,
                'precision' => 0,
                'format'    => '%11s',
                'offset'    => 1,
            ),
            array(
                'name'      => 'BOOL',
                'type'      => 'boolean',
                'length'    => 1,
                'precision' => 0,
                'format'    => '%1s',
                'offset'    => 12,
            ),
            array(
                'name'      => 'DATE',
                'type'      => 'date',
                'length'    => 8,
                'precision' => 0,
                'format'    => '%8s',
                'offset'    => 13,
            )), $dbase->getHeader()->toArray());
        $data = $dbase->find(1)->toArray();
        $this->assertCount(3, $data);
        $this->assertEquals(123, $data['id']);
        $this->assertEquals(true, $data['bool']);
        $this->assertEquals(date('Ymd'), $data['date']->format('Ymd'));
    }
}