<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';

class fb404 extends REST_Controller
{
    public function index_get()
    {
        $this->response(array('status' => false, 'error' => 'Unknown method.'), 404);
    }
}