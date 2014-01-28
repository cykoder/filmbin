<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Titles_model extends CI_Model
{
    function get_latest_entries($limit=10)
    {
        $query = $this->db->get("titles", $limit, 0);
        return $query->result();
    }
}
?>