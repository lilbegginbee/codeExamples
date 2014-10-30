<?php

class AG_lib_Observe_Event
{

	private $_currentEventName = null;

    public function __construct($eventName)
    {
        if (!AG_lib_Observe_EventType::isValid($eventName)) {
            throw new Exception('Undefined Event for Observe');
        }
        $this->_currentEventName = $eventName;
    }

    public function getEventName()
    {
        return $this->_currentEventName;
    }
}
