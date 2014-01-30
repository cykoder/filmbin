<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Search_model extends CI_Model
{
    public function select($table, $query, $fields, $select = "*")
    {
        //Select fields
        $this->db->select($select);

        //From the table
        $this->db->from($table);

        //Parse fields
        for($i=0; $i < count($fields); $i++)
        {
            if($i == 0) //First field only needs "LIKE"
            {
                $this->db->like($fields[$i], $query);
            }
            else //Other fields needs "OR LIKE"
            {
                $this->db->or_like($fields[$i], $query);
            }
        }
    }

    public function order($fields)
    {
        //Check if there are multiple fields
        if(is_array($fields))
        {
            //Parse fields
            for($i=0; $i < count($fields); $i++)
            {
                //Get what to sort by based on the first character
                //(asc, desc, random)
                $first_character = substr($fields[$i], 0, 1);
                switch($first_character)
                {
                    case "-":
                        $sort_by = "desc";
                        $fields[$i] = substr($fields[$i], 1); //Remove first character
                    break;

                    case "~":
                        $sort_by = "random";
                        $fields[$i] = substr($fields[$i], 1); //Remove first character
                    break;

                    default:
                        $sort_by = "asc";
                    break;
                }

                //Order by, bitches!
                $this->db->order_by($fields[$i], $sort_by);
            }
        }
        else //Nope, single, reparse
        {
            return $this->order(array($fields));
        }
    }

    public function search($table, $query, $fields, $order, $limit = 10, $offset = 0, $select = "*")
    {
        //Select
        $this->select($table, $query, $fields, $select = "*");

        //Order
        $this->order($order);

        //Limit
        $this->db->limit($limit, $offset);

        //Execute!
        $query = $this->db->get();

        //Check if we have found anything
        if($query != null)
        {
            //Return the result as an array
            return $query->result_array();
        }
        else //Found nothing
        {
            return null;
        }
    }
}
?>