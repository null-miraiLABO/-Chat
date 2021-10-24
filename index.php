<?php
//セッションスタート
session_name('sesname');
session_start();
session_regenerate_id(true);

//各ソースパス
$INDEX_HTML="src/index.html";
$CHAT_HTML="src/chat.html";

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
        $msg=str_replace(array("\r\n","\r","\n"),'<br>', htmlspecialchars($_POST['msg']));//改行
        newdata($_SESSION["nm"],$msg,date('Y/m/d H:i:s'),$_SERVER["REMOTE_ADDR"]);
}

//dbから消去
if(isset($_GET['del']) && $_GET['del']!=""){
        queryrunpre("DELETE FROM `nmmsg_chat` WHERE `id` ='".$_GET['del']."'",null);
}

//ページ表示。$OnceView個毎
$OnceView=10;
if( !isset($_GET['p']) || !is_numeric($_GET['p']) || $_GET['p']<1 ){
        $_GET['p']=1;
}
$lower = ($_GET['p']-1) * $OnceView;
$db_chatdata = queryrunpre("SELECT * FROM `nmmsg_chat` LIMIT ".$lower.",".$OnceView,null);

//もし、$_GET['selname']がセットされていたら名前ごとに抽出
if(isset($_GET['selname']) && $_GET['selname']!=""){
        $db_chatdata = queryrunpre("SELECT * FROM `nmmsg_chat` WHERE `name` ='".$_GET['selname']."'",null);
}

//時間,名前をソート抽出
if(isset($_GET['sort']) && $_GET['sort']!=""){
        switch($_GET['sort']){
                case "0":
                        $db_chatdata = queryrunpre("SELECT * FROM `nmmsg_chat` ORDER BY `id` ASC LIMIT ".$lower.",".$OnceView,null);
                        break;
                case "1":
                        $db_chatdata = queryrunpre("SELECT * FROM `nmmsg_chat` ORDER BY `date` ASC LIMIT ".$lower.",".$OnceView,null);
                        break;
                case "2":
                        $db_chatdata = queryrunpre("SELECT * FROM `nmmsg_chat` ORDER BY `date` DESC LIMIT ".$lower.",".$OnceView,null);
                        break;
                case "3":
                        $db_chatdata = queryrunpre("SELECT * FROM `nmmsg_chat` ORDER BY `name` ASC LIMIT ".$lower.",".$OnceView,null);
                        break;
                case "4":
                        $db_chatdata = queryrunpre("SELECT * FROM `nmmsg_chat` ORDER BY `name` DESC LIMIT ".$lower.",".$OnceView,null);
                        break;
        }
}

//db_chatdata配列がなくなるまで置き換える
for($i=0;$i<count($db_chatdata);$i+=1){
        $viewchat .= viewchat($db_chatdata[$i]["id"],$db_chatdata[$i]["name"],$db_chatdata[$i]["date"],$db_chatdata[$i]["message"]);
}

//ページリンクの生成(前へ,次へ)
$PageLink=array();
$cn_tmp=queryrunpre("SELECT COUNT(*) FROM `nmmsg_chat`",null);
$count=$cn_tmp[0][0];

if($_GET['p']>=2){
        $nmb=$_GET['p']-1;
        $PageLink[]='<a href="?p='.$nmb.'&sort='.$_GET['sort'].'">前へ</a>';
}
if($lower+$OnceView<=$count){
        $nma=$_GET['p']+1;
        $PageLink[]='<a href="?p='.$nma.'&sort='.$_GET['sort'].'">次へ</a>';
}

//ページリンクの生成(ページ数)
//ページ総数$countを$OnceView毎に分ける
$PageNumber=array();
$flag = ($count/$OnceView)+1;
for($i=1;$i<$flag;$i++){
        $PageNumber[$i]='&nbsp;<a href="?p='.$i.'&sort='.$_GET['sort'].'">'.$i.'</a>&nbsp;';
}

$htmldata=file_get_contents($INDEX_HTML);
$htmldata=str_replace("{{Ppar}}",$_GET['p'],$htmldata);
$htmldata=str_replace("{{Chat}}",$viewchat,$htmldata);
$htmldata=str_replace("{{Page}}",'<p>'.$PageLink[0]."&nbsp;&nbsp;".implode('｜',$PageNumber)."&nbsp;&nbsp;".$PageLink[1].'</p>',$htmldata);

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

//chatデータをchat.html上で置き換え
function viewchat($id,$nm,$date,$msg){
global $CHAT_HTML;
$htmldata=file_get_contents($CHAT_HTML);

$data = array(
        "{{Name}}"=>"$nm",
        "{{Date}}"=>"$date",
        "{{Id}}"=>"$id",
        "{{Msg}}"=>"$msg"
);
foreach($data as $key=>$value){
        $htmldata=str_replace($key,$value,$htmldata);
}

return $htmldata;
}

?>
