<?php
import("db/Db");
import('validator/validator');


class Model extends Db
{

    public $filled = [];

    //需要验证的传参规则
    public $rule;
//validator实例
    public $validator;

    public function __construct()
    {
        parent::__construct($this->table);
        $this->connect();
        //实例化validator
        $this->validator = new Validator($this->table);
    }

//把传进来的参数过滤一遍
    function safe_fill($row)
    {
        $filtered = [];


//        dd($this->all_column());
        foreach ($this->all_column() as $col) {
            $val = @$row[$col];


            if (!$val) {
                continue;
            } else {
                $filtered[$col] = $val;
            }
        }
        //把过滤的合法参数给fill执行
        $this->fill($filtered);
//        if (count($filtered)==0){
//            json_die(e("键值不对"));
//        }
        //执行完毕
        return $this;
    }

    function fill($filtered)
    {

        //把要写入数据库的数据,赋值给$filled;
        $this->filled = $filtered;

    }

    function validate_filled(&$msg)
    {
        foreach ($this->filled as $col => $val) {
            //$col:::title
            //$val::锤子手机
            //rule[$col]::title对应的规则
            //$validator_msg:::返回的信息,现在为空
            $r = $this->validator->validate_rules($val, @$this->rule[$col], $validator_msg);
            if (!$r) {
                $msg[0] = $validator_msg;
                return false;
            }
        }
        return true;
    }


    public function save(&$msg = [])
    {

        if (!($filled = &$this->filled)) {
            $msg = "键值不对";
            return FALSE;
        }
        //从filled借值
//        dd($filled);


//        数据验证函数
        $valid = $this->validate_filled($msg);

        if (!$valid) {
            return false;
        }

        $is_update = (bool)$id = @$filled["id"];
        //查看是否有id,如果有id就是更新,没有id就是增加

        //user方法:
        //用户输入的密码需要经过数据验证以后再进行加密存储
        //再此设立一个函数,判断use的类里面一个定义方法,在方法里面设置密码加密
        //上面的数据验证通过以后,在此处判断方法进行加密,如果数据验证不通过,在数据验证层面直接结束函数.不会执行到数据加密.


        //检查这个方法是否存在
        //this是实例化的类，谁调用的model,就检查那个类
        //cat,和product里面没有定义这个before_save类.只对user有效
        if (method_exists($this, 'before_save'))
            $this->before_save();


        //如果更新,判断id是否存在
        if ($is_update) {
            $this->where("id", $id);
            if (!$this->get()) {
                $msg["id"] = "not_exist";
                return false;
            }
        }

        if ($is_update) {
            //如果更新,添加更新时间
            if (!@$filled["updated_at"]) {
                $this->set_data("updated_at");
            }
            //执行更新
            $this->where("id", $filled["id"]);
             return $r = $this->update($filled) ? $filled['id'] : false;
        } else {
            //如果没有创建是时间,增加创建时间
            if (!@$filled["created_at"]) {
                $this->set_data("created_at");
            }
            //如果更新,返回最后一个数据的id
            if ($this->insert($filled)) {

                return $this->last_id();
            }
            return false;
        }
    }


    //增加创建时间和更新时间
    function set_data($col, $datetime = null)
    {
        //调用函数时传入需要增加的时间键,如果时间键没有数据,就默认等于当前时间
        $this->filled[$col] = $datetime ?: date('Y-m-d H:i:s');
        return $this;
    }


    public function page($page, $limit = 15)
    {
        $this->limit($limit, ($page - 1) * $limit);
        return $this;
    }

}
