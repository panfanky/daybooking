<?
include("config.php");

$admin=false;
if($_GET["mozek"]=="batabata") $admin=true;

//get CSV
$tablearr = array_map(function($v){return str_getcsv($v, ";");}, file('data.csv'));

//edit array
foreach ($tablearr as $k=>$row){
	if($_POST["proposed"][$k]) $tablearr[$k][2]=$_POST["proposed"][$k];
	if($admin && $_POST["approved"]) $tablearr[$k][1]=$_POST["approved"][$k];
}

//save array
$fp = fopen('data.csv', 'w');
foreach ($tablearr as $fields) {
	fputcsv($fp, array($fields[0],$fields[1],$fields[2]), ";");
}
fclose($fp);