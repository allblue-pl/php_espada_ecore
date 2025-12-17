<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E;
use EC\Database\MDatabase;

class TDeletedRows_ByDevice extends TDeletedRows {

    public function __construct(MDatabase $db) {
        parent::__construct($db);

        $this->addColumns_Ref(new TDeviceRows($db), [
            'DeviceId' => [ 'abd_dr.DeviceId', 'DeviceId' ],
        ]);

        $this->setJoin(
            ' INNER JOIN ABData_DeviceRows AS abd_dr' .
            ' ON abd_dr.TableId = abd_dlr.TableId AND abd_dr.RowId = abd_dlr.RowId'
        );
    }

}
