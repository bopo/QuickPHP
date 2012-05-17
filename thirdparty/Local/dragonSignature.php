<?php
define("SAFE_MATCH_STR_LEN",23);
define("MAX_SHORTNAME_LEN",28);

function nameIsDup(&$db, $alarmID, $name)
{
	$qry = "select count(*) from DragonSignature where shortName = '" . $name . "' and alarmID <> " . $alarmID;
    $nDups= $db->fetchOne($qry);
	if ($nDups > 0)
		return TRUE;
	return FALSE;
}

function uniqueName($name, $uniq)
{
	$uniq = "" . $uniq;
	$bytesFree = 28 - strlen($name);
	if ($bytesFree < strlen($uniq)) {
		$bytesToRemove = strlen($uniq) - $bytesFree;
		$name = substr($name, 0, -$bytesToRemove);
	}
	return $name . $uniq;
}

function contains($s1, $s2)
{
	if (strstr($s1, $s2))
		return TRUE;
	if (strstr($s2, $s1))
		return TRUE;
	return FALSE;
}
function redundantComponent(&$db,$comp)
{
	$qry = "select nameComponent from FilteredNameComponents where nameComponent = '" . $comp . "'";
    $result = $db->fetchOne($qry);
	return $result;
}

function removeBestFitComponent($freeBytes, $components)
{
	$joinedLen = strlen(join($components, "_"));
	$removed = 0;

	for ($i = count($components) - 1; $i >= 0; $i--) {
		if ($joinedLen - $removed <= $freeBytes)
			return $components;
		$len = strlen($components[$i]) + 1;
		if ($len < 6)
			continue;
		$removed += $len;
		array_splice($components, $i, 1);
	}
	return $components;
}

function getSigEvGroup(&$db,$alarmID)
{
	$qry = "select EventGroup.EGName, EventGroup.shortName from EventGroup join Alarm on EventGroup.EGID = Alarm.egID where Alarm.alarmID = ".$alarmID;
    $db->query($qry);
    list($evgroup, $shortName) = $db->fetchRow();
    //var_dump("<br>".$evgroup.":".$shortName); //jim--debug
	if ($shortName)
		$evgroup = $shortName;
	return strtoupper($evgroup);
}

/** \brief Get protocol name from AlarmID and call 'http' with 'web'
 *  \param &$db    database object
 *  \param $alarmID
 *  \return string protocol name
 */
function getSigProto(&$db,$alarmID)
{
    $db->setPageSize(0,1);
	$qry = "select Protocol.name from AlarmProtocol join Alarm on AlarmProtocol.alarmID = Alarm.alarmID join Protocol on AlarmProtocol.protocolID = Protocol.protocolID where Alarm.alarmID = " . $alarmID ;
    $proto = $db->fetchOne($qry);
    $db->setPageSize(0,0);
    if(!$proto) return ;
	if ($proto == "http")
		$proto = "web";
	return $proto;
}

function getProduct(&$db,$alarmID)
{
    $cve = $db->fetchOne("select CVE from Alarm where alarmID = '" . $alarmID . "'");
	if (!$cve)
		return;
	if (substr($cve, 0, 4) != "CVE-")
		$cve = "CVE-" . $cve;
    $db->setPageSize(0,1);
	$qry = "select Vendor.name, Product.name from CVEProduct join Product on CVEProduct.productID = Product.productID join Vendor on Product.vendorId = Vendor.vendorId where CVEProduct.cveName = '$cve'";
    $db->query($qry);
    $product=array();
    $product = $db->fetchRow(MYSQLI_NUM);
    $db->setPageSize(0,0);
    return $product;
}


/** \brief limit the product's name to 7 charactors
 *  
 *  \param $product original name
 *  \return $abb    shorten name in string(<=7)
 */
