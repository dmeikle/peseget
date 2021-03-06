<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/5/2016
 * Time: 7:20 PM
 */

namespace Gossamer\Pesedget\Database;

use Gossamer\Pesedget\Entities\AbstractEntity;
use Gossamer\Pesedget\Database\ColumnMappings;
use Gossamer\Pesedget\Database\EntityManager;
use Monolog\Logger;


/**
 * Class PDOConnection
 * @package Gossamer\Pesedget\Database
 *
 * @requirements:
 * sudo apt-get install php5-odbc
 *
 */
class PDOConnection implements ConnectionInterface, GossamerDBConnection
{

    protected $host;
    protected $user;
    protected $pass;
    protected $db;
    private $lastQuery = '';
    protected $logger = null;
    protected $stack;
    private $rows;
    protected $conn = null;
    private $rowCount = 0;

    public function __construct(array $credentials = null) {
        if (!is_null($credentials)) {
            $this->initCredentials($credentials);
        } else {
            //uh-oh... no db credentials exist.
            $this->initCredentials(EntityManager::getInstance()->getCredentials());
        }
    }

    public function __destruct() {
        $this->logger = null;
        $this->conn = null;
    }

    private function initCredentials(array $credentials) {

        $this->user = $credentials['username'];
        $this->pass = $credentials['password'];
        $this->db = $credentials['dbName'];
        $this->host = $credentials['host'];
    }

    public function getRowCount() {
        return $this->rowCount;
    }

    public function setLogger(Logger $logger) {
        $this->logger = $logger;
    }

    public function getAllRowsAsArray() {

        if (isset($this->stack)) {
            return $this->stack;
        }

        $this->stack = array();

        while ($ra = mysqli_fetch_array($this->rows)) {
            array_push($this->stack, $ra);
        }

        unset($this->rows);

        return $this->stack;
    }

   
    public function beginTransaction() {
        $this->getConnection();
        mssql_query("BEGIN TRANSACTION");
    }

    public function commitTransaction() {
        $this->getConnection();
        mssql_query("COMMIT");
    }

    public function rollbackTransaction() {
        $this->getConnection();
        mssql_query("ROLLBACK");
    }



//        $dsn = "dblib:host=$host:1433;dbname=$database;charset=utf8";
//        $dblink = new \PDO ($dsn, $user, $pass);
//        $statement = $dblink->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
//        $statement->execute();
//        //$result = $this->container->get('EntityManager')->getConnection('BHDB4')->query($query);
//        $result = $statement->fetchAll();


    public function getConnection() {
        if (is_null($this->conn) || !($this->conn)) {
            $dsn = 'dblib:host=' . $this->host . ':1433;dbname=' . $this->db . ';charset=utf8';
            $this->conn = new \PDO($dsn, $this->user, $this->pass);

            if (is_bool($this->conn)) {
                throw new \Exception('unable to connect to db with provided credentials');
            }
        }

        return $this->conn;
    }

    public function preparedQuery($query, array $params, $fetch = true) {

        $this->lastQuery = $query;

        //mysql_select_db($this->db);
        if (!is_null($this->logger)) {
            $this->logger->addDebug(utf8_decode($query));
        }

        $stmt = $this->getConnection()->prepare($query);
        //with bind() the first element must be a list of datatypes that correspond
        //to each of the remaining elements of the array.
        //eg: (ssi, 'dave', 'meikle', '10')
//        i - integer
//        d - double
//        s - string
//        b - BLOB
        //stmt does not accept an array so we'll bypass with CUFA method
        $bindNames[] = array_shift($params);
        for ($i = 0; $i < count($params); $i++) {
            $bindName = 'bind' . $i;
            $$bindName = $params[$i];
            $bindNames[] = &$$bindName;
        }

        call_user_func_array(array($stmt, 'bind_param'), $bindNames);
        $results = $stmt->execute();

        //since we are using PDO we need to handle this differently than mysqli
        if (strtolower(substr($query, 0, 6)) == 'delete') {
            return 0;
        } elseif (strtolower(substr($query, 0, 6)) == 'insert') {
            return $stmt->insert_id;
        } elseif (strtolower(substr($query, 0, 6) == 'update')) {
            return;
        } else {

            $stmt->store_result();
            $this->rowCount = $stmt->num_rows;
        }

        $retval = $this->fetchArray($stmt);
        unset($stmt);

        return $retval;
    }

    protected function fetchArray($stmt) {
        $meta = $stmt->result_metadata();
        while ($field = $meta->fetch_field()) {
            $params[] = &$row[$field->name];
        }

        call_user_func_array(array($stmt, 'bind_result'), $params);
        $result = array();
        while ($stmt->fetch()) {
            foreach ($row as $key => $val) {
                $c[$key] = $val;
            }
            $result[] = $c;
        }

        $stmt->close();

        return $result;
    }

    public function query($query, $fetch = true) {

        $this->lastQuery = $query;

        //mysql_select_db($this->db);
        if (!is_null($this->logger)) {
            $this->logger->addDebug(utf8_decode($query));
        }


        $statement = $this->getConnection()->prepare($query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $statement->execute();

        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (strtolower(substr($query, 0, 6)) == 'delete') {
            return 0;
        } elseif (strtolower(substr($query, 0, 6)) == 'insert') {
            return $this->conn->lastInsertId();
        } elseif (strtolower(substr($query, 0, 6) == 'update')) {
            return;
        } else {
            $this->rowCount = $statement->rowCount();
        }


        return $results;
    }


    public function getTableColumnMappings(AbstractEntity $entity) {
        if (!$entity instanceof AbstractEntity) {
            throw new \RuntimeException('DBConnection::getTableColumnMappings - entity my be instance of AbstractEntity');
        }
        
        $mappings = new ColumnMappings($this);
        $columns = $mappings->getTableColumnList($entity->getTableName());
        return $columns;
    }

    public function getLastQuery() {
        return $this->lastQuery;
    }

    public function getCredentials() {
        return array('username' => $this->user,
            'password' => $this->pass,
            'dbName' => $this->db,
            'host' => $this->host);
    }

}

/*
 * I worked on a project with a MS SQL server 2008 containing data of NVARCHAR type in multiple languages,
including asian characters. It is a known issue, that the PHP MSSQL functions are not able to retrieve
unicode data form NVARCHAR or NTEXT data fields.

I spent some time searching for possible solutions and finaly found a work arround, that provides correct
display of latin and asian fonts from a NVARCHAR field.

Do a SQL query, while you convert the NVARCHAR data first to VARBINARY and then to VARCHAR

SELECT
CONVERT(VARCHAR(MAX),CONVERT(VARBINARY(MAX),nvarchar_col)) AS x
FROM dbo.table

While you fetch the result set in PHP, use the iconv() function to convert the data to unicode

<?php $x = iconv("UCS-2LE","UTF-8",$row['x']); ?>

Now you can ouput the text to UTF-8 encoded page with the correct characters.

This workarround did run on IIS 6.0 with PHP 5.2.6 running as FastCGI.
 */