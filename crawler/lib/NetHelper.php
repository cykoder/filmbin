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

class NetHelper
{
    private $curl_instance;

    public function get_true_url($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $header = "Location: ";
        $pos = strpos($response, $header);
        if($pos === false)
        {
            return false;
        }
        else
        {
            $pos += strlen($header);
            return substr($response, $pos, strpos($response, "\r\n", $pos)-$pos);
        }
    }

    public function ufetch($url)
    {
        //Set URL
        $this->set_url($url);

        //Get a response
        $response = curl_exec($this->curl_instance);
        if($response != null)
        {
            return $response;
        }
        else
        {
            throw new Exception("Unable to fetch URL: " . $url); 
        }
    }

    public function upost($url, $fields)
    {
        //Convert fields into a string
        $fields_string = "";
        foreach($fields as $key => $value)
        {
            $fields_string .= $key . '=' . urlencode($value) . '&';
        }

        //Trim the end
        rtrim($fields_string, '&');

        //Set URL
        $this->set_url($url);

        //Set post options
        curl_setopt($this->curl_instance, CURLOPT_POST, count($fields));
        curl_setopt($this->curl_instance, CURLOPT_POSTFIELDS, $fields_string);

        //Get a response
        $response = curl_exec($this->curl_instance);
        $this->recycle();
        if($response != null)
        {
            return $response;
        }
        else
        {
            throw new Exception("Unable to post to URL: " . $url); 
        }
    }

    public function recycle()
    {
        if(isset($this->curl_instance) &&
            $this->curl_instance != null)
        {
            //Close the curl instance
            curl_close($this->curl_instance);

            //Unset
            unset($this->curl_instance);
        }
    }

    protected function set_url($url)
    {       
        //Check if we have an instance or not
        if(!isset($this->curl_instance) ||
            $this->curl_instance == null)
        {
            //Create a CURL instance
            $this->curl_instance = curl_init();

            //Set some options
            curl_setopt($this->curl_instance, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->curl_instance, CURLOPT_VERBOSE, false);
            curl_setopt($this->curl_instance, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->curl_instance, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->curl_instance, CURLOPT_AUTOREFERER, 1);
            curl_setopt($this->curl_instance, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($this->curl_instance, CURLOPT_TIMEOUT, 120);
            curl_setopt($this->curl_instance, CURLOPT_MAXREDIRS, 2);
            curl_setopt($this->curl_instance, CURLOPT_ENCODING, "");
            curl_setopt($this->curl_instance, CURLOPT_USERAGENT, "FilmBin");
        }

        //Set URL
        curl_setopt($this->curl_instance, CURLOPT_URL, $url);
    }
}
?>