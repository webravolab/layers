<?php
namespace Webravo\Persistence\Eloquent\DataTable;

use Webravo\Persistence\Repository\AbstractDataTable;

use App\Events;

class EventDataTable extends AbstractDataTable {

    public function getByGuid($guid)
    {
        // TODO: Implement getByGuid() method.
        throw new \Exception('Unimplemented');
    }

    public function persist($event) {
        // Check parent class
        if (strpos(get_parent_class($event), 'GenericEvent')===false) {
            throw new \Exception('EventDataTable: parameter must be instance of DomainEventInterface');
        }

        // Build array data
        $data = $this->hydrator->Extract($event);

        // Create Eloquent object
        $o_events = Events::create($data);
    }
}