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

$OnceView=10;

//chatデータ、読み込み、chat置き換え
if($_SESSION["nm"]!="" && $_SESSION["msg"]!=""){
        newdata($_SESSION["nm"],$_SESSION["msg"],date('Y/m/d H:i:s'),$_SERVER["REMOTE_ADDR"]);
}

//dbから消去
if(isset($_GET['del']) && $_GET['del']!=""){
        queryrunpre("DELETE FROM `nmmsg_chat` WHERE `id` ='".$_GET['del']."'",null);
}

if( !isset($_GET['p']) || !is_numeric($_GET['p']) || $_GET['p']<1 ){
        $_GET['p']=1;
}
$lower = ($_GET['p']-1) * $OnceView;
//$db_chatdata = queryrunpre("SELECT * FROM `nmmsg_chat` LIMIT ".$upper,null);
$db_chatdata = queryrunpre("SELECT * FROM `nmmsg_chat` LIMIT ".$lower.",".$OnceView,null);

//もし、$_GET['selname']がセットされていたら名前ごとに抽出
if(isset($_GET['selname']) && $_GET['selname']!=""){
        $db_chatdata = queryrunpre("SELECT * FROM `nmmsg_chat` WHERE `name` ='".$_GET['selname']."'",null);
}

//db_chatdata配列がなくなるまで置き換える
for($i=0;$i<count($db_chatdata);$i+=1){
        $viewchat .= viewchat($db_chatdata[$i]["id"],$db_chatdata[$i]["name"],$db_chatdata[$i]["date"],$db_chatdata[$i]["message"]);
}

$PageLink=array();

$cn_tmp=queryrunpre("SELECT COUNT(*) FROM `nmmsg_chat`",null);
$count=$cn_tmp[0][0];

if($_GET['p']>=2){
        $nmb=$_GET['p']-1;
        $PageLink[]='<a href="?p='.$nmb.'">前へ</a>';
}
if($lower+$OnceView<=$count){
        $nma=$_GET['p']+1;
        $PageLink[]='<a href="?p='.$nma.'">次へ</a>';
}

$htmldata=file_get_contents($INDEX_HTML);
$htmldata=str_replace("{{Chat}}",$viewchat,$htmldata);
$htmldata=str_replace("{{Page}}",'<p>'.implode('｜',$PageLink).'</p>',$htmldata);

//チェック用。本番不要
$check="low=".$lower."<br>"."page=".$_GET['p']."<br>"."count=".$count."<br>";
echo $check;

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

function viewchat($id,$nm,$date,$msg){
  $htmldata='<p class="element"><span>名前:<a href="?selname='.$nm.'">'.$nm.'</a></span>&nbsp;'.$date.'&nbsp;<a href="?del='.$id.'">'.'消去'.'</a></br>'.$msg.'</p>';
  return $htmldata;
}

?>