function shortenProduct($product)
{
	if (strlen($product) <= 7)
		return $product;

	// try and form abbreviation
	$prodParts = split(" ", $product);
	if (count($prodParts) > 3) {
		$abb = "";
		foreach ($prodParts as $part) {
			if (ctype_alnum($part[0]))
				$abb .= $part[0];
		}
		return $abb;
	}

	$product = str_replace(" ", "", $product);
	$product = str_replace("-", "", $product);
	$product = str_replace("_", "", $product);
	return substr($product, 0, 7);
}


class DragonSignature 
{
    private $db;
    private $alarmID;       //`alarmID` int(10) unsigned NOT NULL,            
    private $alarmName;
    private $cve;
        
    private $shortName;     //`shortName` varchar(28) default NULL COMMENT 'The finalized dragon name',    
    private $eventGroupID;  //`egID` int(3) default NULL,    
    private $severity;      //`severity` varchar(10) default NULL,          
    private $DOD;           //`dateOfDisclosure` int(10) default NULL,             
    private $GHC;           //`triggerCount` int(10) default NULL,      
    private $maxDOD;
    private $maxGHC;
    private $relevancy;     //`relevancyScore` int(10) default '5000',  
    
    private $eg = array();
    private $riskMap = array('Low'=>3, 'Medium'=>5, 'High'=>8, 'Critical'=>10);     // never used.
    private $sv = array('apps'=>'Low',
                        'attacks'=>'High',
                        'beta'=>'Medium',
                        'compromise'=>'Critical',
                        'dynamic'=>'Medium',
                        'ensrt'=>'High',
                        'failures'=>'Medium', 
                        'legacy'=>'Low',
                        'maint'=>'Low',
                        'misuse'=>'Low',
                        'network'=>'Medium',
                        'probe'=>'Medium',
                        'suspicious'=>'Medium',
                        'trojan'=>'Critical',
                        'virus'=>'High',
                        'vulnerability'=>'Medium');

    /**
     * Construct objct
     *
     * @param res $db
     * @param int $alarmID
     * @return bool
     */
    function __construct($db, $alarmID = null){
        if($db){
            // by object quote, we can easily do resource release.
            $this->db = &$db;
        }
        else {
            echo "Database connection error!";
            return false;
        }
        
        if($alarmID) $this->alarmID = $alarmID;
    }
    
    /**
     * Destruct object
     *
     */
    function __destruct(){
        $this->db = null;
    }
    
    function filterNameComponents($nameComponents, $proto, $vendor, $product, $evgroup)
    {
        $newComps = array();
        $tailComps = array();
        $subsComp = array("INCLUDE" => "INCL", "INCLUSION" => "INCL", "INJECTION" => "INJCT", "METASPLOIT" => "MSF");
        foreach ($nameComponents as $comp) {

            $comp = trim($comp);
            if ($subsComp[$comp]) {
                $comp = $subsComp[$comp];
            }
            else if (strlen($comp) > 2) {
                if (contains($proto, $comp))
                    continue;
                if (contains($vendor, $comp))
                    continue;
                if (contains($product, $comp))
                    continue;
                if (contains($evgroup, $comp))
                    continue;
                if (redundantComponent($this->db,$comp))
                    continue;
            }
            else if (strlen($comp) < 2) {
                $tailComps[] = $comp;
                continue;
            }
            
            if ($comp)
                $newComps[] = $comp;
        }
        //print_r($newComps); //jim debug
        //print_r($tailComps); //jim debug
        return array_merge($newComps, $tailComps);
    }

