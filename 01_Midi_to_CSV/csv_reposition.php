<?php
require("midi_technical_references.php");
$source_directory = "02_CSV_song_files";
$target_directory = "03_Clean_CSV_song_files";
chdir($source_directory);
foreach(glob("*.csv") as $file_name)
{
	if(($read_handle = fopen($file_name, "r")) !== FALSE)
	{
		echo "$file_name\n";
		$row = 1;
		$beat = 0;
		$time_signature = "0/0";
		$key_signature = "";
		$channel = 0;
		while(($data = fgetcsv($read_handle, 1000, ",")) !== FALSE)
		{
			$track = trim($data[0]);
			if(!isset($instrument[$track]))
			{
				$instrument[$track] = '';
			}
			if(!isset($previous_row[$track]))
			{
				$previous_row[$track] = ["time" => 0, "row" => 0];
				$previous_duration[$track] = 0;
			}
			$time = trim($data[1]);
			$code = trim($data[2]);
			$octave = -1;
			$note = "";
			$velocity = 0;
			switch(strtolower($code))
			{
				case 'instrument_name_t':
					$instrument[$track] = trim($data[3]);
					break;
				case 'tempo':
					$beat = round(60000000/trim($data[3]));
					break;
				case 'time_signature':
					$number_of_notes = trim($data[3]);
					$notes_duration = trim($data[4]);
					$time_signature = "$number_of_notes/$notes_duration";
					break;
				case 'key_signature':
					$key = trim($data[3]);
					$key = $scale[$key];
					break;
				case 'note_on_c':
					$channel = trim($data[3]);
					$note = (int)trim($data[4]);
					$note = substr($notes[$note]["note"],0,2);
					switch($note)
					{
						case "Do":
							$note = (int)trim($data[4]) + $key[0];
							break;
						case "Re":
							$note = (int)trim($data[4]) + $key[1];
							break;
						case "Mi":
							$note = (int)trim($data[4]) + $key[2];
							break;
						case "Fa":
							$note = (int)trim($data[4]) + $key[3];
							break;
						case "So":
							$note = (int)trim($data[4]) + $key[4];
							break;
						case "La";
							$note = (int)trim($data[4]) + $key[5];
							break;
						case "Si":
							$note = (int)trim($data[4]) + $key[6];
							break;
					}
					$note = $notes[$note];
					$octave = $note["octave"];
					$frequency = $note["frequency"];
					$note = $note["note"];
					$velocity = round(trim($data[5]) / 127 * 100);
					if($previous_row[$track]["row"])
					{
						$find_time = $previous_row[$track]["time"];
						$find_row = $previous_row[$track]["row"];
						if($time - $find_time)
						{
							$base_duration = round($beat / ($time - $find_time));
							if($base_duration >= 12)
							{
								$output[$find_time][$find_row]["duration"] = "Sixteenth";
								$previous_duration[$track] = "Sixteenth";
							}
							elseif($base_duration >= 6)
							{
								$output[$find_time][$find_row]["duration"] = "Eighth";
								$previous_duration[$track] = "Eighth";
							}
							elseif($base_duration >= 3)
							{
								$output[$find_time][$find_row]["duration"] = "Quarter";
								$previous_duration[$track] = "Quarter";
							}
							elseif($base_duration >= 1.5)
							{
								$output[$find_time][$find_row]["duration"] = "Half";
								$previous_duration[$track] = "Half";
							}
							else
							{
								$output[$find_time][$find_row]["duration"] = "Whole";
								$previous_duration[$track] = "Whole";
							}
						}
						else
						{
							$output[$find_time][$find_row]["duration"] = $previous_duration[$track];
						}
					}
					$previous_row[$track] = ["time" => $time, "row" => $row];
					echo str_pad($note,5," ",STR_PAD_RIGHT) . "\r";
					break;
				case 'note_off_c':
					$channel = trim($data[3]);
					$note = (int)trim($data[4]);
					$note = substr($notes[$note]["note"],0,2);
					switch($note)
					{
						case "Do":
							$note = (int)trim($data[4]) + $key[0];
							break;
						case "Re":
							$note = (int)trim($data[4]) + $key[1];
							break;
						case "Mi":
							$note = (int)trim($data[4]) + $key[2];
							break;
						case "Fa":
							$note = (int)trim($data[4]) + $key[3];
							break;
						case "So":
							$note = (int)trim($data[4]) + $key[4];
							break;
						case "La";
							$note = (int)trim($data[4]) + $key[5];
							break;
						case "Si":
							$note = (int)trim($data[4]) + $key[6];
							break;
					}
					$note = $notes[$note];
					$octave = $note["octave"];
					$note = $note["note"];
					$velocity = 0;
					break;
			}
			$output[$time][$row] = ["time" => $time,
				"track" => $track,
				"instrument" => $instrument[$track],
				"beat" => $beat,
				"octave" => $octave,
				"note" => $note,
				"frequency" => $frequency,
				"duration" => "",
				"force" => $velocity];
			$row++;
		}
		fclose($read_handle);

		if($write_handle = fopen("../$target_directory/$file_name", 'w'))
		{
			fputcsv($write_handle, ["Time","Track","Instrument","Beat","Octave","Note","Frequency","Duration","Force (%)"]);
			ksort($output);
			foreach($output as $row)
			{
				$last_time = 0;
				ksort($row);
				foreach($row as $record)
				{
					if(strlen($record["note"]) && ($record["force"] > 0))
					{
						fputcsv($write_handle, $record);
					}
				}
			}
			fclose($write_handle);
		}
	}
}
