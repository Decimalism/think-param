<?php

namespace decimalism\param;

class Process
{
    /**
     * 返回信息
     * @var array
     **/
    protected static $return_data = [
        'where' => [],
        'order' => null,
        'limit' => null
    ];//返回数据

    protected $data        = [];
    protected $where       = [];
    protected $order       = null;
    protected $alias_array = [];

    //固定排序字段前缀
    protected $order_prefix = 'or_';
    //是否分页
    protected static $is_limit = true;
    protected static $limit_config = [
        'type'        => 'page', // page 页码分页方式 | length 起始分页方式
        'start_attr'  => 'page', // 开始/页码的 字段名
        'length_attr' => 'limit'// 开始/页码的 长度名
    ];

    /**
     *  架构函数
     *  @access public
     *  @param  array  $where 筛选规则
     *  @param  string $order 排序规则
     **/

    public function __construct(array $where = [], string $order = null)
    {
        $this->where = $where;
        $this->order = $order;

        self::$limit_config = [
            'type'        => \Env::get('PARAM.LIMIT_YTPE', 'page'),
            'start_attr'  => \Env::get('PARAM.LIMIT_START_ATTR', 'page'),
            'length_attr' => \Env::get('PARAM.LIMIT_LENGTH_ATTR', 'limit')
        ];
        $this->order_prefix = \Env::get('PARAM.ORDER_PREFIX', 'or_');
    }

    public function setOrderPrefix(string $pre) 
    {
        $this->order_prefix = $pre;
    }

    /**
     *  创建一个查询器类
     *  @access public 
     *  @param  array  $where 筛选规则
     *  @param  string $order 排序规则
     *  @return ThinkProcess
     **/
    public static function init(array $where = [], string $order = null)
    {
        return new self($where, $order);
    }

    /**
     * 处理函数
     * @param array $data 处理数据
     **/
    public function run(array $data = [], array $where = [],string $order = null)
    {

        $this->data  = $this->data ? : $data;
        $this->where = $this->where ? : $where;
        $this->order = $this->order ? : $order;

        $this->where($this->where);
        $this->order($this->order);
        $this->limit();

        return $this;
    }

    /**
     * 获取数据
     **/
    public static function getData()
    {
        return self::$return_data;
    }

    /**
     * 别名设置
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
     * 设置分页状态
     **/
    public static function setIsLimit(bool $value)
    {
        self::$is_limit = $value;
        return new self;
    }

    /**
     * 查询条件处理
     **/
    protected function where(array $rule) 
    {
        if(empty($rule)) {
            return $this;
        }

        $Where = new Where($this->data);
        $Where->alias($this->alias_array);

        foreach($rule as $key => $value) {
            if(method_exists($Where, $key)) {
                $Where->run($key, $value);
            }
        }

        self::$return_data['where'] = $Where->getData();
        return $this;
    }

    /**
     * 排序处理
     **/
    protected function order($items) 
    {
        $order_array = explode(',',$items);
        $order_value_array = [
            0      => 'ASC',
            1      => 'DESC',
            'asc'  => 'ASC',
            'desc' => 'DESC',
            'ASC'  => 'ASC',
            'DESC' => 'DESC'
        ];

        $result = '';
        foreach($order_array as $key => $value) {

            if(isset($this->data[$value]) && '' !== trim($this->data[$value])) {

                if(empty($order_value_array[$this->data[$value]])) {
                    continue;
                }

                if(0 === strpos($value, $this->order_prefix)) {
                    $result .= substr($value, strlen($this->order_prefix)) . ' ' . $order_value_array[$this->data[$value]]. ',';;
                } else {

                    if($this->getAlias($value)) {
                        $name = $this->getAlias($value);
                        $result .= $name.' ' . $order_value_array[$this->data[$value]]. ',';
                    } else {
                        $result .= $value.' ' . $order_value_array[$this->data[$value]] . ',';
                    }
                }

            }
        }
        self::$return_data['order'] = rtrim($result, ',');
    }

    /**
     * 分页处理
     **/
    protected  function limit()
    {
        if(self::$is_limit) {
            $type        = self::$limit_config['type'];
            $start_attr  = self::$limit_config['start_attr'];
            $length_attr = self::$limit_config['length_attr'];
            
            if(!isset($this->data[$start_attr])) {
                $start = 1;
            } else {
                $start  = (int)$this->data[$start_attr];
                $start = $start <= 0 ? 1 : $start;
            }

            if(!isset($this->data[$length_attr])) {
                $length = 10;
            } else {
                $length = (int) $this->data[$length_attr];
                $length = $length <= 0 ? 10 : $length;
            }

            if('page' ===  $type) {
                self::$return_data['limit'] = ($start - 1) * $length . ','. ( $length ?? 10 );
            } else {
                self::$return_data['limit'] = "$start,$length";
            }
        } else {
            self::$return_data['limit'] = null;
        }
        return $this;
    }

}
