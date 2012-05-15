<?php defined('SYSPATH') or die('No direct access allowed.');
/*
 +----------------------------------------------------------------------+
 | QuickPHP Framework Version 0.10                                      |
 +----------------------------------------------------------------------+
 | Copyright (c) 2010 QuickPHP.net All rights reserved.                 |
 +----------------------------------------------------------------------+
 | Licensed under the Apache License, Version 2.0 (the 'License');      |
 | you may not use this file except in compliance with the License.     |
 | You may obtain a copy of the License at                              |
 | http://www.apache.org/licenses/LICENSE-2.0                           |
 | Unless required by applicable law or agreed to in writing, software  |
 | distributed under the License is distributed on an 'AS IS' BASIS,    |
 | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
 | implied. See the License for the specific language governing         |
 | permissions and limitations under the License.                       |
 +----------------------------------------------------------------------+
 | Author: BoPo <ibopo@126.com>                                         |
 +----------------------------------------------------------------------+
*/
/**
 * 对象关系映射 (ORM) 类
 *
 * [ref-orm]: http://wikipedia.org/wiki/Object-relational_mapping
 * [ref-act]: http://wikipedia.org/wiki/Active_record
 *
 * $Id: ORM.php 8761 2012-01-15 05:10:59Z bopo $
 *
 * @category    QuickPHP
 * @package     ORM
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: ORM.php 8761 2012-01-15 05:10:59Z bopo $
 */
class QuickPHP_ORM
{

    // 数据对象关系
    protected $_has_one    = array();
    
    protected $_belongs_to = array();
    
    protected $_has_many   = array();
    
    // 载入的对象关系
    protected $_load_with  = array();
    
    // 验证成员
    protected $_validate   = null;
    
    protected $_rules      = array();
    
    protected $_callbacks  = array();
    
    protected $_filters    = array();
    
    protected $_labels     = array();
    
    // 当前对象
    protected $_object     = array();
    
    protected $_changed    = array();
    
    protected $_related    = array();
    
    protected $_loaded     = false;
    
    protected $_saved      = false;

    protected $_sorting;

    // 外键后缀
    protected $_foreign_key_suffix = '_id';

    // 模型表信息
    protected $_object_name;

    protected $_object_plural;

    protected $_table_name;

    protected $_table_columns;

    protected $_ignored_columns = array();

    // 自动更新列增加和更新
    protected $_updated_column = null;

    protected $_created_column = null;

    // 表的主键和值
    protected $_primary_key = 'id';

    protected $_primary_val = 'name';

    // 模型配置
    protected $_table_names_plural = true;

    // 数据库 _reload_on_wakeup
    protected $_reload_on_wakeup = true;

    // 数据库配置
    protected $_db = null;

    // 数据库已经应用的数据
    protected $_db_applied = array();

    // 数据库中存储的数据
    protected $_db_pending = array();

    // 数据库中重置状态
    protected $_db_reset = true;

    // 资料库构造器
    protected $_db_builder;

    // 已经调用的数据
    protected $_with_applied = array();

    // 已经加载的数据
    protected $_preload_data = array();

    // 存储字段信息
    protected static $_column_cache = array();

    // 可以重载的方法
    protected static $_db_methods = array(
        'where',
        'and_where',
        'or_where',
        'where_open',
        'and_where_open',
        'or_where_open',
        'where_close',
        'and_where_close',
        'or_where_close',
        'distinct',
        'select',
        'from',
        'join',
        'on',
        'group_by',
        'having',
        'and_having',
        'or_having',
        'having_open',
        'and_having_open',
        'or_having_open',
        'having_close',
        'and_having_close',
        'or_having_close',
        'order_by',
        'limit',
        'offset',
        'cached'
    );

    // 可以重载的属性
    protected static $_properties = array(
        'object_name',
        'object_plural',
        'loaded',
        'saved', // Object
        'primary_key',
        'primary_val',
        'table_name',
        'table_columns', // Table
        'has_one', 'belongs_to',
        'has_many',
        'has_many_through',
        'load_with', // Relationships
        'validate',  // Validation
        'rules',
        'callbacks',
        'filters',
        'labels'

     );

