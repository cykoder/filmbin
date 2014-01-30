<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Titles_model extends CI_Model
{
    public function get_latest_entries($limit=10, $type=null)
    {
    	//Limit to 100 at a time
    	if($limit > 100) $limit = 100;

    	//Check if we have a type
    	if($type != null) $this->db->where("type", $type);

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

        //Check if it exists
        if($query->num_rows() > 0)
        {
	        //Check if result is array
	        $result = $query->result();
	        if(is_array($result))
	        {
	        	$result = $result[0];
	        }

	        //Return the result
	        return $this->array_to_object($result);
	    }
	    else //Can't find
	    {
	    	return null;
	    }
    }

    public function get($id)
    {
    	return $this->get_by_field("id", $id);
    }

    public function add($data)
    {
    	//Validate the data
    	$data = $this->validate_data($data);

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

    public function delete($id)
    {
    	//Delete, not much else to do here
    	$this->db->delete("titles", array("id" => $id)); 
    }

    public function update($id, $data)
    {
    	//Validate the data
    	$data = $this->validate_data($data);

    	//Set record to update
    	$this->db->where("id", $id);

    	//Update the database
    	$this->db->update("titles", $data);

		//Return the new title data
		return $this->get($id);
    }

    public function array_to_object($array)
    {
    	//Check if even an array
    	if(is_array($array))
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
		else //Not even an array, just return it back
		{
			return $array;
		}
    }

    private function validate_data($data)
    {
    	//Make sure these are integers!
		if(isset($data["release_date"])) $data["release_date"] = intval($data["release_date"]);
		if(isset($data["runtime"])) $data["release_date"] = intval($data["runtime"]);

		//Return clean data
		return $data;
    }
}
?>