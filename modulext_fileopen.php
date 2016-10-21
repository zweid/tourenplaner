<?php

	$IP=getenv("REMOTE_ADDR");
	if(is_numeric($ID)==TRUE)
	{
		$res=tri_db_query ($GLOBALS['datenbanknamecms'], "SELECT * FROM touren where ID='$ID'");
		while ($row = mysql_fetch_array ($res))
		{
			if(md5(md5($row['generiert_am']).md5($row['datum']).md5($row['kundennummer']))==$keycode)
			{   
				$datei_id 	= tri_wawi_nummernausgabe('touren',$row['ID']);
				$datei_id 	= preg_replace('/[^a-zA-Z0-9-_]/','-',$datei_id);
				
				header("Content-type: application/pdf");
				header("Content-Disposition: attachment; filename=TO_".$datei_id.".pdf");
				readfile("cmssystem/touren/touren/$row[ID].pdf");
			};
		};
	};
?> 
