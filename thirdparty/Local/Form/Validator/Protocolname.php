<?php
/**
* SWS
*
* Endeavor Security Copyright
*
* @version $Id$
*/

class Form_Validator_Protocolname extends Zend_Validate_Abstract
{
    const IS_EXISTED = 'is_exist';

    protected $_messageTemplates = array(self::IS_EXISTED=>"This protocol name has existed");

    public function isValid($name)
    {
        $this->_setValue($name);
        $protocolMod = new Protocol;

        if ($protocolMod->fetchRow("name = '{$name}'"))
        {
            $this->_error(self::IS_EXISTED);
            return false;
        }

        return true;
    }
}
