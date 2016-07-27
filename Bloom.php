<?php

/**
 * Created by PhpStorm.
 * User: 彭哥
 * Date: 2016/6/23
 * Time: 10:36
 */


class Bloom extends CI_Controller
{
	//比较布隆算法的效率
    public function index($query = "15521118549@iloveyoumorethanicansay"){
        echo crc32("Thequickbrownfoxjumpedoverthelazydog.");
        return;
        $len = 1000000;
        $time1 = microtime(true);
        $bf = new BloomFilter($len * 10, $len);
        for ($p = 15521118540; $p < 15521118540 + $len; $p++) {
            $bf->addKey($p . "@iloveyoumorethanicansay");
        }
        echo "共{$len}个数据，加入数组时间" . sprintf("%.10f",(microtime(true) - $time1) * 1000) . "毫秒<br>";
        $time1 = microtime(true);
        if($bf->existKey($query))
            echo $query . "存在于数组中";
        else
            echo $query . "不存在于数组中";
        echo "<br>查询时间" .  sprintf("%.10f",(microtime(true) - $time1) * 1000) . "毫秒<br>";
        $org = array();
        $time1 = microtime(true);
        for ($p = 15521118540; $p < 15521118540 + $len; $p++) {
            $org[] = $p;
        }
        echo "原始处理方法共{$len}个数据，加入数组时间" . sprintf("%.10f",(microtime(true) - $time1) * 1000) . "毫秒<br>";
        $time1 = microtime(true);
        if(in_array($query, $org))
            echo $query . "存在于数组中";
        else
            echo $query . "不存在于数组中";
        echo "<br>查询时间" . sprintf("%.10f",(microtime(true) - $time1) * 1000) . "毫秒";
    }

	
	//斐波那契数列
    public function fbnq(){
        $b = 999999999999999999;
        $e = 1;
        for($p = 0; $p < 1000; $p++)
        {
            $m = $b + $e;
            echo $m . "--" . sprintf("%.3f",($e/$m)) . "--" . sprintf("%.3f",($e  - $m * $b / $e) * $e) . "<br>";
            $b = $e;
            $e = $m;
        }
    }

	//比较各种排序算法的效率
    public function sort(){
        $arr = array();
        for($p =0;$p<1000;$p++){
            $arr[] = rand(0,100000);
        }
        $sort = new sort();
        $time1 = microtime(true);
//        var_dump($arr);
        $newarr = $sort->insert($arr);
        echo "<br>插入排序时间" . sprintf("%.10f",(microtime(true) - $time1) * 1000) . "毫秒";$time1 = microtime(true);
        $newarr = $sort->hill($arr);
        echo "<br>希尔排序时间" . sprintf("%.10f",(microtime(true) - $time1) * 1000) . "毫秒";$time1 = microtime(true);
        $newarr = $sort->maopao($arr);
        echo "<br>冒泡排序时间" . sprintf("%.10f",(microtime(true) - $time1) * 1000) . "毫秒";$time1 = microtime(true);
        $newarr = $sort->quick($arr,0,999);
//        var_dump($arr);
        echo "<br>快速排序时间" . sprintf("%.10f",(microtime(true) - $time1) * 1000) . "毫秒";$time1 = microtime(true);
    }
}

//各种排序算法
class sort{
    //直接插入排序
    public function insert($arr){
        $n = count($arr);
        for($i=1;$i<$n;$i++){
            $temp = $arr[$i];
            $j = $i - 1;
            while($j>=0 && $temp < $arr[$j]){
                $arr[$j + 1] = $arr[$j];
                $j --;
            }
            $arr[$j+1]=$temp;
        }
        return $arr;
    }
    //希尔插入排序
    public function hill($arr){
        $n = count($arr);
        $d = floor($n/2);
        while($d > 0){
            for($i=$d;$i<$n;$i++){
                $j=$i-$d;
                while($j>=0 && $arr[$j]>$arr[$j+$d]){
                    $temp = $arr[$j];
                    $arr[$j] = $arr[$j+$d]; $arr[$j+$d] = $temp;
                    $j = $j-$d;
                }

            }
            $d = floor($d/2);
        }
        return $arr;
    }
    //冒泡排序
    public function maopao($arr){
        $n = count($arr);
        for($i = 0;$i<$n-1;$i++){
            for($j = $n-1;$j>$i;$j--){
                if($arr[$j]<$arr[$j-1]){
                    $temp = $arr[$j];
                    $arr[$j] = $arr[$j-1];
                    $arr[$j-1] = $temp;
                }
            }
        }
        return $arr;
    }
    //快速交换排序
    public function quick(&$arr,$s,$t){
        $n = count($arr);
        $i = $s; $j = $t;
        if($i < $j){
            $temp = $arr[$s];
            while($i != $j){
                while($j>$i && $arr[$j]>$temp) {
                    $j--;
                }
                if($i<$j)
                {
                    $arr[$i] = $arr[$j];
                    $i++;
                }
                while($j>$i && $arr[$i]<$temp) {
                    $i++;
                }
                if($i<$j){
                    $arr[$j] = $arr[$i];
                    $j--;
                }
            }

            $arr[$j] = $temp;
            $this->quick($arr,$s,$i-1);
            $this->quick($arr,$i+1,$t);
        }
    }
}

//布隆算法类
class BloomFilter
{
    var $m; # blocksize
    var $n; # number of strings to hash
    var $k; # number of hashing functions
    var $bitset; # hashing block with size m

    function BloomFilter($mInit, $nInit)
    {
        $this->m = $mInit;
        $this->n = $nInit;
        $this->k = ceil(($this->m / $this->n) * log(2));
        $this->bitset = array_fill(0, $this->m, false);
    }

    function hashcode($str)
    {
        $res = array(); #put k hashing bit into $res
        $seed = crc32($str);
        mt_srand($seed); // set random seed, or mt_rand wouldn't provide same random arrays at different generation
        for ($i = 0; $i < $this->k; $i++) {
            $res[] = mt_rand(0, $this->m - 1);
        }
        return $res;
    }

    function addKey($key)
    {
        foreach ($this->hashcode($key) as $codebit) {
            $this->bitset[$codebit] = true;
        }
    }

    function existKey($key)
    {
        $code = $this->hashcode($key);
        foreach ($code as $codebit) {
            if ($this->bitset[$codebit] == false) {
                return false;
            }
        }
        return true;
    }
}