<?php
/**
 * 数据库连接类(PDO), 使用单件模式只实例化一次
 *
 * @version $VersionId$ @ $UpdateTime$
 * @author 404 <why404@gmail.com>
 * @copyright Copyright (c) 2011-2012 404 <why404@gmail.com>
 * @license MIT License {@link http://www.opensource.org/licenses/mit-license.php}
 * @package Core
 */

final class Core_Db {
    /**
     * _instance
     *
     * @desc Singleton instance
     * @access private
     * @var object
     */
    private static $_instance = null;

    /**
     * _dbh
     *
     * @desc 数据库连接句柄
     * @access private
     * @var Object
     */
    private static $_dbh = null;

    /**
     * __construct
     *
     * @desc 构造器
     * @access private
     * @return void
     */
    private function __construct($dbOptions)
    {
        $dsn = $dbOptions["adapter"] . ':host=' . $dbOptions["host"] . ';dbname=' . $dbOptions["dbname"];
        try
        {
            self::$_dbh = new PDO($dsn, $dbOptions["username"], $dbOptions["password"], array(PDO::ATTR_PERSISTENT => $dbOptions["use_pconnect"]));
            self::$_dbh->query("SET NAMES '".$dbOptions["charset"]."'");
            if (strtolower($dbOptions['adapter']) == 'mysql' && (true === $dbOptions["use_buffered_query"]))
                self::$_dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            if ($dbOptions['throw_exception'])
                self::$_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e)
        {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * getInstance
     *
     * @desc 获得PDO对象（唯一，只实例化一次）
     * @param array $dbOptions
     * @access public
     * @return object
     */
    public static function getInstance(array $dbOptions)
    {
        if (null === self::$_instance)
            self::$_instance = new self($dbOptions);
        return self::$_instance;
    }

    /**
     * getConnection
     *
     * @desc 取得数据库连接句柄
     * @access public
     * @return object
     */
    public function getConnection()
    {
        return self::$_dbh;
    }

    /**
     * getAll
     *
     * @desc 查询数据
     * @param string $sql
     * @access public
     * @return array
     */
    public function getAll($sql)
    {
        try
        {
            return $this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * getOne
     *
     * @desc 只查询一条数据
     * @param string $sql
     * @access public
     * @return array
     */
    public function getOne($sql)
    {
        try
        {
            return $this->getConnection()->query($sql)->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * insert
     *
     * @desc 写入数据
     * @param string $sql
     * @access public
     * @return int
     */
    public function insert($sql)
    {
        try
        {
            $this->getConnection()->exec($sql);
            return $this->getConnection()->lastInsertId();
        }
        catch(PDOException $e)
        {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * execute
     *
     * @desc 执行 UPDATE | DELETE 操作
     * @param string $sql
     * @access public
     * @return void
     */
    public function execute($sql)
    {
        try
        {
            return $this->getConnection()->exec($sql);
        }
        catch(PDOException $e)
        {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * count
     *
     * @desc 统计行数
     * @param string $sql
     * @access public
     * @return int
     */
    public function count($sql)
    {
        try
        {
            return $this->getConnection()->query($sql)->fetchColumn();
        }
        catch(PDOException $e)
        {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * __destruct
     *
     * @desc 释放数据库连接句柄
     * @access public
     * @return void
     */
    public function __destruct()
    {
        self::$_dbh = null;
    }

}
