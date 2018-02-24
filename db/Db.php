<?php

class Db
{

    public $table;
    public $database = "databases";
    public $pdo;
    public $sql;
    public $sql_select = "";
    public $sql_where = '';
    public $where_relation = 'and';
    public $where_count = 0;
    public $sql_limit = '';
    public $sql_order_by = '';
    public $sql_column = '';
    public $sql_value = '';
    public $update = '';
    public $sql_stmt = '';



    public function __construct($table)
    {
        $this->table = $table;
        $this->connect();
    }

    public function connect()
    {
        if ($this->pdo) {
            return;
        }
        $host = config("host");
        $this->pdo = new PDO("mysql:dbname=$this->database;host=$host,charset=utf8",
            config('db_username'), config('db_password'),
            [
                /* 常用设置 */
                PDO::ATTR_CASE => PDO::CASE_NATURAL, /*PDO::CASE_NATURAL | PDO::CASE_LOWER 小写，PDO::CASE_UPPER 大写， */
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, /*是否报错，PDO::ERRMODE_SILENT 只设置错误码，PDO::ERRMODE_WARNING 警告级，如果出错提示警告并继续执行| PDO::ERRMODE_EXCEPTION 异常级，如果出错提示异常并停止执行*/
                PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL, /* 空值的转换策略 */
                PDO::ATTR_STRINGIFY_FETCHES => false, /* 将数字转换为字符串 */
                PDO::ATTR_EMULATE_PREPARES => false, /* 模拟语句准备 */
            ]);
    }

    function where()
    {
        $this->where_relation = "and";
        return call_user_func_array([$this, "make_where"], func_get_args());
    }

    function or_where()
    {
        $this->where_relation = "or";
        return call_user_func_array([$this, "make_where"], func_get_args());
    }


    function make_where()
    {
        $args = func_get_args();//动态的获取传参
        //先判断有没有sql where 里面有没有东西,如果没有,在语句的最前面加上where
        if (!$this->sql_where) {
            $this->sql_where = "where";
        }
        if (count($args) == 2) {
            $this->make_where_condition($args[0], "=", $args[1]);
        } else if (count($args) == 3) {
            $this->make_where_condition($args[0], $args[1], $args[2]);
        } else {
            if (is_array($args[0])) {
                foreach ($args[0] as $col => $val) {
                    $this->sql_where .= $this->make_where_condition($col, "=", $val);
                }
            }
        }
        return $this;
    }


    function make_where_condition($col, $operator, $val)
    {
        if ($this->where_count) {
            $this->sql_where .= "$this->where_relation $col $operator '$val' ";
        } else {
            $this->sql_where .= " $col $operator '$val' ";
            $this->where_count++;
        }
    }


    function like($col, $keyword)
    {
        return $this->where($col, "like", "%$keyword%");
    }

    function or_like($col, $keyword)
    {
        return $this->or_where($col, "like", "%$keyword%");
    }

    function order_by($col, $direction = "desc")
    {
        $this->sql_order_by = "order by $col $direction ";
        return $this;
    }

    function limit($limit = 15, $offset = 0)
    {
        $this->sql_limit = "limit $offset,$limit";
        return $this;
    }


    function select($col_list = null)
    {
        if (!$col_list) {
            $this->sql_select = " * ";
        } else {
            foreach ($col_list as $col) {
                $this->sql_select .= "$col,";
            }
        }
        $this->sql_select = trim($this->sql_select, ",");
        return $this;
    }

    function prepare()
    {
        $this->sql_stmt = $this->pdo->prepare($this->sql);
    }

    function execute()
    {
        $this->prepare();
        return $this->sql_stmt->execute();
    }

    public function init_sql()
    {
        $this->sql =
        $this->sql_select =
        $this->sql_where =
        $this->sql_order_by =
        $this->sql_limit =
        $this->sql_column =
        $this->sql_value =
        $this->sql_update = '';
        $this->where_count = 0;
        $this->where_relation = 'AND';
    }

    function get_data()
    {
        return $this->sql_stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    //增
    function insert($row)
    {
        foreach ($row as $col => $val) {
            $this->sql_column .= "$col,";
            $this->sql_value .= " '$val',";
        }
        $this->sql_column = trim($this->sql_column, ",");
        $this->sql_value = trim($this->sql_value, ",");
        $this->sql = "insert into $this->table ($this->sql_column) VALUES ($this->sql_value)";
//        dd($this->sql);
        $r = $this->execute();
        $this->init_sql();
        return $r;
    }


    //删

    function delete()
    {
        $this->sql = "delete from $this->table $this->sql_where";
//        dd($this->sql);
        $r = $this->execute();
        $this->init_sql();
        return $r;
    }

    //查
    function get()
    {
        if (!$this->sql_select) {
            $this->select();
        }
        $this->sql = "select $this->sql_select from $this->table $this->sql_where $this->sql_order_by $this->sql_limit";
//        dd($this->sql);
        $this->execute();
        $this->init_sql();
        return $this->get_data();
    }

    //改

    function update($row)
    {
        unset($row['id']);
//        var_dump($row);
        foreach ($row as $col => $val) {
            $this->sql_update .= " $col = '$val',";
        }
//        var_dump($this->update);
        $this->sql_update = trim($this->sql_update, ",");
        $this->sql = " update  $this->table set $this->sql_update $this->sql_where ";
//        var_dump($this->sql);
//        dd($this->sql);
        $r = $this->execute();
        $this->init_sql();
        return $r;
    }

    function find($id)
    {
        $id=(int) $id;
            $r = $this->where("id", $id)
                ->get();
            return $r;

    }


    function find_user_id($user_id){
        $r = $this->where("user_id", $user_id)
            ->get();
        return $r;

    }

    function table_column()
    {
        $this->sql = "desc $this->table";
        $this->execute();
        $this->init_sql();
        $r = $this->get_data();
        return $r;
    }


    function all_column()
    {
        $row = [];
        foreach ($this->table_column() as $col) {
            $row[] = $col["Field"];
        }
        return $row;
    }


    public function last_id()
    {//返回最后一个插入的数据的id;
        return $this->pdo->lastInsertId();
    }


    public function exist()
    {
        $this->limit(1);
        return (bool)$this->get();
    }


}