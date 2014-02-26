<?php

#Conectamos con MySQL
$conexion = mysql_connect("localhost","root","")
or die ("Fallo en el establecimiento de la conexión");

#Seleccionamos la base de datos a utilizar
mysql_select_db("testjoomla")
or die("Error en la selección de la base de datos");


//Creamos la tabla Logos

$result2=mysql_query("CREATE TABLE IF NOT EXISTS logos(
id INT NOT NULL AUTO_INCREMENT, 
PRIMARY KEY(id),
 username VARCHAR(30), 
 userid INT,
 logo VARCHAR(30), 
 thumb VARCHAR(30))")
 or die(mysql_error());  

echo "Table Created!";

//Leer archivo csv

$handle = fopen("copia.csv", "r"); //Coloca el nombre de tu archivo .csv que contiene los datos
$lines = file("copia.csv");

//Creamos el archivo datos.txt
//ponemos tipo 'a' para añadir lineas sin borrar
$file=fopen("datos.txt","a") or die("Problemas con el archivo");

function get_file_extension($file_name) {
	return substr(strrchr($file_name,'.'),1);
}

//var_dump(gd_info());

require('/phpThumb/phpthumb.class.php');
$phpThumb = new phpThumb();


foreach ($lines as $line_num => $line) {

    $record = explode(";", $line);
    $simbol = $record[0];
	$logo = trim($record[1]);
	
	/*
	print"simbolo ".$simbol." logo ".$logo;
	echo "<BR/>";
	*/
	#Efectuamos la consulta SQL
	$query = "SELECT id, count( * ) AS cont FROM `sfs4h_users` WHERE username = '".$simbol."'";
	$result = mysql_query ($query) or die("Error en la consulta SQL");	
	
	#Mostramos los resultados obtenidos
	while( $row = mysql_fetch_array ( $result )) {
	
	   if ($row [ "cont" ] == "0")
		{
			if($simbol!="Symbol")
			  {			
			  //vamos añadiendo al archivo el usuario no encontrado
			  fputs($file,$simbol);
			  fputs($file,"\n");	
			  }			  
		}
		else
		{
		/*				
		<img border="0" src="phpThumb/phpThumb.php?src=../final_logos/<?php echo $logo; ?>"&amp;w=300&amp;q=10&amp;sia=custom-filename&amp;hash=6946e6908c50a134f314960817d897a0" alt="">
		*/
		//REGISTRAMOS EN LA  DB EL USUARIO 

		//Generamos el tumbhnail
			
		require_once('phpthumb/phpthumb.class.php');
		$phpThumb = new phpThumb();
		//set the output format. We will save the images as jpg.
		$output_format = 'jpeg';		
		$thumbnail_height=125;
		$maxwidth=100;		
		
		$filename="C:/Apache2.2/htdocs/testjoomla/Proceso/final_logos/".$logo;
		$phpThumb->setParameter('disable_debug', false);
		echo "<BR />imagen origen ".$filename."<BR />";
		$phpThumb->setSourceFilename($filename);
		//$phpThumb->setParameter('h', $thumbnail_height);
		$phpThumb->setParameter('config_output_format', $output_format);		
		$phpThumb->setParameter('w', $maxwidth);
		$phpThumb->setParameter('q', 92);
		
		echo "destino "."C:/Apache2.2/htdocs/testjoomla/Proceso/thumbs/".$logo;
		$store_filename = "C:/Apache2.2/htdocs/testjoomla/Proceso/thumbs/thumb_".$logo;
				
		if ($phpThumb->GenerateThumbnail()) { 
			if ($phpThumb->RenderToFile($store_filename)) {
				//image uploaded - you will probably need to put image info into a database at this point
				echo "Se generó el thumbnail";				
				
				//Registramos en la tabla Logos
				#Efectuamos la consulta SQL
			 
				$query = "INSERT INTO `logos`(`username`, `userid`, `logo`, `thumb`) VALUES ('".$simbol. "','".$row [ "id" ]."','".$logo."','thumb_".$logo."')";
				//echo $query;
				$result = mysql_query ($query) or die("Error en la consulta SQL");	
				
			} else {
				//unable to write file to final destination directory - check folder permissions
				echo "Error al guardar en carpeta";
				
			}
		} else {
			//unable to generate the image
			echo "Error al generar el thumbnail";
			 //Registramos en la tabla Logos
			 
				$query = "INSERT INTO `logos`(`username`, `userid`, `logo`, `thumb`) VALUES ('".$simbol. "','".$row [ "id" ]."','".$logo."','none')";
				//echo $query;
				$result = mysql_query ($query)
				or die("Error en la consulta SQL");	
			}		
		}			
	}
}
	
 fclose($file);

?>