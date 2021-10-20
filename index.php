<?php
//セッションスタート
session_name('sesname');
session_start();
session_regenerate_id(true);

//各ソースパス
$INDEX_HTML="src/index.html";

//設定部
$user ="db_mizukinet";//サーバーの設定による
$password = "4CBEpHSn";//サーバーの設定による
$dbname = "db_mizukinet_1";//サーバーの設定による
$dbtable = "nmmsg_chat";//ここは自分で指定するところ

//データベース初期化部
$dsn  = "mysql:host=localhost;charset=utf8;dbname=".$dbname;
$db = new PDO($dsn,$user,$password);

$_SESSION["nm"]=$_POST["nm"];
$_SESSION["msg"]=$_POST["msg"];

//chatデータ、読み込み、chat置き換え
if($_SESSION["nm"]!="" && $_SESSION["msg"]!=""){
        newdata($_SESSION["nm"],$_SESSION["msg"],date('Y/m/d H:i:s'),$_SERVER["REMOTE_ADDR"]);
}

$db_chatdata = queryrunpre("SELECT * FROM `nmmsg_chat`",null);
for($i=0;$i<count($db_chatdata);$i+=1){
        $viewchat .= viewchat($db_chatdata[$i]["name"],$db_chatdata[$i]["date"],$db_chatdata[$i]["message"]);
}

$htmldata=file_get_contents($INDEX_HTML);
$htmldata=str_replace("{{Chat}}",$viewchat,$htmldata);

echo $htmldata;

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

function viewchat($nm,$date,$msg){
  $htmldata="<p><span>名前:".$nm."</span>&nbsp;".$date."</br>".$msg."</p>";
  return $htmldata;
}
?>
