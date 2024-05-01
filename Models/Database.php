<?php

namespace Models;

use PDO;
use PDOException;

abstract class Database
{
    protected PDO $pdo;

    /**
     * Connect to database
     */
    public function __construct()
    {
        $db_host = 'localhost';
        $db_name = 'transfermate-testtask';
        $db_user = 'postgres';
        $db_password = '12345';

        try {
            $this->pdo = new PDO("pgsql:host=$db_host;dbname=$db_name", $db_user, $db_password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error connecting to database: " . $e->getMessage();
        }
    }
}
