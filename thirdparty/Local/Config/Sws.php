<?php
/**
 * Copyright (c) 2008 Endeavor Security, Inc.
 *
 *
 * @author    Jim Chen <xhorse@users.sourceforge.net>
 * @copyright (c) Endeavor Security, Inc. 2008 
 * @version   $Id$
 *
 */

class Config_Sws
{
    /**
     The section name of system wide configuration
     */ 
    const PATH_CONFIG = 'apps/config/';

    const SYS_CONFIG = 'sys.ini';
    const LDAP_CONFIG = 'ldap.ini';
    const INSTALL_CONFIG = 'install.ini';
    const ERROR_LOG = 'error.log';
    const FORMCONFIGFILE  = 'form.conf';

    const TEST_MODE = 'test';
    const SYS_SECT = 'sys_sect';
    
    const ERR_DETAIL= 2;
    const ERR_TRACE = 1;

    /**
     * Singleton instance
     *
     * Marked only as protected to allow extension of the class. To extend,
     * simply override {@link getInstance()}.
     *
     * @var Config_Fisma
     */
    protected static $_instance = null;

    /**
     * Indicates whether the application is in debug mode or not
     */
    protected static $_debug = false;
    
    /**
     * Log instance to record fatal error message
     *
     */
    protected $_log = null;

    /** 
     * The root path of the installed application
     */
    protected static $_root = null;
    
    
    protected static $_isFresh = true;

    /**
     * Translate instance 
     */
    protected $_trans = null;
    
    /**
     * Constructor
     *
     * Instantiate using {@link getInstance()}; System wide config is a singleton
     * object.
     *
     * @return void
     */

    private function __construct($root=null)
    {
        if (isset($root) && is_dir($root)) {
            self::$_root = $root;
        } else {
            self::$_root = realpath(dirname(__FILE__) . '/../../../');
        }
        $this->initSetting();
    }
    
