<?php

$parentDir  = dirname(__FILE__) ;

function searchString($ppath)
{
    
	$string = $_GET['s'];

	if(!$string)
	{
		echo 'Put string and Search';
		return;
	}
	
	$files = scandir($ppath);
	
	foreach($files as $file) {
	 
	     if(!is_file($ppath.'/'.$file))
		   {
			   
			   if(($file != '..' ) && ($file != '.' ))
			   {
				   $path  = $ppath.'/'.$file;
				  
			       searchString($path); 
			   }
			   
		   }
		   else
		   {
			    $path  = $ppath.'/'.$file;
				 
			 	$content = file_get_contents($path);
				
				$content = explode("\n",$content);
				
				foreach ($content as $lineNumber => $line) {
					
					 if (strpos($line,$string) !== false) 
					   {
						
						  $lineNumber++;
					   
						  echo  "'$string' found at line $lineNumber in $path </br>";
						
						}
				} 
		  }
		 
	}
		


	
}

searchString($parentDir);

?>