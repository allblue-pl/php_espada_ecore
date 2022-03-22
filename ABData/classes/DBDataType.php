<!-- <?php namespace EC\Sys;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class DBDataType
{

    public function __construct(EC\MDatabase $db)
    {
        $this->db = $db;
    }

    public function get($args)
    {
        $type = null;
        $where = [];
        $orderBy = [];
        $limit = [ 0, null ];

        if ($type === 'row') {
            $type = 'select';
            $limit[1] = 1;
        } else if ($type === 'select')
            $type = 'select';

        return $this->db->select_Where();
    }

    public function set()
    {
        
    }

} -->