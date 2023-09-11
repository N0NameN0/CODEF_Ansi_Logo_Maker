<?PHP
/*------------------------------------------------------------------------------
                Copyright (c) 2022 Antoine Santo Aka NoNameNo

                This tool is usinge CODEF
                More info : http://codef.santo.fr
                Demo gallery http://www.wab.com

                Permission is hereby granted, free of charge, to any person obtaining a copy
                of this software and associated documentation files (the "Software"), to deal
                in the Software without restriction, including without limitation the rights
                to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
                copies of the Software, and to permit persons to whom the Software is
                furnished to do so, subject to the following conditions:
                The above copyright notice and this permission notice shall be included in
                all copies or substantial portions of the Software.
                THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
                IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
                FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
                AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
                LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
                OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
                THE SOFTWARE.
                ------------------------------------------------------------------------------*/
if(strpos(php_sapi_name(),"cli")===0)
        $RUNENV = "CLI";
else
        $RUNENV = "WEB";

if($RUNENV == "WEB")
	header('Content-Type: text/html; charset=utf-8');

global $workvar, $matrix, $BGmatrix, $FTmatrix, $fnum, $mytxt, $POSX, $POSY, $BGCOL, $FTCOL, $CHARPOSX, $curspacesize, $curspacing;
$workvar;
$matrix = [];
$BGmatrix = [];
$FTmatrix = [];
$POSX = 1;
$POSY = 1;
$FTCOL = 15;
$BGCOL = 0;
$CHARPOSX = 1;

if($RUNENV == "WEB"){
	$mytxt=(isset($_GET["text"]) ? $_GET["text"] : exit("Please visite <a href=https://github.com/N0NameN0/WAB_Ansi_Logo_Maker>https://github.com/N0NameN0/WAB_Ansi_Logo_Maker</a>"));
	$font=(isset($_GET["font"]) ? $_GET["font"] : 0);
	$curspacing = (isset($_GET["spacing"]) ? $_GET["spacing"] : 2);
	$curspacesize = (isset($_GET["spacesize"]) ? $_GET["spacesize"] : 5);
	$fnum=(isset($_GET["vary"]) ? $_GET["vary"] : 0);
}
else if($RUNENV == "CLI"){
	$mytxt=(isset($argv[1]) ? $argv[1] : "WAB");
	$font=(isset($argv[2]) ? $argv[2] : 0);
	$curspacing = (isset($arv[3]) ? $argv[3] : 2);
	$curspacesize = (isset($argv[4]) ? $argv[4] : 5);
	$fnum=(isset($argv[5]) ? $argv[5] : 0);
}

$files=glob("FONTS/*.TDF", GLOB_BRACE);

LoadTDF($files[$font]);

function LoadTDF($tdf){
	global $workvar;
	$workvar = new stdClass();
	$workvar->signature = "";
        $workvar->fontnum = 0;
        $workvar->size = 0;
        $workvar->headers = [];
        $workvar->data = [];


	$fileHandle = fopen($tdf, 'rb');
	$binString = fread($fileHandle, filesize($tdf));
	fclose($fileHandle);

	file_parser($binString);
	font_parser($binString);
	text_renderer();
        eko();
}

function file_parser($binString){
	global $workvar;
	$workvar->signature=substr($binString,1,18);
	$workvar->size = strlen($binString);
}

