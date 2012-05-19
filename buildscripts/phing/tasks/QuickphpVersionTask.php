<?php
require_once 'phing/Task.php';
include_once 'phing/tasks/system/PropertyTask.php';

class QuickphpVersionTask extends PropertyTask
{
    /**
    * Execute lint check against PhingFile or a FileSet
    */
    public function main()
    {
        $this->addProperty('quickphp.version',  $this->getVersion());
        $this->addProperty('quickphp.revision', $this->getRevision());

        if(substr(PHP_OS, 0, 3) == 'WIN')
        {
            $this->addProperty('quickphp.winbuild', 'true');
        }
        else
        {
            $this->addProperty('quickphp.winbuild', 'false');
        }
    }

    /**
     * @return string Quickphp version
     */
    private function getVersion()
    {
        $coreFile = dirname(__FILE__).'/../../../framework/QuickPHP.php';

        if(is_file($coreFile))
        {
            $contents = file_get_contents($coreFile);
            $matches  = array();

            if(preg_match('/const VERSION.*?= \'(.*?)\'/ms', $contents, $matches) > 0)
            {
                return $matches[1];
            }
        }

        return 'unknown';
    }

    /**
     * @return string QuickPHP SVN revision
     */
    private function getRevision()
    {
        $svnPath = dirname(__FILE__) . '/../../../.svn';

        if(is_file($svnPath . '/all-wcprops'))
        {
            $propFile = $svnPath . '/all-wcprops';
        }
        else if(is_file($svnPath . '/dir-wcprops'))
        {
            $propFile = $svnPath . '/dir-wcprops';
        }
        else if(is_file($svnPath . '/entries'))
        {
            $propFile = $svnPath . '/entries';
        }
        else
        {
            return 'unknown';
        }

        $contents = file_get_contents($propFile);

        if(preg_match('/\\/svn\\/\\!svn\\/ver\\/(\d+)\\//ms', $contents, $matches) > 0)
        {
            return $matches[1];
        }
        else if($contents = file($propFile))
        {
            return rtrim($contents[3], PHP_EOL);
        }
        else
        {
            return 'unknown';
        }
    }
}
