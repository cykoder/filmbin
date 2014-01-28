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

/*
    A crawler class for www.primewire.ag
*/
class PrimeWireCrawler extends Crawler
{
    public function __construct()
    {
        //Set base URL
        parent::__construct("http://www.primewire.ag/index.php?sort=views&page=");
    }

    protected function get_links($content)
    {
        //Create a links array
        $links = array();

        //Create a DOM parser
        $html = new simple_html_dom();
        $html->load($content);

        //Loop through individual items
        $items = $html->find("div.index_item");
        foreach($items as $item)
        {
            //Get the link
            array_push($links, "http://www.primewire.ag" . $item->children[0]->attr['href']);
        }

        //Return the list of links
        return $links;
    }

    public function scrape_data($url)
    {
        //Get the response
        $response = $this->ufetch($url);

        //Get the title from the <title> tag
        if ($c=preg_match_all("#\<title\>Watch (.*?) online #s", $response, $matches))
        {
            //Set media data
            $media_title = $matches[1][0];

            //Get the HTML surrounding the release date
            $release_html = substr($response, strpos($response, "Released:"), 51);

            //Check if we have a released date
            if ($c=preg_match_all("/.*?\\d+.*?(\\d+)/is", $release_html, $matches))
            {
                //Get the release year
                $release_year = $matches[1][0];

                //Get the title data
                $title_data = $this->get_data($media_title, $release_year);

                //Get all external links
                if($c=preg_match_all("#/external\.php(.*?)\"#s", $response, $matches))
                {
                    $title_data['links'] = array();
                    for($i=1; $i < count($matches[0]); $i++)
                    {
                        //Get the true url
                        $true_url = $this->get_true_url("http://www.primewire.ag" . $matches[0][$i]);
                        if(isset($true_url) && $true_url !== false)
                        {
                            //Add the link
                            array_push($title_data['links'], array("base" => $true_url, "video" => "null"));
                        }
                    }
                }

                //Return data
                return $title_data;
            }
            else //Error, no release date
            {
                throw new Exception("No release date for: " . $media_title);
            }
        }
        else //Error, no release date
        {
            throw new Exception("No title found for: " . $url);
        }
    }
}
?>