function font_parser($binString){
	global $workvar, $curspacing;
	$startoffset = 0;

	while ($startoffset + 20 < $workvar->size) {
		$workvar->headers[$workvar->fontnum] = new stdClass();
		$workvar->headers[$workvar->fontnum]->fontname = "";
            	$workvar->headers[$workvar->fontnum]->fonttype = "";
            	$workvar->headers[$workvar->fontnum]->letterspacing = 0;
            	$workvar->headers[$workvar->fontnum]->blocksize = 0;
            	$workvar->headers[$workvar->fontnum]->lettersoffsets = [];

		$workvar->headers[$workvar->fontnum]->fontname = substr($binString,$startoffset + 25, 12);
		$ftype = substr($binString,$startoffset + 41, 1);
		if (ord($ftype[0]) == 0)
                    $workvar->headers[$workvar->fontnum]->fonttype = "oUTLINE";
                else if (ord($ftype[0]) == 1)
                    $workvar->headers[$workvar->fontnum]->fonttype = "bLOCK";
                else if (ord($ftype[0]) == 2)
                    $workvar->headers[$workvar->fontnum]->fonttype = "cOLOR";

		$workvar->headers[$workvar->fontnum]->letterspacing = ord(substr($binString,$startoffset + 42, 1));
		$workvar->headers[$workvar->fontnum]->blocksize = hexdec(sprintf('%02X',ord(substr($binString,$startoffset+44,1))).sprintf('%02X',ord(substr($binString,$startoffset+43,1))));

		$n=0;
		for ($i = 0; $i < 94; $i++) {
                    $workvar->headers[$workvar->fontnum]->lettersoffsets[$i] =hexdec(sprintf('%02X',ord(substr($binString,$startoffset+45+$n+1,1))).sprintf('%02X',ord(substr($binString,$startoffset+45+$n,1))));
                    $n += 2;
                }

		$workvar->data[$workvar->fontnum] = substr($binString,$startoffset + 233, $workvar->headers[$workvar->fontnum]->blocksize);
                $startoffset += 212 + $workvar->headers[$workvar->fontnum]->blocksize + 1;
                $workvar->fontnum++;


	}
}

function text_renderer(){
	global $workvar, $fnum, $mytxt, $matrix, $BGmatrix, $FTmatrix, $POSX, $POSY, $CHARPOSX, $curspacing, $curspacesize, $FTCOL, $BGCOL;

	for($i = 0; $i < 12; $i++){
        	$matrix[$i] = [];
                $FTmatrix[$i] = [];
                $BGmatrix[$i] = [];
        }

	$POSX = 1;
        $POSY = 1;
        $CHARPOSX = 1;


	if ($workvar->headers[$fnum]->fonttype == "oUTLINE") {
		echo ("oUTLINE FONT TYPE \nis NOT SUPPORTED YET");
	}
	else if ($workvar->headers[$fnum]->fonttype == "cOLOR") {
		for ($i = 0; $i < strlen($mytxt); $i++) {
                        if ((ord($mytxt[$i]) >= 33) && (ord($mytxt[$i]) < 126)) {
                                $offset = $workvar->headers[$fnum]->lettersoffsets[ord($mytxt[$i]) - 33];
                                if ($offset != 65535) {
                                        $maxcharwidth = ord($workvar->data[$fnum][$offset]);
                                        $maxcharheight = ord($workvar->data[$fnum][$offset + 1]);
                                        $n = 2;
                                        $OLDPOSX = $POSX;
                                        do {
                                                $char = $workvar->data[$fnum][$offset + $n];
                                                if ($char == "\r") {
							$n--;
							_PRINTCHAR($char);
						}
                                                else if ($char == "\0") {
                                                        /**/
                                                } 
						else {
							$col = ord($workvar->data[$fnum][$offset + $n + 1]);
                                        		$BGCOL = floor($col / 16);
                                        		$FTCOL = $col % 16;
                                                        _PRINTCHAR($char);
                                                }
                                                $n+=2;
                                        } while ($char != "\0");
                                        $POSY = 1;
                                        $POSX = $OLDPOSX + $maxcharwidth + $curspacing;
                                        $CHARPOSX = $POSX;
                                }
                        }
                        else if (ord($mytxt[$i]) == 32) {
                            $POSX += $curspacesize;
                            $CHARPOSX = $POSX;
                        }
                }
	}
	else if ($workvar->headers[$fnum]->fonttype == "bLOCK") {

		$FTCOL = 15;
                $BGCOL = 0;
                for ($i = 0; $i < strlen($mytxt); $i++) {
			if ((ord($mytxt[$i]) >= 33) && (ord($mytxt[$i]) < 126)) {
				$offset = $workvar->headers[$fnum]->lettersoffsets[ord($mytxt[$i]) - 33];
				if ($offset != 65535) {
					$maxcharwidth = ord($workvar->data[$fnum][$offset]);
	                                $maxcharheight = ord($workvar->data[$fnum][$offset + 1]);
        	                        $n = 2;
                	                $OLDPOSX = $POSX;
					do {
                                    		$char = $workvar->data[$fnum][$offset + $n];
                                    		if ($char == "\0") {
                                        		/**/
                                    		} else {
                                        		_PRINTCHAR($char);
                                    		}
                                    		$n++;
                                	} while ($char != "\0");
	                                $POSY = 1;
        	                        $POSX = $OLDPOSX + $maxcharwidth + $curspacing;
                        	        $CHARPOSX = $POSX;
				}
			}
			else if (ord($mytxt[$i]) == 32) {
                            $POSX += $curspacesize;
                            $CHARPOSX = $POSX;
                        }
		}
	}
}

