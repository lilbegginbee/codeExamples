<?php

class AG_lib_Observe_Observer
{
    static private $_instance = null;
    static private $observers = array();

    private function __construct()
    {

    }

    /**
     * @return AG_lib_Observe_Observer|null
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new AG_lib_Observe_Observer();
        }
        return self::$_instance;
    }

    public function notifyObservers (AG_lib_Observe_Event $event, $creator)
    {
        foreach (self::$observers as $obj) {
            $obj->notify($event, $creator);
        }
    }

    /**
     * @param AG_lib_Observe_Observable $obj
     * @return AG_lib_Observe_Observer
     */
    public function registerObserver(AG_lib_Observe_Observable $obj)
    {
        self::$observers[] = $obj;
        return self::$_instance;
    }
}
