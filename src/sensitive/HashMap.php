<?php


namespace sensitive;

/**
 * Class HashMap
 * @package core
 * @author deverz
 * @desc hash表
 */
class HashMap
{
    /**
     * 生成的hashMap
     * @var array
     */
    protected $hashMap = [];

    /**
     * 设置值
     * @param $key
     * @param $val
     * @return mixed|null
     */
    public function put($key, $val)
    {
        //未定义直接赋值，定义了返回旧值并定义新值
        if (!isset($this->hashMap[$key])) {
            $this->hashMap[$key] = $val;
            return null;
        }
        $old = $this->hashMap[$key];
        $this->hashMap[$key] = $val;
        return $old;
    }

    /**
     * 获取值
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        if (!isset($this->hashMap[$key])) {
            return null;
        }
        return $this->hashMap[$key];
    }
}