<?php
/*
Form to allow a user to select 2 Sierra users to compare access rights.

Author: Terry Brady, Georgetown University Libraries

License information is contained below.

Copyright (c) 2014, Georgetown University Libraries All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer. 
in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials 
provided with the distribution. THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, 
BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

See http://techdocs.iii.com/sierradna/

*/
include 'Sierra.php';

$SIERRA = new Sierra();


$user1 = $_GET["user1"];
$user2 = $_GET["user2"];
$status = ($user1 == "") ? "Please select at least one user" : "";
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<script	src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/ui-lightness/jquery-ui.css"></link>
<style type="text/css">
  td.role, th.role {width: 350px;}
  form {border: blue 2px solid; background-color: #EEEEEE;width:400px;margin: 20px auto; padding: 10px;}
  #permtable th {background-color: #EEEEEE;}
  #permtable {margin: 20px auto;}
  #main {text-align: center; width: 800px; margin: 0 auto;}
  #filterdiv {margin: 10px; padding: 10px; border: orange solid 3px; background-color: #FCE4C9;width:550px;margin: 20px auto;}
</style>
<script type="text/javascript">
  $(document).ready(function(){
  	$("#filter").change(function(){
  	  var f = "tr.header, tr." + $("#filter").val();
  	  $("#permtable tr").hide();
  	  $("#permtable").find(f).show();
  	  var arr = [];
  	  $("tr:visible input[name=id]").each(function(){
  	  	arr[arr.length] = Number($(this).val());
  	  });
  	  $("#paste").val(arr.sort(function(a, b){return a-b}).join());
  	});
  	$("#filter option[value=neither]").append(" (" + $("#permtable tr.neither").length + ")");
  	$("#filter option[value=both]").append(" (" + $("#permtable tr.both").length + ")");
  	$("#filter option[value=left]").append(" (" + $("#permtable tr.left").length + ")");
  	$("#filter option[value=right]").append(" (" + $("#permtable tr.right").length + ")");
  });
</script>
</head>
<body>
<div id="main">
<div id="formPermissions">
<form method="GET" action="queryPermissions2.php" >
<p>Run a report to compare the Sierra permissions for 2 different users.</p>
<div id="status"><?php echo $status?></div>
<?php $SIERRA->getUserWidget("user1","User to report on", $user1);?>
<?php $SIERRA->getUserWidget("user2","User to compare with", $user2);?>
<p align="center">
	<input id="ingestSubmit" type="submit" title="Submit Job"/>
</p>
</form>
</div>
<div>
<?php
if ($user1 != "") {
   	showReport($SIERRA, $user1, $user2);
} 

function showReport($SIERRA, $user1, $user2) {  
	echo <<< HERE
<div id="filterdiv">
<label for="filter">Filter Results: </label>
<select id="filter" name="filter">
  <option value="all"/>
  <option value="neither">Neither</option>
  <option value="both">Both</option>
  <option value="left">Left Only</option>
  <option value="right">Right Only</option>
</select>
<input type="text" readonly id="paste" name="paste" size="50"/>
</div>
<table id='permtable'>
<tr class='header'><th class='role'>Role</th>
<th>{$user1}</th>
<th>{$user2}</th>
</tr>
HERE;

    $sql = <<< HERE2
select 
  rn.name, rn.iii_role_id,
  (
    select 'Yes'
    from sierra_view.iii_user_iii_role ur
    inner join sierra_view.iii_user u on
      u.id = ur.iii_user_id
      and u.name = :user1
    where ur.iii_role_id = rn.iii_role_id
  ), 
  (
    select 'Yes'
    from sierra_view.iii_user_iii_role ur
    inner join sierra_view.iii_user u on
      u.id = ur.iii_user_id
      and u.name = :user2
    where ur.iii_role_id = rn.iii_role_id
  )
from sierra_view.iii_role_name rn
order by rn.name
HERE2;

	$dbh = $SIERRA->getPdoDb();
	$stmt = $dbh->prepare($sql);
	$arg = array("user1" => $user1, "user2" => $user2);
	$result = $stmt->execute($arg);
 	if (!$result) {
 		print($sql);
  	    print_r($dbh->errorInfo());
    	die("Error in SQL query: ");
 	}       
	$result = $stmt->fetchAll();
 	foreach ($result as $row) {
 		$cl = getClass($row[2], $row[3]);
 		echo <<< HERE
 		<tr class='all {$cl}'>
 		<td class='role'>{$row[0]} ({$row[1]}) <input name="id" type="hidden" value="{$row[1]}"/></td>
 		<td>{$row[2]}</td>
 		<td>{$row[3]}</td>
 		</tr>
HERE;
	}  
	
	echo "</table>";
}

function getClass($a, $b) {
	if ($a == "" && $b == "") return "neither";
	if ($a != "" && $b != "") return "both";
	if ($a != "") return "left";
	return "right";
}
?>
</div>

</div>
</body>
</html>