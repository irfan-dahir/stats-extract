<?php
require 'stats.extract.php';
$se = new StatsExtract;
$svinfo = $se->parse_live_server_info("149.202.65.171",36532); 
$ranks = $se->parse_userstats("userstats.dat"); //place your userstats.dat file in this directory
$server = $se->parse_htmlstats("serverstats.html"); //place your serverstats.html file in this directory
var_dump($svinfo);

echo "Upload: ".$server['statistics']['upload']." mb<br>\n";
echo "Download: ".$server['statistics']['download']." mb<br>\n";
echo "Total (Up+Down): ".$server['statistics']['total']." mb<br>\n";
echo "Registered USGN: ".$server['statistics']['registered']." players<br>\n";
echo "Server Uptime: ".$server['statistics']['uptime']." hours<br>\n";
$count = count($server['graph-data']);
for($i=0;$i<=$count-1;++$i){
	echo "<h3>Hour: ".($i+1)."</h3>";
	echo "Upload: ".$server['graph-data'][$i]['upload']."<br>\n";
	echo "Download: ".$server['graph-data'][$i]['download']."<br>\n";
	echo "Traffic: ".$server['graph-data'][$i]['traffic']." players<br>\n";
}

var_dump($ranks);
var_dump($server);
?>