<?php
namespace zabbix;
trait work_with_file
{
	public static function getFromXlsx()
  	{
    	$xml              = simplexml_load_file('../xlsx.d/xl/sharedStrings.xml');
    	$sharedStringsArr = array();
    	foreach ($xml->children() as $item)
    	{
      		$sharedStringsArr[] = (string)$item->t;
    	}
    	$handle = @opendir('../xlsx.d/xl/worksheets');
    	$out    = array();
    	while ($file = @readdir($handle))
    	{
      	//проходим по всем файлам из директории /xl/worksheets/
      	if ($file != "." && $file != ".." && $file != '_rels') 
      	{
        	$xml = simplexml_load_file('../xlsx.d/xl/worksheets/' . $file);
        	//по каждой строке
        	$row = 0;
        	foreach ($xml->sheetData->row as $item)
        	{
          		$out[$file][$row] = array();
          		//по каждой ячейке строки
          		$cell = 0;
          		foreach ($item as $child)
          		{
            		$attr                    = $child->attributes();
            		$value                   = isset($child->v)? (string)$child->v:false;
            		$out[$file][$row][$cell] = isset($attr['t']) ? $sharedStringsArr[$value] : $value;
            		$cell++;
          		}
          		$row++;
        	}
      	}
    	}
    	unset($out['sheet1.xml'][0]);
    	self::deleteItem($out, false);
    	return $out;
	}
	/**
 	* Очищает строку от не нужных символов
 	*
 	*/
	private static function clearLine( $line )
	{
    	return strtolower(str_replace([' ','.','(',')','"'],['_',''], $line));
	}

	private static function deleteItem( &$array, $value )
  	{
    	foreach( $array as $key => $val )
    	{
      		if( is_array($val) )
      		{
        		self::deleteItem($array[$key], $value);
      		}
      		elseif( $val === $value )
      		{
        		unset($array[$key]);
      		}
    	}
    $array = array_values($array);
  	}

  	public static function delTree($dir) 
  	{ 
   		$files = array_diff(scandir($dir), array('.','..')); 
    	foreach ($files as $file)
    	{ 
      		(is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file"); 
    	} 
    return rmdir($dir); 
  	}

  	public static function uploadAndGetXlDirForXlsx($files)
  	{
  	 	$uploaddir = '../xlsx.d/';
    	$uploadfile = $uploaddir . basename($files['consumer']['name']['file']);
    	if($files['consumer']['type']['file'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
      		throw new \Exception('Не верный формат файла!');
    	move_uploaded_file( $files['consumer']['tmp_name']['file'], $uploadfile );
    	if(class_exists('ZipArchive'))
    	{
      		$zip = new \ZipArchive();
      		$zip->open( $uploadfile );
      		$zip->extractTo( $uploaddir );
      		unlink($uploaddir  . '[Content_Types].xml');
      		//unlink( $uploadfile );
      		self::delTree($uploaddir . '_rels');
    	}
    	else
      		throw new \Exception('ZipArchive - класс не установлен!');
  	} 
}