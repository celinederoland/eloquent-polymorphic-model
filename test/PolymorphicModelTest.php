<?php

namespace EloquentPolymorphism;

use EloquentPolymorphism\Model\BasicExample\Man;
use EloquentPolymorphism\Model\BasicExample\Person;
use EloquentPolymorphism\Model\BasicExample\Woman;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PolymorphicModelTest extends TestCase {

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PDOException
     */
    public function testRetrieveFromChildren() {

        $this->createTable();

        Database::getPDO()->query(
            '
            insert into Person VALUES (1, \'James\', \'m\');
            insert into Person VALUES (2, \'Sandra\', \'f\');
            insert into Person VALUES (3, \'Paul\', \'m\');
        '
        );

        $actual = Woman::find(1);
        self::assertNull($actual);
        $actual = Woman::find(2);
        self::assertInstanceOf(Woman::class, $actual);

        $actual = Man::where('name', 'Sandra')->get();
        self::assertEmpty($actual);
        $actual = Woman::where('name', 'Sandra')->first();
        self::assertInstanceOf(Woman::class, $actual);
    }

    /**
     * @throws \PDOException
     */
    public function createTable() {

        Database::getPDO()->query(
            '
            create table Person
            (
                person_id int unsigned auto_increment,
                name varchar(50) not null,
                gender set(\'m\', \'f\') not null,
                constraint Person_person_id_index
                    unique (person_id)
            )
            ;
            
            alter table Person
                add primary key (person_id)
            ;
        '
        );
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PDOException
     */
    public function testGroupingQueriesFromChildren() {

        $this->createTable();

        Database::getPDO()->query(
            '
            insert into Person VALUES (1, \'James\', \'m\');
            insert into Person VALUES (2, \'Sandra\', \'f\');
            insert into Person VALUES (3, \'Paul\', \'m\');
        '
        );

        $actual      = Man::where('name', 'Sandra')->orWhere('name', 'Paul');
        $actualQuery = $actual->toSql();
        self::assertEquals('select * from `Person` where (`name` = ? or `name` = ?) and `gender` = ?', $actualQuery);
        $actualBindings = $actual->getBindings();
        self::assertEquals(['Sandra', 'Paul', 'm'], $actualBindings);

        $actualResult = $actual->get();
        self::assertCount(1, $actualResult);
        self::assertInstanceOf(Man::class, $actualResult->first());
        self::assertEquals('Paul', $actualResult->first()->name);

        $actual = Woman::where('name', 'James')->orWhere('name', 'Paul');
        try {
            $actual->firstOrFail();
            self::fail();
        } catch (ModelNotFoundException $e) {
        }
    }

    /**
     * @throws \PDOException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGroupingQueriesFromParent() {

        $this->createTable();

        Database::getPDO()->query(
            '
            insert into Person VALUES (1, \'James\', \'m\');
            insert into Person VALUES (2, \'Sandra\', \'f\');
            insert into Person VALUES (3, \'Paul\', \'m\');
        '
        );

        $actual      = Person::where('name', 'Sandra')->orWhere('name', 'Paul');
        $actualQuery = $actual->toSql();
        self::assertEquals('select * from `Person` where `name` = ? or `name` = ?', $actualQuery);
        $actualBindings = $actual->getBindings();
        self::assertEquals(['Sandra', 'Paul'], $actualBindings);

        $actualResult = $actual->get();
        self::assertCount(2, $actualResult);
        self::assertInstanceOf(Woman::class, $actualResult->first());
        self::assertEquals('Sandra', $actualResult->first()->name);
        self::assertInstanceOf(Man::class, $actualResult->get(1));
        self::assertEquals('Paul', $actualResult->get(1)->name);
    }

    /**
     * @throws \PDOException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testRetrieveFromParent() {

        $this->createTable();

        Database::getPDO()->query(
            '
            insert into Person VALUES (1, \'James\', \'m\');
            insert into Person VALUES (2, \'Sandra\', \'f\');
            insert into Person VALUES (3, \'Paul\', \'m\');
        '
        );

        $actual = Person::all();
        self::assertInstanceOf(Man::class, $actual->get(0));
        self::assertInstanceOf(Woman::class, $actual->get(1));
        self::assertInstanceOf(Man::class, $actual->get(2));

        $actual = Person::find(1);
        self::assertInstanceOf(Man::class, $actual);
        $actual = Person::find(2);
        self::assertInstanceOf(Woman::class, $actual);

        $actual = Person::where('name', 'Sandra')->first();
        self::assertInstanceOf(Woman::class, $actual);
    }

    /**
     * @throws \PDOException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSave() {

        $this->createTable();

        Database::getPDO()->query(
            '
            insert into Person VALUES (1, \'James\', \'m\');
            insert into Person VALUES (2, \'Sandra\', \'f\');
            insert into Person VALUES (3, \'Paul\', \'m\');
        '
        );

        $person         = new Person();
        $person->gender = 'f';
        $person->name   = 'Sophie';
        $saved          = $person->save();
        self::assertTrue($saved);
        $sophie = Woman::find(4);
        self::assertInstanceOf(Woman::class, $sophie);

        $leonie = new Man();
        self::assertEquals('m', $leonie->gender);
        $leonie->name   = 'Leonie';
        $leonie->gender = 'f';
        self::assertFalse($leonie->save());
        self::assertCount(4, Person::all());

        $leonie       = new Woman();
        $leonie->name = 'Leonie';
        $saved        = $leonie->save();
        self::assertTrue($saved);
        $leonie = Woman::find(5);
        self::assertInstanceOf(Woman::class, $leonie);

        $james         = Person::find(1);
        $james->gender = 'f';
        self::assertFalse($james->save());
    }
}