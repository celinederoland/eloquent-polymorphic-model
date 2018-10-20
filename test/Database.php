<?php

namespace EloquentPolymorphism;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\DatabaseManager;
use PDO;
use PDOException;

class Database {

    private static $pdo;

    /**
     * @throws PDOException
     * @return PDO
     */
    public static function getPDO() {

        if (is_null(self::$pdo)) {
            /** @noinspection SpellCheckingInspection */
            $host      = 'mysql:host=' . getenv('SQL_HOST') . ';dbname=' . getenv('SQL_DATABASE');
            $user      = getenv('SQL_USER');
            $password  = getenv('SQL_PASSWORD');
            $exception = new PDOException('Unable to link docker container for database ' . $host);
            for ($i = 0; $i < 5; $i++) { //Wait mariadb container
                try {
                    self::$pdo = new PDO($host, $user, $password);
                    $exception = false;
                    break;
                } /** @noinspection PhpRedundantCatchClauseInspection */ catch (PDOException $e) {
                    $exception = $e;
                    sleep(5);
                }
            }
            if ($exception) {
                throw $exception;
            }
        }

        return self::$pdo;
    }

    /**
     * Define a default sql configuration (using local test database)
     *
     * @return DatabaseManager
     */
    public static function setCapsuleConnection() {

        $maria_config = [
            'driver'      => 'mysql',
            'host'        => getenv('SQL_HOST'),
            'database'    => getenv('SQL_DATABASE'),
            'username'    => getenv('SQL_USER'),
            'password'    => getenv('SQL_PASSWORD'),
            'charset'     => 'utf8mb4',
            'collation'   => 'utf8mb4_unicode_ci',
            'port'        => 3306,
            'prefix'      => '',
            'options'     => [
                PDO::MYSQL_ATTR_FOUND_ROWS => true,
            ],
            'unix_socket' => ''
        ];

        $db = new Capsule;
        $db->addConnection(
            $maria_config, 'test_db'
        );
        $db->getDatabaseManager()->setDefaultConnection('test_db');
        $db->setAsGlobal();
        $db->bootEloquent();
        return $db->getDatabaseManager();
    }
}