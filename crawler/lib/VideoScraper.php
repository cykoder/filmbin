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

class VideoScraper extends NetHelper
{
    public function get_video($url)
    {
        //Parse the URL
        $host = parse_url($url, PHP_URL_HOST);

        //Remove www.
        $host = str_replace("www.", "", $host);

        //Remove domain name extension
        $host = substr($host, 0, strrpos($host, "."));

        //Check if we can scrape data from this host
        $result = null;
        if(method_exists($this, "get_from_" . $host))
        {
            //We can, call get_from_*
            $result = call_user_func(array($this, "get_from_" . $host), $url);
        }

        //Check if we've failed
        if($result != null)
        {
            return $result;
        }
        else
        {
            throw new Exception("Unable to find file link for: " . $url);
        }
    }

    public function get_from_mightyupload($url)
    {
        //Get content
        $response = $this->ufetch($url);

        //Get the id form field
        preg_match("#\<input type\=\"hidden\" name\=\"id\" value\=\"(.*?)\"\>#s", $response, $matches);
        $title_id = $matches[1];

        //Get the rand form field
        preg_match("#\<input type\=\"hidden\" name\=\"rand\" value\=\"(.*?)\"\>#s", $response, $matches);
        $rand = $matches[1];

        //Post so we can bypass bot detection
        $response = $this->upost($url, array("op" => "download2",
                                             "id" => $title_id,
                                             "rand" => $rand,
                                             "referer" => $url,
                                             "plugins_are_not_allowed" => "5",
                                             "method_free" => "",
                                             "method_premium" => "",
                                             "down_direct" => 1,
                                             "submit" => "Click here to Continue"));

        //Remove all whitespace for processing
        $response = preg_replace('/^\s+|\n|\r|\s+$/m', '', $response);

        //Get the file URL
        if($c=preg_match_all("#jwplayer\('container'\)\.setup\(\{file\: '(.*?)',#s", $response, $matches))
        {
            return $matches[1][0];
        }

        //We failed :(
        return null;
    }

    public function get_from_filenuke($url)
    {
        //Get content
        $response = $this->ufetch($url);

        //Get the id form field
        preg_match("#\<input type\=\"hidden\" name\=\"id\" value\=\"(.*?)\"\>#s", $response, $matches);
        $title_id = $matches[1];

        //Get the fname form field
        preg_match("#\<input type\=\"hidden\" name\=\"fname\" value\=\"(.*?)\"\>#s", $response, $matches);
        $fname = $matches[1];

        //Post so we can bypass bot detection
        $response = $this->upost($url, array("op" => "download1",
                                             "usr_login" => "",
                                             "id" => $title_id,
                                             "fname" => $fname,
                                             "referer" => $url,
                                             "method_free" => "Free"));

        //Remove all whitespace for processing
        $response = preg_replace('/^\s+|\n|\r|\s+$/m', '', $response);
        
        //Get the player code (packed)
        if($c=preg_match_all("#\<div id\=\"player_code\"\>\<span id\='flvplayer'\>\</span\>\<script type\='text/javascript' src\='http\://filenuke\.com/player/swfobject\.js'\>\</script\>\<script type\='text/javascript'\>(.*?)\</script\>#s", $response, $matches))
        {
            //Submit packed player code to unpacker
            $unpacked_code = $this->upost("http://jsunpack.jeek.org/", array("urlin" => $matches[1][0]));
            
            //Get the file URL from unpacked code
            if($c=preg_match_all("#s1\.addVariable\('file','(.*?)'\);#s", $unpacked_code, $matches))
            {
                return $matches[1][0];
            }
        }

        //We failed :(
        return null;
    }

    public function get_from_gorillavid($url)
    {
        //Get content
        $response = $this->ufetch($url);

        //Get the id form field
        preg_match("#\<input type\=\"hidden\" name\=\"id\" value\=\"(.*?)\"\>#s", $response, $matches);
        $title_id = $matches[1];

        //Get the fname form field
        preg_match("#\<input type\=\"hidden\" name\=\"fname\" value\=\"(.*?)\"\>#s", $response, $matches);
        $fname = $matches[1];

        //Post so we can bypass bot detection
        $response = $this->upost($url, array("op" => "download1",
                                             "usr_login" => "",
                                             "id" => $title_id,
                                             "fname" => $fname,
                                             "referer" => $url,
                                             "channel" => "",
                                             "method_free" => "Free Download",
                                             "btn_download" => "Continue"));

        //Remove all whitespace for processing
        $response = preg_replace('/^\s+|\n|\r|\s+$/m', '', $response);

        //Get video URL
        if($c=preg_match_all("#file\: \"(.*?)\",#s", $response, $matches))
        {
            return $matches[1][0];
        }

        //We failed :(
        return null;
    }

    public function get_from_sharerepo($url)
    {
        //Get content
        $response = $this->ufetch($url);

        //Get the id form field
        preg_match("#\<input type\=\"hidden\" name\=\"id\" value\=\"(.*?)\"\>#s", $response, $matches);
        $title_id = $matches[1];

        //Get the fname form field
        preg_match("#\<input type\=\"hidden\" name\=\"fname\" value\=\"(.*?)\"\>#s", $response, $matches);
        $fname = $matches[1];

        //Post so we can bypass bot detection
        $response = $this->upost($url, array("op" => "download1",
                                             "usr_login" => "",
                                             "id" => $title_id,
                                             "fname" => $fname,
                                             "referer" => $url,
                                             "down_direct" => 1,
                                             "method_free" => "Free Download"));

        //Remove all whitespace for processing
        $response = preg_replace('/^\s+|\n|\r|\s+$/m', '', $response);

        //Get the player code (packed)
        if($c=preg_match_all("#\<div id\=\"player_code\"\>\<span id\='flvplayer'\>\</span\>\<script type\='text/javascript' src\='http\://sharerepo\.com/player/swfobject\.js'\>\</script\>\<script type\='text/javascript'\>(.*?)\</script\>#s", $response, $matches))
        {
            //Submit packed player code to unpacker
            $unpacked_code = $this->upost("http://jsunpack.jeek.org/", array("urlin" => $matches[1][0]));
            
            //Get the file URL from unpacked code
            if($c=preg_match_all("#s1\.addVariable\('file','(.*?)'\);#s", $unpacked_code, $matches))
            {
                return $matches[1][0];
            }
        }

        //We failed :(
        return null;
    }

    public function get_from_sockshare($url)
    {
        return $this->get_from_putlocker($url);
    }

    public function get_from_putlocker($url)
    {
        //Get content
        $response = $this->ufetch($url);

        //Get hash
        if($c=preg_match("#<input type=\"hidden\" value\=\"(.*?)\" name\=\"hash\"\>#s", $response, $matches))
        {
            //Post the hash so we can bypass bot detection
            $response = $this->upost($url, array("confirm" => "Continue as Free User",
                                                 "hash" => $matches[1]));

            //Get the file link
            if($c=preg_match("#\<a href\=\"/get_file\.php(.*?)&original\=1\"#s", $response, $matches))
            {
                return $this->get_true_url("http://" . parse_url($url, PHP_URL_HOST) . "/get_file.php" . $matches[1]);
            }
        }

        //We failed :(
        return null;
    }
}
?>