<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';

class Search extends REST_Controller
{
    /*
        api/search/titles/:q
        Parameters: q (string)
                    count (optional, int)
                    order (optional, csv string)
                    fields (optional, csv string)
        Access level: 1
    */
    public function titles_get()
    {
        //Check if we even have a query
        $search_term = $this->get("q");
        if(isset($search_term) && !empty($search_term))
        {
            //Search
            $results = $this->do_search("titles", $search_term, "title");

            //Check if we have results
            if($results != null && count($results) > 0)
            {
                //Parse results into objects
                foreach($results as &$title)
                {
                    $title = $this->titles_model->array_to_object($title);
                }

                //Output results
                $this->response($results, 200);
            }
            else //No results
            {
                $this->response(array("error" => "no results"), 404);
            }
        }
        else //Bad request!
        {
            $this->response(array("error" => "no query"), 400);
        }
    }

    /*
        Extracts fields/order/limit/offset and searches
        NOT a part of the actual API
    */
    private function do_search($table, $search_term, $default_fields)
    {
        //Get fields
        $fields = str_getcsv($this->get("fields") ? $this->get("fields") :
                                 $default_fields); //Default fields

        //Get order
        $order = str_getcsv($this->get("order") ? $this->get("order") :
                                 "-id"); //Default ordering

        //Get limit and offset
        $limit = $this->get("count") ? $this->get("count") : 10;
        $offset = $this->get("offset") ? $this->get("offset") : 0;

        //Do the actual search
        return $this->search_model->search($table, $search_term, $fields, $order, $limit, $offset);
    }
}