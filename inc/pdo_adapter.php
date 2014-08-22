<?php

define('DB_FETCHMODE_ORDERED', PDO::FETCH_NUM);

class PdoAdapter {

    //function __construct() {
    //
    //}

    private $pdo = null;

    function connect($dsn) {
        //print_r(PDO::getAvailableDrivers());
        // Array ( [0] => mysql )
        //print_r($dsn);
        //Array ( [phptype] => mysqli [hostspec] => localhost [database] => phpbt_test [username] => phpbt [password] => phpbtpass )

        $drv = $dsn['phptype'];
        if ($drv == 'mysqli') {
            $drv = 'mysql';
        }
        $dbname = $dsn['database'];
        $host = $dsn['hostspec'];
        if (isset($dsn['port'])) {
            $host .= ':' . $dsn['port'];
        }

        //try {
        $this->pdo = new PDO("$drv:dbname=$dbname;host=$host", $dsn['username'], $dsn['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        //} catch (PDOException $e) {
        //    throw new Exception('Mysql Resource failed: ' . $e->getMessage());
        //}
    }

    function query($sqlString, $params = array()) {
        if ($params === null) {
            $params = array();
        }
        //$this->pdo->exec($sqlString);
        $st = $this->pdo->prepare($sqlString);
        //if (count($params) > 0) {
        $res = $st->execute($params);
        //}
        if (!$res) {
            throw new Exception("Error while execute sql: " . $sqlString);
        }
        $resultSt = new PdoStatementAdapter($st);
        return $resultSt;
    }

    function getCol($sqlString, $colNum = 0, $params = array()) {
        $st = $this->pdo->prepare($sqlString);
        $res = $st->execute($params);
        if (!$res) {
            throw new Exception("Error while execute sql: " . $sqlString);
        }
        $result = array();
        while ($row = $st->fetch(PDO::FETCH_NUM)) {
            $result[] = $row[$colNum];
        }
        return $result;
    }

    function getRow($sqlString, $params = array(), $fetchStyle = null) {
        $stAd = $this->query($sqlString, $params);
        return $stAd->fetchRow($fetchStyle);
    }

    function getOne($sqlString) {
        $row = $this->getRow($sqlString, null, PDO::FETCH_NUM);
        return $row[0];
    }

    function getAll($sqlString) {
        $stAd = $this->query($sqlString);
        return $stAd->getPdoStatement()->fetchAll();
    }

    function getAssoc($sqlString) {
        $stAd = $this->query($sqlString);
        $result = array();
        while ($row = $stAd->getPdoStatement()->fetch(PDO::FETCH_NUM)) {
            $result[$row[0]] = $row[1];
        }
        return $result;
    }

    function modifyLimitQuery($sqlString, $offset, $limit) {
        return $sqlString . ' limit ' . (int) $offset . ',' . (int) $limit;
    }

    function limitQuery($sqlString, $offset, $limit) {
        return $this->query($this->modifyLimitQuery($sqlString, $offset, $limit));
    }

    function quote($ipValue) {
        if (is_array($ipValue)) {
            $quoted = array();
            foreach ($ipValue as $val) {
                $quoted[] = $this->quote($val);
            }
            return implode(', ', $quoted);
        } else {
            return $this->pdo->quote($ipValue);
        }
    }

    function nextId($tableName) {
        $seqTableName = $tableName . '_seq';
        $qry = "insert into $seqTableName () values ()";
        $this->pdo->exec($qry);
        return $this->pdo->lastInsertId();
    }

    function getPdo() {
        return $this->pdo;
    }
}

class PdoStatementAdapter {

    private $pdoStatement = null;

    function __construct(PDOStatement $st = null) {
        if ($st == null) {
            throw new Exception('invalid PdoStatementAdapter creation');
        }
        $this->pdoStatement = $st;
    }

    function fetchRow($fetchStyle = null) {
        return $this->pdoStatement->fetch($fetchStyle);
    }

    function fetchInto(&$output) {
        if ($output == null) {
            $output = array();
        }
        $row = $this->pdoStatement->fetch();
        if ($row != false) {
            $output = array_replace($output, $row);
            //print_r($output);
            return true;
        } else {
            return false;
        }
    }

    function getPdoStatement() {
        return $this->pdoStatement;
    }

}
