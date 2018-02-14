<?php
/**
 * User: kurraz
 * Date: 26.04.13
 */

$dirs = array();

$homePath = "/home";
$webPath = "web";
$cronPath = "public_html/scripts/cron";

$dir = opendir($homePath);
while(($file = readdir($dir)) !== false){

    if($file == "." || $file == "..") continue;
	
    $userName = $file;	
	if(is_dir($homePath."/".$userName."/".$webPath)){
	
		$dir2 = opendir($homePath."/".$userName."/".$webPath);
		while(($file2 = readdir($dir2)) !== false){
		
			if($file2 == "." || $file2 == "..") continue;
			$host = $file2;
			if(is_dir($homePath."/".$userName."/".$webPath."/".$host."/".$cronPath)){
			
				$dirs[] = array(
						'path' => $homePath."/".$userName."/".$webPath."/".$host."/".$cronPath,
						'user' => $userName,
					);		
					
			}
			
		}
		
	}
	
}




/**
 *
 * Example of job definition:
 * .---------------- minute (0 - 59)
 * |  .------------- hour (0 - 23)
 * |  |  .---------- day of month (1 - 31)
 * |  |  |  .------- month (1 - 12) OR jan,feb,mar,apr ...
 * |  |  |  |  .---- day of week (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
 * |  |  |  |  |
 * *  *  *  *  * user-name command to be executed
 */
$freqDirAsssoc = array(
    'every2hours' => '0 */2 * * *',
    'every4hours' => '0 */4 * * *',
    'every30mins' => '*/30 * * * *',
    'every10mins' => '*/10 * * * *',
    'every5mins' => '*/5 * * * *',
    'hourly' => '0 * * * *',
    'every6hours' => '0 */6 * * *',
	'everyweek' => '0 0 * * 1',
	'everyday' => '0 0 * * *',
	'everymin' => '* * * * *',
	'everyday7am' => '0 7 * * *',
	'everyday9am' => '0 9 * * *',
	'everyday12am' => '0 12 * * *',
	'digest' => '0 1 * * 0,3',
	'digest2head' => '0 8 * * 1',
);

$content = "
* * * * * root php /root/customCron.php > /root/customCron.log
";

foreach($dirs as $oneDir)
{
    $dir = opendir($oneDir['path']);
    while(($file = readdir($dir)) !== false)
    {
        if($file == '.' || $file == '..') continue;

        if(is_dir($oneDir['path'].'/'.$file))
        {
	    echo $file."\n";
            $freqDir = opendir($oneDir['path'].'/'.$file);
            while(($script = readdir($freqDir)) !== false)
            {
				//only 'php' and 'sh' extensions
		$ext = end(explode('.',$script));
                if(is_dir($oneDir['path'].'/'.$file.'/'.$script) || ($ext != 'php' && $ext != 'sh') ) continue;

                if(isset($freqDirAsssoc[$file]))
                {
            	    if($ext == 'php')
            	    {
                        $content .=  $freqDirAsssoc[$file] . " " . $oneDir['user'] . " cd " . $oneDir['path'].'/'.$file.'/' . " && php " . $script." > " . $oneDir['path'].'/'.$file.'/'.$script . ".log 2>&1\n";
                    }else
                    {
                	    $content .=  $freqDirAsssoc[$file] . " " . $oneDir['user'] . " cd " . $oneDir['path'].'/'.$file.'/' . " && ./" .$script." > " . $oneDir['path'].'/'.$file.'/'.$script . ".log 2>&1\n";
                    }
                    echo $script ."\n";
                }
            }
        }
    }
}

$original = file_get_contents('/etc/crontab');

$pos = strpos($original,"#<devinsight>\n");
if($pos === false)
{
    $content = $original."#<devinsight>\n".$content."\n#<devinsight>\n";
}else
{
    $ar = explode("#<devinsight>\n",$original);
    $content = $ar[0]."#<devinsight>\n".$content."\n#<devinsight>\n".$ar[2];
}

file_put_contents('/etc/crontab',$content);