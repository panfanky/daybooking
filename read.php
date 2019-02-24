<?
include("config.php");
$admin=false;
if($_GET["mozek"]=="batabata") $admin=true;

//get CSV
$tablearr = array_map(function($v){return str_getcsv($v, ";");}, file('data.csv'));
echo json_encode($tablearr);