    /**
     * Returns the event group ID that should be associated with a Signature.
     *
     * @param int $alarmID
     * @return bool
     */
    private function getEventGroupID($alarmID = null)
    {
        if (!$alarmID) $alarmID = $this->alarmID;
        if (!$alarmID) {
        	echo "alarmID error!";
        	return false;
        }

        $this->fillEgArray();
        $sql = "SELECT `serviceCategoryID` FROM `Alarm` WHERE `alarmID` = " . $alarmID;
        if ($scid = $this->db->fetchOne($sql)) {
            if (!$scid || $scid < 2) {
                echo "serviceCategoryID error!";
            	return false;
            }
            $this->severity = $this->sv[$this->eg[$scid]];
        }
        // ...
        $sql = "SELECT `EGID` FROM `EventGroup` WHERE EGName = '".$this->eg[$scid]."'";

    	if ($egid = $this->db->fetchOne($sql)) {
    	    $this->eventGroupID = $egid;
    		return true;
    	}
    	else {
            return false;
    	}
    }
    
    /**
     * Returns the dragon conformant short name that should be associated with a Signature.
     *
     * @param int $alarmID
     * @return bool
     */
    private function getShortName($alarmID = null){
        if (!$alarmID) $alarmID = $this->alarmID;
        if (!$alarmID) {
        	echo "alarmID error!";
        	return false;
        }
        assert($this->db);
        $ret = true;
        $proto =strtoupper(getSigProto($this->db,$alarmID));
        list($vendor,$product)=getProduct($this->db,$alarmID);
        $product = strtoupper(shortenProduct(trim($product)));
        $vendor = strtoupper($vendor);
        $evgroup = strtoupper(getSigEvGroup($this->db,$alarmID));
        /*print_r("<br>prod:".$product); //jim debug
        print_r("prot:".$proto);
        print_r("vendor:".$vendor);
        print_r("eventGroup:".$evgroup);*/
        $name = $this->db->fetchOne("select name from Alarm where alarmID=".$alarmID);
        //print_r("<br>name:".$name);
        //var_dump($alarmID);
        $name = strtoupper(trim($name));
        $name = str_replace(" ", ".", $name);
        $name = str_replace("-", ".", $name);
	    $nameComponents = explode(".",$name);
	    if (count($nameComponents) < 2) {
            $this->shortName = "DC:" . $alarmID;
            return $ret;
        }
        //var_dump($nameComponents);
        $filtered = $this->filterNameComponents($nameComponents, $proto, $vendor, $product, $evgroup);
        //echo "<br>filer:";
        //var_dump($filtered);
        $fields = array();
        // derive fields of the new name
        // this could be cleaned up using funcs and passing by ref
        if ($proto) {
            $fields[0] = $proto;
            if ($product)
                $fields[1] = $product;
            else if ($evgroup)
                $fields[1] = $evgroup;
            else {
                $fields[1] = $filtered[0];
                $filtered = array_slice($filtered, 1);
            }
        }
        else if ($evgroup) {
            $fields[0] = $evgroup;
            if ($product) {
                $fields[1] = $product;
            }
            else {
                $fields[1] = $filtered[0];
                $filtered = array_slice($filtered, 1);
            }
        }
        else if ($product) {
            $fields[0] = $product;
            $fields[1] = $filtered[0];
            $filtered = array_slice($filtered, 1);
        }
        else {
            $fields[0] = $filtered[0];
            $filtered = array_slice($filtered, 1);
        }
        //echo "<br>fil:";
        //var_dump($fields);
        if (!$fields[0])
            return $ret;
        $shortName = $fields[0];
        if ($fields[1])
            $shortName .= ":" . $fields[1];
        $filtered = removeBestFitComponent(28 - strlen($shortName), $filtered);

        if (count($filtered)) {
            $fields[2] = join($filtered, "_");
        }
        if ($fields[2])
            $shortName .= "-" . $fields[2];

        $shortName = substr($shortName, 0, 28);
        $shortName = rtrim($shortName, "_");
        // check for duplicate
        $c_len = strlen($shortName);
        $tmpName = $shortName;
        $tryThis = 1;
        while (nameIsDup($this->db, $alarmID, $tmpName)) {
            //echo "found duplicate name\n";
            $tmpName= uniqueName($shortName, $tryThis);
            $tryThis++;
        }
        /*
        $uniqN = 0;
        $sql = "select count('shortName') from DragonSignature where shortname regexp '^%%.%ds[0-9]+$' and alarmID<>".$alarmID; 
        for($i=MAX_SHORTNAME_LEN;$uniqN==0 && $i>SAFE_MATCH_STR_LEN;$i--) {
            $r_sql=sprintf( sprintf($sql,$i),$shortName);
            //$db->query($r_sql);
            $uniqN = $this->db->fetchOne($r_sql);
            //echo "<BR>".$uniqN."-".$i;
        }
        if($uniqN > 0){
            echo $r_sql . "<BR>$i";
            $n_uniqN = strlen($uniqN);
            $c_print_len = intval(MAX_SHORTNAME_LEN - $n_uniqN);
            if($c_print_len < $c_len) $c_len = $c_print_len;
            $shortName = sprintf("%.".$c_len."s%d",$shortName,$uniqN);
        }*/
        $this->shortName = $tmpName;
        return $ret;
    } //getShortName()
    
