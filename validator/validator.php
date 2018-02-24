<?php
//数据验证
//*****************************************
class Validator extends Db
{


    function __construct($table)
    {
        parent::__construct($table);
        $this->connect();
    }

    //分解传进来的字符串

    function parse_rules($rules)
    {
        $rules = explode("|", $rules);
        $arr = [];
        foreach ($rules as $rule) {
            //把每一条的键和值分来写进一个数组
            //比如:title-max-length:24变为["title-max-length","24"]
            $rule_arr = explode(':', $rule);
            if (count($rule_arr) == 1) {
                $arr[$rule_arr[0]] = true;
            } else {
                $arr[$rule_arr[0]] = $rule_arr[1];
            }
        }
        return $arr;
        //传进来:max_length:24|min_length:3|unique:title
        //返回数组:["max_length"=> "24","min_length"=>"3","unique"=> "title"]
    }


    function validate_rules($col, $rules, &$error = null)
    {
        //先判断rules是否是是字符串,
        //如果是,分解他,变成规则数组
        if (is_string($rules)) {
            $rules = $this->parse_rules($rules);
        }
        //如果没有返回结果,不需要验证验证规则,直接返回true
        if (!$rules) return true;
///****/1/@
        foreach ($rules as $type => $param) {
            //把下列的判断函数名字赋值给method

            $method = "valid_" . $type;
            //执行判断函数
            $r = $this->$method($col, $param);
            //如果返回false
            if (!$r) {
                //把错误信息赋值给$error;
                $error = "invalid_" . $type;
                return false;
            }
        }
        return true;
    }


    //判断最大长度
    function valid_max_length($val, $max)
    {
        //不管是不是字符串,先转换一遍,以免出错
        $val = (string)$val;
        return strlen($val) <= $max;
        //判断val的长度是否小于或者等于最大长度数,返回真假
    }

    //判断是否是否符合最小长度
    function valid_min_length($val, $min)
    {
        $val = (string)$val;
        return strlen($val) >= $min;
    }

    //判断是否在最大和最小长度之间
    function valid_between($val, $min, $max)
    {
        return
            $this->valid_max_length($val, $max) &&
            $this->valid_min_length($val, $min);
    }


    //判断是否是一个整数
    function valid_integer($val)
    {
        if (is_numeric($val)) {
            return false;
        }
        $val = (string)$val;
        $r = strpos($val, ".") === false;
        return $r;
    }

    //判断是否是一个数字类型
    function valid_numeric($val)
    {
        $r = is_numeric($val);
        return $r;
    }

    //判断是否是一个正数
    function valid_positive($val)
    {
        $val = (float)$val;
        return $val >= 0;
    }


    //判断正则表达式
    public function valid_regex($val, $reg)
    {
        return !!preg_match($reg, $val, $r);
    }


    //判断一个值是否在一个数组中
    public function valid_in($val, $arr)
    {
        return in_array($val, $arr);
    }


    //判断一个值是否存在
    public function valid_exist($val, $col)
    {
        return $this->where($col, $val)->exist();
    }


    //判断一个值是否是独有的
    public function valid_unique($val, $col)
    {
        return !$this->valid_exist($val, $col);
    }


}