<!doctype html>
<html lang="en">
<script>
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
</script>

<head>
    <script src="//codef.santo.fr/codef/codef_core.js"></script>
    <style>
        @font-face {
            font-family: 'Perfect DOS VGA 437';
            font-style: normal;
            font-weight: 400;
            src: local('Perfect DOS VGA 437'), url('CSS/Perfect DOS VGA 437.woff') format('woff');
        }
        
        @font-face {
            font-family: 'Perfect DOS VGA 437 Win';
            font-style: normal;
            font-weight: 400;
            src: local('Perfect DOS VGA 437 Win'), url('CSS/Perfect DOS VGA 437 Win.woff') format('woff');
        }
    </style>
    <style>
        body,
        select {
            font-family: 'Perfect DOS VGA 437', sans-serif;
            font-family: 'Perfect DOS VGA 437 Win', sans-serif;
            color: white;
            background-color: black;
            font-size: 16px;
        }
        
        input {
            font-family: 'Perfect DOS VGA 437', sans-serif;
            font-family: 'Perfect DOS VGA 437 Win', sans-serif;
            color: white;
            background-color: blue;
            font-size: 16px;
        }
        
        button {
            font-family: 'Perfect DOS VGA 437', sans-serif;
            font-family: 'Perfect DOS VGA 437 Win', sans-serif;
            color: black;
            /*background-color:grey;*/
            font-size: 16px;
        }
        
        table,
        td,
        th {
            border: 1px solid;
        }
        
        table {
            /*width: 100%;*/
            border-collapse: collapse;
        }
        
        td {
            text-align: center;
            vertical-align: middle;
        }


       details {
        position: absolute;
        top: 0;
        left: 1em;
        margin: 1em 0;
        padding: 10px;
        background: #fff;
        /*background:  rgba(155,155,155,0.1);*/
        border: 1px solid rgb(255,255,255);
        border-radius: 5px;
        max-width: 600px;
        /*font-size: 10pt;*/
        z-index: 100;
        background-color:rgb(30,30,30);
      }
      details > div {
        margin: 10px 0;
      }
      details > summary {
        cursor: pointer;
        white-space: nowrap;
      }
      /* Firefox workaround */
      .no-details details > summary:before { float: left; width: 15px; content: '\25B6'; }
      .no-details details.open > summary:before { content: '\25BC'; }

      a {
         color: rgb(85,85,255);
      }
    </style>
    <script>
        var maxPOSX = 0;
        var maxPOSY = 0;
        var curspacing = 5;
        var curspacesize = 5;
        var curnum = 0;
        var workvar;
        var mycanvas;
        var myfinalcanvas;
        var FTCOL = 15;
        var BGCOL = 0;
        var POSX = 1;
        var POSY = 1;
        var ZOOMX = 1;
        var ZOOMY = 1;
        var FONTW = 8;
        var FONTH = 16;
        var ft = new image('IMG/ft.png');
        var bg = new image('IMG/bg.png');
        bg.initTile(FONTW, FONTH);
        ft.initTile(FONTW, FONTH);



        function LoadTDF(tdf) {
            workvar = new TDFfont();
            var fetch = new XMLHttpRequest();
            fetch.open('GET', tdf);
            fetch.overrideMimeType("text/plain; charset=x-user-defined");
            fetch.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var t = this.responseText || "";
                    var ff = [];
                    var mx = t.length;
                    var scc = String.fromCharCode;
                    for (var z = 0; z < mx; z++) {
                        ff[z] = scc(t.charCodeAt(z) & 255);
                    }
                    var binString = new dataType();
                    binString.data = ff.join("");

                    file_parser(binString);
                    font_parser(binString);

                    aze = "<table><tr style='background-color:grey;'><td>" +
                        "<font style='color:black;'>fONT nAME" + "<" + "/font>" + "<" + "/td>" +
                        "<td>" + "<font style='color:black;'>fONT tYPE" + "<" + "/td>" +
                        "<td>" + "<font style='color:black;'>aVAILABLE cHARS" + "<" + "/td>" +
                        "<" + "/tr>";

                    for (var i = 0; i < workvar.headers.length; i++) {
                        lett = "";
                        aze += "<tr>";
                        aze += "<td><button onclick='text_renderer(" + i + ");'>" + workvar.headers[i].fontname + "</button><" + "/td>";
                        aze += "<td>" + workvar.headers[i].fonttype + "<" + "/td>";
                        for (var j = 0; j < 94; j++) {
                            if (j == 47) lett += "<br>";
                            if (workvar.headers[i].lettersoffsets[j] != 65535) {
                                lett += "<font style='color:green;'>" + String.fromCharCode(33 + j) + "<" + "/font>";
                            } else {
                                lett += "<font style='color:red;'>" + String.fromCharCode(33 + j) + "<" + "/font>";
                            }
                        }
                        aze += "<td>" + lett + "<" + "/td>";

                        aze += "<" + "/tr>";
                    }
                    aze += "<" + "/table>";
                    document.getElementById("infos").innerHTML = aze;
                    text_renderer(0);
                }
            };
            fetch.send();
        }

        function file_parser(binString) {
            workvar.signature = binString.readBytesFromStart(1, 18); //TDF font file signature "TheDraw FONTS file"
            workvar.size = binString.data.length;
        }

        function font_parser(binString) {
            startoffset = 0;

            while (startoffset + 20 < workvar.size) {
                workvar.headers[workvar.fontnum] = new font_header;
                workvar.headers[workvar.fontnum].fontname = binString.readBytesFromStart(startoffset + 25, 12).replace(/\0/g, '');
                ftype = binString.readBytesFromStart(startoffset + 41, 1);
                if (ftype.charCodeAt(0) == 0)
                    workvar.headers[workvar.fontnum].fonttype = "oUTLINE";
                else if (ftype.charCodeAt(0) == 1)
                    workvar.headers[workvar.fontnum].fonttype = "bLOCK";
                else if (ftype.charCodeAt(0) == 2)
                    workvar.headers[workvar.fontnum].fonttype = "cOLOR";

                workvar.headers[workvar.fontnum].letterspacing = binString.readBytesFromStart(startoffset + 42, 1).charCodeAt(0);
                document.getElementById("spacing").value = workvar.headers[workvar.fontnum].letterspacing;
                curspacing = workvar.headers[workvar.fontnum].letterspacing;
                workvar.headers[workvar.fontnum].blocksize = parseInt((binString.readBytesFromStart(startoffset + 44, 1).charCodeAt(0).toString(16).padStart(2, '0') + binString.readBytesFromStart(startoffset + 43, 1).charCodeAt(0).toString(16).padStart(2, '0')), 16);
                var n = 0;
                for (var i = 0; i < 94; i++) {
                    workvar.headers[workvar.fontnum].lettersoffsets[i] = parseInt((binString.readBytesFromStart(startoffset + 45 + n + 1, 1).charCodeAt(0).toString(16).padStart(2, '0') + binString.readBytesFromStart(startoffset + 45 + n, 1).charCodeAt(0).toString(16).padStart(2, '0')), 16);
                    n += 2;
                }
                workvar.data[workvar.fontnum] = binString.readBytesFromStart(startoffset + 233, workvar.headers[workvar.fontnum].blocksize);
                startoffset += 212 + workvar.headers[workvar.fontnum].blocksize + 1
                workvar.fontnum++;
            }



        }

        function text_renderer(num, spsize) {
            curnum = num;
            mycanvas.fill("#000000");
            POSX = 1;
            POSY = 1;
            maxPOSX = 0;
            maxPOSY = 0;
            CHARPOSX = 1;
            var mytext = document.getElementById("mytext").value;
            if (mytext.length > 0) {
                if (workvar.headers[num].fonttype == "oUTLINE") {
                    alert("oUTLINE FONT TYPE \nis NOT SUPPORTED YET");
                } else if (workvar.headers[num].fonttype == "cOLOR") {
                    for (i = 0; i < mytext.length; i++) {
                        if ((mytext[i].charCodeAt(0) >= 33) && (mytext[i].charCodeAt(0) < 126)) {
                            offset = workvar.headers[num].lettersoffsets[mytext[i].charCodeAt(0) - 33];
                            if (offset != 65535) {
                                maxcharwidth = workvar.data[num][offset].charCodeAt(0);
                                maxcharheight = workvar.data[num][offset + 1].charCodeAt(0);
                                var n = 2;
                                OLDPOSX = POSX;

                                do {
                                    char = workvar.data[num][offset + n];

                                    if (char == "\r") {
                                        n--;
                                        _PRINTCHAR(char);
                                    } else if (char == "\0") {
                                        /**/
                                    } else {
                                        col = workvar.data[num][offset + n + 1].charCodeAt(0);
                                        BGCOL = Math.floor(col / 16);
                                        FTCOL = col % 16;
                                        _PRINTCHAR(char);
                                    }

                                    n += 2;
                                } while ((char != "\0"))
                                if (maxPOSY < POSY) maxPOSY = POSY;
                                POSY = 1;
                                POSX = OLDPOSX + maxcharwidth + curspacing;
                                if (maxPOSX < POSX) maxPOSX = POSX;
                                CHARPOSX = POSX;
                            }
                        } else if (mytext[i].charCodeAt(0) == 32) {
                            POSX += curspacesize;
                            CHARPOSX = POSX;
                        }

                    }
                } else if (workvar.headers[num].fonttype == "bLOCK") {
                    FTCOL = 15;
                    BGCOL = 0;
                    for (i = 0; i < mytext.length; i++) {
                        if ((mytext[i].charCodeAt(0) >= 33) && (mytext[i].charCodeAt(0) < 126)) {
                            offset = workvar.headers[num].lettersoffsets[mytext[i].charCodeAt(0) - 33];
                            if (offset != 65535) {
                                maxcharwidth = workvar.data[num][offset].charCodeAt(0);
                                maxcharheight = workvar.data[num][offset + 1].charCodeAt(0);
                                var n = 2;
                                OLDPOSX = POSX;
                                do {
                                    char = workvar.data[num][offset + n];
                                    if (char == "\0") {
                                        /**/
                                    } else {
                                        _PRINTCHAR(char);
                                    }
                                    n++;
                                } while (char != "\0")
                                if (maxPOSY < POSY) maxPOSY = POSY;
                                POSY = 1;
                                POSX = OLDPOSX + maxcharwidth + curspacing;
                                if (maxPOSX < POSX) maxPOSX = POSX;
                                CHARPOSX = POSX;
                            }
                        } else if (mytext[i].charCodeAt(0) == 32) {
                            POSX += curspacesize;
                            CHARPOSX = POSX;
                        }
                    }
                }
            }
            if (document.getElementById("maincanvas") == null) {
                myfinalcanvas = new canvas(0, 0, "main");
            }
            document.getElementById("maincanvas").width = (maxPOSX - 1) * FONTW;
            document.getElementById("maincanvas").height = maxPOSY * FONTH;
            if (maxPOSX > 0) {
                document.getElementById("dl").style = "display:inline";
            } else {
                document.getElementById("dl").style = "display:none";
            }

            mycanvas.draw(myfinalcanvas, 0, 0);
        }

        function _PRINTCHAR(char) {
            if (char.charCodeAt(0) == 13) {
                POSX = CHARPOSX;
                POSY++;
            } else {
                bg.drawTile(mycanvas, BGCOL, (POSX - 1) * FONTW * ZOOMX, (POSY - 1) * FONTH * ZOOMY, 1, 0, ZOOMX, ZOOMY);
                ft.drawTile(mycanvas, char.charCodeAt(0) + (256 * FTCOL), (POSX - 1) * FONTW * ZOOMX, (POSY - 1) * FONTH * ZOOMY, 1, 0, ZOOMX, ZOOMY);
                POSX++;
            }

        }

        function font_header() {
            this.fontname = "";
            this.fonttype = "";
            this.letterspacing = 0;
            this.blocksize = 0;
            this.lettersoffsets = [];
            return this;
        }

        function TDFfont() {
            this.signature = "";
            this.fontnum = 0;
            this.size = 0;
            this.headers = [];
            this.data = [];

            return this;
        }


        function ds(str) {
            console.log(str);
        }

        function dd(str) {
            console.log(str.charCodeAt(0));
        }

        function dh(str) {
            console.log(str.charCodeAt(0).toString(16));
        }

        function dataType() {
            this.data;
            this.pos = 0;
            this.endian = "BIG";

            this.readBytesFromStart = function(offset, nb) {
                var tmp = "";
                for (var i = 0; i < nb; i++) {
                    tmp += this.data[offset++];
                }
                return tmp;
            };

            this.readBytes = function(offset, nb) {
                var tmp = "";
                for (var i = 0; i < nb; i++) {
                    tmp += this.data[offset + this.pos++];
                }
                return tmp;
            };

            this.readMultiByte = function(nb, type) {
                if (type == "txt") {
                    var tmp = "";
                    for (var i = 0; i < nb; i++) {
                        tmp += this.data[this.pos++]
                    }
                    return tmp;
                }
            };

            this.readInt = function() {
                var tmp1 = parseInt(this.data[this.pos + 0].charCodeAt(0).toString(16), 16);
                var tmp2 = parseInt(this.data[this.pos + 1].charCodeAt(0).toString(16), 16);
                var tmp3 = parseInt(this.data[this.pos + 2].charCodeAt(0).toString(16), 16);
                var tmp4 = parseInt(this.data[this.pos + 3].charCodeAt(0).toString(16), 16);
                if (this.endian == "BIG")
                    var tmp = (tmp1 << 24) | (tmp2 << 16) | (tmp3 << 8) | tmp4;
                else
                    var tmp = (tmp4 << 24) | (tmp3 << 16) | (tmp2 << 8) | tmp1;
                this.pos += 4;
                return tmp;
            };

            this.readShort = function() {
                var tmp1 = parseInt(this.data[this.pos + 0].charCodeAt(0).toString(16), 16);
                var tmp2 = parseInt(this.data[this.pos + 1].charCodeAt(0).toString(16), 16);
                var tmp = (tmp1 << 8) | tmp2;
                this.pos += 2;
                return tmp;
            };

            this.readByte = function() {
                var tmp = parseInt(this.data[this.pos].charCodeAt(0).toString(16), 16);
                this.pos += 1;
                return tmp;
            };

            this.readString = function() {
                var tmp = "";
                while (1) {
                    if (this.data[this.pos++].charCodeAt(0) != 0)
                        tmp += this.data[this.pos - 1];
                    else
                        return tmp;
                }
            };

            this.substr = function(start, nb) {
                return this.data.substr(start, nb);
            };

            this.bytesAvailable = function() {
                return this.length - this.pos;
            };
        }

        function dl_img() {
            var link = document.getElementById('link');
            var mydate = new Date();
            link.setAttribute('download', 'WAB_LOGO_MAKER_' + mydate.getTime() + '.png');
            link.setAttribute('href', myfinalcanvas.canvas.toDataURL("image/png").replace("image/png", "image/octet-stream"));
            link.click();
        }

        function init() {
            mycanvas = new canvas(3200, FONTH * 12);
            mycanvas.contex.imageSmoothingEnabled = false;
            LoadTDF("FONTS/1911.TDF");
        }
    </script>
