<?php
/**
 * 获取数据库链接
 */
class DBHanlder {
    
    private $dbh;

    private $errorCode;
    
    private $errorInfo;
    
    public function __construct ($dbCfg) {
        if (!isset($this->dbh)) {
            $dsn = "mysql:dbname={$dbCfg['dbName']};host={$dbCfg['host']}:{$dbCfg['port']}";
            $user = $dbCfg['user'];
            $password = $dbCfg['passwd'];
            $dbEncodig = $dbCfg['charset'] ? $dbCfg['charset'] : "utf8";
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            );
            
            if( version_compare(PHP_VERSION, '5.3.6', '<') ){
                if( defined('PDO::MYSQL_ATTR_INIT_COMMAND') ){
                    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $dbEncodig;
                }
            }else{
                $dsn .= ';charset=' . $dbEncodig;
            }

            try {
                $this->dbh = new PDO($dsn, $user, $password);

                if( version_compare(PHP_VERSION, '5.3.6', '<') && !defined('PDO::MYSQL_ATTR_INIT_COMMAND') ){
                    $sql = 'SET NAMES ' . $dbEncodig;
                    $this->dbh[$key]->exec($sql);
                }
            } catch (PDOException $e) {
                
                if (function_exists('addLog')) {
                    $this->addLog ( 'Connection failed: ' . $e->getMessage(), 'db_error');
                }
                
               
            }

        }
    }


    //插入表
    function addTableRow($tableName, $arrRow) {

        $pdo = $this->dbh;
        $sql = "insert into $tableName set ";
        if (!$arrRow) {
            return false;
        }
        foreach ($arrRow as $key => $v) {
            if ($v != null) {
                $sql .= " `$key` = :$key ,";
            }
        }
        $sql = rtrim($sql, ",");
        $stm = $pdo->prepare($sql);
        foreach ($arrRow as $key => $v) {
            if ($v != null) {
                $stm->bindValue(":$key", $v);
            }
        }
        $exRet = $stm->execute();

        if ($exRet) {
            return $pdo->lastInsertId();
        } else {
            $str = json_encode($stm->errorInfo(), JSON_UNESCAPED_UNICODE);
            $this->addLog ( "insert [{$tableName}] failed: " . $str, "db_error" , $stm);
        }
        return false;
    }

    function getDbError(){
        $pdo = $this->dbh;
        return $pdo->errorInfo();
    }
    
    function getErrorCode() {
        $pdo = $this->dbh;
        return $pdo->errorCode();
    }

    function fetchRow($sql, $params) {
        $pdo = $this->dbh;
        $stm = $pdo->prepare($sql);
        if(!is_array($params)) {
            $params = [$params];
        }
        foreach ($params as $k => $value) {
            if (is_numeric($k)) {
                $stm->bindValue($k + 1, $value);
            } else {
                $stm->bindValue($k, $value);
            }
            
        }
        $ret = $stm->execute();
        if ($ret === false) {
            //执行出错了
            $this->addLog("sql[{$sql}],params[".var_export($params, true)."]info[".var_export($stm->errorInfo())."]", "db_error", $stm);

        }
        return $stm->fetch(PDO::FETCH_ASSOC);
    }

    function DbExec($sql, $params) {
        $pdo = $this->dbh;
        $stm = $pdo->prepare($sql);
        if(!is_array($params)) {
            $params = [$params];
        }
        foreach ($params as $k => $value) {
             if (is_numeric($k)) {
                $stm->bindValue($k + 1, $value);
            } else {
                $stm->bindValue($k, $value);
            }
        }
        $ret = $stm->execute();
        if ($ret === false) {
            //执行出错了
            $this->addLog("sql[{$sql}],params[".var_export($params, true)."]info[".var_export($stm->errorInfo())."]", "db_error", $stm);
        }
        return $ret;
    }
    function fetchRowAll($sql, $params, $fetchMode = PDO::FETCH_ASSOC) {
        $pdo = $this->dbh;
        $stm = $pdo->prepare($sql);
        if(!is_array($params)) {
            $params = [$params];
        }
        foreach ($params as $k => $value) {
             if (is_numeric($k)) {
                $stm->bindValue($k + 1, $value);
            } else {
                $stm->bindValue($k, $value);
            }
        }
        $ret = $stm->execute();
        if ($ret === false) {
            //执行出错了
     
            $this->addLog("sql[{$sql}],params[".var_export($params, true)."]info[".var_export($stm->errorInfo())."]", "db_error", $stm);

        }
        return $stm->fetchAll($fetchMode);
    }

    function db_query($sql) {
        $pdo = $this->dbh;
        return $pdo->exec($sql);
    }


    function db_quote($str) {
        $pdo = $this->dbh;
        return $pdo->quote($str);
    }

    function updateTable ($tableName,$data, $conds) {
        $pdo = $this->dbh;
        $sql = "update $tableName set ";
        if (!$data ) {
            return false;
        }
        foreach ($data as $key => $v) {
            if ($v != null) {
                $sql .= " `$key` = :$key ,";
            }
        }
        $where = " where 1 ";
        if (!is_array($conds)) {
        
           
            $where .= " and id = :id ";
            $conds = ["id" => $conds];
            
        } else {
            foreach ($conds as $key => $v) {
                if ($v != null) {
                    $where .= " and `$key` = :$key ";
                }
            }
        }
        $sql = rtrim($sql, ",");
        $stm = $pdo->prepare($sql.$where);
        foreach ($data as $key => $v) {
            if ($v != null) {
                $stm->bindValue(":$key", $v);
            }
        }
        foreach ($conds as $key => $v) {
                if ($v != null) {
                    $stm->bindValue(":$key", $v);
                }
        }
        $ret = $stm->execute();
        if ($ret === false) {
            //执行出错了
          
            $this->addLog("sql[{$sql}],params[".var_export($params, true)."]info[".var_export($stm->errorInfo())."]", "db_error", $stm);
           
        }
        return $ret;
        
    }
    
    
    private function addLog($message, $type, $stm = null) {
        //echo "[$type] {$message} <br/>";
        $excp = new DBException(Consts::ERR_DB_ERROR, "数据库异常");
        if ($stm) {
            $excp->setDbErrorInfo($stm->errorInfo());
            $excp->setDbErrorCode($stm->errorCode());
        }
        throw $excp;
    }
    
    
    public function beginTransaction() {
        $pdo = $this->dbh;
        $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
        $pdo->beginTransaction(); // 开启一个事务  
    }
    
    public function rollback() {
        $this->dbh->rollback(); // 回滚事务
    }
    
    public function commit() {
         $this->dbh->commit(); // 提交事务  
    }

}