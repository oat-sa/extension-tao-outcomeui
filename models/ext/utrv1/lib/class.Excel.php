<?php

 /**
 * Excel class based on the lib excel-2.0 base on spreadsheet pear
 *
 * little description here :
 *
	 *
	 *  O = obligated
	 *  F = Facultative
	 * structure de params
	 *  array (
	 *	'directory' => if setted, save the file in directory and return the url, else return directly the excel file	F
	 *	'pages' => data array. Structure define on 1)												O
	 *	'name' => 'Name of worbook and file (without extension)'							O
	 *  'formats' => array(																						F (Obligated if some formats declarated in data)
	 *		'formatName' => array(check Format.php to see all options)
     *      format1 and format2 are reserved name. Don't use it !!!!
	 *		you can add an array named border for the border. Structure describe in 2
	 * )
	 *
	 *
	 * 1) data = array(
	 *		//this create and add worksheet and its data
	 *		'worksheetName' =>	 array(
	 *			'startX' => int(starting row),										 F
	 *			'startY' => int(starting column),								  F
	 *			'higlight' => 0/1 (highlight 1 row on 2),			           F
	 *			'title' => array('list', 'of', 'titles', 'of', 'columns'),			F
	 *			'titleSeparator' => int(number of row separating title and data),			F
	 *			'dataFormat' => formatName,									  F
	 *			'empty' => 'replacementForEmptyCells',					F
	 *			'titleFormat' => formatName,								    F
	 *			'width' => array(40,40,40,40,40,40),						F //taille des cellules
	 *			'data' => array(														 O
	 *				 array('data', 'fields', 'for', 'row', '1'),
	 *				 array('data', 'fields', 'for', 'row', '2'),
	 *				 array('data', 'fields', 'for', 'row', '3'),
	 *				 array('data', 'fields', 'for', 'row', '4'),
	 *				 array('data', 'fields', 'for', 'row', '5'),
	 *			),
	 *		'worksheet2' => ......
	 * )
     *
     *
	 * 2) border = array(															F (if present but empty this will set all border in size 1 and black)
	 *	   'size' => int(borderSize),
     *     'position' => array(														positions accepted are : all,top,bottom,left,right
	 *			'all' => borderColor,
	 *			'bottom' => borderColor,
     *		)
     * )
 *
 * @author Matteo Melis, <matteo.melis@tudor.lu>
 * @package osq
 * @subpackage models_classes
 */
class osq_models_classes_Excel
{
	private static $workbook;
	private static $formatsList = array();

	/**
	 *  init the class with the required class
	 */
	public static function init() {
		$path  = EXCEL_ROOT_PATH . 'excel-2.0-custom/';
		require_once $path . "Workbook.php";
	}

	/**
	 *	create an excel workbook and send it to navigator or write it and return the path
	 *
	 * @param array $params
	 */
	public static function createExcel($params) {
		self::init();
		extract($params);
		// if there are a directory so it's for save and don't send in navigator
		if(isset($directory)) {
			$fname = $directory . $name . '.xls';
		} else {
			$fname = tempnam('/tmp', $name . ".xls");
		}
		// name of file definition
		self::$workbook = &new Spreadsheet_Excel_Writer_Workbook($fname);
		// add the formats
		foreach($formats as $formatName => $format) {
			self::setFormat($format, $formatName);
		}
		// write data
		self::writeData($pages, $name);
		// close and send book
		self::$workbook->close();
		if(!isset($directory)) {
			header("Content-Type: application/x-msexcel; name=\"$name.xls\"");
			header("Content-Disposition: inline; filename=\"$name.xls\"");
			$fh=fopen($fname, "rb");
			fpassthru($fh);
			unlink($fname);
			return('file://' . $fname);
		} else {
			return $fname;
		}
	}

