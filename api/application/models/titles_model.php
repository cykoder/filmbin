<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Titles_model extends CI_Model
{
    public function get_latest_entries($limit=10)
    {
    	//Order by ID, descending
    	$this->db->order_by("id", "desc");

    	//Get titles
        $query = $this->db->get("titles", $limit, 0);

        //Return the result
        return $query->result();
    }

    public function get_by_field($field, $value)
    {
    	//Get title based on field
        $query = $this->db->get_where("titles", array($field => $value), 1, 0);

        //Return the result
        return $query->result();
    }

    public function get($id)
    {
    	return $this->get_by_field("id", $id);
    }

    public function add($data)
    {
    	//Insert into the database
		if(@$this->db->insert("titles", $data))
		{
			//Set the insert ID
			$data["id"] = $this->db->insert_id();

			//Return the data as a title object
			return $this->array_to_object($data);
		}
		else //Something went wrong inserting, return null
		{
			return null;
		}
    }

    private function array_to_object($array)
    {
    	//Create a standard class
		$object = new stdClass();

		//Loop through key/value pairs and set
		foreach ($array as $key => $value)
		{
		    $object->$key = $value;
		}

		//Return the object
		return $object;
    }
}
?>