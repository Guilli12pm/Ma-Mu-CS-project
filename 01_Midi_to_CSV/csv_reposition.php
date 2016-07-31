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
		$octave = "";
		$note = "";
		$velocity = "";
		while(($data = fgetcsv($read_handle, 1000, ",")) !== FALSE)
		{
			$track = trim($data[0]);
			if(!isset($instrument[$track]))
			{
				$instrument[$track] = '';
			}
			$time = trim($data[1]);
			$code = trim($data[2]);
			switch($code)
			{
				case 'Instrument_name_t':
					$instrument[$track] = trim($data[3]);
					break;
				case 'Tempo':
					$beat = round(60000000/trim($data[3]));
					break;
				case 'Time_signature':
					$number_of_notes = trim($data[3]);
					$notes_duration = trim($data[4]);
					$time_signature = "$number_of_notes/$notes_duration";
					break;
				case 'Key_signature':
					$key = trim($data[3]);
					$key = $scale[$key];
					$major = trim($data[4]);
					$key_signature = "(";
					foreach($key as $key_item)
					{
						$key_signature .= "$key_item ";
					}
					$key_signature .= ") $major";
					break;
				case 'Note_on_c':
					$channel = trim($data[3]);
					$note = trim($data[4]);
					$note = $notes[$note];
					$octave = trim($note["octave"]);
					$note = $note["note"];
					$velocity = round(trim($data[5]) / 127 * 100);
					echo str_pad($note,5," ",STR_PAD_RIGHT) . "\r";
					break;
				case 'Note_off_c':
					$channel = trim($data[3]);
					$note = trim($data[4]);
					$note = $notes[$note];
					$octave = trim($note["octave"]);
					$note = $note["note"];
					$velocity = 0;
					break;
			}
			$output[$time][$row] = [$time,$track,$instrument[$track],$beat,$time_signature,$key_signature,$octave,$note,$velocity];
			$row++;
		}
		fclose($read_handle);


		if($write_handle = fopen("../$target_directory/$file_name", 'w'))
		{
			fputcsv($write_handle, ["Time","Track","Instrument","Beat","Time Signature","Key Signature","Octave","Note","Force (%)"]);
			ksort($output);
			foreach($output as $row)
			{
				ksort($row);
				foreach($row as $record)
				{
					if(strlen($record[7]) && ($record[8] > 0))
					{
						fputcsv($write_handle, $record);
					}
				}
			}
			fclose($write_handle);
		}
	}
}
