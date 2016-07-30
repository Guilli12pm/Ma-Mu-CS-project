<?php
$row = 1;
if(($handle = fopen($argv[1], "r")) !== FALSE)
{
	while(($data = fgetcsv($handle, 1000, ",")) !== FALSE)
	{
		$track = $data[0];
		$code = $data[2];
		switch($code)
		{
			case 'Tempo':
				$tempo = $data[3];
				break;
			case 'note_on_c':
				$note = $data[4];
				$duration = $data[5] * $tempo;
			default:
		}
		$timestamp = $data[1]
		$instrument = $data[2];
		$frequency = $data[4];
		$duration = $data[5];
		$ouput[$track]["instrument"] = 
		}
		$row++;

	}
	fclose($handle);
}
?>
