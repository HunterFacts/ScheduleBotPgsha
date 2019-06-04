<?php 
function MessageSchedule ($dataText){
	$dateStart = date("d.m.Y");
	if ($dataText == "На завтра"){
		$d = strtotime("+1 day");
		$dateEnd = date("d.m.Y", $d);
		$text = "следующий день";
	}
	else if ($dataText == "На неделю"){
		$d = strtotime("+7 day");
		$dateEnd = date("d.m.Y", $d);
		$text = "следующую неделю";
	}
	$myCurl = curl_init();
	curl_setopt_array($myCurl, array(
		CURLOPT_URL => 'https://pgsha.ru/sys/shedule/getsheduleclasseszo',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query(
			array(
			'stream_id' => 1422,
			'term' => 2,
			'date_start' => $dateStart,
			'date_end' => $dateEnd
			)
		)
	));

	$response = curl_exec($myCurl);
	curl_close($myCurl);
	$massiv = json_decode($response, true);
	//print_r($massiv);
	$check = 1;
	$weekcheck = "";
	$return = "";
	foreach ($massiv as $value){
		if ($check == 1 or $weekcheck != $value["weekday_name"]){
			$return .= "<br />".$value["weekday_name"]." ".$value["date_start_text"]."<br />";
			$check = 0;
			$weekcheck = $value["weekday_name"];
		}
		if ($value["subgroup_id"] == "3135" and $value["classtype_id"] == "3") {
			$subgroup = "2";
		}
		else if ($value["subgroup_id"] == "3338" and $value["classtype_id"] == "3"){
			$subgroup = "1";
		}
		else {
			$subgroup = "все";
		}
		//print_r($value);
		$return .= $value["daytime_name"]." | ".$value["discipline_name"]." | ".$value["cabinet_fullnumber_wotype"]." | ".$value["notes"]." (".$subgroup.")<br />";
	}
	if ($return == ""){
		$return = "Нечего на ".$text." нет";
	}
	return $return;
}
?>
