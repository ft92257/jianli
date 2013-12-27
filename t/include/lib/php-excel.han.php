<?php
/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename php-excel.han.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-24 16:33:35 1971142388 1838261714 4694 $
 *******************************************************************/






class Excel_XML
{

	
	private $header = "";

	
	private $footer = "</Workbook>";

	
	private $lines = array();

	
	private $sEncoding;

	
	private $bConvertTypes;

	
	private $sWorksheetTitle;

	
	public function __construct($sEncoding = 'UTF-8', $bConvertTypes = false, $sWorksheetTitle = 'Table1')
	{
		$this->header = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http:/"."/www.w3.org/TR/REC-html40\">";
		$this->bConvertTypes = $bConvertTypes;
		$this->setEncoding($sEncoding);
		$this->setWorksheetTitle($sWorksheetTitle);
	}

	
	public function setEncoding($sEncoding)
	{
		if('GBK'==trim(strtoupper($sEncoding)))
		{
			$sEncoding = 'GB2312';
		}
		 
		$this->sEncoding = $sEncoding;
	}

	
	public function setWorksheetTitle ($title)
	{
		$title = preg_replace ("/[\\\|:|\/|\?|\*|\[|\]]/", "", $title);
		$title = substr ($title, 0, 31);
		$this->sWorksheetTitle = $title;
	}

	
	private function addRow ($array)
	{
		$cells = "";
		foreach ($array as $k => $v):
		$type = 'String';
		if ($this->bConvertTypes === true && is_numeric($v)):
		$type = 'Number';
		endif;
				$v = htmlspecialchars($v, ENT_COMPAT, $this->sEncoding);
		$cells .= "<Cell><Data ss:Type=\"$type\">" . $v . "</Data></Cell>\n";
		endforeach;
		$this->lines[] = "<Row>\n" . $cells . "</Row>\n";
	}

	
	public function addArray ($array)
	{
		foreach ($array as $k => $v)
		$this->addRow ($v);
	}


	
	public function generateXML ($filename = 'excel-export', $halt = 1)
	{
				$filename = preg_replace('/[^aA-zZ0-9\_\-]/', '', $filename);
		 
				header("Content-Type: application/vnd.ms-excel; charset=" . $this->sEncoding);
		header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");

						echo stripslashes (sprintf($this->header, $this->sEncoding));
		echo "\n<Worksheet ss:Name=\"" . $this->sWorksheetTitle . "\">\n<Table>\n";
		foreach ($this->lines as $line)
		echo $line;

		echo "</Table>\n</Worksheet>\n";
		echo $this->footer;
		$halt && exit;
	}

}

?>