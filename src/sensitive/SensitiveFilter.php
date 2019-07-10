<?php

namespace sensitive;

use \Exception;

/**
 * Class SensitiveFilter
 * @package core
 * @author deverz
 * @desc 敏感词过滤类
 */
class SensitiveFilter
{
    /**
     * 敏感词hashMap
     * @var null
     */
    protected $sensitiveWords = null;

    const ENCODING = 'utf-8';//mb编码
    const END = 'end';//HashMap结束标记

    /**
     * 查找文章中是否存在敏感词
     * @param $content
     * @return array
     */
    public function searchSensitiveWords($content)
    {
        $sensitiveWordsList = [];
        $length = mb_strlen($content, self::ENCODING);
        for ($i = 0; $i < $length; $i++) {
            $substrLen = 0;//截取敏感词个数
            $flag = false;//敏感词全匹配标记
            //获取敏感词map
            $dfaTree = $this->sensitiveWords;
            for ($j = $i; $j < $length; $j++) {
                //截取key
                $key = mb_substr($content, $j, 1, self::ENCODING);
                //查找key是否存在于map中
                $searchTree = $dfaTree->get($key);
                if (!$searchTree) {
                    break;//没有则跳出本层循环
                } else {

                    //存在则进入该HashMap
                    $dfaTree = $searchTree;
                    $substrLen++;//截取个数+1

                    //判断到底了，跳出本层循环
                    if ($searchTree->get(self::END)) {
                        $flag = true;
                        break;
                    }
                }
            }

            //全匹配才开始截取值
            if ($flag && $substrLen) {
                $sensitiveWordsList[] = mb_substr($content, $i, $substrLen, self::ENCODING);
                $i = $i + $substrLen - 1;
            }

        }
        return $sensitiveWordsList;
    }

    /**
     * 初始化敏感词HashMap
     * @param string $file
     * @return null
     * @throws Exception
     */
    public function initSensitiveWords($file = '')
    {
        if (file_exists($file)) {
            //初始化敏感词hashMap
            $this->sensitiveWords = new HashMap();
            foreach ($this->readFile($file) as $sensitiveWord) {
                $this->buildDfaTree($sensitiveWord);
            }
            //返回对象
            return $this;
        }
        throw new Exception('敏感词文件不存在！');
    }

    /**
     * 使用生成器yield去读取文件内容，防止内存暴满
     * @param $file
     * @return \Generator
     */
    private function readFile($file)
    {
        $fp = fopen($file, 'r');
        while (!feof($fp)) {
            yield fgets($fp);
        }
        fclose($fp);
    }

    /**
     * 将敏感词分解并加进hashMap
     * @param $sensitiveWord
     */
    private function buildDfaTree($sensitiveWord)
    {
        $sensitiveWord = trim($sensitiveWord);
        if ($sensitiveWord) {
            $dfaTree = $this->sensitiveWords;//获取hashMap实例
            //计算字符长度
            $length = mb_strlen($sensitiveWord, self::ENCODING);
            //开始分解字符
            for ($i = 0; $i < $length; $i++) {
                $key = mb_substr($sensitiveWord, $i, 1, self::ENCODING);
                //查询单个字符是否存在
                $tmpTree = $dfaTree->get($key);
                if ($tmpTree) {
                    //存在切换到子节点
                    $dfaTree = $tmpTree;
                } else {
                    //创建新的子节点
                    $newTree = new HashMap();
                    //将结束标志置为false
                    $newTree->put(self::END, false);
                    //将key值加入到节点
                    $dfaTree->put($key, $newTree);
                    //切换到子节点继续往下遍历赋值
                    $dfaTree = $newTree;
                }
                //到达最后，那么记录结束标志
                if ($i == $length - 1) {
                    $dfaTree->put(self::END, true);
                }
            }
        }
    }
}