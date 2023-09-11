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
    <script src="JS/FileSaver.min.js"></script>

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
        
	#curlink,
	#phplink {
		color: blue;
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
            border: 1px solid rgb(255, 255, 255);
            border-radius: 5px;
            max-width: 600px;
            /*font-size: 10pt;*/
            z-index: 100;
            background-color: rgb(30, 30, 30);
        }
        
        details>div {
            margin: 10px 0;
        }
        
        details>summary {
            cursor: pointer;
            white-space: nowrap;
        }
        /* Firefox workaround */
        
        .no-details details>summary:before {
            float: left;
            width: 15px;
            content: '\25B6';
        }
        
        .no-details details.open>summary:before {
            content: '\25BC';
        }
        
        a {
            color: rgb(85, 85, 255);
        }
    </style>
    <script>
        var matrix = [];
        var BGmatrix = [];
        var FTmatrix = [];
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
		    dolink(0);
                }
            };
            fetch.send();
        }

	function dolink(fnum){
		    document.getElementById("curlink").innerHTML="&nbsp;&nbsp;curl \"https://wab-ansi-logo-maker-api.santo.fr/api.php?text="+encodeURI(document.getElementById("mytext").value)+"&font="+document.getElementById("FONTS").selectedIndex+"&spacing="+document.getElementById("spacing").value+"&spacesize="+document.getElementById("spacesize").value+"&vary="+fnum+"\" > /etc/motd&nbsp;&nbsp;";
		    document.getElementById("phplink").innerHTML="&nbsp;&nbsp;php api.php \""+document.getElementById("mytext").value+"\" "+document.getElementById("FONTS").selectedIndex+" "+document.getElementById("spacing").value+" "+document.getElementById("spacesize").value+" "+fnum+" > /etc/motd&nbsp;&nbsp;";
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

            for (var i = 0; i < 12; i++) {
                matrix[i] = [];
                FTmatrix[i] = [];
                BGmatrix[i] = [];
            }
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
                document.getElementById("dltxt").style = "display:inline";
            } else {
                document.getElementById("dl").style = "display:none";
                document.getElementById("dltxt").style = "display:none";
            }

            mycanvas.draw(myfinalcanvas, 0, 0);
	    dolink(num);
        }

        function _PRINTCHAR(char) {
            matrix[POSY - 1][POSX - 1] = char;
            BGmatrix[POSY - 1][POSX - 1] = BGCOL;
            FTmatrix[POSY - 1][POSX - 1] = FTCOL;

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

        function colconv(var1, var2) {
            var col1 = FTmatrix[var1][var2];
            var col2 = BGmatrix[var1][var2];


            switch (col1) {
                case 0:
                    col1 = 30;
                    break;
                case 1:
                    col1 = 34;
                    break;
                case 2:
                    col1 = 32;
                    break;
                case 3:
                    col1 = 36;
                    break;
                case 4:
                    col1 = 31;
                    break;
                case 5:
                    col1 = 35;
                    break;
                case 6:
                    col1 = 33;
                    break;
                case 7:
                    col1 = 37;
                    break;
                case 8:
                    col1 = 90;
                    break;
                case 9:
                    col1 = 94;
                    break;
                case 10:
                    col1 = 92;
                    break;
                case 11:
                    col1 = 96;
                    break;
                case 12:
                    col1 = 91;
                    break;
                case 13:
                    col1 = 95;
                    break;
                case 14:
                    col1 = 93;
                    break;
                case 15:
                    col1 = 97;
                    break;

            }

            switch (col2) {
                case 0:
                    col2 = 40;
                    break;
                case 1:
                    col2 = 44;
                    break;
                case 2:
                    col2 = 42;
                    break;
                case 3:
                    col2 = 46;
                    break;
                case 4:
                    col2 = 41;
                    break;
                case 5:
                    col2 = 45;
                    break;
                case 6:
                    col2 = 43;
                    break;
                case 7:
                    col2 = 47;
                    break;

            }
            return "\x1b[" + col2 + ";" + col1 + "m";
        }

        function dl_txt() {
            var mystring = "";
            var newcolconv = "";
            var oldcolconv = "";

            for (var i = 0; i < 12; i++) {
                if (matrix[i].length != 0) {
                    for (var n = 0; n < matrix[i].length; n++) {
                        if (matrix[i][n] == "\r") {
                            if (workvar.headers[curnum].fonttype == "cOLOR") {
                                if (newcolconv[2] != 4 || newcolconv[3] != 0) {
                                    if (oldcolconv[2] != 0 && oldcolconv[3] != "m") {
                                        oldcolconv = "\x1b[0m";
                                        mystring += oldcolconv;
                                    }
                                }
                            }
                            mystring += " ";
                        } else if (typeof matrix[i][n] === 'undefined') {
				oldcolconv = "\x1b[0m";
                                mystring += oldcolconv;
  	                        mystring += " ";
                        } else {
                            if (workvar.headers[curnum].fonttype == "cOLOR") {
                                newcolconv = colconv(i, n);
                                if (newcolconv != oldcolconv) {
                                    mystring += newcolconv;
                                    oldcolconv = newcolconv;
                                }
                            }
                            mystring += fuckUTF8(matrix[i][n].charCodeAt(0));
                        }

                    }
                    mystring += "\n";
                }
            }
            if (workvar.headers[curnum].fonttype == "cOLOR") {
                mystring += "\x1b[0m";
            }
            var blob = new Blob([mystring], {
                type: "text/plain;charset=utf-8"
            });
            var mydate = new Date();
            saveAs(blob, 'WAB_LOGO_MAKER_' + mydate.getTime() + '.txt');
        }

        function init() {
            mycanvas = new canvas(3200, FONTH * 12);
            mycanvas.contex.imageSmoothingEnabled = false;
            LoadTDF("FONTS/1911.TDF");
        }

        function fuckUTF8(str) {
            switch (str) {
                case 128:
                    str = 199;
                    break;
                case 129:
                    str = 252;
                    break;
                case 130:
                    str = 233;
                    break;
                case 131:
                    str = 226;
                    break;
                case 132:
                    str = 228;
                    break;
                case 133:
                    str = 224;
                    break;
                case 134:
                    str = 229;
                    break;
                case 135:
                    str = 231;
                    break;
                case 136:
                    str = 234;
                    break;
                case 137:
                    str = 235;
                    break;
                case 138:
                    str = 232;
                    break;
                case 139:
                    str = 239;
                    break;
                case 140:
                    str = 238;
                    break;
                case 141:
                    str = 236;
                    break;
                case 142:
                    str = 196;
                    break;
                case 143:
                    str = 197;
                    break;
                case 144:
                    str = 201;
                    break;
                case 145:
                    str = 230;
                    break;
                case 146:
                    str = 198;
                    break;
                case 147:
                    str = 244;
                    break;
                case 148:
                    str = 246;
                    break;
                case 149:
                    str = 242;
                    break;
                case 150:
                    str = 251;
                    break;
                case 151:
                    str = 249;
                    break;
                case 152:
                    str = 255;
                    break;
                case 153:
                    str = 214;
                    break;
                case 154:
                    str = 220;
                    break;
                case 155:
                    str = 162;
                    break;
                case 156:
                    str = 163;
                    break;
                case 157:
                    str = 165;
                    break;
                case 158:
                    str = 8359;
                    break;
                case 159:
                    str = 402;
                    break;
                case 160:
                    str = 225;
                    break;
                case 161:
                    str = 237;
                    break;
                case 162:
                    str = 243;
                    break;
                case 163:
                    str = 250;
                    break;
                case 164:
                    str = 241;
                    break;
                case 165:
                    str = 209;
                    break;
                case 166:
                    str = 170;
                    break;
                case 167:
                    str = 186;
                    break;
                case 168:
                    str = 191;
                    break;
                case 169:
                    str = 8976;
                    break;
                case 170:
                    str = 172;
                    break;
                case 171:
                    str = 189;
                    break;
                case 172:
                    str = 188;
                    break;
                case 173:
                    str = 161;
                    break;
                case 174:
                    str = 171;
                    break;
                case 175:
                    str = 187;
                    break;
                case 176:
                    str = 9617;
                    break;
                case 177:
                    str = 9618;
                    break;
                case 178:
                    str = 9619;
                    break;
                case 179:
                    str = 9474;
                    break;
                case 180:
                    str = 9508;
                    break;
                case 181:
                    str = 9569;
                    break;
                case 182:
                    str = 9570;
                    break;
                case 183:
                    str = 9558;
                    break;
                case 184:
                    str = 9557;
                    break;
                case 185:
                    str = 9571;
                    break;
                case 186:
                    str = 9553;
                    break;
                case 187:
                    str = 9559;
                    break;
                case 188:
                    str = 9565;
                    break;
                case 189:
                    str = 9564;
                    break;
                case 190:
                    str = 9563;
                    break;
                case 191:
                    str = 9488;
                    break;
                case 192:
                    str = 9492;
                    break;
                case 193:
                    str = 9524;
                    break;
                case 194:
                    str = 9516;
                    break;
                case 195:
                    str = 9500;
                    break;
                case 196:
                    str = 9472;
                    break;
                case 197:
                    str = 9532;
                    break;
                case 198:
                    str = 9566;
                    break;
                case 199:
                    str = 9567;
                    break;
                case 200:
                    str = 9562;
                    break;
                case 201:
                    str = 9556;
                    break;
                case 202:
                    str = 9577;
                    break;
                case 203:
                    str = 9574;
                    break;
                case 204:
                    str = 9568;
                    break;
                case 205:
                    str = 9552;
                    break;
                case 206:
                    str = 9580;
                    break;
                case 207:
                    str = 9575;
                    break;
                case 208:
                    str = 9576;
                    break;
                case 209:
                    str = 9572;
                    break;
                case 210:
                    str = 9573;
                    break;
                case 211:
                    str = 9561;
                    break;
                case 212:
                    str = 9560;
                    break;
                case 213:
                    str = 9554;
                    break;
                case 214:
                    str = 9555;
                    break;
                case 215:
                    str = 9579;
                    break;
                case 216:
                    str = 9578;
                    break;
                case 217:
                    str = 9496;
                    break;
                case 218:
                    str = 9484;
                    break;
                case 219:
                    str = 9608;
                    break;
                case 220:
                    str = 9604;
                    break;
                case 221:
                    str = 9612;
                    break;
                case 222:
                    str = 9616;
                    break;
                case 223:
                    str = 9600;
                    break;
                case 224:
                    str = 945;
                    break;
                case 225:
                    str = 223;
                    break;
                case 226:
                    str = 915;
                    break;
                case 227:
                    str = 960;
                    break;
                case 228:
                    str = 931;
                    break;
                case 229:
                    str = 963;
                    break;
                case 230:
                    str = 181;
                    break;
                case 231:
                    str = 964;
                    break;
                case 232:
                    str = 934;
                    break;
                case 233:
                    str = 920;
                    break;
                case 234:
                    str = 937;
                    break;
                case 235:
                    str = 948;
                    break;
                case 236:
                    str = 8734;
                    break;
                case 237:
                    str = 966;
                    break;
                case 238:
                    str = 949;
                    break;
                case 239:
                    str = 8745;
                    break;
                case 240:
                    str = 8801;
                    break;
                case 241:
                    str = 177;
                    break;
                case 242:
                    str = 8805;
                    break;
                case 243:
                    str = 8804;
                    break;
                case 244:
                    str = 8992;
                    break;
                case 245:
                    str = 8993;
                    break;
                case 246:
                    str = 247;
                    break;
                case 247:
                    str = 8776;
                    break;
                case 248:
                    str = 176;
                    break;
                case 249:
                    str = 8729;
                    break;
                case 250:
                    str = 183;
                    break;
                case 251:
                    str = 8730;
                    break;
                case 252:
                    str = 8319;
                    break;
                case 253:
                    str = 178;
                    break;
                case 254:
                    str = 9632;
                    break;
            }
            return String.fromCharCode(str);
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
                <center>
                    <div id="dl" style="display:none;"><button onclick="dl_img()">Download Image</button></div>
                    <div id="dltxt" style="display:none;"><button onclick="dl_txt()">Download text file (utf8)</button></div>
		    <br>
		    <br>
		    <div>Using the "API" (remote/web) way : </div>
		    <div id="curlink"></div>
		    <br>
		    <div>Using the "API" (local/php/cli) way : </div>
		    <div id="phplink"></div>
		    <br>
		    <br>
                </center>
            </td>
        </tr>
    </table>
</body>

</html>
<!--  -->
