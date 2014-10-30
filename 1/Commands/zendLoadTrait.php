<?php
/**
 * Для работы команды с zend приложением
 */

namespace App\Commands;

use Zend_Application;
use Zend_Loader_Autoloader;
use Zend_Registry;
use Zend_Config_Ini;
use Zend_Cache;
use Zend_Db_Table_Abstract;
use Zend_Db_Table;
use Zend_View;
use CORE_System_DB;

trait zendLoadTrait
{

    protected function initZend()
    {
        // Define path to application directory
        defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../../application'));

        // Define application environment
        defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

        // Ensure library/ is on include_path
        set_include_path(implode(PATH_SEPARATOR, array(
            realpath(APPLICATION_PATH . '/../library'),
            realpath(APPLICATION_PATH . '/models/'),
            realpath(APPLICATION_PATH . '/../crons/')
        )));

        require_once APPLICATION_PATH . '/../library/global_vars.php';

        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);
        $model_autoloader = function ($class) {
            if (strstr($class, 'Model_')) {
                $class = str_replace('Model_', '', $class);
                require_once APPLICATION_PATH . "/./models/" . implode('/', explode('_', $class)) . '.php';
                return true;
            }
            return false;
        };
        $autoloader->pushAutoloader($model_autoloader);

        $registry = Zend_Registry::getInstance();

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $registry->set('config', $config);

        // Адаптер работы с БД
        //$db = Zend_Db::factory($config->db->adapter, $config->db->config->toArray());
        $db = new CORE_System_DB($config->db->config->toArray());
        $registry->set('db', $db);
        $db->getProfiler()->setEnabled(true);
        $db->query('SET NAMES utf8');


        // Кеш для хранения мета информации о БД
        $frontendOptions = array (
            'lifetime' => 86400*31,
            'automatic_serialization' => true );
        $backendOptions = array (
            'cache_dir' => APPLICATION_PATH . '/cache/dbschema',
            'hashed_directory_perm' => 0777 );
        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
        // Set db cache
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
        Zend_Db_Table::setDefaultAdapter($db);

        Zend_Registry::set('View', new Zend_View(array('basePath' => APPLICATION_PATH . '/views/')));

    }
}
