<?php
//設定部
$user ="db_mizukinet";
$password = "4CBEpHSn";
$dbname = "db_mizukinet_1";
$dbtable = "nmmsg_chat";

//データベース初期化部
$dsn  = "mysql:host=localhost;charset=utf8;dbname=".$dbname;
$db = new PDO($dsn,$user,$password);

//mysql文を使ってデータを得る
function queryrunpre($query,$param)
{
        global $db;
        $pre = $db->prepare($query);
        if($pre->execute($param))
                return $pre->fetchAll();
        else
                return false;
}

//新しいデータを作る関数
function newdata($name,$message,$date,$ip)
{
        global $db,$dbtable;
        $insert_query = "INSERT INTO ".$dbtable." (name,message,date,ip) ".
          "VALUES(".$db->quote($name).",".$db->quote($message).",".$db->quote($date).",".$db->quote($ip).")";

        queryrunpre($insert_query,null);
}

?>