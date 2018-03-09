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
	$startime=microtime(true);
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
 
	// TODO: Name of trace filename where traces are dumped.  
	$fp = fopen('/home/ubuntu/NewDist/dist_25_75.trace', 'a+');
        $keys_str = "";

        $keys=array();
        // Slab1 keys
	for ($i=0; $i<25; $i++){
                $geo_rand=floor(log(rand01())/log(1-$p));
//      var_dump($geo_rand);var_dump(rand());
                $valsize= (rand()%10)+ 1;    // 50-50
                // $valsize=(rand()%20) + 22; // 25-75
                // $valsize=(rand()%20) + 25; // 10-90
                $a=1;
                $b=10000;
                $indx=floor($b*pow(-log(1-rand01()),1/$a))%1000000;
                // $indx=$geo_rand%1000000;
                $key=sprintf("%05d%06d",$valsize,$indx);
                array_push($keys,$key);
//              $mc->set($key,"testval");
                $keys_str = $keys_str.$key.",";
        }
	
	// Slab2 keys
	for ($i=25; $i<100; $i++){
                $geo_rand=floor(log(rand01())/log(1-$p));
//      var_dump($geo_rand);var_dump(rand());
                $valsize= (rand()%10)+ 27;    // 50-50
                // $valsize=(rand()%20) + 22; // 25-75
                // $valsize=(rand()%20) + 25; // 10-90
                $a=1;
                $b=10000;
                $indx=floor($b*pow(-log(1-rand01()),1/$a))%1000000;
                // $indx=$geo_rand%1000000;
                $key=sprintf("%05d%06d",$valsize,$indx);
                array_push($keys,$key);
//              $mc->set($key,"testval");
                $keys_str = $keys_str.$key.",";
        }

        fwrite($fp, $keys_str);
        fclose($fp);

	// $fp = fopen($fName, 'r+');
	//$data = fread($fp);
	// ftruncate($fp, 1200);
	//fclose($fp);        
	// $keys_str = "";

	// $keys=array();
	// for ($i=0; $i<100; $i++){
	//	$geo_rand=floor(log(rand01())/log(1-$p));
//	var_dump($geo_rand);var_dump(rand());
	//	$valsize= (rand()%20)+ 17;    // 50-50
		// $valsize=(rand()%20) + 22; // 25-75
		// $valsize=(rand()%20) + 25; // 10-90
	//	$a=1;
	//	$b=10000;
	//	$indx=floor($b*pow(-log(1-rand01()),1/$a))%1000000;
		// $indx=$geo_rand%1000000;
	//	$key=sprintf("%05d%06d",$valsize,$indx);
	//	array_push($keys,$key);
//		$mc->set($key,"testval");
		// $keys_str = $keys_str.$key.",";
	// }
        /* replacing above for loop for reading from traces */
	// for ($i=0; $i<100; $i++){
		// $key=sprintf("%05d%06d",$valsize,$indx);
	//	$key = fread()
	//	array_push($keys,$key);
	// }
 

	// fwrite($fp, $keys_str);
	// fclose($fp);
	
	$genel=microtime(true)-$startime;
//	$mc->set('00010111222',"testval");
//	$mc->set('00010333444',"testval");
//	var_dump($keys);echo "<br>";
	//echo "<br>";
	$found=$mc->getMulti($keys);
	$mcel=microtime(true)-$startime;

//	$mc->quit();
//	var_dump($found);echo "<br>";
//	var_dump($mc->get($keys));
//	var_dump($mc->getResultCode());echo "<br>";
	//$found_flip=array_flip($found);
	$found_keys=array_keys($found);
	$not_found=array_diff($keys,$found_keys);
	var_dump($not_found); echo "<br>";

//	for ($i=0; $i<20; $i++){
//		echo "<br>    ".$keys[$i]." ".$found[$keys[$i]];
//	}
	$date = date('Y-m-d H:i:s');
	$dbel=0;
	if (count($not_found)>0){
		$db= new Redis();
		$db->pconnect("database",16379,0,"php2");
		$missing_vals=$db->mGet($not_found);
		$dbel=microtime(true)-$startime;
	//	var_dump($missing_vals);echo "<br>";
		/* code added to make value size constant(populate to slab1 alone) */
		// for ($i=0; $i<count($missing_vals); $i++){
		//	echo "$missing_vals[$i]";;
		// }

		$misses=array_combine($not_found,$missing_vals);
		var_dump($misses);echo "<br>";
		$mc->setMulti($misses);
//		var_dump($mc->getResultCode());
	}
	$setel=microtime(true)-$startime;
	$log_line=sprintf("%s found: %d hit_RT: %f misses: %d miss_RT: %f gen_time: %f total_RT: %f\n",$date,count($found),$mcel,count($not_found),$dbel,$genel,$setel);
	error_log($log_line, 3, "/var/tmp/mymsg.log");
//	exit;

?>


