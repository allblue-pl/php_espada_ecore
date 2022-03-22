<?php namespace EC\ABData;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC,
    EC\Database;

class TDeletedRows extends _TDeletedRows
{

    public function __construct(EC\MDatabase $db)
    {
        parent::__construct($db, 'abd_dlr');

        $this->addColumns_Ref(new TDeviceRows($db), [
            'DeviceId' => [ 'abd_dr.DeviceId', 'DeviceId' ],
        ]);

        $this->setJoin(
            ' INNER JOIN _ABData_DeviceRows AS abd_dr' .
            ' ON abd_dr.TableId = abd_dlr.TableId AND abd_dr.RowId = abd_dlr.RowId'
        );
    }

}
