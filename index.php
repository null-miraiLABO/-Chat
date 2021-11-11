<?php
// db設定,queryrunpre(),newdata()
require_once('db.php');

//セッションスタート
session_name('sesname');
session_start();
session_regenerate_id(true);

//各ソースパス
$INDEX_HTML="src/index.html";
$CHAT_HTML="src/chat.html";

$_SESSION["nm"]=$_POST["nm"];
$_SESSION["msg"]=$_POST["msg"];

//新規作成
if($_SESSION["nm"]!="" && $_SESSION["msg"]!=""){
        $msg=str_replace(array("\r\n","\r","\n"),'<br>', htmlspecialchars($_POST['msg']));//改行
        newdata($_SESSION["nm"],$msg,date('Y/m/d H:i:s'),$_SERVER["REMOTE_ADDR"]);
}else if($_SERVER["REQUEST_METHOD"]=='POST'){
        $_SESSION['Err'].="※入力してください";
}

//dbから消去
if(isset($_GET['del']) && $_GET['del']!=""){
        $dbwhere=" WHERE `id` = :whereid";
        $queryParam[":whereid"]=$_GET['del'];
        queryrunpre("DELETE FROM ".$dbtable.$dbwhere,$queryParam);
}


//ページ表示。$OnceView個毎
$OnceView=10;
if( !isset($_GET['p']) || !is_numeric($_GET['p']) || $_GET['p']<1 ){
        $_GET['p']=1;
}
$lower = ($_GET['p']-1) * $OnceView;

//chat表示順
switch($_GET['sort']){
        case "1":
                $sort = "`date` ASC";
                break;
        case "2":
                $sort = "`date` DESC";
                break;
        case "3":
                $sort = "`name` ASC";
                break;
        case "4":
                $sort = "`name` DESC";
                break;
        default:
                $sort = "`id` ASC";
                break;
}

//もし、$_GET['selname']がセットされていたら名前ごとに抽出
if(isset($_GET['selname']) && $_GET['selname']!=""){
        $queryParam[":selname"]=$_GET['selname'];
        $dbsel=" WHERE `name` = :selname";
}

//表示データ
$db_chatdata = queryrunpre("SELECT * FROM ".$dbtable.$dbsel." ORDER BY ".$sort." LIMIT ".$lower.",".$OnceView,$queryParam);

//db_chatdata配列がなくなるまで置き換える
for($i=0;$i<count($db_chatdata);$i+=1){
        $viewchat .= viewchat($db_chatdata[$i]["id"],$db_chatdata[$i]["name"],$db_chatdata[$i]["date"],$db_chatdata[$i]["message"]);
}

//ページリンクの生成(前へ,次へ)
$PageLink=array();
$cn_tmp=queryrunpre("SELECT COUNT(*) FROM ".$dbtable."",null);
if(isset($_GET['selname']) && $_GET['selname']!=""){
        $cn_tmp=queryrunpre("SELECT COUNT(*) FROM ".$dbtable." WHERE `name` ='".$_GET['selname']."'",null);
}
$count=$cn_tmp[0][0];

if($_GET['p']>=2){
        $nmb=$_GET['p']-1;
        $PageLink[]='<a href="?p='.$nmb.'&sort='.$_GET['sort'].'&selname='.$_GET['selname'].'">前へ</a>';
}
if($lower+$OnceView<=$count){
        $nma=$_GET['p']+1;
        $PageLink[]='<a href="?p='.$nma.'&sort='.$_GET['sort'].'&selname='.$_GET['selname'].'">次へ</a>';
}

//ページリンクの生成(ページ数)
//ページ総数$countを$OnceView毎に分ける
$PageNumber=array();
$flag = ($count/$OnceView)+1;
for($i=1;$i<$flag;$i++){
        $PageNumber[$i]='&nbsp;<a href="?p='.$i.'&sort='.$_GET['sort'].'&selname='.$_GET['selname'].'">'.$i.'</a>&nbsp;';
        //表示中のページはリンク付けない
        if($i==$_GET['p']){
                $PageNumber[$i]='&nbsp;'.$i.'&nbsp;';
        }
}

$htmldata=file_get_contents($INDEX_HTML);
$htmldata=str_replace("{{Err}}",$_SESSION['Err'],$htmldata);
$htmldata=str_replace("{{Chat}}",$viewchat,$htmldata);
$htmldata=str_replace("{{Page}}",'<p>'.$PageLink[0]."&nbsp;&nbsp;".implode('｜',$PageNumber)."&nbsp;&nbsp;".$PageLink[1].'</p>',$htmldata);

$_SESSION['Err']="";

echo $htmldata;

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
