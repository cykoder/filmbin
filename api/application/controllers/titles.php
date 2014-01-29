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
        api/titles/:id
        Parameters: id (optional)
        Access level: 1
    */
    public function index_get()
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
            $entries = $this->titles_model->get_latest_entries();
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
            //Intval!
            $data["release_date"] = intval($data["release_date"]);
            $data["runtime"] = intval($data["runtime"]);

            //Insert into the database, get the ID
            $title = $this->titles_model->add($data);
            
            //Did it work?!
            if($title != null)
            {
                //Success! return the title object
                $this->response($title, 200);
            }
            else
            {
                //Something went wrong, return an error
                $this->response(array("error" => "Unable to add title", "data" => $data), 400);
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
        print_r($this->put('title'));
	}
    
    /*  
        api/titles/
        Parameters: id (int)
        Access level: 3
    */
    public function index_delete()
    {
        //$this->some_model->deletesomething( $this->get('id') );
        $message = array('id' => $this->get('id'), 'message' => 'DELETED!');
        
        $this->response($message, 200); // 200 being the HTTP response code
    }
}