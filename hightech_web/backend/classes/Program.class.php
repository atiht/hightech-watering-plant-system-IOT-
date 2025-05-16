<?php
class Program extends Database
{
    public $MonthNameTH = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
    public $MonthLongNameTH = ["", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม",  "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];

    public function clearTransaction()
    {
        $this->dbConn = null;
    }
    public function insert($tbl, $param = [])
    {
        try {
            $this->dbConn->beginTransaction();
            $cols = implode(', ', array_keys($param));
            $placeholders = ':' . implode(', :', array_keys($param));
            $sql = "INSERT INTO $tbl ($cols) VALUES ($placeholders)";
            $stmt = $this->dbConn->prepare($sql);

            // Binding parameters
            foreach ($param as $col => $val) {
                if (gettype($val) == "integer") {
                    $stmt->bindValue(":$col", $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(":$col", $val);
                }
            }

            if ($stmt->execute()) {
                if ($this->dbConn->lastInsertId() > 0) {
                    $r = [
                        'status' => 'success',
                        'type' => 'insert',
                        'lastInsertID' => $this->dbConn->lastInsertId()
                    ];
                    $this->dbConn->commit();
                } else {
                    $this->dbConn->rollBack();
                    $r = [
                        'status' => 'error',
                        'errType' => 'insert',
                        'type' => 'insert',
                        'msgErr' => 'No rows inserted'
                    ];
                }
            } else {
                $this->dbConn->rollBack();
                $r = [
                    'status' => 'error',
                    'errType' => 'execute',
                    'type' => 'execute',
                    'msgErr' => 'No rows inserted'
                ];
            }
        } catch (PDOException $e) {
            $this->dbConn->rollBack();
            $r = [
                'status' => 'error',
                'errType' => 'tryCatch',
                'type' => 'tryCatch',
                'msgErr' => $e->getMessage()
            ];
        }
        return $r;
    }

    public function update($tbl, $param = [], $condition = "", $bindParam = [])
    {
        try {
            $this->dbConn->beginTransaction();
            $setClauses = [];
            foreach ($param as $col => $val) {
                $setClauses[] = "$col = :$col";
            }
            $setClause = implode(', ', $setClauses);
            $sql = "UPDATE $tbl SET $setClause";
            if (!empty($condition)) {
                $sql .= " WHERE $condition";
            }
            $stmt = $this->dbConn->prepare($sql);
            // Binding parameters
            foreach ($param as $col => $val) {
                if (gettype($val) == "integer") {
                    $stmt->bindValue(":$col", $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(":$col", $val);
                }
            }
            // bindparam condition
            foreach ($bindParam as $key => $val) {
                if (gettype($val) == "integer") {
                    $stmt->bindValue("$key", $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue("$key", $val);
                }
            }
            $stmt->execute();
            $this->dbConn->commit();
            $r = [
                'status' => 'success',
                'type' => 'update'
            ];
        } catch (PDOException $e) {
            $this->dbConn->rollBack();
            $r = [
                'status' => 'error',
                'errType' => 'updateBindParam',
                'type' => 'tryCatch',
                'error' => $e->getMessage()
            ];
        }
        return $r;
    }

    public function selectAll($tbl, $cols = "*", $condition = "", $orderBy = "", $limit = "", $bindParam = [])
    {
        try {
            $sql = "SELECT $cols FROM $tbl";
            if (!empty(trim($condition))) {
                $sql .= " WHERE $condition";
            }
            if (!empty(trim($orderBy))) {
                $sql .= " ORDER BY $orderBy";
            }
            if (!empty(trim($limit))) {
                $sql .= " LIMIT $limit";
            }
            $stmt = $this->db()->prepare($sql);
            if (count($bindParam) > 0) {
                foreach ($bindParam as $key => $val) {
                    if (gettype($val) == "integer") {
                        $stmt->bindValue($key, $val, PDO::PARAM_INT);
                    } else {
                        $stmt->bindValue($key, $val, PDO::PARAM_STR);
                    }
                }
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return [
                'status' => 'success',
                'data' => $result
            ];
        } catch (PDOException $e) {
            // Return error response
            return [
                'status' => 'error',
                'type' => 'tryCatch',
                'msgErr' => $e->getMessage()
            ];
        }
    }
    public function selectOne($tbl, $cols = "*", $condition = "", $orderBy = "", $limit = "", $bindParam = [])
    {
        try {
            $sql = "SELECT $cols FROM $tbl";
            if (!empty(trim($condition))) {
                $sql .= " WHERE $condition";
            }
            if (!empty(trim($orderBy))) {
                $sql .= " ORDER BY $orderBy";
            }
            if (!empty(trim($limit))) {
                $sql .= " LIMIT $limit";
            }
            $stmt = $this->db()->prepare($sql);
            if (count($bindParam) > 0) {
                foreach ($bindParam as $key => $val) {
                    if (gettype($val) == "integer") {
                        $stmt->bindValue($key, $val, PDO::PARAM_INT);
                    } else {
                        $stmt->bindValue($key, $val, PDO::PARAM_STR);
                    }
                }
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'status' => 'success',
                'data' => $result
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'errType' => 'selectOneBindParam',
                'type' => 'tryCatch',
                'msgErr' => $e->getMessage()
            ];
        }
    }

    public function insertSQL($tbl, $param = [])
    {
        $cols = implode(', ', array_keys($param));
        $placeholders = ':' . implode(', :', array_keys($param));
        $sql = "INSERT INTO $tbl ($cols) VALUES ($placeholders)";
        return $sql;
    }
    public function selectSQL($tbl, $cols = "*", $condition = "", $orderBy = "", $limit = "", $bindParam = [])
    {
        $sql = "SELECT $cols FROM $tbl";
        if (!empty(trim($condition))) {
            $sql .= " WHERE $condition";
        }
        if (!empty(trim($orderBy))) {
            $sql .= " ORDER BY $orderBy";
        }
        if (!empty(trim($limit))) {
            $sql .= " LIMIT $limit";
        }
        return $sql;
    }

    public function updateSQL($tbl, $param = [], $condition = "", $bindParam = [])
    {
        $setClauses = [];
        foreach ($param as $col => $val) {
            $setClauses[] = "$col = :$col";
        }
        $setClause = implode(', ', $setClauses);
        $sql = "UPDATE $tbl SET $setClause";
        if (!empty($condition)) {
            $sql .= " WHERE $condition";
        }
        return $sql;
    }

    public function UploadFile($File, $Path, $AllowedExtensions, $UserToUpload, $ReferenceName)
    {
        if (!is_dir($Path)) {
            return [
                'status' => 'error',
                'errType' => 'path',
                'type' => 'path',
                'msg' => 'not found path'
            ];
        }
        if (is_null($AllowedExtensions)) {
            $AllowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        }
        $FileTmp = $File['tmp_name']; //tmp file
        $FileError = $File['error']; // error file
        $FileExt = strtolower(pathinfo($File['name'], PATHINFO_EXTENSION)); //file type
        if (in_array($FileExt, $AllowedExtensions)) {
            if ($FileError === 0) {
                $FileName = "{$ReferenceName}_{$UserToUpload}_" . time() . ".{$FileExt}";
                if (move_uploaded_file($FileTmp, "{$Path}{$FileName}")) {
                    return [
                        'status' => 'success',
                        'filename' => $FileName
                    ];
                } else {
                    return [
                        'status' => 'error',
                        'type' => "upload",
                        'errType' => "upload"
                    ];
                }
            } else {
                return [
                    'status' => 'error',
                    'type' => 'fileErr',
                    'errType' => 'fileErr'
                ];
            }
        } else {
            return [
                'status' => 'error',
                'type' => 'fileType',
                'errType' => 'fileType'
            ];
        }
    }

    public function SetAccessCheckRedirectPath($RedirectPath)
    {
        $MM_qsChar = "?";
        $MM_referrer = $_SERVER['PHP_SELF'];
        if (strpos($RedirectPath, "?")) {
            $MM_qsChar = "&";
        }
        if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) {
            $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
        }
        $RedirectPath = $RedirectPath . $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
        return $RedirectPath;
    }
    public function XssProtection($var)
    {
        return isset($var) && !empty(trim($var)) ? htmlspecialchars(trim($var), ENT_QUOTES, "UTF-8") : null;
    }

    public function MD5Token()
    {
        return md5(uniqid() . mt_rand());
    }
    public function ConvertDate($var, $DefaultDateType, $ConvertType)
    {
        if ($DefaultDateType == "Y-m-d" && $ConvertType == "d/m/Y") {
            $var = explode("-", $var);
            return "{$var[2]}/{$var[1]}/{$var[0]}";
        }
        if ($DefaultDateType == "d/m/Y" && $ConvertType == "Y-m-d") {
            $var = explode("/", $var);
            return "{$var[2]}-{$var[1]}-{$var[0]}";
        }
        return $var;
    }
    public function DateThai($Date, $DateType, $ResultMonthType = "ShortMonth", $ResultYearType = "ShortYear")
    {
        if ($DateType == "Y-m-d") {
            $Date = explode("-", $Date);
        } elseif ("d/m/Y") {
            $Date = explode("/", $Date);
        } else {
            return "Invalid DateType";
        }
        if (count($Date) != 3) {
            return "Invalid Date";
        }
        // แยกวันที่ เดือน และปี
        if ($DateType == "Y-m-d") {
            $year = $Date[0];
            $month = $Date[1];
            $day = $Date[2];
        } elseif ($DateType == "d/m/Y") {
            $day = $Date[0];
            $month = $Date[1];
            $year = $Date[2];
        }
        if ($ResultYearType == "ShortYear") {
            $thaiYear = $year + 543;
            $thaiYear = substr($thaiYear, -2);
        } elseif ($ResultYearType == "FullYear") {
            $thaiYear = $year + 543;
        } else {
            return "Invalid ResultYearType";
        }
        $thaiMonths = [
            "01" => ["มกราคม", "ม.ค."],
            "02" => ["กุมภาพันธ์", "ก.พ."],
            "03" => ["มีนาคม", "มี.ค."],
            "04" => ["เมษายน", "เม.ย."],
            "05" => ["พฤษภาคม", "พ.ค."],
            "06" => ["มิถุนายน", "มิ.ย."],
            "07" => ["กรกฎาคม", "ก.ค."],
            "08" => ["สิงหาคม", "ส.ค."],
            "09" => ["กันยายน", "ก.ย."],
            "10" => ["ตุลาคม", "ต.ค."],
            "11" => ["พฤศจิกายน", "พ.ย."],
            "12" => ["ธันวาคม", "ธ.ค."]
        ];

        if ($ResultMonthType == "ShortMonth") {
            $thaiMonth = $thaiMonths[$month][1];
            return "{$day} {$thaiMonth} {$thaiYear}";
        } elseif ($ResultMonthType == "FullMonth") {
            $thaiMonth = $thaiMonths[$month][0];
            return "{$day} {$thaiMonth} {$thaiYear}";
        } else {
            return "Invalid ResultType";
        }
    }
}
