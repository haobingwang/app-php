<?php

// 自增ID（每个商家独有的自增序号|主表的自增序号）
$ids = [1, 9999, 10000, 10001, 300000795];

foreach ($ids as $id) {
	$table_number = sharding_table($id, 1000000);
	$log = "序号{$id}的水平切分后存储到表{$table_number}\n";
	debug($log);
}

/**
 * @param  integer $id           数据表的自增 ID
 * @param  integer $size         切分尺寸，每 10000 条记录存为一张表
 * @return integer $table_number 数据表序号
 */
function sharding_table($id, $size = 10000) {
	$table_number  = ceil($id / $size);
	return $table_number;
}

function debug($str) {
	echo php_sapi_name() == "cli" ? $str : nl2br($log);
}