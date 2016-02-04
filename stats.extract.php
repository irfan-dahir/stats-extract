<?php

/**
*
*	Stats:Extract
*	@author Nighthawk/Nekomata <thesynchronousdeveloper@gmail.com>
*	@version 0.3
*	@copyright Copyright (c) 2016, Nighthawk
*
*/


/**
*	Preload
*/
define("SE_VERSION", "0.3");
define("SE_LOG", true); //Enable/Disable Debug/Error Logging
define("SE_LOG_PATH", "log.se"); //this will generate a log file in the working directory
define("SE_DEFAULT_SOCKET_TIMEOUT", 5);
/**
*	Class: StatsExtract (Main Class)
*	Methods:
*		[Public]
*			@method powered_by() @return string
*			@method parse_htmlstats(string $file) @return array
*			@method parse_userstats(string $file) @return array
*			@method parse_live_server_info(string $ip,int $port {,int $timeout}) array
*			@method float_fix(float $float, int $round) int|float
*			@method calc_avg(array $arr) 
*			@method calc_peak(array $arr) 
*			@method calc_total(array $arr) 
*			@method calc_tos(int $s) 
*			@method calc_kpd(int $usgn) 
*			@method usgn_avatar(int $usgn) @return string
*			@method usgn_name(int $usgn) @return string
*/
class StatsExtract{
	//	Vars
	protected $log = null;
	//	Public
	//	Main Methods
/**
*	__construct(), On loading, calls the simple_html_dom library.
*	If LOG is true then opens the log path. It's done in __construct() because more efficiency
*/
	public function __construct(){
		if(SE_LOG){
			if(file_exists(SE_LOG_PATH)){$this->log = fopen(SE_LOG_PATH, "a+");}
		}
	}
/**
*	__construct(), On loading, calls the simple_html_dom library.
*	If LOG is true then closes the log path. 
*/
	public function __destruct(){
		if(SE_LOG){
			if(file_exists(SE_LOG_PATH)){fclose($this->log);}
		}
	}
/**
*	@param string $string
*	@method mixed log() log(string $string) logs error/debugs
*	@access protected
*/
	protected function log($string){if(SE_LOG){if($this->log){fwrite($this->log, "[".date('d/m/Y h.i.sa')."] ".$string."\n");}}}

/**
*	Parses the serverstats.html to extract the user ranks, server information & graph values.
*	@param array parse_htmlstats() parse_htmlstats(string $file) Returns server HTML statistics
*	in an array; @return array(
*			['stats']=>array(
*				int ['uptime'],
*				float ['upload'],
*				float ['download'],
*				float ['total'],
*				int ['ranked-players']
*			),
*			['graph']=>array(
*				[0]=>array(
*					float ['upload'],
*					float ['download'],
*					int ['players']
*				),
*				[n]=>array(
*					float ['upload'],
*					float ['download'],
*					int ['players']
*				),
*				[23]=>array(
*					float ['upload'],
*					float ['download'],
*					int ['players']
*				),
*			)
*		)
*	@example $svstats = $this->parse_htmlstats("path/to/serverstats.html")
*	@return array
*	@access public
*/
	public function parse_htmlstats($file_name){
		if(!is_null($file_name)){
			if(file_exists($file_name) && preg_match("/[Hh][Tt][Mm][Ll]$/", basename($file_name))){
				$file = fopen($file_name, "r");
				$this->log("Debug: [".__FUNCTION__."] Loaded \"".$file_name."\", ready for parsing");
			}else{
				die($this->log("Error: [".__FUNCTION__."] File \"".$file_name."\" does not exist or invalid file type"));
			}
		}else{
			die($this->log("Error: [".__FUNCTION__."] Missing path parameter"));
		}
		if(!is_null($file)){
			$this->log("Debug: [".__FUNCTION__."] Parsing \"".$file_name."\"");
			$_serverstats = array();
			$_graphstats = array();
			$this->log("Debug: [".__FUNCTION__."] Parsing server statistics");
			$l=0;$ul=0;$ll=0;
			while(($line=fgets($file))!==false){
				$l++;
				$lines[] = $line;
				if(preg_match("/<body>/", $line)){
					$ul = $l+9;
				}
				if(preg_match("/<div class=\"sec\">Player Ranking <span class=\"descr\">Top 30<\/span>/", $line)){
					$ll = $l-4;
				}
			}
			$temp2 = array();
			for($i=$ul;$i<=$ll;++$i){
				$temp2[] = trim($lines[$i]);
			}
			$_serverstats['uptime'] = (int)substr($temp2[0], strpos($temp2[0], "</span>")+9,strpos($temp2[0], " h"));
			$_serverstats['upload'] = floatval(substr($temp2[1], strpos($temp2[1], "</span>")+7,strpos($temp2[1], " mb")));
			$_serverstats['download'] = floatval(substr($temp2[2], strpos($temp2[2], "</span>")+7,strpos($temp2[2], " mb")));
			$_serverstats['total'] = floatval($_serverstats['upload']+$_serverstats['download']);
			$_serverstats['registered'] = (int)substr($temp2[4], strpos($temp2[4], "</span>")+7,strpos($temp2[0], "<br>"));
			fseek($file, 0);
			$index = 0;
			$this->log("Debug: [".__FUNCTION__."] Parsing Graph Data");
			$l=0;$ul=0;$ll=0;
			while(($line=fgets($file))!==false){
				$l++;
				$lines[] = $line;
				if(preg_match("/<div class=\"sec\">Traffic Today <span class=\"descr\">/", $line)){
					$ul = $l+33;
				}
				if(preg_match("/<div class=\"sec\">Traffic Yesterday <span class=\"descr\">/", $line)){
					$ll = $l-6;
					break;
				}elseif(preg_match("/<p class=\"note\">/", $line)){
					$ll = $l-7;
					break;
				}
			}
			$temp3 = array();
			for($i=$ul;$i<=$ll;++$i){
				$temp3[] = trim(substr($lines[$i],strpos($lines[$i],"title=\"")+7));
			}
			$temp3_count = count($temp3)-1;
			for($i=0;$i<=$temp3_count;++$i){
				$upload_preset = strpos($temp3[$i], "up ")+3; 
				$upload_offset = strpos($temp3[$i], "mb,")-strlen($temp3[$i]);
				$_graphstats[$index]['upload'] = floatval(trim(substr($temp3[$i], $upload_preset, $upload_offset)));
				$download_preset = strpos($temp3[$i], "down ")+5;
				$_graphstats[$index]['download'] = floatval(trim(substr($temp3[$i], $download_preset)));
				$players_preset = strpos($temp3[$i], "<span class=\"plc\">")+18;
				$players_offset = strpos($temp3[$i], "</span></td>")-strlen($temp3[$i]);
				$_graphstats[$index]['traffic'] = (int)trim(substr($temp3[$i], $players_preset, $players_offset));
				$index++;
			}
			//temp arrays to final array
			$server = array();
			$server['statistics'] = $_serverstats;
			$server['graph-data'] = $_graphstats;
			$this->log("Debug: [".__FUNCTION__."] Parse complete");
			fclose($file);
			return $server;
		}else{die($this->log("Error: [".__FUNCTION__."] \"".$file_name."\" not loaded."));}
	}

/**
*	Parses the userstats.dat to extract user ranks and data.
*	Credits go to MikuAuahDark for the decoding script!
*	@param array parse_userstats() parse_userstats(string $file) Returns user statistics
*	in an array; @return array(
*		[n]=>array(
*			string ['name'], //name used when first joined server
*			int ['usgn'],
*			int ['score'],
*			int ['frags'], //kills
*			int ['deaths'],
*			int ['tos'] //time on server in seconds
*			),
*		)
*	@example $user = $this->parse_userstats("path/to/userstats.dat")
*	@return array
*	@access public
*/
	public function parse_userstats($file_name){
		if(!is_null($file_name)){
			if(file_exists($file_name) && preg_match("/[Dd][Aa][Tt]$/", basename($file_name))){
				$file = fopen($file_name, "r");
				$this->log("Debug: [".__FUNCTION__."] Loaded \"".$file_name."\", ready for parsing");
			}else{
				die($this->log("Error: [".__FUNCTION__."] File \"".$file_name."\" does not exist or invalid file type"));
			}
		}else{
			die($this->log("Error: [".__FUNCTION__."] Missing path parameter"));
		}
		if($file){
			$this->log("Debug: [".__FUNCTION__."] Parsing \"".$file_name."\"");
			fseek($file, 0, SEEK_END);
			$size = ftell($file);
			fseek($file, 17, SEEK_SET);
			$index = 1;
			$return = array();
			while(ftell($file)!=$size){
				$return[$index] = array(
					"name"=>trim(fgets($file)),
					"usgn"=>(int)$this->decode($file),
					"score"=>(int)$this->decode($file),
					"frags"=>(int)$this->decode($file),
					"deaths"=>(int)$this->decode($file),
					"tos"=>(int)$this->decode($file),
				);
				$index++;
			}
			fclose($file);
			$this->log("Debug: [".__FUNCTION__."] Parse complete");
			return $return;
		}else{die($this->log("Error: [".__FUNCTION__."] \"".$file_name."\" not loaded."));}
	}
/**
*	parse_live_server_info() connects to a server that's online on it's respective port to get server details. Such as the ones displayed in CS2D's serverlist.
*	Credits go to DC for sharing his script for this!
*	@param array parse_live_server_info() parse_live_server_info(string $ip, int $port {, int $timeout}) Returns server stats via socket connection
*	in an array; @return array(
*		string ['name'],
*		string ['map'],
*		int ['players'],
*		int ['max-players'],
*		int ['bots'],
*		string ['total-players'],
*		bool ['password'],
*		bool ['usgn-only'],
*		bool ['fow'],
*		bool ['ff'],
*		bool ['lua'],
*		string ['game-mode']
*		)
*	@example $svinfo = $this->parse_live_server_info(192.168.0.1,25235) With SE_DEFAULT_SOCKET_TIMEOUT seconds timeout
*	@example $svinfo = $this->parse_live_server_info(192.168.0.1,25235,20) With 20 seconds timeout
*	@return array
*	@access public
*/
	protected $socket_read;
	public function parse_live_server_info($ip, $port, $timeout = SE_DEFAULT_SOCKET_TIMEOUT){
		if(empty($ip)){
			$this->log("Error: [".__FUNCTION__."] No IP Address Specified");
			return false;
		}else{
			if(!filter_var($ip, FILTER_VALIDATE_IP)){
				$this->log("Error: [".__FUNCTION__."] Invalid IP Address (".$ip.")");
				return false;
			}
		}
		if(empty($port)){
			$this->log("Error: [".__FUNCTION__."] No Port Specified");
			return false;
		}else{
			if(is_integer($port)){
				if($port<1 || $port>65535){
					$this->log("Error: [".__FUNCTION__."] Invalid Port");
				}
			}else{
				$this->log("Error: [".__FUNCTION__."] Invalid Port");
				return false;
			}
		}
		$timeout = (int)$timeout;
		if($timeout<1){$timeout=SE_DEFAULT_SOCKET_TIMEOUT;}
		$this->log("Debug: [".__FUNCTION__."] Connecting to (".$ip.":".$port.")");
		$fp = @fsockopen("udp://".$ip.":".$port);
		if($fp){
			$this->log("Debug: [".__FUNCTION__."] Connected to (".$ip.":".$port.")");
			$string = chr(1).chr(0).chr(251).chr(1);
			stream_set_timeout($fp, $timeout);
			fwrite($fp, $string);
			$this->socket_read = fread($fp, 256);
			$this->log("Debug: [".__FUNCTION__."] Fetching data");
			//timeout?
			$meta_data = stream_get_meta_data($fp);
			if($meta_data['timed_out'] || (ord(substr($this->socket_read, 0, 1)) == 0)){
				$this->log("Error: [".__FUNCTION__."] Connection timed out (".$ip.":".$port.")");
				fclose($fp);
				return false;
			}
			//fetch stuff
			$b0 = $this->readByte();
			$b1 = $this->readByte();
			$b2 = $this->readByte();
			$b3 = $this->readByte();
			if($b0==1 && $b1==0 && $b2=251 && $b3==1){
				$this->log("Debug: [".__FUNCTION__."] Parsing data");
			    $mode = $this->readByte(); //Mode Bit Flags
			   	$name = $this->readString(); //Server Name
			    $map = $this->readString(); //Map
			    $players = min($this->readByte(),32); //Players
			    $players_max = min($this->readByte(),32); //Players Max
       			$state = $this->readByte();                  //Game State
			    $gmi = ($mode & 32) ? $this->readByte() : 0;
			    $bots = $this->readByte();
			    //return array
			    $svinfo = array();
			    $svinfo['name'] = (string)$this->utf8_htmlentities($name);
			    $svinfo['map'] = (string)$map;
			    $svinfo['players'] = (int)$players;
			    $svinfo['max-players'] = (int)$players_max;
			    $svinfo['bots'] = (int)$bots;
			    $svinfo['password'] = ($mode & 1) ? true : false;
			    $svinfo['usgn-only'] = ($mode & 2) ? true : false;
			    $svinfo['fow'] = ($mode & 4) ? true : false;
			    $svinfo['ff'] = ($mode & 8) ? true : false;
			    $svinfo['lua'] = ($mode & 64) ? true : false;
			    switch ($gmi){
			    	case 0: $svinfo['game-mode'] ='Standard'; break;
			    	case 1: $svinfo['game-mode'] ='Deathmatch'; break;
			    	case 2: $svinfo['game-mode'] ='Team Deathmatch'; break;
			    	case 3: $svinfo['game-mode'] ='Construction'; break;
			    	case 4: $svinfo['game-mode'] ='Zombies'; break;
			    	default: $svinfo['game-mode']='Unknown ('.$gmi.')';
			    }
			    //finish up
			    fclose($fp);
			    $this->log("Debug: [".__FUNCTION__."] Complete; Disconnected");
			    return $svinfo;
			}else{
				$this->log("Error: [".__FUNCTION__."] Unexpected server reply (',".$b0.",',',".$b1.",',',".$b2.",',',".$b3.",') - expected (1,0,251,1)");
				fclose($fp);
				return false;
			}
		}else{
			$this->log("Error: [".__FUNCTION__."] Failed to connect (".$ip.":".$port.")");
			return false;
		}
	}

