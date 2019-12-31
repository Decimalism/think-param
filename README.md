# Process 类说明

------

基于thinkphp5.1，封装数据库列表查询的参数，包括where方法，order方法，limit方法的参数处理

### 调用方法
```php
<?php
  
    $Process = new  \decimalism\param\Process;

    $request = [
        'title'       => '啥',
        'name'        => '',
        'or_create_time' => '1',
        'category_id' => 1,
        'test'        => 'aa',
        'page'        => 2,
        'limit'       => 20
    ];
  
    //筛选条件规则，作用于where方法
    $rule = [
        'eq'   => 'category_id',
        'like' => 'title,name' //多个字段用,分割
    ];

    //排序规则， 作用于order方法
    $order = 'or_create_time';//多个字段用,分割
    

    //运行
    $Process->run($request, $rule, $order);
    //获取结果集
    $param_data = $Process->getData();

    //打印结果如下
    array(3) {
        ["where"] => array(2) {
            [0] => array(3) {
                [0] => string(11) "category_id"
                    [1] => string(1) "="
                        [2] => string(1) "1"
            }
            [1] => array(3) {
                [0] => string(5) "title"
                    [1] => string(4) "LIKE"
                        [2] => string(5) "%啥%"
            }
        }
        ["order"] => string(16) "create_time DESC"
            ["limit"] => string(5) "20,20"
    }
    
    //查询
    $this->where($param_data['where'])->order($param_data['order']) ->limit($param_data['limit'])->select(); 
    
```

### 别名设置

```php
<?php
    $Process = new  \decimalism\param\Process;
    //别名设置


    $request = [
        'title'       => '啥',
        'name'        => 'a',
        'create_time' => '1',
        'category_id' => 1,
        'q'        => 'aa',
        'page'        => 2,
        'limit'       => 20

    ];

    $alias = [
        'q' => 'title|name',
        'name' => 'alias_name',
        //设置了排序前缀的以排序前缀优先 别名不起作用 例如别名设置or_create_time => 'cteate' 因为排序前缀是or_ 则别名不起作用
        'create_time' => 'create'
    ];
  
    //筛选条件规则，作用于where方法
    $rule = [
        'eq'   => 'category_id,name',
        'like' => 'q' //多个字段用,分割
    ];

    //排序规则， 作用于order方法
    $order = 'create_time';//多个字段用,分割

    $Process = new  \decimalism\param\Process;

    //设置别名
    $Process->alias($alias);
    //运行 
    $Process->run($request, $rule, $order);
    //获取结果集
    $param_data = $Process->getData();

    
    //打印结果集
    array(3) {
        ["where"] => array(3) {
            [0] => array(3) {
                [0] => string(11) "category_id"
                    [1] => string(1) "="
                        [2] => string(1) "1"
            }
            [1] => array(3) {
                [0] => string(10) "alias_name"
                    [1] => string(1) "="
                        [2] => string(1) "a"
            }
            [2] => array(3) {
                [0] => string(10) "title|name"
                    [1] => string(4) "LIKE"
                        [2] => string(4) "%aa%"
            }
        }
        ["order"] => string(11) "create DESC"
            ["limit"] => string(5) "20,20"
    }
```

### 排序前缀

```php
    
    默认的排序前缀为 or_

    排序规则 前缀加上 or_  处理后默认去除or_ 不用设置别名

    列:

    $Process = new  \decimalism\param\Process;

    $request = [
        'sr_create_time' => 1,
        'sr_update_time' => 0
    ];


    //排序规则， 作用于order方法
    $order = 'sr_create_time,sr_update_time';//多个字段用,分割
    $rule = null;

    //运行 
    $Process->run($request, [] , $order);
    //获取结果集
    $param_data = $Process->getData();

    //当前为未设置的打印结果
    array(3) {
        ["where"] => array(0) {
        }
        ["order"] => string(38) "sr_create_time DESC,sr_update_time ASC"
            ["limit"] => string(4) "0,10"
    }


    //默认未设置为or_  设置排序前缀以区分参数传入同时有条件和排序规则  比如 create_time 同时作为条件筛选和排序
    $Process->setOrderPrefix('sr_');
    $Process->run($request, [] , $order);
    //获取结果集
    $param_data = $Process->getData();
        
    array(3) {
        ["where"] => array(0) {
        }
        ["order"] => string(32) "create_time DESC,update_time ASC"
            ["limit"] => string(4) "0,10"
    }


```

### 设置分页

```php
添加静态方法 setIsLimit()
当前默认数据返回带有分页 调用该方法可取消分页

    $Process = new  \decimalism\param\Process;

    $request = [
        'or_create_time' => 1,
        'or_update_time' => 0
    ];

    //运行 
    $Process::setIsLimit(false);
    $Process->run($request, [] , null);
    //获取结果集
    $param_data = $Process->getData();
    //打印结果集
    array(3) {
        ["where"] => array(0) {
        }
        ["order"] => string(0) ""
            ["limit"] => NULL
    }

```

### 默认值配置
```

    //分页类型 page 页码类型 和 length 长度分页
    Env::get('PARAM.LIMIT_YTPE', 'page'),

    //页码字段 
    Env::get('PARAM.LIMIT_START_ATTR', 'page'),
    //长度字段
    Env::get('PARAM.LIMIT_LENGTH_ATTR', 'limit')


    //排序前缀
    Env::get('PARAM.ORDER_PREFIX', 'or_')

```