    /**
     * Returns the severity of a signature. 
     *
     * @param int $alarmID
     * @return bool
     */
    private function getSeverity($alarmID = null){
        if (!$alarmID) $alarmID = $this->alarmID;
        if (!$alarmID) {
        	echo "alarmID error!";
        	return false;
        }
        // ...
    	$sevMap = array("Low" => 3, "Medium" => 5, "High" => 8, "Critical" => 10);
    	$sql = "SELECT `severity` FROM `Alarm` WHERE `alarmID` = " . $alarmID;
    	
    	if ($s = $this->db->fetchOne($sql)) {
    	    $this->severity = $sevMap[$s];
    		return true;
    	}
    	else {
            return false;
    	}
    }
    
    /**
     * Returns the number of days since the vulnerability associated with the supplied signature was disclosed.
     *
     * @param int $alarmID
     * @return bool
     */
    private function getDOD($alarmID = null){
        if (!$alarmID) $alarmID = $this->alarmID;
        if (!$alarmID) {
        	echo "alarmID error!";
        	return false;
        }
        // ...
    	$sql = "SELECT `published` 
    	       FROM `CVE` c JOIN `Alarm` a ON c.name = a.CVE AND a.alarmID = " . $alarmID;
    	$disclDate = $this->db->fetchOne($sql);
    	if (!$disclDate){
            echo __FUNCTION__." error when get CVE.published. ";
    		return false;
    	}

    	if($disclDate != ''){
            $dodSeconds = time() - strtotime($disclDate);
    	    $this->DOD = floor($dodSeconds / (3600 * 24));
    	}
        return true;
    }
    
    /**
     * Returns the global hit count associated with a signature. 
     * For new signatures the result will always be 0 (zero). 
     *
     * @param int $alarmID
     * @return bool
     */
    private function getGHC($alarmID = null){
        if (!$alarmID) $alarmID = $this->alarmID;
        if (!$alarmID) {
        	echo "alarmID error!";
        	return false;
        }
        // bad performance...
    	//$sql = "SELECT COUNT(*) FROM `Event` WHERE alarmID = " . $alarmID;
		$sql = "SELECT SUM(`aCount`) FROM `AlarmSummary` WHERE alarmID =" . $alarmID;
    	$ghc = $this->db->fetchOne($sql);
    	if (!$ghc){
            echo __FUNCTION__." error when get count(event.*). ";
    		return false;
    	}
	
        $this->GHC = ($ghc=='')?0:intval($ghc);
        return true;
    }
    
