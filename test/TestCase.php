<?php

namespace EloquentPolymorphism;

use PDOException;

class TestCase extends \PHPUnit\Framework\TestCase {

    /**
     * @throws PDOException
     */
    public function setUp() {

        parent::setUp();
        date_default_timezone_set('Europe/Paris');

        /** @noinspection SqlResolve */
        $tables = Database::getPDO()->query('SELECT TABLE_NAME FROM  information_schema.TABLES WHERE TABLE_SCHEMA = \'MyTestDatabase\'')->fetchAll();
        $tables = array_map(function ($row) { return $row[0]; }, $tables);
        if (!empty($tables)) {
            Database::getPDO()->exec('DROP TABLE ' . implode(',', $tables));
        }

        Database::setCapsuleConnection();
    }
}