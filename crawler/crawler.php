<?php
/*
    Copyright (C) 2014 Sam Hellawell (sshellawell@gmail.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//Include required classes
include("lib/simple_html_dom.php");
include("lib/DB.php");
include("lib/NetHelper.php");
include("lib/Crawler.php");
include("lib/PrimeWireCrawler.php");
include("lib/VideoScraper.php");

//Include the configuration file
include("config.php");

//Create a video scraper
$video_scraper = new VideoScraper();

//These are all test subjects
//print_r($video_scraper->get_video("http://www.putlocker.com/file/5C70E593CA536247"));
//print_r($video_scraper->get_video("http://www.sockshare.com/file/4A5DCE322B1DFC5B"));
//print_r($video_scraper->get_video("http://sharerepo.com/edr7lk0npx1o"));
//print_r($video_scraper->get_video("http://gorillavid.in/v714au2tmksn"));
//print_r($video_scraper->get_video("http://filenuke.com/vbpa9x3uvnc5"));
//print_r($video_scraper->get_video("http://mightyupload.com/mqk50nigg0xl/Sleep_Awake_(Dormi_Trezeste-te)2012.mp4.html"));

//Debug info
print "############################\n";
print "#                          #\n";
print "#   FilmBin site crawler   #\n";
print "#            v1            #\n";
print "#                          #\n";
print "############################\n\n";

//Check if we already have arguments to work with
if(isset($argv) && count($argv) > 1)
{
	//Set based on command line arguments
	$service = $argv[1];
	$start_page = $argv[2];
	$end_page = $argv[3];
}
else
{
	//Get the start page
	print "Enter start page to crawl: ";
	$start_page = intval(fgets(STDIN));

	//Get the end page
	print "Enter the end page: ";
	$end_page = intval(fgets(STDIN));

	//Get the service
	print "Enter the service to crawl: ";
	$service = trim(fgets(STDIN));
	print "\n";
}

//Initialise a crawler based on the service entered
switch($service)
{
	case "primewire":
	case "primewire.ag":
	case "pw":
		$crawler = new PrimeWireCrawler();
	break;

	default:
		print "Service not found. Quitting.";
		die();
	break;
}

//Debug info
print "Crawling " . $service . "...\n\n";

//Set start time for stats
$crawl_start = microtime(true);

//Get title links
$title_links = $crawler->crawl($start_page, $end_page);

//Output stats and some debug info
print "Crawl took " . (microtime(true) - $crawl_start) . " seconds and found " . count($title_links) . " titles.";
print "\n\n";
print "Scraping data...";
print "\n\n";


$titles_array = array();

//Set start time for stats
$time_start = microtime(true);

//Loop through all links
for($index=0; $index < count($title_links); $index++)
{
	print "\tScraping " . $title_links[$index] . "...\n";

	//Scrape the title data
	$title_data = $crawler->scrape_data($title_links[$index]);
	if($title_data != null)
	{
		//Remove links from title data
		$links = $title_data['links'];
		unset($title_data['links']);

		//Add title to array
		array_push($titles_array, $title_data);

		//Insert into the database
		DB::insertUpdate("titles", $title_data);

		//Process links
		$insertid = DB::insertId();
		if($insertid > 0)
		{
			//Go through each link
			$valid_links = array();
			for($i=0; $i < count($links); $i++)
			{
				try
				{
					//Set title ID
					$links[$i]['title_id'] = $insertid;

					//Get video URL
					$links[$i]['video'] = $video_scraper->get_video($links[$i]['base']);
				}
				catch(Exception $e) //A wild error appears!
				{
					//print "Error: " . $e->getMessage();
				}

				//Check if video link is null/empty
				if(!isset($links[$i]['video']) ||
				   is_null($links[$i]['video']) ||
				   empty($links[$i]['video']) ||
				   ((string)$links[$i]['video']) == "null" ||
				   ((string)$links[$i]['video']) == "")
				{
					//Nothing, need to reverse the polarity of the neutron flow ^^^
				}
				else
				{
					//Add to valid array
					array_push($valid_links, $links[$i]);

					//Debug info
					print "\t\tAdded link: " . $links[$i]['base'] . "\n";
				}
			}

			//Insert links into the database
			if(count($valid_links) > 0)
			{
				DB::insertIgnore("title_links", $valid_links);
			}

			//Get rid of garbage
			unset($links);
			unset($valid_links);
		}

		print "\n\n";
	}
	else //An error occured, let's log it
	{
		print "\tError - unable to scrape data\n\n";
	}
}

//Debug info
print "\n\n";
print "Finished. Scrape took " . (microtime(true) - $time_start) . " seconds and added " . count($title_links) . " titles";
print "\n\n";
?>