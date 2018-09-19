<?php

	require __DIR__.'/bootstrap/autoload.php';
	require __DIR__.'./SqlsrvDao.php';
	require __DIR__.'./LogFile.php';
	
	use Carbon\Carbon;
	use ZenEnv\ZenEnv;
	
	$dotenv = new Dotenv\Dotenv(__DIR__);
	$dotenv->load();
	// $dotenv->overload();

	$env = new ZenEnv(__DIR__.'./.env');	
		
	try {		
   
        $db_host = getenv('DB_HOST');   
		$db_name = getenv('DB_DATABASE');
		$db_url = "sqlsrv:Server=".$db_host.";Database=".$db_name;   

        $db_user = getenv('DB_USERNAME');         
        $db_password = getenv('DB_PASSWORD');   

        $db = new PDO($db_url, $db_user, $db_password); 
	} catch (PDOException $e) {
		echo "Failed to get DB handle: " . $e->getMessage() . "\n";
		exit;
	}

	$dao = new SqlsrvDao($db);
	
	$lastDate = getenv('Last_Date');
	
	$chs = explode(':', trim(getenv('CHANNEL')));

	$ch_titles = explode(':', trim(getenv('CH_TITLE')));	
	
	$init_dt = '';
		
	if (!$lastDate) {
		$init_dt='2018-05-01 00:00:00';
	}else
	{
		$init_dt=$lastDate.' 00:00:00';
	}

	$nextDate =  Carbon::createFromFormat('Y-m-d H:i:s', $init_dt)->addDay();
	$toDay= Carbon::now()->toDateString();

	while ($nextDate<$toDay) {

		foreach ($chs as $ch) {
			$contracts=collect($dao->getADInfoByCH($nextDate->format('Ymd'), $ch));
			// dd($contracts);
			$filename=getenv('OUTPUT_PATH').$nextDate->format('Ymd').$ch.'.txt';
			
			$str='';
			
			$contracts->each(function ($contract) use (&$str) {
				//计算广告长度
				
					$len = intval($contract['LDURATION']);
					$ilen = (int)floor($len/25);

					$h=(int)floor($ilen/3600);
					$m=(int)floor(($ilen%3600)/60);
					$s=$ilen-$h*3600-$m*60;		
					$f=(int)($len%25);

					$slen=str_pad($h,2,'0',STR_PAD_LEFT).str_pad($m,2,'0',STR_PAD_LEFT).str_pad($s,2,'0',STR_PAD_LEFT).str_pad($f,2,'0',STR_PAD_LEFT);			
					$d_time= iconv('utf-8', 'gbk', str_replace(':', '', (str_replace(' ', '', $contract['STRSCEDULEPLAYTIME']))));
					$line=$d_time.iconv('utf-8','gbk',$slen).
							substr((iconv('utf-8','gbk',$contract['strCHName']).'                '),0,16).
							substr((iconv('utf-8','gbk', $contract['STRITEMNAME']).'                '),0,16).
							iconv('utf-8','gbk', trim($contract['STRDESCRIPTION'])).
							"\r\n";
					$str=$str.$line;
				
			});
			//dd($contracts);
			if ($str) {
				file_put_contents($filename, $str, LOCK_EX);	
				$env->set([
					'Last_Date'=>$nextDate->format('Y-m-d')
				]);
			}
		}
		//fclose($file);			
		$nextDate = $nextDate->addDay();
    }

	echo 'success';