	/**
	 *
	 *	write an entire row
	 *
	 * @param excelWorksheet $worksheet
	 * @param int $col starting col
	 * @param int $row starting row
	 * @param array $data row data to write
	 * @param string $format format name to apply
	 * @return int rowNumber
	 */
	public static function writeRow($worksheet, $col, $row, $data, $format = null, $empty = "") {
		$height = 0;
		// format definition
		$format = isset($format) ? self::$formatsList[$format] : '';
		foreach($data as $field) {
			if($field == "")
				$field = $empty;
			$worksheet->write($row, $col,  self::unhtmlentities($field), $format);
			$col++;
		}
		$row++;
		return $row;
	}

	/**
	 *	write an excel book (multiple tab are allowed)
	 *
	 * @param array $data excel tabs configuration and data
	 * @param string $name  book and filename (without extension)
	 */
	public static function writeData( $data, $name) {
		// for each pages
		foreach($data as $pageName => $page) {
			$worksheet = &self::$workbook->addworksheet($pageName);
			// starting position
			$startRow = isset($page['startX']) ? $page['startX'] : 0;
			$startCol = isset($page['startY']) ? $page['startY'] : 0;
			$highlight = isset($page['highlight']) ? $page['highlight'] : 0;
			//width of cells
			if(isset($page['width'])) {
				foreach($page['width'] as $cell => $width) {
					$x = $cell + $startCol;
					$worksheet->setColumn($x, $x, $width);
				}
			}
			$row = $startRow;
			// title creation (if there are)
			if(isset($page['title'])) {
				// title formatting (if there is)
				$format = isset($page['titleFormat']) ? $page['titleFormat'] : null;
				// title writting 
				$row = self::writeRow($worksheet, $startCol, $row, $page['title'], $format);
				if(isset($page['titleSeparator']))
					$row+=$page['titleSeparator'];
			}
			// data formatting (if there is)
			$format = isset($page['dataFormat']) ? $page['dataFormat'] : null;
			//highlighting one row on 2 rows
			if($highlight) {
				$format1 = $format2 = self::$formatsList[$format . '_array'];
				$format1['bgColor'] = 'silver';
				$format2['bgColor'] = 'white';
				self::setFormat($format1, 'format1');
				self::setFormat($format2, 'format2');
			}
			// empty cell will fill with ?
			$empty = isset($page['empty']) ? $page['empty'] : "";
			// data writing
			foreach($page['data'] as $dataRow) {
				if(isset($format1)) {
					$format = $row % 2 ? 'format1': 'format2';
				}
				$row = self::writeRow($worksheet, $startCol, $row, $dataRow, $format, $empty);
			}
		}
	}

	/**
	 * avoid htmlentities function
	 *
	 * @param striing $chaineHtml
	 * @return string the string unhtmlized
	 */
	public static function unhtmlentities($chaineHtml) {
		 $tmp = get_html_translation_table(HTML_ENTITIES);
		 $tmp = array_flip ($tmp);
		 $chaineTmp = strtr ($chaineHtml, $tmp);

		 return $chaineTmp;
	}

	/**
	 *	manage the border for a format (work on the entire row)
	 *  if border is set but empty so all border will be set as size 1  and black color
	 * @param array $format
	 * @param string $formatName
	 */
	public static function setFormat(array $format, $formatName) {
		self::$formatsList[$formatName . '_array'] = $format;
		if(isset($format['border'])) {
			$size = isset($format['border']['size']) ? $format['border']['size'] : 1;
			$position = isset($format['border']['position']) ? $format['border']['position'] : array('all' => 'black');
			foreach($position as $pos => $color) {
				if($pos == 'all') {
					$format['bottom'] = $format['top'] = $format['left'] = $format['right'] = $size;
					$format['bottom_color'] = $format['top_color'] = $format['left_color'] = $format['right_color'] = $color;
				} else {
					$format[$pos . '_color'] = $color;
					$format[$pos] = $size;
				}
			}
			unset($format['border']);
		}
		self::$formatsList[$formatName] = & self::$workbook->addformat ($format);
	}
}

?>