    /**
     * Returns the maximum dateOfDisclosure over all signatures.
     *
     * @param int $alarmID
     * @return bool
     */
    private function getMaxDOD($alarmID = null){
        if (!$alarmID) $alarmID = $this->alarmID;
        if (!$alarmID) {
        	echo "alarmID error!";
        	return false;
        }
	   
        $sql = "SELECT MIN(`published`) FROM `CVE` c JOIN `Alarm` a ON c.name = a.CVE";
    	$disclDate = $this->db->fetchOne($sql);
    	if (!$disclDate){
    	    echo __FUNCTION__." error when get CVE.published. ";
    		return false;
    	}

    	$dodSeconds = time() - strtotime($disclDate);
    	$this->maxDOD = floor($dodSeconds / (3600 * 24));
        return true;
    }
    
    /**
     * Returns the maximum global hit count over all signatures.
     *
     * @param int $alarmID
     * @return bool
     */
    private function getMaxGHC($alarmID = null){
        if (!$alarmID) $alarmID = $this->alarmID;
        if (!$alarmID) {
        	echo "alarmID error!";
        	return false;
        }

    	$sql = "SELECT SUM(aCount) AS sa 
    	       FROM AlarmSummary asum JOIN Alarm a ON asum.alarmID = a.alarmID 
    	       WHERE a.sid > 0 GROUP BY a.alarmID ORDER BY sa DESC";
    	//$this->db->setPageSize(0,1);   // limit 1
    	$maxghc = $this->db->fetchOne($sql);
    	//$this->db->setPageSize(0,0);   // retore pagesize setting
    	if (!$disclDate){
    	    echo __FUNCTION__." error when get MaxGHC. ";
    		return false;
    	}
	
        $this->maxGHC = $maxghc;
        return true;
    }
    
    /**
     * Computes and returns the relevancy score for a given signature. 
     * The metrics are obtained by calling getSeverity, getDOD, getGHC and computeRelevancy.
     *
     * @param int $alarmID
     * @return bool
     */
    private function getRelevancy($alarmID = null){
        if (!$alarmID) $alarmID = $this->alarmID;
        if (!$alarmID) {
        	echo "alarmID error!";
        	return false;
        }
        $this->severity = $this->getSeverity($alarmID);

    	$severity = $this->severity;
    	$dateOfDisclosure = $this->DOD;
    	$globalHitCount = $this->GHC;
    
    	if (!$severity)
    		$severity = 5;
    	if (!$dateOfDisclosure)
    		$dateOfDisclosure = $this->maxDOD/2;
    
    	
    	$v = 1.0 - ($dateOfDisclosure / $this->maxDOD);
    	$g = ($globalHitCount / $this->maxGHC);
    	$s = $severity / 10.0;
    	$relevancy = round(10000 * (.6 * $s + .2 * $v + .2 * $g));

        $this->relevancy = $relevancy;
        return true;
    }
    