</head>

<body onLoad='init();'>
	     <details>
                <summary>Infos</summary>
                <div>
                  <p><b>Author</b> : NoNameNo^WAB aka Antoine Santo</p>
                  <p><b>Twitter</b> : <a href="https://twitter.com/alonetrio" target="_blank">@alonetrio</a></p>
                  <p><b>Using</b> : <a href="https://codef.santo.fr" target="_blank">CODEF</a></p>
                  <p><b>Usage</b> : 
                              <lu>
                              <li>Enter your name/nick/handle/text.</li>
			      <li>Choose a font.</li>
			      <li>Some font file provides variation like (colors,...) click on it.</li>
			      <li>You can play with sliders (mostly for space and spacing ).</li>
			      <li>Profit ;)</li>
                              </lu>
              
                  </p>
                  <p><b>GitHub</b> : <a href="https://github.com/N0NameN0/WAB_Ansi_Logo_Maker" target="_blank">https://github.com/N0NameN0/WAB_Ansi_Logo_Maker</a></p>
                </div>
              </details>
    <p style="text-align: center;"><img src="IMG/logo.png" /></p>
    <br> yOUR tEXT hERE : <input id="mytext" type="text" value="old school" onInput="text_renderer(curnum);"><br> fONT sPACING :
    <input type="range" id="spacing" min="0" max="5" oninput="curspacing=parseInt(this.value,10);text_renderer(curnum);"><br> "sPACE" sIZE : <input type="range" id="spacesize" value="5" min="0" max="15" oninput="curspacesize=parseInt(this.value,10);text_renderer(curnum);"><br>    tHE fONT :
    <select name="FONTS" id="FONTS" onChange="LoadTDF(this.value);">

        <?php
        $files=glob('FONTS/*.TDF', GLOB_BRACE);
        for ($i = 0; $i < count($files); $i++){
            echo '<option id="FONTNUM'.$i.'" value="'.$files[$i].'">'.substr($files[$i],6,-4).'</option>'."\n";
        }
    ?>

            </select> (Hint : give the focus to this selector then use UP/DOWN keyboard arrow)
    <br><br>
    <table>
        <tr>
            <td>
                <div id="infos"></div>
            </td>
            <td style="vertical-align: top;text-align:left">
                <div id="main"></div><br>
                <div id="createPNGButton">
                    <a id="link"></a>
                </div>
                <div id="dl" style="display:none;"><button onclick="dl_img()">Download Image</button></div>
            </td>
        </tr>
    </table>
</body>

</html>
