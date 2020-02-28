<?php

namespace App\Util;

class Utils
{
    const LOGFLAG = 1;
    const AUTHFLAG = 2;
    const VALIDATIONFLAG = 4;

    static public function randomStr($length, $keyspace = '123456789ABCDEFGHJKLMNPQRSTUVWXYZ')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    static public function arrayWhiteList($array, $list) {
        return array_intersect_key($array, array_flip($list));
    }

    static public function traceStr($str)
    {
        return preg_replace('/[^[:alnum:]]/ui', '', $str);
    }
}