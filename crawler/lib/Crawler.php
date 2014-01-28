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

class Crawler extends NetHelper
{
    private $base_url;

    public function __construct($base_url)
    {
        //Set base URL
        $this->base_url = $base_url;
    }

    public function crawl($start_page = 1, $end_page = 2)
    {
        //Create an array for links
        $links_array = array();

        //Loop through pages
        for($page = $start_page; $page <= $end_page; $page++)
        {
            //Get links on the page
            $links = $this->get_links($this->ufetch($this->base_url . $page));

            //Add to main array
            $links_array = array_merge($links_array, $links);

            //Unset links from this page
            unset($links);
        }

        //Return the links
        return $links_array;
    }

    public function stop()
    {
        //Call NetHelper's recycle method
        $this->recycle();
    }

    public function scrape_data($url)
    {
        //Throw an exception, we should override this method
        throw new Exception("Crawler scrape_data must be overridden!");
    }

    protected function get_data($media_title, $release_year, $check_imdb=true)
    {
        //Query OMDB, check if we're searching for the right thing
        $check_data = json_decode($this->ufetch("http://www.omdbapi.com/?s=" . urlencode($media_title) . "&y=" . $release_year));
        if(isset($check_data) && isset($check_data->Search))
        {
            //Get actual data
            $check_data = $check_data->Search[0];

            //Check if title is different
            if(strtolower($check_data->Title) != strtolower($media_title))
            {
                unset($release_year);
            }
        }

        //Build the OMDB url
        $omdb_url = "http://www.omdbapi.com/?plot=full&t=" . urlencode($media_title);
        if(isset($release_year)) $omdb_url .= "&y=" . $release_year;

        //Query OMDB for media data
        $omdb_data = json_decode($this->ufetch($omdb_url));

        //Check if we have a valid response
        if(isset($omdb_data) && isset($omdb_data->Response) && $omdb_data->Response == "True")
        {
            //Create a title data array
            $title_data = array("title" => $media_title,
                                "rated" => $omdb_data->Rated,
                                "release_date" => strtotime($omdb_data->Released),
                                "runtime" => str_replace(" min", "", $omdb_data->Runtime),
                                "genre" => $omdb_data->Genre,
                                "director" => $omdb_data->Director,
                                "actors" => $omdb_data->Actors,
                                "plot" => $omdb_data->Plot,
                                "poster" => $omdb_data->Poster,
                                "rating" => $omdb_data->imdbRating,
                                "type" => $omdb_data->Type);

            //Check for invalid data to nullify
            if($title_data['rated'] == "N/A") $title_data['rated'] = null;
            if($title_data['runtime'] == "N/A") $title_data['runtime'] = null;
            if($title_data['genre'] == "N/A") $title_data['genre'] = null;
            if($title_data['director'] == "N/A") $title_data['director'] = null;
            if($title_data['actors'] == "N/A") $title_data['actors'] = null;
            if($title_data['plot'] == "N/A") $title_data['plot'] = null;
            if($title_data['rating'] == "N/A") $title_data['rating'] = null;
            if($title_data['poster'] == "N/A") $title_data['poster'] = null;

            //Check if release date is invalid
            if(isset($release_year) && ($title_data['release_date'] == "" || $title_data['release_date'] == 0))
            {
                $title_data['release_date'] = strtotime("1st Jan " . $release_year);
            }

            //Return data
            return $title_data;
        }
        else if($check_imdb) //Nothing from OMDB, get right title from IMDB
        {
            //Query mobile IMDB
            $reponse = $this->ufetch("http://m.imdb.com/find?q=" . urlencode($media_title));

            //Get html between first .title element
            if($c=preg_match("#\<div class\=\"title\"\>(.*?)\</div\>#s", $reponse, $matches))
            {
                //Get the fixed title
                $fixed_title = preg_replace('/<a[^>]*?>([\\s\\S]*?)<\/a>/','\\1', $matches[1]);

                //Remove whitespace
                $fixed_title = preg_replace('/(\s)+/', ' ', $fixed_title);

                //Get the release year
                $release_year = intval(substr($fixed_title, -6, 4));

                //Chop off the release year
                $fixed_title = substr($fixed_title, 1, strlen($fixed_title)-9);
                
                //Get data again
                return $this->get_data($fixed_title, $release_year, false);
            }

            //Return nothing, atleast we tried!
            return null;
        }
    }

    protected function get_links($content)
    {
        //Throw an exception, we should override this method
        throw new Exception("Crawler get_links must be overridden!");
    }
}
?>