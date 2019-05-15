<?php
/**
 * Author: Pavel Naumenko
 */


/**
 * Class Db
 */
class Db
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;port=3307;dbname=spyse;charset=utf8',
            'root',
            'root',
            [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function insert($row): int
    {
        $sql = 'INSERT INTO domain(' . implode(',', array_keys($row));
        $sql .= ') VALUES(' . str_repeat('?,', \count($row));
        $sql = rtrim($sql, ',');
        $sql .= ')';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($row));

        return (int)$this->pdo->lastInsertId();
    }

    //I steal this code snippet from the web
    public function bulkInsert($data)
    {
        if (empty($data)) {
            return false;
        }
        //Will contain SQL snippets.
        $rowsSQL = [];

        //Will contain the values that we need to bind.
        $toBind = [];

        //Get a list of column names to use in the SQL statement.
        $columnNames = array_keys($data[0]);

        //Loop through our $data array.
        foreach ($data as $arrayIndex => $row) {
            $params = [];
            foreach ($row as $columnName => $columnValue) {
                $param = ':' . $columnName . $arrayIndex;
                $params[] = $param;
                $toBind[$param] = $columnValue;
            }
            $rowsSQL[] = "(" . implode(", ", $params) . ")";
        }

        //Construct our SQL statement
        $sql = "INSERT INTO `domain` (" . implode(", ", $columnNames) . ") VALUES " . implode(", ", $rowsSQL);

        //Prepare our PDO statement.
        $pdoStatement = $this->pdo->prepare($sql);

        //Bind our values.
        foreach ($toBind as $param => $val) {
            $pdoStatement->bindValue($param, $val);
        }

        //Execute our statement (i.e. insert the data).
        return $pdoStatement->execute();
    }

    public function selectId($field, $value): int
    {
        $pdoStatement = $this->pdo->prepare("SELECT `id` FROM `domain` WHERE $field = ?");
        $pdoStatement->execute([$value]);
        return (int) $pdoStatement->fetchColumn();
    }
}
