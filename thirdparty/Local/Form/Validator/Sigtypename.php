<?php
/**
* SWS
*
* Endeavor Security Copyright
*
* @version $Id$
*/

class Form_Validator_Sigtypename extends Zend_Validate_Abstract
{
    const IS_EXISTED = 'is_exist';

    protected $_messageTemplates = array(self::IS_EXISTED=>"This type name has existed");

    public function isValid($name)
    {
        $this->_setValue($name);
        $typeMod = new SigType;

        if ($typeMod->fetchRow("sTypeName = '{$name}'"))
        {
            $this->_error(self::IS_EXISTED);
            return false;
        }

        return true;
    }
}
