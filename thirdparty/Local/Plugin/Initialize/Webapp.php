<?php
/**
 * The plugin to initialize the Framework in application mode
 */
class Plugin_Initialize_Webapp extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var array the path architecture of this mode
     */
    protected $_path = array('CONTROLLER'=>'apps/controllers',
                             'MODEL'=>'apps/models',
                             'LAYOUT'=> 'apps/views/layouts',
                             'VIEW'=>'apps/views');

    /**
     * @var Zend_Controller_Front
     */
    protected $_front;

    /**
     * @var string Path to application root
     */
    protected $_root;

    /**
     * Constructor
     *
     * Initialize environment, root path, and configuration.
     *
     * @param  string|null $root
     * @return void
     */
    public function __construct($root = null)
    {
        if (null === $root) {
            $root = realpath(dirname(__FILE__) . '/../../../../');
        }
        $this->_root = $root;
        $this->_front = Zend_Controller_Front::getInstance();
    }

    /**
     * Route startup
     *
     * @return void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        $this->_front->setControllerDirectory(array(
            'default'=>"{$this->_root}/{$this->_path['CONTROLLER']}"
            )
        );
        //This should be initialized first for Error handling
        $this->initControllers();
        $this->initPlugins();
        $this->initDb();
        $this->initHelpers();
        $this->initView();
        $this->initRouters();
    }

    /**
     * Initialize customized helpers
     */
    public function initHelpers()
    {
        //Customized helpers
        Zend_Controller_Action_HelperBroker::addPath($this->_root . '/library/local/Sws/Helper',
            'Local_Sws_Helper');
    }

    /**
     * Initialize database
     */
    public function initDb()
    {
        if (!Config_Sws::isInstalled()) {
            throw new Sws_Exception('Database setting missing! Is the application properly installed?');
        }
        $config = Zend_Registry::get('datasource');
        $db = Zend_Db::factory($config->main);
        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);
    }

    public function initView()
    {
        $options = array(
            'layout'     => 'default',
            'layoutPath' => "{$this->_root}/{$this->_path['LAYOUT']}",
            'contentKey' => 'CONTENT'           // ignored when MVC not used
        );
        Zend_Layout::startMvc($options)->setViewSuffix('tpl');
        // VIEW SETUP - Initialize properties of the view object
        // The Zend_View component is used for rendering views. Here, we grab a "global"
        // view instance from the layout object, and specify the doctype we wish to
        // use -- in this case, HTML4 Strict.
        $view = Zend_Layout::getMvcInstance()->getView();
        $view->setHelperPath($this->_root . '/library/local/View/Helper', 'View_Helper');
        $view->doctype('HTML4_STRICT');

        $viewRender = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRender->setViewSuffix('tpl')->setNeverRender();
        Zend_Controller_Action_HelperBroker::addHelper($viewRender);
    }

    public function initPlugins()
    {
        // The installer has its own error handler which is registered here:
        $this->_front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(
            array(
                'module' => 'default',
                'controller' => 'Error',
                'action' => 'error'
                )
            ));
    }


    /**
     * Initialize the Controller
     *
     */
    public function initControllers()
    {
        // This configuration option tells Zend_Date to use the standard PHP date format
        // instead of standard ISO format. This is convenient for interfacing Zend_Date
        // with legacy PHP code.
        Zend_Date::setOptions(array('format_type' => 'php'));
    }

    /**
     * Initialize the routers
     *
     * Using the default router
     */
    public function initRouters()
    {
    }

}