    /**
     * Convert signature to dragon format.
     *
     * @param int $alarmID
     * @return bool
     */
    private function fillEgArray(){
        $this->eg[2] = "suspicious";
        $this->eg[3] = "attacks";
        $this->eg[4] = "attacks";
        $this->eg[5] = "suspicious";
        $this->eg[6] = "compromise";
        $this->eg[7] = "suspicious";
        $this->eg[8] = "suspicious";
        $this->eg[9] = "suspicious";
        $this->eg[10] = "suspicious";
        $this->eg[11] = "attacks";
        $this->eg[12] = "attacks";
        $this->eg[13] = "suspicious";
        $this->eg[15] = "suspicious";
        $this->eg[16] = "attacks";
        $this->eg[17] = "attacks";
        $this->eg[18] = "attacks";
        $this->eg[19] = "suspicious";
        $this->eg[20] = "suspicious";
        $this->eg[21] = "attacks";
        $this->eg[22] = "attacks";
        $this->eg[23] = "attacks";
        $this->eg[24] = "attacks";
        $this->eg[25] = "apps";
        $this->eg[26] = "suspicious";
        $this->eg[27] = "apps";
        $this->eg[28] = "apps";
        $this->eg[29] = "suspicious";
        $this->eg[30] = "suspicious";
        $this->eg[31] = "attacks";
        $this->eg[32] = "suspicious";
        $this->eg[33] = "suspicious";
        $this->eg[34] = "attacks";
        $this->eg[36] = "suspicious";
        $this->eg[37] = "suspicious";
        $this->eg[38] = "attacks";
        $this->eg[39] = "suspicious";
        $this->eg[40] = "attacks";
        $this->eg[41] = "attacks";
        $this->eg[42] = "misuse";
        $this->eg[43] = "attacks";
        $this->eg[44] = "suspicious";
        $this->eg[45] = "suspicious";
        $this->eg[46] = "suspicious";
        $this->eg[47] = "attacks";
        $this->eg[48] = "suspicious";
        $this->eg[49] = "suspicious";
        $this->eg[50] = "suspicious";
        $this->eg[51] = "suspicious";
        $this->eg[52] = "suspicious";
        $this->eg[53] = "suspicious";
        $this->eg[54] = "suspicious";
        $this->eg[55] = "suspicious";
        $this->eg[56] = "suspicious";
        $this->eg[57] = "attacks";
        $this->eg[58] = "attacks";
        $this->eg[59] = "failure";
        $this->eg[60] = "attacks";
        $this->eg[61] = "suspicious";
        $this->eg[62] = "suspicious";
        $this->eg[63] = "attacks";
        $this->eg[64] = "suspicious";
        $this->eg[65] = "suspicious";
        $this->eg[67] = "suspicious";
        $this->eg[68] = "suspicious";
        $this->eg[69] = "suspicious";
        $this->eg[70] = "attacks";
        $this->eg[71] = "suspicious";
        $this->eg[72] = "suspicious";
        $this->eg[73] = "suspicious";
        $this->eg[74] = "suspicious";
        $this->eg[75] = "suspicious";
        $this->eg[76] = "suspicious";
        $this->eg[79] = "suspicious";
        $this->eg[80] = "attacks";
        $this->eg[82] = "attacks";
        $this->eg[83] = "attacks";
        $this->eg[84] = "attacks";
        $this->eg[85] = "attacks";
        $this->eg[86] = "attacks";
        $this->eg[87] = "virus";
        $this->eg[88] = "malware";
        $this->eg[90] = "apps";
        $this->eg[91] = "apps";
        $this->eg[92] = "attacks";
        $this->eg[93] = "attacks";
        $this->eg[94] = "attacks";
        $this->eg[95] = "suspicious";
        $this->eg[96] = "apps";
        $this->eg[97] = "apps";
        $this->eg[98] = "suspicious";
        $this->eg[99] = "suspicious";
        $this->eg[101] = "suspicious";
        $this->eg[103] = "suspicious";
        $this->eg[105] = "suspicious";
        $this->eg[106] = "suspicious";
        $this->eg[107] = "trojan";
        $this->eg[109] = "suspicious";
        $this->eg[110] = "suspicious";
        $this->eg[111] = "suspicious";
        $this->eg[112] = "suspicious";
        $this->eg[113] = "suspicious";
        $this->eg[114] = "suspicious";
        $this->eg[115] = "suspicious";
        $this->eg[116] = "suspicious";
        $this->eg[117] = "suspicious";
        $this->eg[118] = "suspicious";
        $this->eg[119] = "suspicious";
        $this->eg[120] = "attacks";
        $this->eg[121] = "suspicious";
        $this->eg[122] = "suspicious";
        $this->eg[123] = "suspicious";
        $this->eg[124] = "attacks";
        $this->eg[125] = "attacks";
        $this->eg[126] = "suspicious";
        $this->eg[127] = "suspicious";
        $this->eg[128] = "attacks";
        $this->eg[129] = "suspicious";
        $this->eg[130] = "suspicious";
        $this->eg[131] = "suspicious";
        $this->eg[132] = "suspicious";
        $this->eg[133] = "suspicious";
        $this->eg[134] = "suspicious";
        $this->eg[135] = "suspicious";
        $this->eg[136] = "suspicious";
        $this->eg[137] = "suspicious";
        $this->eg[138] = "suspicious";
        $this->eg[139] = "suspicious";
        $this->eg[140] = "suspicious";
        $this->eg[141] = "misuse";
        $this->eg[142] = "misuse";
        $this->eg[143] = "probe";
        $this->eg[144] = "probe";
        $this->eg[146] = "suspicious";
        $this->eg[147] = "suspicious";
        $this->eg[148] = "suspicious";
        $this->eg[149] = "suspicious";
        $this->eg[150] = "suspicious";
        $this->eg[151] = "misuse";
        $this->eg[152] = "suspicious";
        $this->eg[153] = "suspicious";
        $this->eg[154] = "suspicious";
        $this->eg[155] = "suspicious";
        $this->eg[156] = "suspicious";
        $this->eg[157] = "suspicious";
        $this->eg[158] = "suspicious";
        $this->eg[159] = "suspicious";
        $this->eg[160] = "suspicious";
        $this->eg[161] = "suspicious";
        $this->eg[162] = "suspicious";
        $this->eg[163] = "suspicious";
        $this->eg[164] = "suspicious";
        $this->eg[165] = "suspicious";
        $this->eg[166] = "suspicious";
        $this->eg[167] = "suspicious";
        $this->eg[168] = "suspicious";
        $this->eg[169] = "suspicious";
        $this->eg[170] = "suspicious";
        $this->eg[171] = "suspicious";
        $this->eg[172] = "suspicious";
        $this->eg[173] = "suspicious";
        $this->eg[174] = "suspicious";
        $this->eg[175] = "suspicious";
        $this->eg[176] = "suspicious";
        $this->eg[177] = "suspicious";
        $this->eg[178] = "suspicious";
        $this->eg[179] = "suspicious";
        $this->eg[180] = "suspicious";
        $this->eg[181] = "suspicious";
        $this->eg[182] = "suspicious";
        $this->eg[183] = "suspicious";
        $this->eg[184] = "misuse";
        $this->eg[185] = "misuse";
        $this->eg[186] = "misuse";
        $this->eg[187] = "misuse";
        $this->eg[188] = "misuse";
        $this->eg[189] = "misuse";
        $this->eg[190] = "misuse";
        $this->eg[191] = "misuse";
        $this->eg[192] = "misuse";
        $this->eg[193] = "misuse";
        $this->eg[194] = "misuse";
        $this->eg[195] = "misuse";
        $this->eg[196] = "misuse";
        $this->eg[197] = "misuse";
        $this->eg[198] = "misuse";
        $this->eg[199] = "misuse";
        $this->eg[200] = "misuse";
        $this->eg[201] = "misuse";
        $this->eg[202] = "misuse";
        $this->eg[203] = "misuse";
        $this->eg[204] = "misuse";
        $this->eg[205] = "misuse";
        $this->eg[206] = "misuse";
        $this->eg[207] = "misuse";
        $this->eg[208] = "misuse";
        $this->eg[209] = "suspicious";
	$this->eg[210] = "attacks";
	$this->eg[212] = "attacks";
	$this->eg[213] = "attacks";
	$this->eg[214] = "attacks";
	$this->eg[215] = "attacks";
	$this->eg[216] = "attacks";
	$this->eg[217] = "attacks";
	$this->eg[218] = "attacks";
	$this->eg[219] = "attacks";
	$this->eg[220] = "attacks";
	$this->eg[221] = "attacks";
	$this->eg[222] = "attacks";
	$this->eg[223] = "attacks";
	$this->eg[224] = "attacks";
	$this->eg[225] = "attacks";
	$this->eg[226] = "attacks";



    }
    
