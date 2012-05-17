<?php
class Plugin_Initialize_Install extends Plugin_Initialize_Webapp
{
    /**
     * @var array
     */
    protected $_path = array('CONTROLLER'=>'apps/modules/install/controllers',
                             'LAYOUT'=> 'apps/modules/install/views/layouts',
                             'MODEL'=>'apps/models',
                             'VIEW'=>'apps/modules/install/views');
    public function initDb()
    {//overload the parent initDb doing nothing here
    }

    public function initPlugins()
    {
        // The installer has its own error handler which is registered here:
        $this->_front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(
            array(
                'model' => 'install',
                'controller' => 'install',
                'action' => 'error'
                )
            ));
    }

    public function initRouters()
    {
        $router = $this->_front->getRouter();
        $route['install'] = new Zend_Controller_Router_Route_Regex (
                                    '([^/]*)/?(.*)$',
                                    array('module' => 'default','controller' => 'install'),
                                    array('action' => 1),
                                    '%1$s'
                                );
        $router->addRoute('default', $route['install']);
    }

    public function initControllers()
    {
        $this->_front->setControllerDirectory(array(
            'default'=>"{$this->_root}/{$this->_path['CONTROLLER']}"
            )
        );

        // This configuration option tells Zend_Date to use the standard PHP date format
        // instead of standard ISO format. This is convenient for interfacing Zend_Date
        // with legacy PHP code.
        Zend_Date::setOptions(array('format_type' => 'php'));
        $this->_front->throwExceptions(true);
    }
}
