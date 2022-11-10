<?php

namespace Jawabapp\Community\Traits;

use Illuminate\Support\Str;

trait HasDynamicRelation
{
    /**
     * Store the relations
     *
     * @var array
     */
    protected static $dynamic_relations = [];
    protected static $dynamic_appends = [];
    protected static $dynamic_hidden = [];
    protected static $dynamic_appends_actions = [];

    /**
     * Add a new relation
     *
     * @param $name
     * @param $closure
     */
    public static function addDynamicRelation($name, $closure)
    {
        static::$dynamic_relations[$name] = $closure;
    }

    /**
     * Determine if a relation exists in dynamic relationships list
     *
     * @param $name
     *
     * @return bool
     */
    public static function hasDynamicRelation($name)
    {
        return array_key_exists($name, static::$dynamic_relations);
    }

    /**
     * Add a new append
     *
     * @param $name
     * @param $closure
     */
    public static function addDynamicAppend($name, $closure)
    {
        $action = 'get' . Str::studly($name) . 'Attribute';

        static::$dynamic_appends_actions[$action] = $closure;
        static::$dynamic_appends[$name] = $action;
    }

    /**
     * Determine if a relation exists in dynamic relationships list
     *
     * @param $name
     *
     * @return bool
     */
    public static function hasDynamicAppend($name)
    {
        return array_key_exists($name, static::$dynamic_appends);
    }

    /**
     * Determine if a relation exists in dynamic relationships list
     *
     * @param $name
     *
     * @return bool
     */
    public static function hasDynamicAppendAction($name)
    {
        return array_key_exists($name, static::$dynamic_appends_actions);
    }

    /**
     * Get all of the appendable values that are arrayable.
     *
     * @return array
     */
    protected function getArrayableAppends()
    {

        if(array_keys(static::$dynamic_appends)) {
            $this->appends = array_unique(array_merge(array_keys(static::$dynamic_appends), $this->appends ?? []));
        }

        return parent::getArrayableAppends();
    }

    /**
     * Add a new hidden
     *
     * @param $name
     */
    public static function addDynamicHidden($name)
    {
        static::$dynamic_hidden[$name] = $name;
    }

    /**
     * Determine if a hidden exists in dynamic hidden list
     *
     * @param $name
     *
     * @return bool
     */
    public static function hasDynamicHidden($name)
    {
        return array_key_exists($name, static::$dynamic_hidden);
    }

    /**
     * Get an attribute array of all arrayable values.
     *
     * @param  array  $values
     * @return array
     */
    protected function getArrayableItems(array $values)
    {
        if (count(static::$dynamic_hidden) > 0) {
            $values = array_diff_key($values, array_flip(static::$dynamic_hidden));
        }

        return parent::getArrayableItems($values);
    }

    /**
     * If the key exists in relations then
     * return call to relation or else
     * return the call to the parent
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (static::hasDynamicRelation($name)) {
            // check the cache first
            if ($this->relationLoaded($name)) {
                return $this->relations[$name];
            }

            // load the relationship
            return $this->getRelationshipFromMethod($name);
        }

        if (static::hasDynamicAppend($name)) {
            return call_user_func(static::$dynamic_appends_actions[static::$dynamic_appends[$name]], $this);
        }

        return parent::__get($name);
    }

    /**
     * If the method exists in relations then
     * return the relation or else
     * return the call to the parent
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (static::hasDynamicRelation($name)) {
            return call_user_func(static::$dynamic_relations[$name], $this);
        }

        if (static::hasDynamicAppendAction($name)) {
            return call_user_func(static::$dynamic_appends_actions[$name], $this);
        }

        return parent::__call($name, $arguments);
    }
}
