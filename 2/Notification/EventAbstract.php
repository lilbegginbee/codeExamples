<?php
/**
 * Абстракция для обработчика события
 * User: timur
 * Date: 9/29/14
 * Time: 2:47 PM
 */

abstract class AG_lib_Notification_EventAbstract
{
    /**
     * @var AG_lib_Observe_Event
     */
    protected $observerEvent= null;

    /**
     * Данные от источника события
     * @var mixed
     */
    protected $obj;

    /**
     * Если не null, то обработчик события использует его
     * и не выясняет всех получателей
     * по заранее установленой логике
     * @var array of Model_User
     */
    protected $recipients = null;

    /**
     * Установка ссылки на возникшее событие
     * @param $event AG_lib_Observe_Event
     */
    protected function setObserverEvent($event)
    {
        $this->observerEvent = $event;
    }

    protected function setObj($obj)
    {
        $this->obj = $obj;
    }

    /**
     * Установка получателей события
     * @param $recipients
     */
    protected function setRecipients($recipients)
    {
        $mUsers = new Model_User();
        if (is_numeric($recipients) && $mUsers->loadById($recipients)) {
            $this->recipients = [$mUsers];
        } elseif (is_array($recipients)) {
            $this->recipients = $recipients;
        }
    }

    /**
     * Предуказаны ли получатели
     * @return bool
     */
    protected function hasRecipients()
    {
        return is_null($this->recipients)?false:true;
    }

    /**
     * Добавляет в список получателей
     * @param $recipient Model_User
     */
    protected function addRecipient($recipient)
    {
        $this->recipients[] = $recipient;
    }

    /**
     * @return AG_lib_Observe_Event
     */
    protected function getObserverEvent()
    {
        return $this->observerEvent;
    }

    /**
     * Экшин push
     * @param $obj
     * @return mixed
     */
    abstract public function push();

    /**
     * Экшин email
     * @param $obj
     * @return mixed
     */
    abstract public function email();

    /**
     * Экшин лента событий (+ баббл-не-баббл)
     * @param $obj
     * @return mixed
     */
    abstract public function feed($isBubble = false);

    /**
     * Экшин алёрта, всплывающего сообщения
     * @param $obj
     * @return mixed
     */
    abstract public function alert();

    /**
     * Экшин кросспоста в фейсбук
     */
    abstract public function postToFB();

    /**
     * Экшин кросспоста в ВК
     */
    abstract public function postToVK();

    /**
     * Запуск события,
     * т.е. проверка какие экшины нужно запустить и запустить их
     * @param AG_lib_Observe_Event $event
     * @param $params array
     * @param $obj mixed
     * @param $recipients mixed
     * @return mixed
     */
    abstract public function run(AG_lib_Observe_Event $event, $params, $obj, $recipients = null);

    /**
     * Получатели уведомлений
     * @return mixed
     */
    abstract public function getRecipients();
}

