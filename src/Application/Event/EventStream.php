<?php


namespace Webravo\Application\Event;


class EventStream
{
    private $_aggregate_type;
    private $_aggregate_id;


    public function getAggregateType()
    {
        return $this->_aggregate_type;
    }

    public function getAggregateId()
    {
        return $this->_aggregate_id;
    }
}