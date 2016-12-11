<?php

// DEBUG permet de ne traiter qu'un fichier a la fois
$runMNTFileOnce = true;//on implente nuage de point d'abord
$runPCFileOnce = false;//test ok il reste a enregistrer les données en bdd 

$currentFile_DATASRC = 'UNKNOW';
$currentFile_NW_XPLONDD=0.0;
$currentFile_NW_YPLATDD=0.0;
$currentFile_DATE ='YYYYMMDD';
$currentFile_PCS ='UNKNOW'; 

function dirToArray($dir) { 
   global $runMNTFileOnce,$runPCFileOnce;

   $result = array(); 

   $cdir = scandir($dir); 
   foreach ($cdir as $key => $value) 
   { 
      if (!in_array($value,array(".",".."))) 
      { 
         if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) 
         { 
            $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value); 
         } 
		else 
         { 		
         	if (is_file($dir . DIRECTORY_SEPARATOR . $value)) 
         	{ 
         	
         	$path_parts = pathinfo($value);
			switch($path_parts['extension'])
				{
    			case "asc":
					if ( $runMNTFileOnce) { _AnalyseFichierMNT($dir . DIRECTORY_SEPARATOR . $value);$runMNTFileOnce=false; }
					//echo 'mnt trouvé : '.$value.'<br/>';//v0.02	
   					break;

    			case "xyz":
    				if ( $runPCFileOnce) { _AnalyseFichierPC($dir . DIRECTORY_SEPARATOR . $value);$runPCFileOnce=false;	}
					//echo 'nuage de point trouvé : '.$value.'<br/>';
    				break;

    			case "":break; // Handle file extension for files ending in '.'
    			case NULL:break; // Handle no file extension
				}
         	}	 
            $result[] = $value; 
         }

         
      } 
   } 
   
   return $result; 
} 
//---------------------------------------------------------------------------------------------------------------
function _AnalyseFichierMNT( $fpathstring )
{
	$MNT_META_NCOLS=0;

ExtractFileNameInfo($fpathstring);

	$lines = file($fpathstring);
  $dataline_num=0;
  // Affiche toutes les lignes du tableau comme code HTML, avec les numéros de ligne
/*
echo $currentFile_DATASRC. "<br />\n"; // piece2
echo $currentFile_NW_XPLONDD. "<br />\n"; // piece2
echo $currentFile_NW_YPLATDD. "<br />\n"; // piece2
echo $currentFile_DATE. "<br />\n"; // piece2
echo $currentFile_PCS. "<br />\n"; // piece2
*/

  foreach ($lines as $line_num => $line) {

//les lignes 0 à 5 soit les 6 premieres lignes sont des metas data
//on affiche
    if($line_num < 6 )
    {
      echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";

      //on va selectionner les metas et intilaslies les variables
      // nRow et nCol
   /*
    ncols 200
    nrows 200
    xllcenter 153000.000000
    yllcenter 6767005.000000
    cellsize 5.000000
    nodata_value -99999.000000
 */
$TEMP = explode(" ", $line);

if($line_num ==0 )
    {
$MNT_META_NCOLS = intval($TEMP[1]); echo "MNT_META_NCOLS = >" . $MNT_META_NCOLS . "<br />\n";

    }

switch($line_num)
        {
          
          case 0:
            $MNT_META_NCOLS = intval($TEMP[1]); echo "MNT_META_NCOLS " . $MNT_META_NCOLS . "<br />\n"; break;

          case 1:
            $MNT_META_NROWS = intval($TEMP[1]); echo " MNT_META_NROWS" . $MNT_META_NROWS . "<br />\n"; break;
          case 2:
            $MNT_META_XLLC = intval($TEMP[1]); echo " MNT_META_XLLC" . $MNT_META_XLLC . "<br />\n"; break;

          case 3:
            $MNT_META_YLLC = intval($TEMP[1]); echo " MNT_META_YLLC" . $MNT_META_YLLC . "<br />\n"; break;

          case 4:
            $MNT_META_CS = intval($TEMP[1]); echo " MNT_META_CS" . $MNT_META_CS . "<br />\n"; break;

          case 5:
            $MNT_META_NODATA = intval($TEMP[1]); echo " MNT_META_NODATA" . $MNT_META_NODATA . "<br />\n"; break;
                  
           default: break;
        }

    }
    else
    {
    echo "Line #{$line_num} : LIGNE DATA A EXTRAIRE <b>{$dataline_num}</b><br />\n";

     
      $dataline_datas = explode(" ", $line);
      $dataline_datas_good=0;
      $dataline_datas_rejected=0;
      
      echo "Nombre de colonnes : ". count($dataline_datas). "<br />\n";
      //on trouve 1001 colone mais la derniere est le \n de fin de ligne

      foreach ($dataline_datas as $dataline_datas_num => $dataline_data) {
        
        //if (strcmp($dataline_data, "-99999.00") == 0) // if ( $dataline_data = "-99999.00")
        //on rejet les -999999..... et les fins d eligne
        if ( ( floatval($dataline_data) == -99999.00) || ( ord($dataline_data) == 10 ) )
        {
          $dataline_datas_rejected++;

        }
        else
        {
    //echo $metas[0]. "<br />\n"; // piece1
    echo $dataline_data. "<br />\n"; // piece2
//$dataline_datas_num => nCol



//-------------------------

//extraction x,y,z , on gere les datas 

//------------------------- V0.03




      $dataline_datas_good++;
        }


      }//fin foreach $dataline_datas
        echo $dataline_datas_good / (count($dataline_datas)-1) * 100 . " de données <br />\n";
 
 $dataline_num++;//nRow
    }
   
  }

}
//-------------------------------------------------------------------------------------------
function _AnalyseFichierPC( $fpathstring )
{

	ExtractFileNameInfo($fpathstring);
//meta source , date et projection
//on peut controler que les points sont bien dans la dallle ???


	$lines = file($fpathstring);

  // Affiche toutes les lignes du tableau comme code HTML, avec les numéros de ligne
  foreach ($lines as $line_num => $line) {

	 echo "<hr/>\n";
    echo htmlspecialchars($line) . "<br />\n";
    echo "<hr/>\n";
    $datapoints = explode(" ", $line);
		foreach ($datapoints as $datapoint ) {
    	echo htmlspecialchars($datapoint) . "<br />\n";
	}

	

  }

}