    /**
     * 创建一个新模型.
     *
     * @chainable
     * @param   string  模型名称
     * @param   mixed   查询条件 主键值，或者数组形式条件
     * @return  ORM
     */
    public static function factory($model, $id = null)
    {
        $model = explode("_", $model);

        foreach ($model as $key => $val)
        {
            $model[$key] = ucfirst($val);
        }

        $model = implode("_", $model) . '_Model';
        $model = new $model($id);

        return $model;
    }

    /**
     * 初始化模型数
     *
     * @param   mixed  查询条件 主键值，或者数组形式条件
     * @return  void
     */
    public function __construct($id = null)
    {
        $this->_object_name   = strtolower(substr(get_class($this), 0, - 6));
        $this->_object_plural = inflector::plural($this->_object_name);

        if( ! isset($this->_sorting))
        {
            $this->_sorting = array($this->_primary_key => 'ASC');
        }

        if( ! empty($this->_ignored_columns))
        {
            $this->_ignored_columns = array_combine($this->_ignored_columns, $this->_ignored_columns);
        }

        $this->_initialize();
        $this->clear();

        if($id !== null)
        {
            if(is_array($id))
            {
                foreach ($id as $column => $value)
                {
                    $this->where($column, '=', $value);
                }

                $this->find();
            }
            else
            {
                $this->_object[$this->_primary_key] = $id;
                $this->_saved = true;
            }
        }
        elseif( ! empty($this->_preload_data))
        {
            $this->_load_values($this->_preload_data);
            $this->_preload_data = array();
        }
    }

    /**
     * 魔术方法 __isset
     *
     * @param   string  column name
     * @return  boolean
     */
    public function __isset($column)
    {
        $this->_load();

        return (isset($this->_object[$column]) 
            or isset($this->_related[$column]) 
            or isset($this->_has_one[$column]) 
            or isset($this->_belongs_to[$column]) 
            or isset($this->_has_many[$column]));
    }

    /**
     * 魔术方法 __unset
     *
     * @param   string  列名
     * @return  void
     */
    public function __unset($column)
    {
        $this->_load();
        unset($this->_object[$column], $this->_changed[$column], $this->_related[$column]);
    }

    /**
     * 魔术方法 __toString
     *
     * @return  string
     */
    public function __toString()
    {
        return (string) $this->pk();
    }

    /**
     * 魔术方法 __sleep
     *
     * @return  array
     */
    public function __sleep()
    {
        return array('_object_name', '_object', '_changed', '_loaded', '_saved', '_sorting');
    }

    /**
     * 魔术方法 __wakeup
     *
     * @return  void
     */
    public function __wakeup()
    {
        $this->_initialize();

        if($this->_reload_on_wakeup === true)
        {
            $this->reload();
        }
    }

    /**
     * 魔术方法 __call
     *
     * @param   string  方法名
     * @param   array   方法参数
     * @return  mixed
     */
    public function __call($method, array $args)
    {
        if(in_array($method, ORM::$_properties))
        {
            if($method === 'loaded')
            {
                if( ! isset($this->_object_name))
                {
                    return false;
                }

                $this->_load();
            }
            elseif($method === 'validate')
            {
                if( ! isset($this->_validate))
                {
                    $this->_validate();
                }
            }

            return $this->{'_' . $method};
        }
        elseif(in_array($method, ORM::$_db_methods))
        {
            $this->_db_pending[] = array('name' => $method, 'args' => $args);
            return $this;
        }
        else
        {
            throw new ORM_Exception('Invalid_method_call', array($method, get_class($this)));
        }
    }

