<?php
/** 
* XML2Assoc Class to creating 
* PHP Assoc Array from XML File 
* 
* @author godseth (AT) o2.pl & rein_baarsma33 (AT) hotmail.com (Bugfixes in parseXml Method) 
* @uses XMLReader 
*
Date        Ver   Who  Change
----------  ----- ---  -----------------------------------------------------
2019-05-06  1.1   FHO  removed try/catch so exceptions can be catched by caller
                       added prototypes, reformatted, removed useless brackets
*/ 

class Xml2Assoc
{
	/** 
	* Optimization Enabled / Disabled 
	* 
	* @var bool 
	*/ 
	protected $bOptimize = false; 

	/** 
	* Method for loading XML Data from String 
	* 
	* @param string $sXml 
	* @param bool $bOptimize 
	* @return array 
	*/ 

	public function parseString(string $sXml ,bool $bOptimize = false): ?array
	{ 
		$oXml = new XMLReader(); 
		$this -> bOptimize = (bool) $bOptimize; 

		// Set String Containing XML data 
		$oXml->XML($sXml); 

		// Parse Xml and return result 
		return $this->parseXml($oXml); 
	} 

	/** 
	* Method for loading Xml Data from file 
	* 
	* @param string $sXmlFilePath 
	* @param bool $bOptimize 
	* @return array 
	*/ 
	public function parseFile(string $sXmlFilePath , bool $bOptimize = false ): ?array
	{ 
		$oXml = new XMLReader(); 
		$this -> bOptimize = $bOptimize; 

		// Open XML file 
		if (!@$oXml->open($sXmlFilePath))
			throw new Exception ('cannot open ' . $sXmlFilePath);

		// Parse Xml and return result 
		return $this->parseXml($oXml); 
	} 

	/** 
	* XML Parser 
	* 
	* @param XMLReader $oXml 
	* @return array 
	*/ 
	protected function parseXml( XMLReader $oXml ): ?array
	{ 
		$aAssocXML = null; 
		$iDc = -1; 

		while($oXml->read())
		{ 
			switch ($oXml->nodeType)
			{ 
			case XMLReader::END_ELEMENT: 
				if ($this->bOptimize)
					$this->optXml($aAssocXML); 
				return $aAssocXML; 

			case XMLReader::ELEMENT: 
				if(!isset($aAssocXML[$oXml->name]))
				{ 
					if($oXml->hasAttributes)
						$aAssocXML[$oXml->name][] = $oXml->isEmptyElement ? '' : $this->parseXML($oXml); 
					else
					{ 
						if($oXml->isEmptyElement)
							$aAssocXML[$oXml->name] = ''; 
						else
							$aAssocXML[$oXml->name] = $this->parseXML($oXml); 
					} 
				}
				elseif (is_array($aAssocXML[$oXml->name]))
				{ 
					if (!isset($aAssocXML[$oXml->name][0])) 
					{ 
						$temp = $aAssocXML[$oXml->name]; 
						foreach ($temp as $sKey=>$sValue) 
							unset($aAssocXML[$oXml->name][$sKey]); 
						$aAssocXML[$oXml->name][] = $temp; 
					} 

					if($oXml->hasAttributes)
						$aAssocXML[$oXml->name][] = $oXml->isEmptyElement ? '' : $this->parseXML($oXml); 
					else
						if($oXml->isEmptyElement)
							$aAssocXML[$oXml->name][] = ''; 
						else
							$aAssocXML[$oXml->name][] = $this->parseXML($oXml); 
				}
				else
				{ 
					$mOldVar = $aAssocXML[$oXml->name]; 
					$aAssocXML[$oXml->name] = array($mOldVar); 
					if ($oXml->hasAttributes)
						$aAssocXML[$oXml->name][] = $oXml->isEmptyElement ? '' : $this->parseXML($oXml); 
					else
						if($oXml->isEmptyElement)
							$aAssocXML[$oXml->name][] = ''; 
						else
							$aAssocXML[$oXml->name][] = $this->parseXML($oXml); 
				} 

				if($oXml->hasAttributes)
				{ 
					$mElement =& $aAssocXML[$oXml->name][count($aAssocXML[$oXml->name]) - 1]; 
					while($oXml->moveToNextAttribute())
						$mElement[$oXml->name] = $oXml->value; 
				} 
				break; 

			case XMLReader::TEXT: 
			case XMLReader::CDATA: 
				$aAssocXML[++$iDc] = $oXml->value; 
				break;
		    } // switch 
		} // while 

		return $aAssocXML; 
	}

	/** 
	* Method to optimize assoc tree. 
	* ( Deleting 0 index when element 
	*  have one attribute / value ) 
	* 
	* @param array $mData 
	*/ 
	public function
	optXml(&$mData): void
	{ 
		if (is_array($mData))
			if (isset($mData[0]) && count($mData) == 1 )
			{ 
				$mData = $mData[0]; 
				if (is_array($mData))
					foreach ($mData as &$aSub)
						$this->optXml($aSub); 
			}
			else
				foreach ($mData as &$aSub)
					$this->optXml($aSub); 
	} 
} 
?>
