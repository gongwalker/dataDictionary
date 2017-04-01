<?php
/**
 * 生成mysql数据字典
 * author gongcoder@gmail.com
 * 注:
 *      1)强制不走缓存 应该请求 run.php?run
 *      2)配置段在18行
 */

if(empty($_GET) && file_exists('./index.html')){
    header("Location: index.html");
    exit;
}

//配置要生成数据字典的数据库start=======================================================================
ini_set( 'display_errors', 0 );
set_time_limit(300);
$link=array(
    array(
        'dbserver'   => "127.0.0.1",
        'dbusername' => "youruser",
        'dbpassword' => "yourpw",
        'database'   => 'yourdb',
        'title' => '数据库一',
    ),
  
   
     array(
        'dbserver'   => "127.0.0.1",
        'dbusername' => "youruser",
        'dbpassword' => "yourpw",
        'database'   => 'yourdb',
        'title' => '数据库二',
    ),
    
);
//配置要生成数据字典的数据库end=======================================================================

$html='';
$gourl_height='100px';
$gourl='<div style="position: fixed;font-size:14px;width:100%;background:#faaa00;padding:8px;max-height:'.$gourl_height.';top:0px;left:0px; box-shadow: 5px 5px 5px #AAA;overflow-x:auto;white-space:nowrap;">';
foreach ($link as $value) {
   $html.=zd($value['dbserver'],$value['dbusername'],$value['dbpassword'],$value['database'],$value['title'],$gourl_height);
   $gourl.='<a style="display:block,float:left;color:#333;text-decoration:none;" href="#'.md5($value['title']).'">'.$value['title'].'</a>&nbsp;&nbsp;';
}
$gourl.='</div>';
function zd($dbserver,$dbusername,$dbpassword,$database,$title,$gourl_height){
    $mysql_conn = @mysql_connect("$dbserver", "$dbusername", "$dbpassword") or die("Mysql connect is error.");
    mysql_select_db($database, $mysql_conn);
    mysql_query('SET NAMES utf8', $mysql_conn);
    $table_result = mysql_query('show tables', $mysql_conn);
    //取得所有的表名
    while ($row = mysql_fetch_array($table_result)) {
        $tables[]['TABLE_NAME'] = $row[0];
    }

    //循环取得所有表的备注
    foreach ($tables AS $k=>$v) {
        $sql  = 'SELECT * FROM ';
        $sql .= 'INFORMATION_SCHEMA.TABLES ';
        $sql .= 'WHERE ';
        $sql .= "table_name = '{$v['TABLE_NAME']}'  AND table_schema = '{$database}'";
        $table_result = mysql_query($sql, $mysql_conn);
        while ($t = mysql_fetch_assoc($table_result) ) {
            $tables[$k]['TABLE_COMMENT'] = $t['TABLE_COMMENT'];
        }

        $sql  = 'SELECT * FROM ';
        $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
        $sql .= 'WHERE ';
        $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database}'";

        $fields = array();
        $field_result = mysql_query($sql, $mysql_conn);
        while ($t = mysql_fetch_array($field_result) ) {
            $fields[] = $t;
        }
        $tables[$k]['COLUMN'] = $fields;
    }
    mysql_close($mysql_conn);


    $html = '';
    //循环所有表
    foreach ($tables AS $k=>$v) {
        $tablename='';
        if(!empty($v['TABLE_COMMENT'])){
            $tablename='('. $v['TABLE_COMMENT'] .')';
        }
        
    
        $table_id = md5($database.'<->'.$v['TABLE_NAME']);
        $html .= '<table style="width:1024px;"  border="1" cellspacing="0" cellpadding="0" align="center">';
        $html .= '<caption style="cursor:pointer" onclick="op('."'".$table_id."'".')"><span style="color:#faaa00">' .$database.'</span>.'. $v['TABLE_NAME'] . $tablename . '</caption>';
        $html .= '<tbody style="width:100%;display:none" id="'.$table_id.'"><tr><th style="padding-left:5px;">字段名</th><th style="padding-left:5px;">数据类型</th><th style="padding-left:5px;">默认值</th>
          <th style="text-align:center">非空</th><th style="text-align:center">索引</th>
         <th style="text-align:center">递增</th><th style="padding-left:5px;">备注</th></tr>';
        $html .= '';

        foreach ($v['COLUMN'] AS $f) {
            //var_dump($f);exit;
            $html .= '<tr><td class="c1">' . $f['COLUMN_NAME'] . '</td>';
            $html .= '<td class="c2">' . $f['COLUMN_TYPE'] . '</td>';
            $html .= '<td class="c3">&nbsp;' . $f['COLUMN_DEFAULT'] . '</td>';
            $html .= '<td class="c4">&nbsp;' . $f['IS_NULLABLE'] . '</td>';
            $html .= '<td class="c5">&nbsp;' . $f['COLUMN_KEY'] . '</td>';
            $html .= '<td class="c5">' . ($f['EXTRA']=='auto_increment'?'是':'&nbsp;') . '</td>';
            $html .= '<td class="c6">&nbsp;' . $f['COLUMN_COMMENT'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
    }

    $head='<div style="height:'.$gourl_height.'" id="'.md5($title).'"></div><div style="margin:10 auto;font-size:26px;width:500px;text-align:center;color:#faaa00;font-weight:bold">' . $title . '</div>';
    $html=$head.$html;
    return $html;
}
$all_html='';
//输出
$all_html.= '<html>
 <head>
 <title>' . '数据字典' . '</title>
 <meta http-equiv="content-type" content="text/html; charset=utf-8" />
 <style>
 body,td,th {font-family:"宋体"; font-size:12px;padding:0px;margin:0px;}
 table{border-collapse:collapse;border:1px solid #CCC;background:#efefef;margin-bottom:30px;}
 table caption{text-align:left; background-color:#fff; line-height:2em; font-size:14px; font-weight:bold; }
 table th{text-align:left; font-weight:bold;height:26px; line-height:26px; font-size:12px; border:1px solid #CCC;}
 table td{height:20px; font-size:12px; border:1px solid #CCC;background-color:#fff;}
 .c1{ width: 120px;}
 .c2{ width: 120px;}
 .c3{ width: 200px;}
 .c4{ width: 26px;text-align:center}
 .c5{ width: 26px;text-align:center}
 .c6{ width: 270px;}
 </style>
 </head>
 <body>';
$all_html.=  $gourl;
$all_html.=  $html;
$all_html.='<div style="position: fixed;width:100%;background:#CCC;height:22px;bottom:0px;left:0px;text-align:right;color:#faaa00;line-height:22px">数据字典V1.0 by Gwalker &nbsp;&nbsp;&nbsp;&nbsp;</div>';
$all_html.='<script>function op(id){var table = document.getElementById(id);if(table.style.display=="none"){table.style.display="";}else{table.style.display="none";}/*table.style.display="none";*/ console.log(table.style.display);}</script>';
$all_html.=  '</body></html>';
file_put_contents('index.html', $all_html);//生成缓存

echo $all_html;