    /**
     * 魔术方法 __get
     *
     * @param   string  字段名
     * @return  mixed
     */
    public function __get($column)
    {
        if(array_key_exists($column, $this->_object))
        {
            $this->_load();
            return $this->_object[$column];
        }
        elseif(isset($this->_related[$column]) and $this->_related[$column]->_loaded)
        {
            return $this->_related[$column];
        }
        elseif(isset($this->_belongs_to[$column]))
        {
            $this->_load();

            $model  = $this->_related($column);
            $col    = $model->_table_name . '.' . $model->_primary_key;
            $val    = $this->_object[$this->_belongs_to[$column]['foreign_key']];

            $model->where($col, '=', $val)->find();
            return $this->_related[$column] = $model;
        }
        elseif(isset($this->_has_one[$column]))
        {
            $model = $this->_related($column);
            $col   = $model->_table_name . '.' . $this->_has_one[$column]['foreign_key'];
            $val   = $this->pk();

            $model->where($col, '=', $val)->find();
            return $this->_related[$column] = $model;
        }
        elseif(isset($this->_has_many[$column]))
        {
            $model = ORM::factory($this->_has_many[$column]['model']);

            if(isset($this->_has_many[$column]['through']))
            {
                $through   = $this->_has_many[$column]['through'];
                $join_col1 = $through . '.' . $this->_has_many[$column]['far_key'];
                $join_col2 = $model->_table_name . '.' . $model->_primary_key;

                $model->join($through)->on($join_col1, '=', $join_col2);

                $col = $through . '.' . $this->_has_many[$column]['foreign_key'];
                $val = $this->pk();
            }
            else
            {
                $col = $model->_table_name . '.' . $this->_has_many[$column]['foreign_key'];
                $val = $this->pk();
            }

            return $model->where($col, '=', $val);
        }
        else
        {
            throw new ORM_Exception('invalid_property', array($column, get_class($this)));
        }
    }

    /**
     * 魔术方法 __set
     *
     * @param   string  字段名
     * @param   mixed   字段值
     * @return  void
     */
    public function __set($column, $value)
    {
        if( ! isset($this->_object_name))
        {
            $this->_preload_data[$column] = $value;
            return;
        }

        if(array_key_exists($column, $this->_ignored_columns))
        {
            $this->_object[$column] = $value;
        }
        elseif(array_key_exists($column, $this->_object))
        {
            $this->_object[$column] = $value;

            if(isset($this->_table_columns[$column]))
            {
                $this->_changed[$column] = $column;
                $this->_saved = false;
            }
        }
        elseif(isset($this->_belongs_to[$column]))
        {
            $this->_related[$column] = $value;
            $this->_object[$this->_belongs_to[$column]['foreign_key']] = $value->pk();
            $this->_changed[$column] = $this->_belongs_to[$column]['foreign_key'];
        }
        else
        {
            throw new ORM_Exception("invalid_property", array($column, get_class($this)));
        }
    }

