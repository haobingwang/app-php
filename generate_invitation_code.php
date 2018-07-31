<?php

$start = 1;
$limit = 1000;

foreach (xrange($start, $limit) as $id) {
    $code       = InvitationCode::createCode($id);
    $id_decoded = InvitationCode::decode($code);
    $log = "{$id} 生成对应 code 为 {$code}，解码为{$id_decoded}\n";
    echo php_sapi_name() == "cli" ? $log : nl2br($log);
}

/**
 * 根据数据表的自增主键 id 生成与之对应的唯一code，范围为‘0-9A-Z’。
 * 这个需求的重点在于加粗的部分，也就是要能够根据 code 反推出 id，这样 code 就不用入库了，在数据量很大的情况下，可以显著提升性能。
 *
 * 应用场景：
 *  1. 根据用户 id 生成邀请码
 *  2. 根据链接 id 生成 CPS track code
 *
 * 参考链接：http://www.php.cn/php-weizijiaocheng-388405.html
 *
 * Class InvitationCode
 */
class InvitationCode
{
    // 思考：一个10进制的数字短还是一个16进制的数字短？
    // 肯定是16进制相对短一些，所以我们可以直接把用户id转成 10+26=36 进制的不就可以了吗？
    // 10 个数字和26个字母
    //private static $source_string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    //private static $source_length = 36;

    // 优化
    // 把 0 剔除，当做补位符号，比如小于四位的邀请码在高位补 0，这样36进制就变成了35进制;
    // 然后把字符串顺序打乱（https://www.dcode.fr/deranged-alphabet-generator）;
    // 这样，在不知道 $source_string 的情况下，是没办法解出正确的 id 的;
    //private static $source_string = 'E5FCDG3HQA4B1NOPIJ2RSTUV67MWX89KLYZ';
    //private static $source_length = 35;

    // 进一步优化
    // 增加大小区区分
    private static $source_string = '8vAPNSwYjLrEGc5bpzXKsqWF7ouxDnaJVh2mC3Z4tRHBOI9TgklUQ6Md1yief';
    private static $source_length = 61;

    /**
     * @param $id
     * @return string
     */
    public static function createCode($id)
    {
        $code = '';
        while ($id > 0) {
            $mod  = $id % static::$source_length;
            $id   = ($id - $mod) / static::$source_length;
            $code = static::$source_string[$mod] . $code;
        }
        if (empty($code[3])) {
            $code = str_pad($code, 4, '0', STR_PAD_LEFT);
        }
        return $code;
    }

    /**
     * @param $code
     * @return float|int
     */
    public static function decode($code)
    {
        if (strrpos($code, '0') !== false) {
            $code = substr($code, strrpos($code, '0') + 1);
        }
        $len  = strlen($code);
        $code = strrev($code);
        $id   = 0;
        for ($i = 0; $i < $len; $i++) {
            $id += strpos(static::$source_string, $code[$i]) * pow(static::$source_length, $i);
        }
        return $id;
    }
}

function xrange($start, $limit, $step = 1)
{
    if ($start < $limit) {
        if ($step <= 0) {
            throw new LogicException('Step must be +ve');
        }

        for ($i = $start; $i <= $limit; $i += $step) {
            yield $i;
        }
    } else {
        if ($step >= 0) {
            throw new LogicException('Step must be -ve');
        }

        for ($i = $start; $i >= $limit; $i += $step) {
            yield $i;
        }
    }
}