    /**
     * Application setting initialization
     *
     * Read settings from the ini file and make them effective
     *
     * @return void
     */
    public function initSetting()
    {
        $path['lib'] = $this->getPath('library');
        $path[] = "{$path['lib']}/local";
        $path[] = "{$path['lib']}/pear";
        $path[] = $this->getPath() . "/apps/models";
        $path[] = get_include_path();
        set_include_path(implode(PATH_SEPARATOR, $path));
        require_once 'Zend/Loader.php';
        Zend_Loader::registerAutoload();
        Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('SWS_S'));
        $sysfile = self::$_root."/" . self::PATH_CONFIG . self::SYS_CONFIG;
        try {
            $config = new Zend_Config_Ini($sysfile);
            self::addSysConfig($config);

        } catch(Zend_Config_Exception $e) {
            //using default configuration
            $config = new Zend_Config(array());
        }
        if (!empty($config->debug)) {
            if ($config->debug->level > 0) {
                self::$_debug = true;
                error_reporting(E_ALL);
                ini_set('display_errors', 1);
                foreach ($config->debug->xdebug as $k => $v) {
                    if ($k == 'start_trace') {
                        if (1 == $v && function_exists('xdebug_start_trace')) {
                            xdebug_start_trace();
                        }
                    } else {
                        @ini_set('xdebug.' . $k, $v);
                    }
                }
            }
        }
    }
    /**
     * Enforce singleton; disallow cloning 
     * 
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Singleton instance
     *
     * @return Config_Fisma
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function bootstrap($mode=null)
    {
        $frontController = Zend_Controller_Front::getInstance();

        if ($mode == self::TEST_MODE) {
            $initPlugin = new Plugin_Initialize_Unittest(self::$_root);
        } else {
            if (self::isInstalled()) {
                $initPlugin = new Plugin_Initialize_Webapp(self::$_root);
                $config = Config_Sws::readSysConfig('general');
                $frontController->throwExceptions('1'===$config->throw_exception);
            } else {
                $initPlugin = new Plugin_Initialize_Install(self::$_root);
            }
        }
        $frontController->registerPlugin($initPlugin);
    }
    
    /** 
     * bootstrap the unit test mode
     */
    public function unitBootstrap()
    {
        $this->bootstrap(self::TEST_MODE);
    }

    /**
     * debug() - Returns true if the application is in debug mode, false otherwise
     *
     * @return boolean
     */
    static function debug() {
        return self::$_debug;
    }

    /**
     * Initialize the log instance
     *
     * As the log requires the authente information, the log should be only initialized 
     * after the successfully login.
     *
     * @return Zend_Log
     */
    public function getLogInstance()
    {
        if (is_null($this->_log)) {
            $write = new Zend_Log_Writer_Stream(LOG . '/' . self::ERROR_LOG);
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $me = $auth->getIdentity();
                $format = '%timestamp% %priorityName% (%priority%): %message% by ' .
                    "$me->userName($me->userID) from {$_SERVER['REMOTE_ADDR']}" . PHP_EOL;
            } else {
                $format = '%timestamp% %priorityName% (%priority%): %message% by ' .
                    "{$_SERVER['REMOTE_ADDR']}" . PHP_EOL;
            }
            $formatter = new Zend_Log_Formatter_Simple($format);
            $write->setFormatter($formatter);
            $this->_log = new Zend_Log($write);
        }
        return $this->_log;
    }

    /** 
        Exam the Acl of the existing logon user to decide permission or denial.

        @param $resource resources
        @param $action actions
        @return bool permit or not
    */
    function isAllow($resource, $action)
    {
        $auth = Zend_Auth::getInstance();
        $me = $auth->getIdentity();
        if ( $me->account == "root" ) {
            return true;
        }
        $roleArray = &$me->roleArray;
        $acl = Zend_Registry::get('acl');
        try{
            foreach ($roleArray as $role) {
                if ( true == $acl->isAllowed($role, $resource, $action) ) {
                    return true;
                }
            }
        } catch(Zend_Acl_Exception $e){
            /// @todo acl log information
        }
        return false;
    }

    /** 
        Read configurations of any sections.
        This function manages the storage, the cache, lazy initializing issue.
        
        @param $key string key name
        @param $is_fresh boolean to read from persisten storage or not.
        @return string configuration value.
     */
    public static function readSysConfig($key)
    {
        assert( !empty($key) );
        if( !Zend_Registry::isRegistered(self::SYS_SECT) || self::$_isFresh ){
            require_once( MODELS . DS . 'config.php' );
            //At the very beginning of the chaoes, we don't set the default db instance
            $db = Zend_Db::factory(Zend_Registry::get('datasource')->main);
            $m = new config($db);
            $pairs = $m->fetchAll();
            $configs = array();
            foreach( $pairs as $v ) {
                $configs[$v->key] = $v->value;
            }
            //Zend_Registry::set(self::SYS_SECT, new Zend_Config($configs) );
            self::addSysConfig(new Zend_Config($configs));
            self::$_isFresh = false;
        }
        if( !isset(Zend_Registry::get(self::SYS_SECT)->$key) ){
            throw new Sws_Exception("$key does not exist in system configuration");
        }
        return Zend_Registry::get(self::SYS_SECT)->$key;
    }
    
     /**
    * use Registry SYSCONFIG to merge other config
    * @param object @config
    * @return Zend_Registry
    */
    public static function addSysConfig($config)
    {
        if (Zend_Registry::isRegistered(self::SYS_SECT)) {
            $sysconfig = Zend_Registry::get(self::SYS_SECT);
            $sysconfig = new Zend_Config($sysconfig->toArray(), $allowModifications = true);
            $sysconfig->merge($config);
            Zend_Registry::set(self::SYS_SECT, $sysconfig);
        } else {
            Zend_Registry::set(self::SYS_SECT, $config);
        }
    }
        
    /**
     * To determind if the application has been properly installed.
     * 
     * @return bool 
     */
    public static function isInstalled()
    {
        $reg = Zend_Registry::getInstance();
        if ( $reg->isRegistered('datasource') ) {
            return true;
        } 

        try {
            $config = new Zend_Config_Ini(self::$_root."/" . 
                self::PATH_CONFIG . self::INSTALL_CONFIG);
            if (!empty($config->database)) {
                Zend_Registry::set('datasource', $config->database); 
                return true;
            }
        } catch (Zend_Config $e) {
            //logging
        }
        return false;
    }
    
    /**
     * Get real paths of the installed application
     *
     * @param string $part the component of the path
     * @return string the path
     */ 
    public function getPath($part='root')
    {
        $ret = self::$_root;
        if ('library'== $part) {
            $ret .= '/library';
        } elseif ('data' == $part) {
            $ret .= '/data';
        } elseif ('log' == $part) {
            $ret .= '/data/log';
        } elseif ('lang' == $part) {
            $ret .= '/lang';
        } elseif ('application' == $part) {
            $ret .= '/apps';
        } elseif ('config' == $part) {
            $ret .= '/apps/config';
        } elseif ('tmp' == $part) {
            $ret .= '/data/tmp';
        } elseif ('root' != $part) {
            assert(false);
        }
        return $ret;
    }
                                        
    /**
     * loadForm() - Loads a specified form by looking in the standard forms
     * directory.
     *
     * @param string $formName The name of the form to load. This form should
     * exist inside the forms directory. (Do not include the '.form' file
     * extension.)
     * @return Zend_Form
     */
    public function loadForm($formName)
    {
        assert(isset($formName));
        // Load the form from a .form file
        $path = $this->getPath('config') . '/form';
        $config = new Zend_Config_Ini($path . "/{$formName}.form");
        $form = new Zend_Form($config->$formName);
        if (isset($config->action)) {
            foreach ($config->action as $name=>$spec) {
                $form->addElement($spec->type, $name, $spec->options);
            }
        }
        return $form;
    }
    
    /**
     * Initialize a translate instance
     *
     * Replace or translate special strings on pages
     *
     * @return Zend_Translate
     */
    public function getTranslate()
    {
         if (null == $this->_trans) {
            $this->_trans = new Zend_Translate('gettext',
                $this->getPath('lang') . '/default.mo');
        }
        return $this->_trans;
    }

    /**
     * Get analysis db object
     *
     * @return Zend_Db object 
     */
    public function getAnalysisDb()
    {
        $dbConfig = array();
        $dbKey = array('host' => 'adbhost',
                       'port' => 'adbport',
                       'username' => 'adbuser',
                       'password' => 'adbpass',
                       'dbname' => 'adbname');
        foreach ($dbKey as $k => $v) {
            $dbConfig[$k] = self::readSysConfig($v);
        }
        return Zend_Db::factory('mysqli', $dbConfig);  
    }

}
