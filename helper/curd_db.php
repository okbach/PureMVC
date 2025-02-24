<?php


class DynamicCrud
{
    private PDO $db;
    public int $limit = 16;
    public string $returnType = 'opj';//opj array
    public string $orderBy = '';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function select_search(string $table, string $keys, array $condition = [], string $searchTerm = '', int $page = 1)
    {
        $limit = $this->limit;
        $returnType = $this->returnType;
        $orderBy = $this->orderBy;

        define('ACTIVE_STATUS', 1);

        $page = ($page - 1) * $limit;

        $whereClauses = ["status = " . ACTIVE_STATUS];
        $parameters = [];

        if (!empty($condition)) {
            foreach ($condition as $field => $value) {
                $whereClauses[] = "$field = :$field";
                $parameters[$field] = $value;
            }
        }

        if (!empty($searchTerm)) {
            $keywords = explode(" ", $searchTerm);
            $searchTerm = '+' . implode(' +', $keywords);
            $whereClauses[] = "MATCH (keyword, name) AGAINST (:searchTerm IN BOOLEAN MODE)";
            $parameters['searchTerm'] = $searchTerm;
        }

        $where = "WHERE " . implode(" AND ", $whereClauses);

        $sql = "SELECT $keys FROM $table $where";

        if (!empty($orderBy)) {
            $sql .= " ORDER BY $orderBy";
        }

        $sql .= " LIMIT $page, $limit";

        try {
            $query = $this->db->prepare($sql);

            foreach ($parameters as $key => $value) {
                $query->bindValue(":$key", $value);
            }

            $query->execute();

            if ($returnType === 'array') {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return $query->fetch(PDO::FETCH_OBJ);
            }
        } catch (PDOException $e) {
            $this->handleError($e);
            return false;
        }
    }

    public function selectWhere(string $table, string $keys, array $condition = [], int $page = 1,  bool $distinct = false, string $distinctColumn = null): mixed
    {

        $limit = $this->limit;
        $returnType = $this->returnType;
        $orderBy = $this->orderBy;



        $page = ($page * $limit) - $limit;
        $a = 0;

        foreach ($condition as $key => $value) {
            $setCondition[$a] = "$key=?";
            $a++;
        }

        if (!empty($condition)) {
            $setCondition = implode(" AND ", $setCondition);
            $WHERE = 'WHERE';
        } else {
            $setCondition = '';
            $WHERE = '';
        }

        try {
            $distinctKeyword = $distinct ? 'DISTINCT' : '';

            if ($distinct && $distinctColumn) {
                $sql = "SELECT DISTINCT($distinctColumn), $keys FROM $table $WHERE $setCondition $orderBy LIMIT $page,$limit";
            } else {
                $sql = "SELECT $distinctKeyword $keys FROM $table $WHERE $setCondition $orderBy LIMIT $page,$limit";
            }

            $query = $this->db->prepare($sql);

            $a = 1;
            foreach ($condition as $key => &$value) {
                $query->bindParam($a, $value);
                $a++;
            }

            $query->execute();

            if ($returnType === 'array') {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return $query->fetch(PDO::FETCH_OBJ);
            }

        } catch (Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

  /*public function fetchall(string $tablename, array $arrayval): mixed
    {
        try {
            $array_keys = implode(", ", $arrayval);
            $stmt = $this->db->prepare("SELECT $array_keys FROM $tablename");
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->handleError($e);
            return false;
        }
    }*/

    public function update(string $tablename, array $setvals, array $condition): bool
    {
        try {
            $setExp = [];
            foreach ($setvals as $key => $value) {
                $setExp[] = "$key = :$key";
            }
            $setExp = implode(", ", $setExp);

            $setCondition = [];
            foreach ($condition as $key => $value) {
                $setCondition[] = "$key = :cond_$key";
            }
            $setCondition = implode(" AND ", $setCondition);

            $sql = "UPDATE $tablename SET $setExp WHERE $setCondition";
            $stmt = $this->db->prepare($sql);

            foreach ($setvals as $key => &$value) {
                $stmt->bindParam(":$key", $value);
            }

            foreach ($condition as $key => &$value) {
                $stmt->bindParam(":cond_$key", $value);
            }

            return $stmt->execute();

        } catch (Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    public function delete(string $tablename, array $conditions): bool
    {
        try {
            $whereClause = '';
            $params = [];
            foreach ($conditions as $column => $value) {
                $whereClause .= ($whereClause ? ' AND ' : '') . "$column = ?";
                $params[] = $value;
            }

            $stmt = $this->db->prepare("DELETE FROM $tablename WHERE $whereClause");
            $stmt->execute($params);

            return ($stmt->rowCount() > 0);
        } catch (Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    public function insertMultiple(string $tablename, array $data): bool
    {
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $record) {
                if (empty($record) || !is_array($record)) {
                    continue;
                }

                try {
                    $result = $this->insert($tablename, $record);
                    if (!$result) {
                        // Log the error or handle it appropriately
                    }
                } catch (Exception $e) {
                    $this->handleError($e);
                    continue;
                }
            }
            return true;
        }

        return false;
    }

    public function insert(string $tablename, array $arrayval): mixed
    {
        if (is_array($arrayval)) {
            try {
                $array_ks = array_keys($arrayval);
                $array_ks_1 = implode(", ", $array_ks);

                $stmtVal = [];
                $stmtParam = [];
                foreach ($arrayval as $key => $value) {
                    $stmtVal[] = $value;
                    $stmtParam[] = ":" . $key;
                }
                $stmtParam_1 = implode(", ", $stmtParam);

                $sql = "INSERT INTO $tablename ($array_ks_1) VALUES ($stmtParam_1)";

                $stmt = $this->db->prepare($sql);

                foreach ($stmtParam as $key => $value) {
                    $stmt->bindParam($value, $stmtVal[$key]);
                }

                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $id = $this->db->lastInsertId();
                    return $id ?: $stmt->rowCount();
                }
                return false;

            } catch (Exception $e) {
                $this->handleError($e);
            }
        }
        return false;
    }

    private function handleError(Exception $e): void
    {
        if (defined('mode_env') && mode_env === 'dev') {
            echo $e . "<br>" . $e->getMessage();
        } else {
            error_log($e->getMessage());
        }
    }
}