	//	Util Methods
	public function powered_by(){
		echo "Powered by <a target=\"_blank\" title=\"Running Stats:Extract ".SE_VERSION."\" href=\"http://www.unrealsoftware.de/files_show.php?file=16081\">Stats:Extract</a>";
	}
	public function float_fix($float,$round){return round(floatval($float),$round);}
	public function calc_avg($arr){$sum = 0;$arr_count=count($arr)-1;for($i=0;$i<=$arr_count;++$i){$sum = $sum + $arr[$i];}return $sum/count($arr);}
	public function calc_peak($arr){$peak = 0;$arr_count=count($arr)-1;for($i=0;$i<=$arr_count;++$i){if($arr[$i] > $peak){$peak = $arr[$i];}}return $peak;}
	public function calc_total($arr){$total = 0;$arr_count=count($arr)-1;for($i=0;$i<=$arr_count;++$i){$total = $total + $arr[$i];}return $total;}
	public function calc_tos($s){$day = 0;$min = 0;$hour = 0;$time = "";$s=$this->float_fix($s/60,0);if($s >= 1440){while($s >= 1440){$day++;$s -= 1440;}}if($s >= 60){while($s >= 60){$hour++;$s -= 60;}}if($s < 60){$min = $s;}if($day > 0){if($day == 1){$time .= $day." day ";}elseif($day > 1){$time .= $day." days ";}if($hour > 0){$time .= $hour." h ";}if($min > 0){$time .= $min." m";}}elseif($day == 0 && $hour > 0){if($hour == 1){$time .= $hour." hour ";}elseif($hour > 1){$time .= $hour." hours ";}if($min > 0){$time .= $min." m";}}elseif($day == 0 && $hour == 0 && $min > 0){if($min == 1){$time .= $min." minute";}elseif($min > 1){$time .= $min." minutes";}}elseif($day == 0 && $hour == 0 && $min == 0 && $s > 0){if($s == 1){$time .= $s." second";}elseif($s > 1){$time .= $s." seconds";}}else{$time .= "N/A";}return $time;}
	public function calc_kpd($frags,$deaths){if($frags>0 && $deaths>0){return $this->float_fix($frags/$deaths,2);}elseif($deaths == 0){if($frags>0){return $frags;}elseif($frags==0){return floatval(0.00);}}elseif($frags == 0){return floatval(0.00);}}
	//uses unrealsoftware's API
	public function usgn_avatar($usgn){$usgn = trim($usgn);$avatar = file_get_contents("http://www.unrealsoftware.de/getuserdata.php?id=".$usgn."&data=avatar");if(!empty($avatar)){$avatar = "http://www.unrealsoftware.de/".$avatar;}else{$avatar = "";}return $avatar;}
	public function usgn_name($usgn){$usgn = trim($usgn);return file_get_contents("http://www.unrealsoftware.de/getuserdata.php?id=".$usgn."&data=name");}
	//	Protected
	protected function utf8_htmlentities($t,$q=ENT_COMPAT,$char='UTF-8',$denc=TRUE){return htmlentities($t,$q,$char,$denc);}
	protected function readString(){$length=ord(substr($this->socket_read,0,1));$string=substr($this->socket_read,1,$length);$this->socket_read=substr($this->socket_read,$length+1);return $string;}
	protected function readByte(){$byte=ord(substr($this->socket_read,0,1));$this->socket_read=substr($this->socket_read,1);return $byte;}
	protected function decode($file){return ord(fread($file,1))+ord(fread($file,1))*256+ord(fread($file,1))*65536+ord(fread($file,1))*16777216;}
/*	
	//soon, perhaps, perhaps nOT
	protected function isLink($file_name){if(filter_var($file_name, FILTER_VALIDATE_URL)){return true;}return false;}
	protected function linkExists($file_name){
		$headers = get_headers($file_name);
		return stripos($headers[0],"200 OK")?true:false;
	}
	protected function token($l=10){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $l; $i++) {$randomString .= $characters[rand(0, $charactersLength - 1)]; }
		return $randomString;	
	}
*/
}