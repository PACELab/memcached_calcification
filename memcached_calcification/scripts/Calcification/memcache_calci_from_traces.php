<?php
//	$str = "A 'quote' is <b>bold</b> ";
//	echo htmlspecialchars($str);

	function rand01()
	{   // auxiliary function
	     // returns random number with flat distribution from 0 to 1
        return (float)rand()/(float)getrandmax();
}



    //database ip=130.245.127.21
	ini_set('display_errors', 1);
//	error_reporting(E_ALL & ~E_NOTICE);
//	$startime=microtime(true);
        $mc=new Memcached();
        if(!count($mc->getServerList()))
        {
                $mc->setOption(Memcached::OPT_DISTRIBUTION,Memcached::DISTRIBUTION_CONSISTENT);
                $mc->setOption(Memcached::OPT_REMOVE_FAILED_SERVERS,true);
                $mc->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
        //      echo "return value of addServer<br>";
                $mc->addServer("mc1",11211);
                // $mc->addServer("mc2",11211);
		// $mc->addServer("mc3",11211);
        }

	//var_dump($mc->addServer("mc1",11211));
	//var_dump($mc->addServer("mc2",11211));
	//var_dump($mc->addServer("mc3",11211));
	//echo "<br>";

	$p=0.08;

	// Mem-Calcification
	if(isset($_GET['fileName'])) {
                // error_log("Entering if condition \n", 3, "/var/tmp/myphp.log");
                // error_log($_SERVER['REQUEST_URI'] , 3, "/var/tmp/myphp.log");
                $fName = $_GET['fileName'];
                // error_log($fName, 3, "/var/tmp/myphp.log");
       
        } else {
                // error_log("File Name is required", 3, "/var/tmp/myphp.log");
                exit();
        }
 	$fName = "/home/ubuntu/" . $fName;
        $fp = fopen($fName, 'r') or die("Unable to open file!");
	// error_log("opened the file ", 3, "/var/tmp/myphp.log");

	while(!feof($fp)) {
		$startime = microtime(true);		
		#error_log("nsk1 \n", 3, "/var/tmp/myphp.log");
		$data = fread($fp, 1200);
		#error_log("nsk2 \n", 3, "/var/tmp/myphp.log");
		fseek($fp, 1200, SEEK_CUR);
		#error_log("nsk3 \n", 3, "/var/tmp/myphp.log");
		$keys = explode(",", $data);
		array_pop($keys);
		if (count($keys) < 100) {
			error_log("ERROR \n", 3, "/var/tmp/myphp.log");
			exit();
		}
		// error_log($data, 3, "/var/tmp/myphp.log");
		$genel=microtime(true)-$startime;
		
		$found=$mc->getMulti($keys);
                $mcel=microtime(true)-$startime;

                $found_keys=array_keys($found);
                $not_found=array_diff($keys,$found_keys);
                var_dump($not_found); echo "<br>";

                $date = date('Y-m-d H:i:s');
                $dbel=0;
                if (count($not_found)>0){
                        
			#error_log("nsk4 \n", 3, "/var/tmp/myphp.log");
			$db= new Redis();
			#error_log("nsk5 \n", 3, "/var/tmp/myphp.log");
                        $db->pconnect("database",16379,0,"php2");
			#error_log("nsk6 \n", 3, "/var/tmp/myphp.log");
                        $missing_vals=$db->mGet($not_found);
			#error_log("nsk7 \n", 3, "/var/tmp/myphp.log");
                        $dbel=microtime(true)-$startime;

                        $misses=array_combine($not_found,$missing_vals);
                        var_dump($misses);echo "<br>";
                        $mc->setMulti($misses);
                }
                $setel=microtime(true)-$startime;
                $log_line=sprintf("%s found: %d hit_RT: %f misses: %d miss_RT: %f gen_time: %f total_RT: %f\n",$date,count($found),
$mcel,count($not_found),$dbel,$genel,$setel);
                error_log($log_line, 3, "/var/tmp/mymsg.log");
		// TODO: configure this sleep to control how quickly to populate memcache.
                // Php thread sleeps for a while before populating next 100 keys.
		usleep(500000);
	}       

	fclose($fp);
?>


