<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';

class Titles extends REST_Controller
{
    /*
      api/titles/show/:id
      Parameters: id
    */
    public function index_get()
    {
        if(!$this->get('id'))
        {
            $entries = $this->titles_model->get_latest_entries();
            if (count($entries) > 0)
            {
                $this->response($entries, 200);
            }
            else
            {
                $this->response(NULL, 400);
            }
        }
        else
        {
            $query = $this->db->get_where("titles", array('id' => $this->get('id')), 1, 0);

            if ($query->num_rows() > 0)
            {
                $this->response($query->row(), 200);
            }
            else
            {
                $this->response(array('error' => 'User could not be found'), 404);
            }
        }
    }
    
	public function index_post()
	{
        //$this->some_model->updateUser( $this->get('id') );
        $message = array('id' => $this->get('id'), 'name' => $this->post('name'), 'email' => $this->post('email'), 'message' => 'ADDED!');
        
        $this->response($message, 200); // 200 being the HTTP response code
	}


	public function index_put()
	{
        print_r($this->put('foo'));
	}
    
    public function index_delete()
    {
        //$this->some_model->deletesomething( $this->get('id') );
        $message = array('id' => $this->get('id'), 'message' => 'DELETED!');
        
        $this->response($message, 200); // 200 being the HTTP response code
    }
}