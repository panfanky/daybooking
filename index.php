<?include("config.php");?>
<html>
<head>
<meta charset="UTF-8">
<title>Daybooking</title>
<style>
.approved{
	background:lightgreen;
}
</style>
</head>
<body>
<form id="bookform">
<table>
<?

$admin=false;
if($_GET["whoisthere"]==ADMINPASS) $admin=true;

//get CSV
$tablearr = array_map(function($v){return str_getcsv($v, ";");}, file('data.csv'));

//edit array
foreach ($tablearr as $k=>$row){
	if($_POST["proposed"][$k]) $tablearr[$k][2]=$_POST["proposed"][$k];
	if($admin && $_POST["approved"]) $tablearr[$k][1]=$_POST["approved"][$k];
}
// echo date("Y-m-d", strtotime($tablearr[$k][0]."+1day"));

// extend array to 6 months
 while(strtotime($tablearr[$k][0]) < strtotime(DAYS_UP_TO)){
	$nextday=date("Y-m-d", strtotime($tablearr[$k][0]."+1day"));
	  $k++;
	$tablearr[$k][0]=$nextday;
 }

//save array
$fp = fopen('data.csv', 'w');
foreach ($tablearr as $fields) {
    fputcsv($fp, $fields, ";");
}
fclose($fp);

//print data to form
foreach ($tablearr as $row){
	$rowtime=strtotime($row[0]);
	
	//show only future and near past
	if(date("d", $rowtime)=="01"){
	?>
	<tr><td colspan="3" style="text-align:center"><strong><?echo date("F", $rowtime);?></strong></td></tr>
	<?
	}
	?>
		<tr id="<?echo$row[0];?>"<?if($rowtime<strtotime(DAYS_FROM)){echo ' style="display:none;"';
}?>>
		 <td>
		  <?echo date("D",$rowtime)." ".date("d.m.", $rowtime);?>
		 </td>
		 <td>
		<input<?if(!$admin)echo " disabled";?> class="approved" name="approved[]" type="text" value="<?echo $row[1];?>">
		 </td>
		 <td>
		  <input class="proposed" type="text" name="proposed[]" value="<?echo $row[2]?>">
		 </td>
		 <?if($admin){?>
		 <td>
		  <a href="#" class="approve">Approve</a>
		 </td>
		 <?}?>
		</tr>
	<?
}
?>
</table>
</form>
<script src="jquery-3.3.1.min.js"></script>
<script>
	function write(){
		$.post("write.php?mozek=<?echo$_GET["mozek"];?>",$("#bookform").serialize(), function(data){	
			// debug here
			// console.log(data);
		});
	}
	function read(){
		$.post("read.php?mozek=<?echo$_GET["mozek"];?>",$("#bookform").serialize(), function(data){	
			//get json to array
			var gotobject = JSON.parse(data);			
			
			//make dates array keys of multidimensional array with values like "2019-02-16": Array [ "realprogram", "proposal" ]
			var datesarr=[];
			$.each(gotobject,function(){
				datesarr[this[0]]=[this[1],this[2]];
			});
			
			//print it to fields that are not focused
			$("tr").each(function(){
				//0 is approved, 1 is proposed
				var approved=$(this).find(".approved");
				if(!approved.is(":focus"))
					approved.val(datesarr[$(this).attr("id")][0]);
					
				var proposed=$(this).find(".proposed");
				if(!proposed.is(":focus"))
					proposed.val(datesarr[$(this).attr("id")][1]);
			});
			
		});
	}
	
$(document).ready(function(){
	//get others inputs every 5000m	s
	setInterval(function(){
		read();
	},5000);
});
$(document).on("keyup change","input",function(){
	write();
});
$(document).on("click",".approve",function(e){
	e.preventDefault();
	$(this).closest("tr").find(".approved").val($(this).closest("tr").find(".proposed").val());
	write();
});
</script>
</body>
</html>