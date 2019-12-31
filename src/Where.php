<?php

namespace decimalism\param;

class Where 
{
    protected static $data;
    protected $key;
    protected $value;
    protected $return_data = [];
    protected $alias_array = [];

    public function __construct($data)
    {
        self::$data = $data;
    }

    /**
     * 运行函数
     * @param $method string 规则
     * @param $item string 字段
     **/
    public function run($method,$items)
    {
        $result = [];
        $items  = $this->toArray($items);
        foreach($items as  $value) {
            if(isset(self::$data[$value]) && '' !== trim(self::$data[$value])) {
                if($this->getAlias($value)) {
                    $name = $this->getAlias($value);
                    $this->return_data[] = $this->$method($name, self::$data[$value]);
                } else {
                    $this->return_data[] = $this->$method($value,self::$data[$value]);
                }
            }
        }
    }

    /**
     * 获取返回数据
     **/
    public function getData()
    {
        return $this->return_data;
    }

    /**
     * 别名
     **/
    public function alias(array $data)
    {
        $this->alias_array = $data;
        return $this;
    }

    /**
     * 获取别名值
     **/
    public function getAlias(string $value) {
        if(!empty($this->alias_array[$value])) {
            return $this->alias_array[$value];
        }
        return false;
    }

    /**
     * 恒等于
     **/
    public function eq(string $item, string $value) : array
    {
        return [$item, '=',  $value];
    }

    /**
     * 不等于
     **/
    public function neq(string $item, string $value) : array
    {
        return [$item, '<>',  $value];
    }

    /**
     * 小于
     **/
    public function lt(string $item, string $value) : array
    {
        return [$item, '<', $value];
    }

    /**
     * 小于等于
     **/
    public function elt(string $item, string $value) : array
    {
        return [$item, '<=', $value];
    }

    /**
     * 大于
     **/
    public function gt(string $item, string $value) : array
    {
        return [$item, '>', $value];
    }
    /**
     *  大于等于
     **/
    public function egt(string $item, string $value) : array
    {
        return [$item, '>=', $value];
    }

    /**
     * 模糊
     **/
    public function like(string $item, string $value) : array
    {
        return [$item, 'LIKE', "%{$value}%"];
    }

    /**
     * 左模糊
     **/
    public function llike(string $item, string $value) : array
    {
        return [$item, 'LIKE', "%{$value}"];
    }

    /**
     * 右模糊
     **/
    public function rlike(string $item, string $value) : array
    {
        return [$item,  'LIKE', "{$value}%"];
    }

    /**
     * in
     **/
    public function in(string $item, string $value) : array
    {
        return [$item,  'IN', $value];
    }


    /**
     * 转换为数组
     **/
    protected function toArray($value) : array
    {
        if(is_string($value)) {
            $value = explode(',',$value);
        }

        return $value;
    }

}
