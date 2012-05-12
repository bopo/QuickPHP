<?php
/*
 *	$Id: PhpcsTask.php 309 2008-08-18 02:03:26Z jimchen $
 */

require_once 'phing/Task.php';

/**
 * A PHP code sniffer
 *
 */
class PhpcsTask extends Task
{

	protected $file;					// the source file (from xml attribute)
	protected $errorProperty;
	protected $filesets      = array(); // all fileset objects assigned to this task
	protected $standard      = 'Squiz';
	protected $logfile       = false;
	protected $haltOnFailure = false;
	protected $hasErrors     = false;

	private $badFiles        = array();
	private $loghandle       = false;

	/**
	 * The log files
	 * @param string $aValue
	 */
	public function setLogfile($aValue) 
	{
		$this->logfile = $aValue;
	}
	/**
	 * The standard option
	 * @param string $aValue
	 */
	public function setStandard($aValue) 
	{
		$this->standard = $aValue;
	}
	/**
	 * The haltonfailure property
	 * @param boolean $aValue
	 */
	public function setHaltOnFailure($aValue) 
	{
		$this->haltOnFailure = $aValue;
	}

	/**
	 * File to be performed syntax check on
	 * @param PhingFile $file
	 */
	public function setFile(PhingFile $file) 
	{
		$this->file = $file;
	}

	/**
	 * Set an property name in which to put any errors.
	 * @param string $propname
	 */
	public function setErrorproperty($propname)
	{
		$this->errorProperty = $propname;
	}

	/**
	 * Nested creator, creates a FileSet for this task
	 *
	 * @return FileSet The created fileset object
	 */
	function createFileSet() 
	{
		$num = array_push($this->filesets, new FileSet());
		return $this->filesets[$num-1];
	}

	/**
	 * Execute validate check against PhingFile or a FileSet
	 */
	public function main() 
	{
		if(!isset($this->file) and count($this->filesets) == 0) 
		{
			throw new BuildException("Missing either a nested fileset or attribute 'file' set");
		}
        
        if (!empty($this->logfile)) 
        {
            $this->loghandle = fopen($this->logfile, "a");
            
            if ( ! $this->loghandle ) 
            {
			    throw new BuildException("Cannot create the log file");
            }
        }

		if($this->file instanceof PhingFile) 
		{
			$this->validate($this->file->getPath());
		}
		else
		{ 
			// process filesets
			$project = $this->getProject();
			
			foreach($this->filesets as $fs) 
			{
				$ds    = $fs->getDirectoryScanner($project);
				$files = $ds->getIncludedFiles();
				$dir   = $fs->getDir($this->project)->getPath();
				
				foreach($files as $file) 
				{
					$this->validate($dir.DIRECTORY_SEPARATOR.$file);
				}
			}
		}

        if ($this->loghandle) 
        {
            fclose($this->loghandle);
        }

		if ($this->haltOnFailure && $this->hasErrors) 
		{
			throw new BuildException('Syntax error(s) in PHP files: '.implode(', ',$this->badFiles));
		}
	}

	/**
	 * Performs the actual syntax check
	 *
	 * @param string $file
	 * @return void
	 */
	protected function validate($file)
    {
		$command = "phpcs --standard=$this->standard ";

		if(file_exists($file)) 
		{
			if(is_readable($file)) 
			{
				$messages = array();
				exec($command.'"'.$file.'"', $messages);
			
				if(preg_match('/^FOUND *[1-9]/', $messages[3])) 
				{
                    if ($this->errorProperty) 
                    {
                        $this->project->setProperty($this->errorProperty, $messages[3]);
                    }
                    
                    if (empty($this->logfile)) 
                    {
                        $this->log("{$file}\n{$messages[3]}", Project::MSG_ERR);
                    } 
                    else 
                    {
                        $this->log("{$file}\n{$messages[3]}", Project::MSG_ERR);
                        fwrite($this->loghandle, implode("\n",$messages));
                    }
					
					$this->badFiles[] = $file;
					$this->hasErrors = true;

				} 
				else 
				{
					$this->log($file.': No errors detected', Project::MSG_INFO);
				}
			} 
			else 
			{
				throw new BuildException('Permission denied: '.$file);
			}
		} 
		else 
		{
			throw new BuildException('File not found: '.$file);
		}
	}
}