//----------------------------------------------------------------------------------------------------------------

//on conserve un espace pour les besoins futur
//L3D-MAR FRA PTS-SurSol
//L3D-MAR FRA PTS
//L3D-MAR FRA MNT
//L3D-MAR FRA MNT5


//20140923 date de réalisation 

//projection coordonnées et système associé
//


function ExtractFileNameInfo($filenamepath)
{
	global $currentFile_DATASRC,$currentFile_NW_XPLONDD,$currentFile_NW_YPLATDD,$currentFile_DATE,$currentFile_PCS;



	$filenamepath_parts = pathinfo($filenamepath);
	$metas = explode("_", $filenamepath_parts['filename']);

	$currentFile_DATASRC = $metas[0] .' ' . $metas[1].' ' . $metas[4];//
    echo htmlspecialchars($currentFile_DATASRC) . "<br />\n";
//angle NW dalle p7 doc shom bathymétrie
$currentFile_NW_XPLONDD=floatval($metas[2])*1000;
echo number_format($currentFile_NW_XPLONDD, 2, ',', ' '). "<br />\n";

//mise en forme donnes virgule pour decimale et  espace sep milliers
$currentFile_NW_YPLATDD=floatval($metas[3])*1000;
echo number_format($currentFile_NW_YPLATDD, 2, ',', ' '). "<br />\n";

	$currentFile_DATE = $metas[5];//
	echo htmlspecialchars($currentFile_DATE) . "<br />\n";

	$currentFile_PCS = $metas[6].' ' . $metas[7].' ' . $metas[8];//prjection coordonnees ellipsoide
	echo htmlspecialchars($currentFile_PCS) . "<br />\n";

echo "<hr/>\n";
echo "Extraction des meta data<br/>\n";
echo $currentFile_DATASRC. "<br />\n"; // piece2
echo $currentFile_NW_XPLONDD. "<br />\n"; // piece2
echo $currentFile_NW_YPLATDD. "<br />\n"; // piece2
echo $currentFile_DATE. "<br />\n"; // piece2
echo $currentFile_PCS. "<br />\n"; // piece2

}





?>