    /**
     * 数组 key => val 形式输入数据
     *
     * @param   array  数组形式：key => val
     * @return  ORM
     */
    public function values($values)
    {
        foreach ($values as $key => $value)
        {
            if(array_key_exists($key, $this->_object) or array_key_exists($key, $this->_ignored_columns))
            {
                $this->__set($key, $value);
            }
            elseif(isset($this->_belongs_to[$key]) or isset($this->_has_one[$key]))
            {
                $this->_related[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * 初始化操作
     *
     * @return  void
     */
    protected function _initialize()
    {
        if( ! is_object($this->_db))
        {
            $this->_db = Database::instance($this->_db);
        }

        if(empty($this->_table_name))
        {
            $this->_table_name = $this->_object_name;

            if($this->_table_names_plural === true)
            {
                $this->_table_name = inflector::plural($this->_table_name);
            }
        }

        foreach ($this->_belongs_to as $alias => $details)
        {
            $defaults['model']          = $alias;
            $defaults['foreign_key']    = $alias . $this->_foreign_key_suffix;
            $this->_belongs_to[$alias]  = array_merge($defaults, $details);
        }

        foreach ($this->_has_one as $alias => $details)
        {
            $defaults['model']       = $alias;
            $defaults['foreign_key'] = $this->_object_name . $this->_foreign_key_suffix;
            $this->_has_one[$alias]  = array_merge($defaults, $details);
        }

        foreach ($this->_has_many as $alias => $details)
        {
            $defaults['model']       = inflector::singular($alias);
            $defaults['foreign_key'] = $this->_object_name . $this->_foreign_key_suffix;
            $defaults['through']     = null;
            $defaults['far_key']     = inflector::singular($alias) . $this->_foreign_key_suffix;
            $this->_has_many[$alias] = array_merge($defaults, $details);
        }

        $this->reload_columns();
    }

    /**
     * 初始化数据字段验证规则
     *
     * @return void
     */
    protected function _validate()
    {
        $this->_validate = Validate::factory($this->_object);

        foreach ($this->_rules as $field => $rules)
        {
            $this->_validate->rules($field, $rules);
        }

        foreach ($this->_filters as $field => $filters)
        {
            $this->_validate->filters($field, $filters);
        }

        $columns = array_keys($this->_table_columns);
        $labels  = array_merge(array_combine($columns, $columns), $this->_labels);

        foreach ($labels as $field => $label)
        {
            $this->_validate->label($field, $label);
        }

        foreach ($this->_callbacks as $field => $callbacks)
        {
            foreach ($callbacks as $callback)
            {
                if(is_string($callback) and method_exists($this, $callback))
                {
                    $this->_validate->callback($field, array($this, $callback));
                }
                else
                {
                    $this->_validate->callback($field, $callback);
                }
            }
        }
    }

    /**
     * 返回值的数组形式的结果
     *
     * @return  array
     */
    public function as_array()
    {
        $object = array();

        foreach ($this->_object as $key => $val)
        {
            $object[$key] = $this->__get($key);
        }

        foreach ($this->_related as $key => $model)
        {
            $object[$key] = $model->as_array();
        }

        return $object;
    }

    /**
     * 多表查询操作
     *
     * @param   string  要绑定的目标模型
     * @return  void
     */
    public function with($target_path)
    {
        if(isset($this->_with_applied[$target_path]))
        {
            return $this;
        }

        $aliases = explode(':', $target_path);
        $target  = $this;

        foreach ($aliases as $alias)
        {
            $parent = $target;
            $target = $parent->_related($alias);

            if( ! $target)
            {
                return $this;
            }
        }

        $target_alias = $alias; array_pop($aliases);
        $parent_path  = implode(':', $aliases);

        if(empty($parent_path))
        {
            $parent_path = $this->_table_name;
        }
        else
        {
            if( ! isset($this->_with_applied[$parent_path]))
            {
                $this->with($parent_path);
            }
        }

        $this->_with_applied[$target_path] = true;

        foreach (array_keys($target->_object) as $column)
        {
            $name  = $target_path . '.' . $column;
            $alias = $target_path . ':' . $column;
            $this->select(array($name, $alias));
        }

        if(isset($parent->_belongs_to[$target_alias]))
        {
            $join_col1 = $target_path . '.' . $target->_primary_key;
            $join_col2 = $parent_path . '.' . $parent->_belongs_to[$target_alias]['foreign_key'];
        }
        else
        {
            $join_col1 = $parent_path . '.' . $parent->_primary_key;
            $join_col2 = $target_path . '.' . $parent->_has_one[$target_alias]['foreign_key'];
        }

        $this->join(array($target->_table_name, $target_path), 'LEFT')->on($join_col1, '=', $join_col2);

        return $this;
    }

    /**
     * 初始化数据库查询, 并设置查询类型
     *
     * @param   int  Type of Database query
     * @return  ORM
     */
    protected function _build($type)
    {
        switch ($type)
        {
            case Database::SELECT :
                $this->_db_builder = Database::select();
            break;

            case Database::UPDATE :
                $this->_db_builder = Database::update($this->_table_name);
            break;

            case Database::DELETE :
                $this->_db_builder = Database::delete($this->_table_name);
            break;
        }

        foreach ($this->_db_pending as $method)
        {
            $name = $method['name'];
            $args = $method['args'];

            $this->_db_applied[$name] = $name;

            switch (count($args))
            {
                case 0 :
                    $this->_db_builder->$name();
                break;
                
                case 1 :
                    $this->_db_builder->$name($args[0]);
                break;
                
                case 2 :
                    $this->_db_builder
                        ->$name($args[0], $args[1]);
                break;
                
                case 3 :
                    $this->_db_builder->$name($args[0], $args[1], $args[2]);
                break;
                
                case 4 :
                    $this->_db_builder->$name($args[0], $args[1], $args[2], $args[3]);
                break;
                
                default :
                    call_user_func_array(array($this->_db_builder, $name), $args);
                break;
            }
        }

        return $this;
    }

    /**
     * 载入所建模型
     *
     * @return  ORM
     */
    protected function _load()
    {
        if( ! $this->_loaded and ! $this->empty_pk() and ! isset($this->_changed[$this->_primary_key]))
        {
            return $this->find($this->pk());
        }
    }

    /**
     * 查找数据库单行数据。
     *
     * @chainable
     * @param   mixed  主键
     * @return  ORM
     */
    public function find($id = null)
    {
        if( ! empty($this->_load_with))
        {
            foreach ($this->_load_with as $alias)
            {
                $this->with($alias);
            }
        }

        $this->_build(Database::SELECT);

        if($id !== null)
        {
            $this->_db_builder->where($this->_table_name . '.' . $this->_primary_key, '=', $id);
        }

        return $this->_load_result(false);
    }

    /**
     * 查找数据库多行数据。
     *
     * @chainable
     * @return  Database_Result
     */
    public function find_all()
    {
        if( ! empty($this->_load_with))
        {
            foreach ($this->_load_with as $alias)
            {
                $this->with($alias);
            }
        }

        $this->_build(Database::SELECT);

        return $this->_load_result(true);
    }

    /**
     * 按当前模型所配置的验证规则验证数据
     *
     * @return  boolean
     */
    public function check()
    {
        if( ! isset($this->_validate))
        {
            $this->_validate();
        }
        else
        {
            $this->_validate->exchangeArray($this->_object);
        }

        if($this->_validate->check())
        {
            return $this->_object = array_merge($this->_object, $this->_validate->getArrayCopy());
        }
        else
        {
            return false;
        }
    }

    /**
     * 保存数据到当前对象方法.主键为空为INSERT操作，否则UPDATE操作
     *
     * @chainable
     * @return  ORM
     */
    public function save()
    {
        if(empty($this->_changed))
        {
            return $this;
        }

        $data = array();

        foreach ($this->_changed as $column)
        {
            $data[$column] = $this->_object[$column];
        }

        if( ! $this->empty_pk() and ! isset($this->_changed[$this->_primary_key]))
        {
            if(is_array($this->_updated_column))
            {
                $column = $this->_updated_column['column'];
                $format = $this->_updated_column['format'];
                $data[$column] = $this->_object[$column] = ($format === true) ? time() : date($format);
            }

            $query = Database::update($this->_table_name)
                ->set($data)
                ->where($this->_primary_key, '=', $this->pk())
                ->execute($this->_db);

            $this->_saved = true;
        }
        else
        {
            if(is_array($this->_created_column))
            {
                $column = $this->_created_column['column'];
                $format = $this->_created_column['format'];
                $data[$column] = $this->_object[$column] = ($format === true) ? time() : date($format);
            }

            $result = Database::insert($this->_table_name)
                ->columns(array_keys($data))
                ->values(array_values($data))
                ->execute($this->_db);

            if($result)
            {
                if($this->empty_pk())
                {
                    $this->_object[$this->_primary_key] = $result[0];
                }

                $this->_loaded = $this->_saved = true;
            }
        }

        if($this->_saved === true)
        {
            $this->_changed = array();
        }

        return $this;
    }

    /**
     * 更新全部相关数据
     *
     * @chainable
     * @return  ORM
     */
    public function save_all()
    {
        $this->_build(Database::UPDATE);

        if(empty($this->_changed))
        {
            return $this;
        }

        $data = array();

        foreach ($this->_changed as $column)
        {
            $data[$column] = $this->_object[$column];
        }

        if(is_array($this->_updated_column))
        {
            $column = $this->_updated_column['column'];
            $format = $this->_updated_column['format'];
            $data[$column] = $this->_object[$column] = ($format === true) ? time() : date($format);
        }

        $this->_db_builder->set($data)->execute($this->_db);

        return $this;
    }

    /**
     * 删除当前对象的数据。 这并不破坏,已经创造了关系,与其他对象。
     *
     * @chainable
     * @param   mixed  id to delete
     * @return  ORM
     */
    public function delete($id = null)
    {
        if($id === null)
        {
            $id = $this->pk();
        }

        if( ! empty($id) or $id === '0')
        {
            Database::delete($this->_table_name)
                ->where($this->_primary_key, '=', $id)
                ->execute($this->_db);
        }

        return $this;
    }

    /**
     * 删除所有的对象在相关的桌子上。 这并不破坏,已经创造了关系,与其他对象。
     *
     * @chainable
     * @return  ORM
     */
    public function delete_all()
    {
        $this->_build(Database::DELETE);
        $this->_db_builder->execute($this->_db);

        return $this->clear();
    }

    /**
     * 清空状态操作
     *
     * @chainable
     * @return  ORM
     */
    public function clear()
    {
        $values = array_combine(array_keys($this->_table_columns), array_fill(0, count($this->_table_columns), null));

        $this->_object  = array();
        $this->_changed = array();
        $this->_related = array();
        $this->_load_values($values);
        $this->reset();

        return $this;
    }

    /**
     * 重载当前对象的数据。
     *
     * @chainable
     * @return  ORM
     */
    public function reload()
    {
        $primary_key    = $this->pk();
        $this->_object  = array();
        $this->_changed = array();
        $this->_related = array();

        return $this->find($primary_key);
    }

    /**
     * 重载读取字段定义信息
     *
     * @chainable
     * @param   boolean  强行重载
     * @return  ORM
     */
    public function reload_columns($force = false)
    {
        if($force === true or empty($this->_table_columns))
        {
            if(isset(ORM::$_column_cache[$this->_object_name]))
            {
                $this->_table_columns = ORM::$_column_cache[$this->_object_name];
            }
            else
            {
                $this->_table_columns = $this->list_columns(true);
                ORM::$_column_cache[$this->_object_name] = $this->_table_columns;
            }
        }

        return $this;
    }

    /**
     * 返回主表相关的附表是否关联
     * 考验,如果这个对象与不同的模式。
     *
     * @param   string   一(多)对多关系的别名
     * @param   ORM      相应 ORM 模型
     * @return  boolean
     */
    public function has($alias, $model)
    {
        return (bool) Database::select(array('COUNT("*")', 'records_found'))
            ->from($this->_has_many[$alias]['through'])
            ->where($this->_has_many[$alias]['foreign_key'], '=', $this->pk())
            ->where($this->_has_many[$alias]['far_key'], '=', $model->pk())
            ->execute($this->_db)
            ->get('records_found');
    }

    /**
     * 添加一个新的关系,在这个模型和另一个表。
     *
     * @param   string   一(多)对多关系的别名
     * @param   ORM      相应 ORM 模型
     * @param   array    要添加到中间关系表的数据
     * @return  ORM
     */
    public function add($alias, ORM $model, $data = null)
    {
        $columns = array($this->_has_many[$alias]['foreign_key'], $this->_has_many[$alias]['far_key']);
        $values  = array($this->pk(), $model->pk());

        if($data !== null)
        {
            $data    = array_merge(array_combine($columns, $values), $data);
            $columns = array_keys($data);
            $values  = array_values($data);
        }

        Database::insert($this->_has_many[$alias]['through'])
            ->columns($columns)
            ->values($values)
            ->execute($this->_db);

        return $this;
    }

    /**
     * 删除一个关系模型的中间关系表。
     * [code]
     *      $user->remove('roles', ORM::factory('role',array('name'=>'login')));
     * [/code]
     * @param   string   一(多)对多关系的别名
     * @param   ORM      相应 ORM 模型
     * @return  ORM
     */
    public function remove($alias, ORM $model)
    {
        Database::delete($this->_has_many[$alias]['through'])
            ->where($this->_has_many[$alias]['foreign_key'], '=', $this->pk())
            ->where($this->_has_many[$alias]['far_key'], '=', $model->pk())
            ->execute($this->_db);

        return $this;
    }

    /**
     * 计算表的记录数
     *
     * @return  integer
     */
    public function count_all()
    {
        $selects = array();

        foreach ($this->_db_pending as $key => $method)
        {
            if($method['name'] == 'select')
            {
                $selects[] = $method;
                unset($this->_db_pending[$key]);
            }
        }

        $this->_build(Database::SELECT);

        $records = $this->_db_builder
            ->from($this->_table_name)
            ->select(array('COUNT("*")', 'records_found'))
            ->execute($this->_db)
            ->get('records_found');

        $this->_db_pending += $selects;
        $this->reset();

        return $records;
    }

    /**
     * 返回表中的字段信息
     *
     * @return  array
     */
    public function list_columns()
    {
        return $this->_db->list_columns($this->_table_name);
    }

    /**
     * 清空缓存
     *
     * @chainable
     * @param   string  要清理的SQL
     * @return  ORM
     */
    public function clear_cache($sql = null)
    {
        $this->_db->clear_cache($sql);
        ORM::$_column_cache = array();

        return $this;
    }

    /**
     * 根据输入的关系结构别名返回ORM模型
     *
     * @param   string  别名
     * @return  ORM
     */
    protected function _related($alias)
    {
        if(isset($this->_related[$alias]))
        {
            return $this->_related[$alias];
        }
        elseif(isset($this->_has_one[$alias]))
        {
            return $this->_related[$alias] = ORM::factory($this->_has_one[$alias]['model']);
        }
        elseif(isset($this->_belongs_to[$alias]))
        {
            return $this->_related[$alias] = ORM::factory($this->_belongs_to[$alias]['model']);
        }
        else
        {
            return false;
        }
    }

    /**
     * Loads an array of values into into the current object.
     *
     * @chainable
     * @param   array  values to load
     * @return  ORM
     */
    protected function _load_values(array $values)
    {
        if(array_key_exists($this->_primary_key, $values))
        {
            $this->_loaded = $this->_saved = ($values[$this->_primary_key] !== null);
        }

        $相关 = array();

        foreach ($values as $column => $value)
        {
            if(strpos($column, ':') === false)
            {
                if( ! isset($this->_changed[$column]))
                {
                    $this->_object[$column] = $value;
                }
            }
            else
            {
                list ($prefix, $column) = explode(':', $column, 2);
                $相关[$prefix][$column] = $value;
            }
        }

        if( ! empty($相关))
        {
            foreach ($相关 as $object => $values)
            {
                $this->_related($object)->_load_values($values);
            }
        }

        return $this;
    }

    /**
     * 加载数据库结果，一个对象模式或者数组模式
     *
     * @chainable
     * @param   boolean       返回迭代器结果或者单行结果
     * @return  ORM           单行形式
     * @return  ORM_Iterator  多行形式
     */
    protected function _load_result($multiple = false)
    {
        $this->_db_builder->from($this->_table_name);

        if($multiple === false)
        {
            $this->_db_builder->limit(1);
        }

        if( ! isset($this->_db_applied['order_by']) and ! empty($this->_sorting))
        {
            foreach ($this->_sorting as $column => $direction)
            {
                if(strpos($column, '.') === false)
                {
                    $column = $this->_table_name . '.' . $column;
                }

                $this->_db_builder->order_by($column, $direction);
            }
        }

        if($multiple === true)
        {
            $result = $this->_db_builder
                ->as_assoc(get_class($this))
                ->execute($this->_db);

            $this->reset();
            return $result;
        }
        else
        {
            $result = $this->_db_builder->as_assoc()->execute($this->_db);
            $this->reset();

            if($result->count() === 1)
            {
                $this->_load_values($result->current());
            }
            else
            {
                $this->clear();
            }

            return $this;
        }
    }

    /**
     * 返回主键的值
     *
     * @return  mixed  主键
     */
    public function pk()
    {
        return $this->_object[$this->_primary_key];
    }

    /**
     * 返回主键是否为空
     *
     * @return  bool
     */
    protected function empty_pk()
    {
        return (empty($this->_object[$this->_primary_key]) and $this->_object[$this->_primary_key] !== '0');
    }

    /**
     * 返回最后请求SQL字符串
     *
     * @return  string
     */
    public function last_query()
    {
        return $this->_db->last_query;
    }

    /**
     * 复位请求构建器.
     *
     * @param  bool 
     */
    public function reset($next = true)
    {
        if($next and $this->_db_reset)
        {
            $this->_db_builder = null;
            $this->_db_pending = $this->_db_applied = $this->_with_applied = array();
        }

        $this->_db_reset = $next;

        return $this;
    }
}