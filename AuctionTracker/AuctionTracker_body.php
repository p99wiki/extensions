<?php
		 
/*************************************************

p99wiki - extensions (AuctionTracker)
Copyright (C) 2013 Dylan Nelson (dnelson@destinati.com)
Version: 0.1

* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, version 3. This license is available
* in its entirety at <http://www.gnu.org/licenses/>.

* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.

*************************************************/			 
		 
class AuctionTracker extends SpecialPage {

function parseAuctionLog($filename,$zipFlag,$output="")
{
		if ($filename == "") {
				$output .= 'Error: Filename not specified.\n';
				return;
		}
		
		$savePath = "auctiontracker/";
		
		$itemRenames = array("GEB"                   => "Golden Efreeti Boots",
												 "SSOY"                  => "Short Sword of the Ykesha",
												 "EBCP"                  => "Enameled Black Chestplate",
												 "GSD"                   => "Green Silken Drape",
												 "RBG"                   => "Runebranded Girdle",
												 "FBSS"                  => "Flowing Black Silk Sash",
												 "Bear Skin Potion Bags" => "Bearskin Potion Bag",
												 "HQ Bear skins"         => "High Quality Bear Skin",
												 "HQ Bear Skin"          => "High Quality Bear Skin",
												 "hq bear pelt"          => "High Quality Bear Skin",
												 "Hand Made Backpacks"   => "Hand Made Backpack",
												 "A Dark Reaver"         => "Dark Reaver",
												 "A Shimmering Orb"      => "Shimmering Orb",
												 "Plat Ruby Veil"        => "Platinum Ruby Veil",
												 "jade prod"             => "Jade Chokidai Prod",
												 "bone razor"            => "Bone Razor",
												 "runed bone fork"       => "Runed Bone Fork",
												 "Fire giant toes"       => "Fire Giant Toes",
												 "Charred Guardian shield" => "Charred Guardian Shield",
												 "Kld-hide Boots"        => "Kobold-hide Boots",
												 "An Executioners Axe"   => "Executioner's Axe",
												 "JBoots MQ"             => "Ring of the Ancients",
												 "JBoot MQ"              => "Ring of the Ancients",
												 "jboots mq"             => "Ring of the Ancients",
												 "Journeyman's Boots MQ" => "Ring of the Ancients",
												 "Journeyman's Boots"    => "Ring of the Ancients",
												 "Ring of the Ancients MQ"    => "Ring of the Ancients",
												 "Hammer of the Scourge" => "Blight, Hammer of the Scourge");
												 
		$preReplaces = array("+6 WIS Ring"           => "Platinum Jasper Ring",
												 "+3 WIS Ears"           => "Jasper Gold Earring",
												 "+6 STA Ears"           => "Platinum Bloodstone Earring",
												 "+3 STR Ears"           => "Golden Amber Earring");
												 
		$skipLucyCheck = array("Bearskin Potion Bag",
													 "Minor Conjuration: Air");
		
		$badItemList = array("and","paying");
		
		// load item list
    $itemlistFile = "extensions/AuctionTracker/itemlist_30k.txt";    
    $lines = file($itemlistFile);
		$itemListArr = array();
    
    foreach ($lines as $line) {
        $tLine = explode(",",$line);
        
        //check for comma in name
        if (count($tLine) == 4)
            $tLine = array($tLine[0],$tLine[1] . "," . $tLine[2],$tLine[3]);
        
				//add itemname to list
        if (count($tLine) == 3) {
					if (substr($tLine[1],0,1) == "\"" && substr($tLine[1],-1) == "\"")
							$tLine[1] = substr($tLine[1],1,-1);
					$itemListArr[] = $tLine[1];
        }
    }
		
		// handle compressed inputs
		if ($zipFlag) {
				$lines = array();
				$zip = zip_open($filename);
				if ($zip) {
						// loop over files in zip
						while ($zip_entry = zip_read($zip)) {
								//.txt extension only
								if (substr(zip_entry_name($zip_entry),-4) == ".txt") {
										// maximum uncompressed filesize for security
										if (zip_entry_filesize($zip_entry)/(1024*1024) > 20) {
												$output .= "<p>ERROR. Textfile in zip (" . zip_entry_name($zip_entry) . ") too big (over 20MB)!" .
												           "Please split this upload into smaller chunks or use a parser to include just the auctions.</p>\n";
												//$lines = array("");
												break;
										}
										$output .= "<p style='margin-left:10px;font-style:italic;'>Found textfile in zip: " . zip_entry_name($zip_entry) . "";
										$output .= " (uncompressed size:    " . round(zip_entry_filesize($zip_entry)/1024) . " Kb)</p>\n";
										//$output .= "<p>Compressed Size:    " . zip_entry_compressedsize($zip_entry) . "\n";

										if (zip_entry_open($zip, $zip_entry, "r")) {
												$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
												$lines = array_merge($lines,explode("\n",$buf));
												zip_entry_close($zip_entry);
										}
								} else {
										$output .= "<p style='margin-left:10px;font-style:italic;'>Skipping non-textfile in zip: " . zip_entry_name($zip_entry) . "";
								}
						}
				} else {
						// error opening zip
						//$lines = array("");
				}
		
		} else {
				// read plaintext
				$lines = file($filename);
		}
		
		$rows = array();
		$aucLineCount = 0;
		
		// open output for auction line backup
		$i = 0;
		while (file_exists($savePath."auctions.bak.".$i.".txt") ||
		       file_exists($savePath."auctions.bak.".$i.".zip"))
				$i++;
		$fAuc = fopen($savePath."auctions.bak.".$i.".txt","w");
		$saveFilename = "auctions.bak.".$i.".txt";
		
		// config
		$tsLen   = strlen("[Thu Sep 16 17:55:22 2010]");
		$hashLen = 8;
		$verbose = false;
		
            $aucMsgRep    = array('||||','|||','||','|','////','///','//','/','(',')','----','---','--','-');
		$skipAucRegex = "/(PC|WTB|Buying|port|res|rez|Paying|Casino|account|Trade|Trading|bone chips)/i";
		$badItemRegex = "/(selling|WTS|port|WTB|PC|Buying|each|Dose|piece|pair|per ac|Sets|stack|level|lvl|offers|twink|stuff|ea or)/i";
		
		//$output .= "<p>Log file has [" . count($lines) . "] lines.\n";
		if ($verbose) print "Log file has [" . count($lines) . "] lines.\n\n";
		
		$maxTS = 0;
		$minTS = 1577858400; //2020
		
		// loop over each line
		for ($i=0; $i < count($lines); $i++)
		{
				$line = $lines[$i];
				
				// prompt skip of non-auctions
				if (strpos($line,"auctions, '") === false)
						continue;
						
				$aucLineCount += 1;
						
				// split timestamp
				$timestamp = substr($line,0,$tsLen);
				$timestamp = substr($timestamp,1,-1);
				$timestamp = strtotime($timestamp);
				
				// split seller
				$line2 = substr($line,$tsLen+1);
				list($seller,$aucMsg) = explode(" auctions, '",$line2);
				$seller = trim($seller);
				
				// auction text
				$aucMsg = trim($aucMsg);
				$aucMsg = substr($aucMsg,0,-1);
				
				foreach ($preReplaces as $search => $replace)
						$aucMsg = str_replace($search,$replace,$aucMsg);
				
				// price modifiers
				$priceMod = 1.0;
				if (strpos($aucMsg," 2 for ") !== false || strpos($aucMsg," two for ") !== false) {
						$aucMsg = str_replace(" 2 for ","",$aucMsg);
						$aucMsg = str_replace(" two for ","",$aucMsg);
						$priceMod = 0.5;
				}
				
				if ($timestamp == "" || $seller == "" || $aucMsg == "") {
						//if ($verbose) print 'Error: Blank strings.';
						continue;
				}
				if (preg_match($skipAucRegex,$aucMsg,$block)) {
						//if ($verbose) print "skipAucRegex: $aucMsg\n";
						continue;
				}
						
				// record auctions that have made it this far (selling)
				fwrite($fAuc,$line);
				
				// discard e.g. "x20" (can wrongly be interpreted as prices, or in any case price is unknown)
				$regex = "/x\s?\d{1,3}/";
				$ret = preg_match($regex,$aucMsg,$multMatches);
				if ($ret) {
						//if ($verbose) print "multMatch: $aucMsg\n";
						continue;
				}
                        
                        // list syntax formatting
                        foreach ($aucMsgRep as $repStr)
                          $aucMsg = str_replace($repStr,",",$aucMsg);
		
				// parse items
				$regex = "/([-_`\'\sa-zA-Z:]+)[-\s\/,;]([0-9\.k]+)/";
				$ret = preg_match_all($regex,$aucMsg,$matches);
				
				if (!$ret)
					continue;
                              
                        //if ($seller == "Yoozdgear")
				//  print $timestamp . " -- " . $seller . " -- " . $aucMsg . "<br><br>\n\n";
				
				foreach ($matches[1] as $j => $item) {
						// item name formatting
						$item = preg_replace("/(wts|wtt|wtb|obo|only|per)/i", "", $item);
						$item = preg_replace("/[\s]+[p|-]+$/i", "", $item);
						$item = trim($item);
						$item = preg_replace("/^['\"p|-]+[\s]+/i", "", $item);
						$item = str_replace("Spell:","",$item);
						$item = trim($item);
                                    
						// exclude items
						if (preg_match($badItemRegex,$item,$block))
								continue;
						if (in_array($item,$badItemList))
								continue;
								
						// check for junk at beginning/end (alpha characters)
						if (substr($item,0,6) == "------")
								$item = substr($item,6);
						if (substr($item,0,5) == "-----")
								$item = substr($item,5);
						if (substr($item,0,4) == "----")
								$item = substr($item,4);
						if (substr($item,0,3) == "ll " || substr($item,0,3) == ":  " ||
						    substr($item,0,3) == "-  " || substr($item,0,3) == "-- " ||
								substr($item,0,3) == "---")
								$item = substr($item,3);
						if (substr($item,0,2) == "I " || substr($item,0,2) == "x " || substr($item,0,2) == ": " ||
						    substr($item,0,2) == "- " || substr($item,0,2) == "--" || substr($item,0,2) == "s ")
								$item = substr($item,2);
						if (substr($item,0,1) == "-")
								$item = substr($item,1);
								
						if (substr($item,-2) == " X")
								$item = substr($item,0,-2);
						if (substr($item,-1) == "-")
								$item = substr($item,0,-1);
						
						// rename/abbreviations
						if (array_key_exists($item,$itemRenames))
								$item = $itemRenames[$item];
								
						if (strlen($item) <= 3) {
								//if ($verbose) print "Error: Item too short, skipping: $item\n";
								continue;
						}
						
						// price formatting
						$price = $matches[2][$j];
                                    
						//junk on end
						if (substr($price,-4) == "....")
								$price = substr($price,0,-4);
						if (substr($price,-3) == "...")
								$price = substr($price,0,-3);
						if (substr($price,-2) == "..")
								$price = substr($price,0,-2);
								
						// k=1000
						if (substr($price,-1) == "k") {
								$price = substr($price,0,-1);
								$price *= 1000.0;
						}
						if (!is_numeric($price)) {
								//if ($verbose) print "Error: Non-numeric price: $price\n";
								continue;
						}
						$price *= $priceMod;
						
						// plural
						if (substr($item,-1) == "s" && in_array(substr($item,0,-1),$itemListArr))
								$item = substr($item,0,-1);
						
						// "A "
						if (substr($item,0,2) == "A " && in_array(substr($item,2),$itemListArr))
								$item = substr($item,2);
						
						// check item exists in lucy DB
						if (!in_array($item,$skipLucyCheck) && !in_array($item,$itemListArr) && 
						    !in_array("Spell: ".$item,$itemListArr)) {
								//if ($verbose) print "\nError: No lucy ID: " . $item . "\n\n";
								continue;
						}
                                    
						// debug output
						//print str_pad($item,32) . " -- " . str_pad($price,6) . " - " . $seller . "\n";
						
						// compute hash and add
						$hash = md5(date('Y-m-d',$timestamp) . $seller . $item . $price);
						$hash = substr($hash,0,$hashLen);
						
						// min/max date
						if ($timestamp > $maxTS)
							$maxTS = $timestamp;
						if ($timestamp < $minTS)
							$minTS = $timestamp;

						$rows[$hash] = array($timestamp,$item,$seller,$price);
				}
		}

		// close backup file
		fclose($fAuc);
		
		// zip backup file
		$zip = new ZipArchive();
		$zipFilename = $savePath . substr($saveFilename,0,-4) . ".zip";
		
		if ($zip->open($zipFilename, ZIPARCHIVE::CREATE)===TRUE) {
				$zip->addFile($savePath . $saveFilename);
				$zip->close();
				
				// delete text original
				unlink($savePath . $saveFilename);
		}
		
		$output .= "<p>Processed [" . $i . "] log lines, found [" . $aucLineCount . "] auctions, with [" . 
					count($rows) . "] unique prices.\n";
		if ($verbose)
			print "\nProcessed [" . $i . "] log lines, found [" . $aucLineCount . "] auctions, with [" . 
					count($rows) . "] unique prices.\n";
		
		// form sql insert statement
		$sqlCmd = "REPLACE INTO `rav_auctiontracker` (hash,datetime,itemname,seller,price) VALUES ";
		foreach ($rows as $hash => $row) {
				$sqlCmd .= "('" . $hash . "','" . date("Y-m-d H:m:s",$row[0]) . "','" . addslashes($row[1]) . 
										"','" . addslashes($row[2]) . "','" . addslashes($row[3]) . "'), ";
		}
		$sqlCmd = substr($sqlCmd,0,-2) . ";";
		
		// write sql import file
		//$fSql = fopen("auctions.sql.txt","w");
		//fwrite($fSql,$sqlCmd);
		//fclose($fSql);
		
		// return (sqlCmd,num_lines,num_auctions,num_prices,date_min,date_max)
		return array($output,$sqlCmd,$i,$aucLineCount,count($rows),$minTS,$maxTS,$saveFilename);

}

function __construct()
{
  //parent::__construct( "AuctionTracker" );
	//to allow transclusion of this special page, ($name,$restriction,$listed,$function,$file,$includable);
  parent::__construct("AuctionTracker","",true,false,'default',true);
}

function execute( $par )
{
  global $wgRequest, $wgOut, $wgUser, $wgParser, $wgTitle;
	global $wgDBserver, $wgDBname, $wgDBuser, $wgDBpassword;

	ini_set('display_errors', 1);
	error_reporting(E_ERROR | E_PARSE); //E_WARNING
	
	// config
	$tableName        = "rav_auctiontracker";
	$tableNameContrib = "rav_auctioncontrib";
	$shortTimeInDays  = 30;
	$mediumTimeInDays = 90;
	$numPlotLimit     = 500;
	$daysPlotLimit    = 90;
	$detailLimit      = 20;
	
	// connect to DB
  $SQLcon = mysql_connect($wgDBserver, $wgDBuser, $wgDBpassword);
  $DBcon = mysql_select_db($wgDBname);	
	
	// check FILES for file upload submitted
	if ($_FILES["file"]["name"] != "") {
		if ($_FILES["file"]["error"] == 1)
		{
			$wgOut->setPagetitle("Project 1999 Auction Tracker");
			$wgOut->addWikiText("'''Error! File size exceeds maximum upload size permitted (2MB).'''\n\n" . 
			                    "Please break into smaller pieces!\n\n".
													"[[Special:AuctionTracker|Return to AuctionTracker]].\n\n");
			return;
		} else if ($_FILES["file"]["error"] > 1)
		{
			$wgOut->setPagetitle("Project 1999 Auction Tracker");
			$wgOut->addWikiText("'''File Upload Error! [num=" . $_FILES["file"]["error"] . "]'''\n\n" . 
			                    "Please contact Ravhin.\n\n".
													"[[Special:AuctionTracker|Return to AuctionTracker]].\n\n");
			return;
		}
		else
		{
			// only allow text/plain filetypes or zip files
			$fType = $_FILES["file"]["type"];
			$zipFlag = FALSE;
			
			if (($fType == 'application/zip' || $fType == 'application/x-zip-compressed' || 
			    $fType == 'multipart/x-zip' || $fType == 'application/x-compressed') && 
					substr($_FILES["file"]["name"],-4) == ".zip") {
					$zipFlag = TRUE;
			} else {
				// check for invalid filetype and die
				if ($_FILES["file"]["type"] != "text/plain" || substr($_FILES["file"]["name"],-4) != ".txt") {
					$wgOut->setPagetitle("Project 1999 Auction Tracker");
					$wgOut->addWikiText("'''ERROR: Only plain text and .zip upload supported currently.'''\n\n[[Special:AuctionTracker|Return to AuctionTracker]].\n");
					return;
				}
			}
			
			$output = "<h3>Thanks for the upload! Working...</h3>\n";
			$output .= "<p>Upload filename: <b>" . $_FILES["file"]["name"] . "</b>\n";
			$output .= "<p>Size: " . round($_FILES["file"]["size"] / 1024) . " Kb\n";
			//$output .= "<p>Stored in: " . $_FILES["file"]["tmp_name"] . "\n";
			
			// run processing on temp file, will extract only auction lines and save those permanently
			// as well as make REPLACE command for DB (temp file automatically deleted on script end)
			list($output,$QueryCMD,$num_lines,$num_auctions,
					 $num_prices,$minTS,$maxTS,$saveFilename) = AuctionTracker::parseAuctionLog($_FILES["file"]["tmp_name"], $zipFlag, $output);
                  
			$res = mysql_query($QueryCMD);
			if (!res)
					$output .= "<p>ERROR: REPLACE INTO failed (please contact Ravhin).\n";

			$mysql_info = explode(" ",mysql_info()); //records: X duplicates: X warnings: X

			$num_added = intval($mysql_info[1])-intval($mysql_info[4]);
			$num_dupe  = intval($mysql_info[4]);
			
			$output .= "<p>Added [" . $num_added . "] new prices to the auction database! (" . $num_dupe . " duplicates)\n";
			
			if ($num_added > 0) {
				// add entry to contributions table
				$QueryCMD = "INSERT INTO `" . $tableNameContrib . 
									"` (player,submitted,min_log,max_log,filename,num_lines,num_auctions,num_prices,num_added,submit_ip) VALUES ('" .
										addslashes(substr($_POST["player"],0,16)) . "','" . date("Y-m-d H:m:s") . "','" . date("Y-m-d H:m:s",$minTS) . "','" . 
										date("Y-m-d H:m:s",$maxTS) . "','" . $saveFilename . "'," . $num_lines . "," . $num_auctions . "," . $num_prices . "," . 
										$num_added . ",'" . $_SERVER['REMOTE_ADDR'] . "');";
										
				$res = mysql_query($QueryCMD);
				if (!res)
						$output .= "<p>ERROR: INSERT INTO contrib failed (please contact Ravhin).\n";
			}
			
			$output .= "<h3>Done.</h3>\n";
			$output .= "<a href='./Special:AuctionTracker'>Return to AuctionTracker</a>.\n\n";
			
			$wgOut->setPagetitle("Project 1999 Auction Tracker");
			$wgOut->addHTML($output);
			return;
		}
	}
	
  // parse and sanitize item name
  $itemName = trim($par);
	$itemName = mysql_real_escape_string($itemName);
	if (strlen($itemName) > 40) { $itemName = substr($itemName,0,40); }
	$itemName = str_replace("_"," ",$itemName);
	
	// stats		
	$QueryCMD = "SELECT COUNT(player) as cCount, DATE(MIN(min_log)) AS min_log," . 
								"DATE(MAX(max_log)) as max_log,SUM(num_auctions) as nAuc,SUM(num_added) as nAdd FROM `" . 
								$tableNameContrib . "` ;";
		$res = mysql_query($QueryCMD, $SQLcon);
	
		if ($res) {
			$rowContrib = mysql_fetch_assoc($res);						
		}
		
	$QueryCMD = "SELECT COUNT(DISTINCT itemname) as iCount FROM `" . 
								$tableName . "` ;";
		$res = mysql_query($QueryCMD, $SQLcon);
	
		if ($res) {
			$rowItem = mysql_fetch_assoc($res);						
		}
		
	// welcome page
	// ------------
  if (!$itemName) {
	  $output = "<span style='color:green;'>Current stats: <b>[" . $rowContrib["cCount"] . "]</b> submissions included <b>[" . 
		          $rowContrib["nAuc"] . "]</b> auctions with <b>[" . $rowContrib["nAdd"] . "]</b> unique item prices. Have records on <b>[" . 
							$rowItem["iCount"] . "]</b> items from <b>" . $rowContrib["min_log"] . "</b> to <b>" . $rowContrib["max_log"] . "</b>.</span>";
		$output .= "<p>Welcome to the p1999 auction tracker service, which tracks the market prices of ".
		           "items and goods offered for sale via the /auction channel, primarily in ".
							 "<a href='East_Commonlands'>East Commonlands</a>. It works as follows:\n\n";
		$output .= "<ol><li> User turns on /log while in EC and records a few hours/days of auctions.\n";
		$output .= "</li><li> User uploads logfile to the p99 wiki using the form below.\n";
		$output .= "</li><li> The wiki processes the log file and adds detected item prices to its database.\n";
		$output .= "</li><li> Details about an items value are added automatically to each item page, when available.\n\n";
		$output .= "</li></ol>\n\n";
		
		$output .= "<div style='border:1px solid #dd9; margin: 2px; padding: 10px;'>\n";
		$output .= "<h3> Upload a Log File </h3>\n";
		$output .= "The auction tracker <b>only works with the help of players who upload auction logs.</b>\n";
		$output .= "<p>To upload a log, first strip it to contain only auctions (optional, use ".
							 "<a href='http://gambosoft.com/Pages/Downloads.htm'>GamParse</a>, grep, etc.), then ".
		           "submit it below.</p>\n\n";
							 
		$output .= "<form action=\"./Special:AuctionTracker\" method=\"post\" enctype=\"multipart/form-data\">\n";
		$output .= "<label for=\"file\">Choose Log File:</label>\n";
		$output .= "<input type=\"file\" name=\"file\" id=\"file\" /><br />\n";
		$output .= "<label for=\"player\">Your Character Name:</label>\n";
		$output .= "<input type=\"text\" name=\"player\" id=\"player\" maxlength=\"14\"> (for contribution list)<br />\n";
		$output .= "<input type=\"submit\" name=\"submit\" value=\"Upload\" /> <b>Note: 2MB file size limit (.txt or .zip)!</b>\n";
		$output .= "</form>\n";
		$output .= "</div>\n\n";
		
		$output .= "<div style='border:1px solid #dd9; margin: 10px 2px 5px 2px; padding: 10px;'>\n";
		$output .= "<h3> Help Delete Bad Entries </h3>\n";
		$output .= "<p>To help weed out bad entries (either abnormally high or low, relative to the market average) ";
		$output .= "please <a href='./Special:AuctionTracker/TRIMOUTLIERS'>click here and remove entries that are inappropriate</a>. Thanks for your help!</p>";
		$output .= "</div>\n\n";
		
		$output .= "<p>Most recent contributions:</p>\n";
		$output .= "<table class='eoTable3'>\n";
		$output .= "<tr><th>Player</th><th>Date</th><th>Log Date Range</th><th>&#35; Auctions</th>";
		$output .= " <th>&#35; Item Prices</th><th>&#35; New Prices Added</th></tr>\n";
		
		$QueryCMD = "SELECT player,DATE(submitted) as date,DATE(min_log) AS min_log," . 
								"DATE(max_log) as max_log,num_auctions,num_prices,num_added FROM `" . $tableNameContrib . 
								"` ORDER BY submitted DESC LIMIT 20;";
		$res = mysql_query($QueryCMD, $SQLcon);
	
		if ($res) {
			while ($row = mysql_fetch_assoc($res)) {
					$date    = date('d M Y',strtotime($row["date"]));
					$min_log = date('d M Y',strtotime($row["min_log"]));
					$max_log = date('d M Y',strtotime($row["max_log"]));
					if ($row["player"] == "")
						$row["player"] = "<i>Anonymous</i>";
						
					$output .= " <tr><td>" . $row["player"] . "</td><td>" . $date . "</td><td>" . $min_log . 
					           " to " . $max_log . "</td><td>" . $row["num_auctions"] . "</td><td>" . 
										 $row["num_prices"] . "</td><td>" . $row["num_added"] . "</td></tr>\n";
			}
		}
		
		$output .= "</table>\n\n";
		
		$output .= "<p>Ten most frequently seen items:</p>\n";
		$output .= "<table class='eoTable3'>\n";
		$output .= "<tr><th>Item</th><th>Occurences</th><th>All Time Avg Price</th><th>All Time Min Max</th></tr>\n";
			
		$QueryCMD = "SELECT itemname,AVG(price) as mean,STDDEV(price) AS stddev," . 
								"COUNT(price) as count,MIN(price) as min,MAX(price) as max FROM `" . $tableName . 
								"` GROUP BY itemname ORDER BY COUNT(price) DESC LIMIT 10;";
		$res = mysql_query($QueryCMD, $SQLcon);		
		
		if ($res) {
			while ($row = mysql_fetch_assoc($res)) {
				$avg   = round($row["mean"]);
				$std = round($row["stddev"]);
					$output .= " <tr><td><a href=\"./" . str_replace(" ","_",$row["itemname"]) . "\">" . $row["itemname"] . 
										 "</a></td><td>" . $row["count"] . "</td><td>" . 
										 $avg . " &#177; " . $std . "</td><td>" . $row["min"] . " / " . $row["max"] . 
										 "</td></tr>\n";
			}
		}
		
		$output .= "</table>\n\n";
		
		$wgOut->setPagetitle("Project 1999 Auction Tracker");
    $wgOut->addHTML($output);
    return;
  }
	
	// outlier trimming
	// ----------------
	$minNumForTrim = 30;
	$sigmaClip = 6.0;
	
	if (substr($itemName,0,12) == "TRIMOUTLIERS") {
			$output = "<p>Removal Candidates:</p>\n";
			
			// get list of unique itemnames
			$QueryCMD = "SELECT itemname FROM `" . $tableName . 
									"` GROUP BY itemname HAVING COUNT(itemname) >= ".$minNumForTrim.";";
			$res = mysql_query($QueryCMD, $SQLcon);
			
			$items = array();
			$remHashList = array();
			
			while ($row = mysql_fetch_array($res))
				$items[] = $row[0];
				
			if (substr($itemName,0,16) != "TRIMOUTLIERSCONF") {
				//$output .= "<p>[[Special:AuctionTracker/TRIMOUTLIERSCONF|Execute Delete Statement]]\n"; 
				$output .= "[[Special:AuctionTracker|Return to AuctionTracker]].\n";
			} else {
				$hash = substr($itemName,17);
				if (strlen($hash) == 8) {
					$QueryCMD = "DELETE FROM `" . $tableName . "` WHERE hash='" . addslashes($hash) . "';";
					$res = mysql_query($QueryCMD, $SQLcon);
					if ($res)
						$output .= "<h3>".mysql_affected_rows()." entries deleted.</h3>\n";
					$output .= "[[Special:AuctionTracker|Return to AuctionTracker]].\n";
				}
			}
				
			$output .= "{| class='eoTable3'\n";
			$output .= "! Item Name \n! Price\n! Seller \n!Mean Price \n! Stddev\n! Num Seen\n! Remove \n";				
				
			foreach ($items as $item)
			{
				// get common statistics (exclude min/max for outliers)
				$QueryCMD = "SELECT AVG(price) as mean,STDDEV(price) AS stddev," . 
										"COUNT(price) as count,MIN(price) as min,MAX(price) as max FROM `" . $tableName . 
										"` WHERE itemname='" . $item . "' AND price != ".
										"(SELECT MIN(price) FROM `" . $tableName . "` WHERE itemname='" . $item . "') " .
										"AND price != (SELECT max(price) FROM `". $tableName . "` WHERE " .
										"itemname='" . $item . "');";
				$res = mysql_fetch_array(mysql_query($QueryCMD, $SQLcon));
				
				$stats = array("mean"=>round($res["mean"]),"stddev"=>round($res["stddev"]),"count"=>$res["count"],
											 "min"=>$res["min"],"max"=>$res["max"]);
		
				// get removal candidates
				$QueryCMD = "SELECT hash,price,seller FROM `" . $tableName . "` WHERE itemname='" . $item . "';";
				$res = mysql_query($QueryCMD, $SQLcon);
				
				if ($res) {
					while ($row = mysql_fetch_assoc($res)) {
							// sigma clipping
							if (($stats["stddev"] && ($row["price"] < $stats["mean"] - $sigmaClip*$stats["stddev"] ||
																			 $row["price"] > $stats["mean"] + $sigmaClip*$stats["stddev"])) ||
									($stats["mean"] > 300 && $row["price"] < 30) ||
									($stats["mean"] < 1000 && $row["price"] > 5000)) {
									
								$output .= "|-\n| " . $item . " || " . $row["price"] . " || " . $row["seller"] . 
													 " || " . $stats["mean"] . " || " . $stats["stddev"] . " || " . $stats["count"] . " || " .
													 "[[Special:AuctionTracker/TRIMOUTLIERSCONF/" . $row["hash"] . "|REMOVE]]\n";
								$remHashList[] = $row["hash"];
							}
					}
				}
			}
			
			$output .= "|}\n";
				
			// construct delete statement from remHastList and execute
		/*	$QueryCMD = "DELETE FROM `" . $tableName . "` WHERE hash IN (";
			foreach ($remHashList as $hash)
				$QueryCMD .= "'".$hash."',";
			$QueryCMD = substr($QueryCMD,0,-1);
			$QueryCMD .= ");";
		*/
	
			$wgOut->setPagetitle("Project 1999 Auction Tracker");
			$wgOut->addWikiText($output);
			return;
	}
	
	// query DB - pricing stats (all time)
  $QueryCMD = "SELECT AVG(price) as mean,STDDEV(price) AS stddev," . 
              "COUNT(price) as count,MIN(price) as min,MAX(price) as max FROM `" . $tableName . 
							"` WHERE itemname='" . $itemName . "';";
  $res = mysql_fetch_array(mysql_query($QueryCMD, $SQLcon));
	
	$meanPriceAlltime   = round($res["mean"]);
	$stddevPriceAlltime = round($res["stddev"]);
	//$medianPriceAlltime = round($res["median"]);
	$minPriceAlltime    = round($res["min"]);
	$maxPriceAlltime    = round($res["max"]);
	$countAlltime  = round($res["count"]);
	
	if ($meanPriceAlltime == 0) {
	  //$wgOut->addWikiText("<p>Error: No pricing data found for all time (output nothing and silent, for NO DROPS, etc).");
		return;
	}
	
	// query DB - pricing stats (short)
	$dateCutoffShort = date('Y-m-d',time()-$shortTimeInDays*24*60*60);
  $QueryCMD = "SELECT AVG(price) as mean,STDDEV(price) AS stddev FROM `".$tableName."` WHERE itemname='" .
             	$itemName . "' AND datetime >= '" . $dateCutoffShort . "';";
  $res = mysql_fetch_array(mysql_query($QueryCMD, $SQLcon));
	
	if ($res["mean"]) {
		$meanPriceShort   = round($res["mean"]);
		$stddevPriceShort = round($res["stddev"]);
	} else {
		$meanPriceShort   = "?";
		$stddevPriceShort = "?";
	}
	
	// query DB - pricing stats (medium)
	$dateCutoffMedium = date('Y-m-d',time()-$mediumTimeInDays*24*60*60);
  $QueryCMD = "SELECT AVG(price) as mean,STDDEV(price) AS stddev FROM `".$tableName."` WHERE itemname='" .
             	$itemName . "' AND datetime >= '" . $dateCutoffMedium . "';";
  $res = mysql_fetch_array(mysql_query($QueryCMD, $SQLcon));
	
	if ($res["mean"]) {	
		$meanPriceMedium   = round($res["mean"]);
		$stddevPriceMedium = round($res["stddev"]);
	} else {
		$meanPriceMedium   = "?";
		$stddevPriceMedium = "?";
	}	
	
	// query DB - price time series
	$dateCutoffMysql = date('Y-m-d',time()-$daysPlotLimit*24*60*60);
	$QueryCMD = "SELECT DATE(datetime) as date,AVG(price) as price FROM `".$tableName."` WHERE itemname='" .
	            $itemName . "' AND datetime >= '" . $dateCutoffMysql . 
							"' GROUP BY DATE(datetime) ORDER BY datetime ASC LIMIT " . $numPlotLimit . ";";
	$res = mysql_query($QueryCMD, $SQLcon);
	
	// pChart library inclusions (must be before VOID use)
	include("pChart2.1.3/class/pData.class.php");
	include("pChart2.1.3/class/pDraw.class.php");
	include("pChart2.1.3/class/pImage.class.php");	
	//include("pChart2.1.3/class/pCache.class.php");	
	
	// pChart: construct data
	// ------
	$dates      = array();
	$prices     = array();
	$pricesReal = array(); // only non-padded values for point overplot (tag padded as VOID)
	
	$prevprice = 0;
	$prevdate  = 0;
	$i = 0;
	
	// make sure have a data point for each date (pad with previous pricing)
	if ($res) {
		while ($row = mysql_fetch_assoc($res)) {
				$date = date('M-d',strtotime($row["date"]));
				$price = intval($row["price"]);
				
				// missing days since last entry
				if (strtotime($row["date"]) - $prevdate > 24*60*60 && count($dates) > 0) {
					$numDaysToAdd = (strtotime($row["date"]) - $prevdate) / (24*60*60) - 1;
					
					// pad with previous pricing for the number of missing days
					for ($j=0; $j < $numDaysToAdd; $j++) {
							$paddate = date('M-d',$prevdate + $j*(24*60*60));
							$dates[] = $paddate;
							$prices[] = $prevprice;
							$pricesReal[] = VOID;
					}
					
					// finally add the entry we just read
					$dates[] = $date;
					$prices[] = $price;
					$pricesReal[] = $price;
				} else {
					// wait to start additions until we are within the requested date range of now
					//if (strtotime($row["date"]) < time()-$daysPlotLimit*24*60*60)
					//	continue;
						
					// if this is the first entry, pad back to the requested date range
					if (count($dates) == 0 && (strtotime($row["date"]) - strtotime($dateCutoffMysql)) > 24*60*60)
					{
						$numDaysToAdd = (strtotime($row["date"]) - strtotime($dateCutoffMysql)) / (24*60*60) - 1;
						
						for ($j=0; $j < $numDaysToAdd; $j++) {
								$paddate = date('M-d',strtotime($dateCutoffMysql) + $j*(24*60*60));
								$dates[] = $paddate;
								$prices[] = $price;
								$pricesReal[] = VOID;
						}
					}
						
					// no skipped days, just add entry
				  $dates[] = $date;
					$prices[] = $price;
					$pricesReal[] = $price;
				}
				
				$prevdate = strtotime($row["date"]); // last one compared to file modification time
				$prevprice = intval($row["price"]);
				$i++;
		}
	}
	
	//" . $dates[count($dates)-1]
	$filename = "auctiontracker/".str_replace("`","",str_replace("'","",str_replace(" ","_",stripslashes($itemName)))).".png";
	
	// check if up to date chart already exists, in which case don't remake it
	if (file_exists($filename) && filemtime($filename) >= $prevdate) {
		$picStr = "http://wiki.project1999.org/".$filename."\n";
	} else {
		if (count($prices) > 0) {
		  // construct pData object and add series
			$myData = new pData();
			
			$path = "extensions/AuctionTracker/pChart2.1.3/";
			$myData->loadPalette($path."palettes/summer.color", TRUE);
		
			$myData->addPoints($dates,"Date");
			$myData->addPoints($prices,"Price");
			$myData->addPoints($pricesReal,"PriceReal");
			$myData->setAbscissa("Date");
			
			// set color palettes for data series
      $serieSettings = array("R"=>75,"G"=>255,"B"=>75,"Alpha"=>95);
      $myData->setPalette("Price",$serieSettings);
      $serieSettings = array("R"=>255,"G"=>255,"B"=>255,"Alpha"=>90);
      $myData->setPalette("PriceReal",$serieSettings);
			
			// create hash
			//$myCache = new pCache(array("CacheFolder"=>$path."cache/"));
			//$chartHash = $myCache->getHash($myData);
			
			// check if hash exists already
			//if ( $myCache->isInCache($chartHash)) {
			//		$myCache->saveFromCache($chartHash,$filename);
			//} else {
			
			// pChart: construct plot
			$myData->setAxisName(0,"");
			//$myData->setAxisColor(0,array("R"=>255,"G"=>255,"B"=>255));
			$myPicture = new pImage(500,350,$myData);
			
			/* Draw a solid background */
			//$Settings = array("R"=>179, "G"=>217, "B"=>91, "Dash"=>1, "DashR"=>199, "DashG"=>237, "DashB"=>111);
			$Settings = array("R"=>88, "G"=>88, "B"=>247, "Dash"=>1, "DashR"=>68, "DashG"=>68, "DashB"=>227);
			$myPicture->drawFilledRectangle(0,0,500,350,$Settings);
			 
			/* Overlay some gradient areas */
			//$Settings = array("StartR"=>43, "StartG"=>107, "StartB"=>58, "EndR"=>194, "EndG"=>231, "EndB"=>44, "Alpha"=>50);
			//$Settings = array("StartR"=>13, "StartG"=>28, "StartB"=>57, "EndR"=>44, "EndG"=>24, "EndB"=>131, "Alpha"=>50);
			//$myPicture->drawGradientArea(0,0,500,350,DIRECTION_VERTICAL,$Settings);
			$myPicture->drawGradientArea(0,0,500,350,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>150,"EndG"=>150,"EndB"=>150,"Alpha"=>30));
			$myPicture->drawGradientArea(0,0,500,350,DIRECTION_HORIZONTAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>10));
			 
			/* Draw the border */
			$myPicture->drawRectangle(0,0,499,349,array("R"=>0,"G"=>0,"B"=>0));

			$myPicture->setFontProperties(array("FontName"=>$path."fonts/MankSans.ttf","FontSize"=>22));
			$TextSettings = array("Align"=>TEXT_ALIGN_MIDDLEMIDDLE, "R"=>255, "G"=>255, "B"=>255);
			$myPicture->drawText(265,25,"p1999 Pricing - ".stripslashes($itemName),$TextSettings);
			
			$myPicture->setFontProperties(array("FontName"=>$path."fonts/verdana.ttf","FontSize"=>8,"R"=>255,"G"=>255,"B"=>255));
			
			$myPicture->setGraphArea(40,50,480,330);
			
			// calc skipping
			if (count($prices) > 10) {
					$skipping = intval(floor(count($prices)/10));
			} else {
					$skipping = 0;
			}
			
			// draw scale
			$scaleSettings = array("LabelSkip"=>$skipping,"DrawSubTicks"=>FALSE,"CycleBackground"=>TRUE,"AxisR"=>255,"AxisG"=>255, "AxisB"=>255);
			$myPicture->drawScale($scaleSettings);
			
			// draw filled cubic spline chart and overplot points from "Real" arrays
			$Config = array("DisplayValues"=>FALSE);
			$myData->setSerieDrawable("PriceReal",FALSE);
			$myPicture->drawFilledSplineChart($Config);
			
			$myData->setSerieDrawable("PriceReal",TRUE);
			$myData->setSerieDrawable("Price",FALSE);
			$myPicture->drawPlotChart($Config);
		
			//$myCache->writeToCache($chartHash, $myPicture);
			$myPicture->render($filename);
			
			$picStr = "http://wiki.project1999.org/".$filename."\n";
			
			//} //cache
			
		} else {
			$picStr = "'''Warning: No pricing data (in the last ".$daysPlotLimit." days) found to make chart.'''";
		}
	} // file_exists
	
	// query DB - detailed listings
	$QueryCMD = "SELECT DATE(datetime) as datetime,seller as seller,price as price FROM `".$tableName."` WHERE itemname='" .
	            $itemName . "' ORDER BY datetime DESC LIMIT " . $detailLimit . ";";
	$res = mysql_query($QueryCMD, $SQLcon);
	
	$detailedList = array();
	if ($res) {
	  while ($row = mysql_fetch_assoc($res)) {
		  $detailedList[] = array("datetime" => $row["datetime"],
			                        "seller"   => $row["seller"],
															"price"    => $row["price"]);
		}
	}

	// output header
	// ------
	$output = "<div class='auctrackerbox'>";
	
	$output .= "<span style='text-align:center;'><span style='font-size:180%; padding-bottom:8px;'>Project 1999 Auction Tracker</span></span>\n";	
	
	// output stats
	$output .= "{| class='eoTable3' style='width:500px;'\n";
	$output .= "! ".$shortTimeInDays."d Avg \n! ".$mediumTimeInDays."d Avg\n! All Time Avg\n! All Time Range\n! &#35; Seen\n";
	$output .= "|-\n";
  $output .= "| "	. $meanPriceShort . " &#177; " . $stddevPriceShort . "\n";
  $output .= "| "	. $meanPriceMedium . " &#177; " . $stddevPriceMedium . "\n";
	$output .= "| " . $meanPriceAlltime . " &#177; " . $stddevPriceAlltime . "\n";
	$output .= "| " . $minPriceAlltime . " / " . $maxPriceAlltime . "\n";
	$output .= "| " . $countAlltime . "\n";
	$output .= "|}\n";
	
	// output chart
	$output .= $picStr . "\n";
	
	// output truncated (most recent) time series data
	if (count($detailedList) > 0) {
		$output .= "{| class='eoTable' style='width:100%;'\n";
		$output .= "! Date \n! Seller \n! Price\n! Date \n! Seller \n! Price\n";
		foreach ($detailedList as $i => $detail) {
		  if ($i % 2 == 0) {
				$output .= "|-\n";
				$output .= "| " . $detail["datetime"] . " || " . $detail["seller"] . " || " . $detail["price"] . "";
			} else {
				// second set of columns
				$output .= " || " . $detail["datetime"] . " || " . $detail["seller"] . " || " . $detail["price"] . "\n";
			}
		}
		// add final newline if odd number
		if ($i % 2 == 0)
			$output .= "\n";
			
		$output .= "|}\n";
		$output .= "<span style='float:right;'> ''(Last ".$detailLimit." Recorded Auctions)'' </span>\n";
	}
	
	// output footer
  $output .= "</div>";
	
	// parser (attempt to suppress extra space at top, no good)
	//$result = $wgOut->parseInline($output);
	//$wgOut->addHTML($result);
	
	$wgOut->addWikiText($output);

}

}
