<?php
class Plugin_Initialize_Unittest extends Plugin_Initialize_Webapp
{
    public function initDb()
    {
        parent::initDb();
        try {
            $config = new Zend_Config_Ini(
                $this->_root."/test/Unit/testdata/test.ini"
            );
            if (!empty($config->database)) {
                Zend_Registry::set('datasource', $config->database); 
                $config = Zend_Registry::get('datasource');
                $db = Zend_Db::factory($config->main);
                Zend_Db_Table::setDefaultAdapter($db);
                Zend_Registry::set('db', $db);
                return;
            }
        } catch (Zend_Config_Exception $e) {
            echo $e->getMessage();
        }
        echo " Using installed db\n";
    }
}
