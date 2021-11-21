<?php

namespace Mkk\DbaseBundle\Tests\Component;

use Mkk\DbaseBundle\Component\Dbase;
use Mkk\DbaseBundle\Component\DbaseException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class DbaseTest extends TestCase
{
    protected function setUp(): void
    {
        if (file_exists(__DIR__.'/../Fixtures/test.dbf')) {
            unlink(__DIR__.'/../Fixtures/test.dbf');
        }
        parent::setUp();
    }

    public function testConnect(): void
    {
        $dbase = new Dbase();
        $connection = $dbase->connect(['path' => __DIR__.'/../Fixtures/dbase.dbf']);
        static::assertNotFalse($connection);
    }

    public function testConnectNotExists(): void
    {
        $dbase = new Dbase();
        $this->expectException(DbaseException::class);
        $dbase->connect(['path' => __DIR__.'/../Fixtures/notexists.dbf']);
    }

    public function testConnectNoPath(): void
    {
        $dbase = new Dbase();
        $this->expectException(DbaseException::class);
        $dbase->connect(['path' => null]);
    }

    public function testNumRecords(): void
    {
        $dbase = new Dbase();
        $dbase->connect(['path' => __DIR__.'/../Fixtures/dbase.dbf']);
        static::assertSame(200, $dbase->getNumRecords());
    }

    public function testNumRecordsIfDatabaseClosed(): void
    {
        $dbase = new Dbase();
        $dbase->connect(['path' => __DIR__.'/../Fixtures/dbase.dbf']);
        $dbase->close();
        $this->expectException(DbaseException::class);
        $dbase->getNumRecords();
    }

    public function testGetRecord(): void
    {
        $dbase = new Dbase();
        $dbase->connect(['path' => __DIR__.'/../Fixtures/dbase.dbf']);
        $data = $dbase->find(5)->toArray();
        static::assertCount(3, $data);
        static::assertSame(5, $data['id']);
        static::assertSame('foo5', $data['name']);
        static::assertSame('20100105', $data['date']->format('Ymd'));
    }

    public function testGetRecordIfDatabaseClosed(): void
    {
        $dbase = new Dbase();
        $dbase->connect(['path' => __DIR__.'/../Fixtures/dbase.dbf']);
        $dbase->close();
        $this->expectException(DbaseException::class);
        $dbase->find(1);
    }

    public function testAddRecordIfDatabaseClosed(): void
    {
        $dbase = new Dbase();
        $dbase->connect(['path' => __DIR__.'/../Fixtures/dbase.dbf']);
        $dbase->close();
        $this->expectException(DbaseException::class);
        $dbase->addRecord([]);
    }

    public function testGetAllRecords(): void
    {
        $dbase = new Dbase();
        $dbase->connect(['path' => __DIR__.'/../Fixtures/dbase.dbf']);
        $data = $dbase->findAll();
        static::assertCount(200, $data);
        static::assertSame(1, $data[1]->toArray()['id']);
        static::assertSame('foo1', $data[1]->toArray()['name']);
        static::assertSame('20100101', $data[1]->toArray()['date']->format('Ymd'));
        static::assertSame(200, $data[200]->toArray()['id']);
        static::assertSame('foo200', $data[200]->toArray()['name']);
        static::assertSame('20100719', $data[200]->toArray()['date']->format('Ymd'));
    }

    public function testGetHeader(): void
    {
        $dbase = new Dbase();
        $dbase->connect(['path' => __DIR__.'/../Fixtures/dbase.dbf']);
        static::assertSame(
            [
                [
                    'name' => 'ID',
                    'type' => 'number',
                    'length' => 11,
                    'precision' => 0,
                    'format' => '%11s',
                    'offset' => 1,
                ],
                [
                    'name' => 'NAME',
                    'type' => 'character',
                    'length' => 11,
                    'precision' => 0,
                    'format' => '%-11s',
                    'offset' => 12,
                ],
                [
                    'name' => 'DATE',
                    'type' => 'date',
                    'length' => 8,
                    'precision' => 0,
                    'format' => '%8s',
                    'offset' => 23,
                ],
            ],
            $dbase->getHeader()->toArray()
        );
    }

    public function testCreate(): void
    {
        $dbase = new Dbase();
        $dbase->create(
            [
                'path' => __DIR__.'/../Fixtures/test.dbf',
                'fields' => [
                    ['ID', 'N', 11, 0],
                    ['BOOL', 'L'],
                    ['DATE', 'D'],
                ],
                'type' => DBASE_TYPE_FOXPRO,
            ]
        );
        $dbase->addRecord([123, 'Y', date('Ymd')]);
        $dbase->close();

        $dbase->connect(['path' => __DIR__.'/../Fixtures/test.dbf']);
        static::assertSame(
            [
                [
                    'name' => 'ID',
                    'type' => 'number',
                    'length' => 11,
                    'precision' => 0,
                    'format' => '%11s',
                    'offset' => 1,
                ],
                [
                    'name' => 'BOOL',
                    'type' => 'boolean',
                    'length' => 1,
                    'precision' => 0,
                    'format' => '%1s',
                    'offset' => 12,
                ],
                [
                    'name' => 'DATE',
                    'type' => 'date',
                    'length' => 8,
                    'precision' => 0,
                    'format' => '%8s',
                    'offset' => 13,
                ],
            ],
            $dbase->getHeader()->toArray()
        );
        $data = $dbase->find(1)->toArray();
        static::assertCount(3, $data);
        static::assertSame(123, $data['id']);
        static::assertTrue($data['bool']);
        static::assertSame(date('Ymd'), $data['date']->format('Ymd'));
    }

    public function testCreateExists(): void
    {
        touch(__DIR__.'/../Fixtures/test.dbf');
        $dbase = new Dbase();
        $this->expectException(DbaseException::class);
        $dbase->create(['path' => __DIR__.'/../Fixtures/test.dbf']);
    }

    public function testCreateNoPath(): void
    {
        $dbase = new Dbase();
        $this->expectException(DbaseException::class);
        $dbase->create(['path' => null]);
    }

    public function testArrayAccess(): void
    {
        $dbase = new Dbase();
        $dbase->connect(['path' => __DIR__.'/../Fixtures/dbase.dbf']);
        $data = $dbase->find(4);
        static::assertTrue(isset($data['id']));
        static::assertSame(4, $data['id']);
        static::assertSame('foo4', $data['name']);
        unset($data['name']);
        static::assertFalse(isset($data['name']));
        $data['id'] = 123;
        static::assertSame(123, $data['id']);
    }

    public function testDeleteRecord(): void
    {
        copy(__DIR__.'/../Fixtures/dbase.dbf', __DIR__.'/../Fixtures/test.dbf');
        $dbase = new Dbase();
        $dbase->connect(['path' => __DIR__.'/../Fixtures/test.dbf', 'mode' => Dbase::DBASE_MODE_READ_WRITE]);
        static::assertSame(200, $dbase->getNumRecords());
        $dbase->deleteRecord(4);
        $dbase->close(true);

        $dbase->connect(['path' => __DIR__.'/../Fixtures/test.dbf']);
        static::assertSame(199, $dbase->getNumRecords());
    }
}