    /**
     * Convert signature to dragon format.
     *
     * @param int $alarmID
     * @return bool
     */
    public function convertSignature($alarmID = null)
    {
        if (!$alarmID) $alarmID = $this->alarmID;
        if (!$alarmID) {
        	echo "alarmID error!";
        	return false;
        }
		$this->alarmID = $alarmID;
        // ...
        // update field alarm.egid 
        // 这个函数是获取不到数据的，每次到这里就出错误提示
        /*if (!$this->getEventGroupID()) {
        	echo __FUNCTION__." get egid fail!";
        	return false;
        }*/
//        $sql = "UPDATE `Alarm` SET 
//                `egID` = ?, 
//                `severity` = ?,
//                `epochModified` = UNIX_TIMESTAMP(),
//                `dayIndexModified` = CURDATE()+0 
//                WHERE `alarmID` = ?";
//        if (!$this->db->query($sql, 'isi', $this->eventGroupID, $this->severity, $alarmID)) {
		unset($row);
		$row = array(
			'egID'=>$this->eventGroupID,
			'severity'=>$this->severity,
			'epochModified'=>time(),
			'dayIndexModified'=>'CURDATE()+0');
        if (!$this->db->update('Alarm',$row,'alarmID='.$alarmID)) {
        	echo __FUNCTION__." update egid fail!";
//        	echo $this->db->errorInfo();
        	return false;
        }
        
        // update table DragonSignature   
        $this->getDOD();
        if (!$this->getMaxDOD() || !$this->getMaxGHC() || !$this->getGHC() ) { // || !$this->getShortName()
            echo __FUNCTION__." error in parameter preparation.";
            return false;
        }
        else {
            if (!$this->getShortName()) {
            	echo __FUNCTION__." error when get short name. ";
            }
            if (!$this->getRelevancy()) {
            	echo __FUNCTION__." error when get getRelevancy. ";
            }
        	if (!$this->severity) $this->severity = 5;
        	if (!$this->DOD) $this->DOD = $this->maxDOD/2;
    		
            $sql = "SELECT COUNT(*) FROM `DragonSignature` WHERE alarmID=".$alarmID;
            if($this->db->fetchOne($sql)==0) {
//            	$sql = "INSERT INTO `DragonSignature` 
//            	       (`alarmID`, `shortName`, `severity`, `dateOfDisclosure`, `triggerCount`, `relevancyScore`)
//            	       VALUES(?,?,?,?,?,?)";
				unset($row);
				$row = array(
					'alarmID'=>$alarmID,
					'shortName'=>$this->shortName,
					'severity'=>$this->severity,
					'dateOfDisclosure'=>$this->DOD,
					'triggerCount'=>$this->GHC,
					'relevancyScore'=>$this->relevancy);
            	$res = $this->db->insert('DragonSignature',$row);
            	//$res = $this->db->query($sql, 'issiii', $alarmID, $this->shortName, $this->severity, $this->DOD, $this->GHC, $this->relevancy);
            }
            else {
//            	$sql = "UPDATE `DragonSignature` SET
//            	       `shortName`=?, `severity`=?, `dateOfDisclosure`=?, `triggerCount`=?, `relevancyScore`=?
//            	       WHERE `alarmID`=? ";
//            	$res = $this->db->query($sql, 'ssiiii', $this->shortName, $this->severity, $this->DOD, $this->GHC, $this->relevancy, $alarmID);
				$row = array(
					'shortName'=>$this->shortName,
					'severity'=>$this->severity,
					'dateOfDisclosure'=>$this->DOD,
					'triggerCount'=>$this->GHC,
					'relevancyScore'=>$this->relevancy);
            	$res = $this->db->update('DragonSignature',$row,'alarmID='.$alarmID);
            }
        	if (!$res) {
        	    echo $this->db->errorInfo();
        		return false;
        	}
        	return true;
        }
    }

    function test($from)
    {
            $this->getShortName($from);
            return $this->shortName;
    }
}
?>
