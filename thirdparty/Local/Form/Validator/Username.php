<?php
/**
* SWS
*
* Endeavor Security Copyright
*
* @version $Id$
*/

class Form_Validator_Username extends Zend_Validate_Abstract
{
    const IS_EXISTED = 'is_exist';

    protected $_messageTemplates = array(self::IS_EXISTED=>"This username has existed");

    public function isValid($username)
    {
        $this->_setValue($username);
        $userMod = new User;

        if ($userMod->fetchRow("userName = '{$username}'"))
        {
            $this->_error(self::IS_EXISTED);
            return false;
        }

        return true;
    }
}
