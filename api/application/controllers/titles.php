<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';

class Titles extends REST_Controller
{
    /*
        Access levels
    */
    protected $methods = array(
        'index_post' => array('level' => 2),
        'index_put' => array('level' => 2),
        'index_delete' => array('level' => 3)
    );

    /*
        api/titles/:id,:type
        Parameters: id (optional)
                    type (optional) (movie, series)
        Access level: 1
    */
    public function index_get()
    {
        //Check if we're requesting a certain type
        if($this->get("type") != null)
        {
            if($this->get("type") == "movie")
            {
                //Same as api/titles/movies
                $this->movies_get();
            }
            else if($this->get("type") == "series")
            {
                //Same as api/titles/series
                $this->series_get();
            }
        }
        else
        {
            //Check if we're requesting a single title
            if($this->get('id'))
            {
                //Get title
                $result = $this->titles_model->get($this->get("id"));

                //Check if title exists
                if ($result != null)
                {
                    $this->response($result[0], 200);
                }
                else //Title doesn't exist
                {
                    $this->response(array("error" => "Title not found"), 404);
                }
            }
            else //Requesting a list of latest entries
            {
                //Set a limit
                $limit = $this->get("count") ? $this->get("count") : 10;

                //Get the latest entries
                $entries = $this->titles_model->get_latest_entries($limit);
                if (count($entries) > 0)
                {
                    $this->response($entries, 200);
                }
                else //Some kind of error, hide!
                {
                    $this->response(null, 400);
                }
            }
        }
    }

    /*  
        api/titles/
        Parameters: title (string)
                    rated (optional, string)
                    release_date (unix timestamp)
                    runtime (int)
                    genre (string)
                    director (string)
                    actors (string)
                    plot (string)
                    poster (url string)
                    rating (optional, decimal)
                    type (movie, series, episode)
        Access level: 2
    */
	public function index_post()
	{
        //Set data into array
        $data = $this->post();

        //Check all required parameters are there
        if(isset($data["title"]) &&
           isset($data["release_date"]) &&
           isset($data["runtime"]) &&
           isset($data["genre"]) &&
           isset($data["director"]) &&
           isset($data["actors"]) &&
           isset($data["plot"]) &&
           isset($data["poster"]) &&
           isset($data["type"]) && ($data["type"] == "movie" || $data["type"] == "series" || $data["type"] == "episode"))
        {
            //Insert into the database, get the ID
            $title = $this->titles_model->add($data);
            
            //Did it work?!
            if($title != null)
            {
                //Success! return the title object
                $this->response($title, 201);
            }
            else
            {
                //Something went wrong, return an error
                $this->response(array("error" => "Unable to add title, possibly already added", "data" => $data), 400);
            }
        }
        else //Bad request!
        {
            $this->response(null, 400);
        }
	}

    /*  
        api/titles/
        Parameters: id (int)
                    title (optional, string)
                    rated (optional, string)
                    release_date (optional, unix timestamp)
                    runtime (optional, int)
                    genre (optional, string)
                    director (optional, string)
                    actors (optional, string)
                    plot (optional, string)
                    poster (optional, string)
                    rating (optional, optional, decimal)
                    type (optional, string)
        Access level: 2
    */
	public function index_put()
	{
        //Check if we even have an ID
        if($this->get("id"))
        {
            //Set data into array
            $data = $this->put();

            //Update the title
            $title = $this->titles_model->update($this->get("id"), $data);
            
            //Did it work?!
            if($title != null)
            {
                //Success! return the title object
                $this->response($title, 200);
            }
            else
            {
                //Something went wrong, return an error
                $data['id'] = $this->get("id");
                $this->response(array("error" => "Unable to update title", "data" => $data), 400);
            }
        }
        else //Bad request!
        {
            $this->response(null, 400);
        }
	}
    
    /*  
        api/titles/
        Parameters: id (int)
        Access level: 3
    */
    public function index_delete()
    {
        //Check if we even have an ID
        if($this->get("id"))
        {
            //Delete the title
            $title = $this->titles_model->delete($this->get("id"));

            //Return an OK
            $this->response(array("id" => $this->get('id'), "message" => "deleted"), 200);
        }
        else //Bad request!
        {
            $this->response(null, 400);
        }
    }

    /*
        api/titles/movies
        Parameters:
        Access level: 1
    */
    public function movies_get()
    {
        //Set a limit
        $limit = $this->get("count") ? $this->get("count") : 10;

        //Get the latest movies
        $entries = $this->titles_model->get_latest_entries($limit, "movie");
        if (count($entries) > 0)
        {
            $this->response($entries, 200);
        }
        else //Some kind of error, hide!
        {
            $this->response(array("error" => "Unable to select latest movies"), 400);
        }
    }

    /*
        api/titles/series
        Parameters:
        Access level: 1
    */
    public function series_get()
    {
        //Set a limit
        $limit = $this->get("count") ? $this->get("count") : 10;

        //Get the latest series
        $entries = $this->titles_model->get_latest_entries($limit, "series");
        if (count($entries) > 0)
        {
            $this->response($entries, 200);
        }
        else //Some kind of error, hide!
        {
            $this->response(array("error" => "Unable to select latest series"), 400);
        }
    }
}