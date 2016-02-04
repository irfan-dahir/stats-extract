Stats.Extract ver.0.3
Developed by Nighthawk (http://www.unrealsoftware.de/profile.php?userid=116310)
February 2016 Update
----------------------------------

[1] Installation {
	1. On your website host, copy the s.e library anywhere you want. Make sure you open it up and configure linking to the simple html dom lib as well.
	2. Seriously, open up stats.extract.php and check the defines to set your configuration. I'll be taking it as you know PHP well.
	3. Check example.php if you need help. It's not that hard. o.O Everything returns in an array, remember that.
	4. Does this even need a step 4? If you don't have programming knowledge, especially PHP, drop this and go download some weapon skins.
	5. brb, booty call.
}

[2] Changelog {
	[0.3] {
		-Fixed many bugs
		-Removed top 30 user extracts from serverstats.html (You have userstats.dat extraction for that + no more simpleHTMLDOM dependency!)
		-Optimized the code
		-Changed defined constants to prevent namespace collisions (defines now start with SE_)
		-Changed some returning array key names
		-[b]parse_live_server_stats[/b] is now [b]parse_live_server_info[/b]
		-Re-wrote [b]parse_live_server_info[/b]
		-Removed unnecessary code
		-If there's an error, the script won't stop (die()) and instead will now return FALSE
	}
	[0.2d] {
		-Fixed another fclose issue
	}
	[0.2c] {
		-Removed 'rank' in parsed html stats. You can get the ranks on your own based on the arrangement of the array.
		-Added util method [calc_kpd($frags,$deaths)] to calculate the kills per death ratio. 2 obvious parameters.
		-Added [usgn_avatar($usgn)]; gets the usgn's avatar link from usgn.de. Returns empty if he has no avatar of his own.
		-Added [usgn_name($usgn)]; gets the usgn owner's name from usgn.de.
		-Updated Documentation
		-A bit of more efficiency with extraction algorithm.
		-Added a file check for the simple_html_dom library (dependacy), you can turn this filecheck off in the preload defines!
	}
	[0.2b] {
		-Fixed extracted and sorted serverstats user ranks
	}
	[0.2a] {
		-Fixed fclose issue.
	}
	[0.2] {
		-Added Retrieving server stats via PHP sockets
		-Re-did the whole damn lib
		-No more token temp files!
		-No more extra configuration files!
		-A shit ton more efficient. And there should be an increase in speed as well. ;0
		-That's pretty much it, I suppose?
	}
}

[3] Copyrights {
	This lib is developed by Nighthawk.
		-You have permission to alter/develop it
		-You have permission host this as a working script (ofcourse, how else will you use it?)
		-You don't have permission to re-distribute ;o
		-Er - that's it I suppose
}

[4] Links {
	Live Infograph-ish thing: http://irfandahir.com/stats-extract/
	-[IFwsI]- uses S:E: http://fwsstatistics.tk
	Project extract 3 lolwutisthisdoinghere: http://www.unrealsoftware.de/files_show.php?file=16266
	Stat Extract 0.3; Go gimme sum feedback if you haven't: http://www.unrealsoftware.de/files_show.php?file=16081
	Need help with this? Implementation issues? Any questions? PM me on the forums or; thesynchronousdeveloper@gmail.com
}

[5] Package Structure {
	-stats.extract.php (core)
	-example.php (example)
	-Readme.txt (you are here)
}

I am not responsible for any damage you cause.