function _PRINTCHAR($char) {
	global $POSX, $POSY, $BGCOL, $FTCOL, $CHARPOSX, $matrix, $BGmatrix, $FTmatrix;

        $matrix[$POSY - 1][$POSX - 1] = $char;
        $BGmatrix[$POSY - 1][$POSX - 1] = $BGCOL;
        $FTmatrix[$POSY - 1][$POSX - 1] = $FTCOL;

        if (ord($char[0]) == 13) {
            	$POSX = $CHARPOSX;
        	$POSY++;
        } 
	else {
                $POSX++;
         }
}

function eko(){
	global $matrix, $BGmatrix, $FTmatrix, $fnum, $workvar;

	$mystring = "";
        $newcolconv = "";
        $oldcolconv = "";

	for ($i = 0; $i < 12; $i++) {
	        if (array_key_last($matrix[$i]) != 0) {
         	       	for ($n = 0; $n <= array_key_last($matrix[$i]); $n++) {
				if(!isset($matrix[$i][$n])){
                                        $oldcolconv = "\x1b[0m";
                                        echo $oldcolconv;
                                        echo " ";
                                }
				else if ($matrix[$i][$n] == "\r") {
					if ($workvar->headers[$fnum]->fonttype == "cOLOR") {
		                                if ($newcolconv[2] != 4 || $newcolconv[3] != 0) {
                		                	if ($oldcolconv[2] != 0 && $oldcolconv[3] != "m") {
                                		        	$oldcolconv = "\x1b[0m";
                                        			echo $oldcolconv;
                                    			}
                                		}
                            		}
					echo " ";
				}
				else{
					if ($workvar->headers[$fnum]->fonttype == "cOLOR") {
						$newcolconv = colconv($i, $n);
		                                if ($newcolconv != $oldcolconv) {
							echo $newcolconv;
                                		    	$oldcolconv = $newcolconv;
                                		}

					}

					echo iconv('IBM437', 'UTF8', $matrix[$i][$n]);
				}
			}

		echo "\x1b[0m\n";
		}
	}
	if ($workvar->headers[$fnum]->fonttype == "cOLOR") {
		echo "\x1b[0m";
	}
}

function colconv($var1, $var2) {
	global $FTmatrix, $BGmatrix;

	$col1 = $FTmatrix[$var1][$var2];
        $col2 = $BGmatrix[$var1][$var2];

        switch ($col1) {
        	case 0:
                	$col1 = 30;
                    	break;
                case 1:
                    	$col1 = 34;
                    	break;
                case 2:
                    	$col1 = 32;
                    	break;
                case 3:
                    	$col1 = 36;
                    	break;
                case 4:
                    	$col1 = 31;
                    	break;
                case 5:
                    	$col1 = 35;
                    	break;
                case 6:
                    	$col1 = 33;
                    	break;
                case 7:
                    	$col1 = 37;
                    	break;
                case 8:
                    	$col1 = 90;
                    	break;
                case 9:
                    	$col1 = 94;
                    	break;
                case 10:
                    	$col1 = 92;
                    	break;
                case 11:
                   	$col1 = 96;
                    	break;
                case 12:
                    	$col1 = 91;
                    	break;
                case 13:
                    	$col1 = 95;
                    	break;
                case 14:
                    	$col1 = 93;
                    	break;
                case 15:
                    	$col1 = 97;
                    	break;
            }

            switch ($col2) {
            	case 0:
                	$col2 = 40;
                    	break;
                case 1:
                    	$col2 = 44;
                    	break;
                case 2:
                    	$col2 = 42;
                    	break;
                case 3:
                    	$col2 = 46;
                    	break;
                case 4:
                    	$col2 = 41;
                    	break;
                case 5:
                   	$col2 = 45;
                    	break;
                case 6:
                    	$col2 = 43;
                    	break;
                case 7:
                    	$col2 = 47;
                    	break;
        }
	return "\x1b[" . $col2 . ";" . $col1 . "m";
}


?>
