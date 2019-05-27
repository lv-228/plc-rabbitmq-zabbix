<?php

/**
 *
 *Модуль для управления цодами через RabbitMQ/AMQP
 */

function getFromXlsx(){
    $xml = simplexml_load_file('xlsx.d/xl/sharedStrings.xml');
    $sharedStringsArr = array();
    foreach ($xml->children() as $item) {
        $sharedStringsArr[] = (string)$item->t;
    }
    $handle = @opendir('xl/worksheets');
    $out = array();
    while ($file = @readdir($handle)) {
        //проходим по всем файлам из директории /xl/worksheets/
        if ($file != "." && $file != ".." && $file != '_rels') {
            $xml = simplexml_load_file('xl/worksheets/' . $file);
            //по каждой строке
            $row = 0;
            foreach ($xml->sheetData->row as $item) {
                $out[$file][$row] = array();
                //по каждой ячейке строки
                $cell = 0;
                foreach ($item as $child) {
                    $attr = $child->attributes();
                    $value = isset($child->v)? (string)$child->v:false;
                    $out[$file][$row][$cell] = isset($attr['t']) ? $sharedStringsArr[$value] : $value;
                    $cell++;
                }
                $row++;
            }
        }
    }
    deleteItem($out, false);
    $out['timestamp'] = date('U');
    for($i = 1; $i < count($out[0]);$i++){
        $minmax = explode('…',$out[0][$i][7]);
        if($out[0][$i][6] == "byte"){
            preg_match_all("/[0-9]?[0-9]?[0-9]/",$out[0][$i][count($out[0][$i]) - 1], $bitarray);
            $out[0][$i][7] = $bitarray[0][rand(0,count($bitarray[0]) - 1)];
        }
        else
            $out[0][$i][7] = rand((int)$minmax[0],(int)$minmax[1]);
//        preg_match_all("/[0-9]?[0-9]?[0-9]/",$out[0][$i][count($out[0][$i]) - 1], $bitarray);
    }
    return $out;
}

function deleteItem( &$array, $value )
{
    foreach( $array as $key => $val ){
        if( is_array($val) ){
            deleteItem($array[$key], $value);
        }elseif( $val===$value ){
            unset($array[$key]);
        }
    }
    //unset($array[0]);
    $array = array_values($array);
}

function getId($message){
    return $message[0] . "." . $message[1] . "." . $message[2] . "." . $message[3];
}
?>
<?php include('navbar.php'); ?>
<!-- <button onclick="mqtt()">MQTT</button>
<button onclick="ajaxSendMessageToRabbit('off_server')">Отключить сервер</button> -->
<ol id="messageOut"></ol>

<div id="view_mdc">
    <style>
        label {
            cursor: pointer;
            user-select: none;
        }

        .button {
            padding: 10px;
            text-transform: uppercase;
            text-align: center;
            color: #fff;
            background-color: #2c2c2c;
        }

        .modal {
            position: fixed;
            z-index: 1;
            top: 0;
            right: 100%;
            bottom: 0;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0,0,0,.2);
            opacity: 0;
            transition: opacity .3s;
        }

        .modal__check {display: none;}

        .modal__info {
            position: relative;
            width: 90%;
            max-width: 400px;
            max-height: 90%;
            padding: 20px 20px 5px;
            background-color: #fff;
            overflow: hidden;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .modal__close {
            font-family: serif;
            position: absolute;
            z-index: 2;
            top: 5px;
            right: 5px;
            width: 25px;
            border-radius: 50%;
            font-size: 36px;
            line-height: 25px;
            text-align: center;
        }

        .modal__closetwo {
            position: absolute;
            z-index: -1;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .modal__check:checked+.modal {
            opacity: 1;
            right: 0;
        }

        /* The switch - the box around the slider */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        /* Hide default HTML checkbox */
        .switch input {display:none;}

        /* The slider */
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        .button:hover,.modal__close:hover {opacity: 0.7;}
        .modal__info::-webkit-scrollbar {display: none;}
    </style>
    <!--svg-->
    <svg
            xmlns:dc="http://purl.org/dc/elements/1.1/"
            xmlns:cc="http://creativecommons.org/ns#"
            xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
            xmlns:svg="http://www.w3.org/2000/svg"
            xmlns="http://www.w3.org/2000/svg"
            xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
            xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
            width="293.82175mm"
            height="59.907146mm"
            viewBox="0 0 293.82175 59.907146"
            version="1.1"
            id="svg8"
            sodipodi:docname="my_mdc.svg"
            inkscape:version="0.92.3 (5aff6ba, 2018-11-25)">
        <defs
                id="defs2">
            <pattern
                    id="EMFhbasepattern"
                    patternUnits="userSpaceOnUse"
                    width="6"
                    height="6"
                    x="0"
                    y="0" />
            <pattern
                    id="EMFhbasepattern-2"
                    patternUnits="userSpaceOnUse"
                    width="6"
                    height="6"
                    x="0"
                    y="0" />
            <pattern
                    id="EMFhbasepattern-6"
                    patternUnits="userSpaceOnUse"
                    width="6"
                    height="6"
                    x="0"
                    y="0" />
        </defs>
        <sodipodi:namedview
                id="base"
                pagecolor="#ffffff"
                bordercolor="#666666"
                borderopacity="1.0"
                inkscape:pageopacity="0.0"
                inkscape:pageshadow="2"
                inkscape:zoom="0.7"
                inkscape:cx="571.61886"
                inkscape:cy="297.34333"
                inkscape:document-units="mm"
                inkscape:current-layer="layer1-3"
                showgrid="false"
                inkscape:window-width="1366"
                inkscape:window-height="648"
                inkscape:window-x="0"
                inkscape:window-y="27"
                inkscape:window-maximized="1"
                units="mm"
                fit-margin-top="0"
                fit-margin-left="0"
                fit-margin-right="0"
                fit-margin-bottom="0" />
        <metadata
                id="metadata5">
            <rdf:RDF>
                <cc:Work
                        rdf:about="">
                    <dc:format>image/svg+xml</dc:format>
                    <dc:type
                            rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
                    <dc:title></dc:title>
                </cc:Work>
            </rdf:RDF>
        </metadata>
        <g
                inkscape:label="Layer 1"
                inkscape:groupmode="layer"
                id="layer1"
                transform="translate(-0.74621092,-165.2916)">
            <g
                    transform="translate(2.3268872,55.527414)"
                    id="layer1-3"
                    inkscape:label="Layer 1">
                <path
                        sodipodi:nodetypes="cccccccc"
                        inkscape:connector-curvature="0"
                        d="m 158.13281,162.55612 h 121.70736 l 1.37016,-1.18118 V 131.51579 M 158.13281,165.5629 h 121.70736 l 4.37694,-4.18796 v -29.85915"
                        style="fill:none;stroke:#3bbded;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                        id="path1454-3-5-6-9" />
                <rect
                        ry="0"
                        y="113.97036"
                        x="34.66114"
                        height="8"
                        width="8"
                        id="rect1428"
                        style="opacity:1;fill:none;fill-opacity:1;stroke:#3bbded;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                <rect
                        ry="0"
                        y="115.09794"
                        x="8.7887487"
                        height="5"
                        width="11"
                        id="rect1428-1"
                        style="opacity:1;fill:none;fill-opacity:1;stroke:#3dbeed;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                <path
                        inkscape:connector-curvature="0"
                        id="path1454"
                        d="M 34.66114,116.09455 H 19.661139"
                        style="fill:none;stroke:#3bbded;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                <path
                        inkscape:connector-curvature="0"
                        id="path1454-3"
                        d="M 34.661141,119.10133 H 19.66114"
                        style="fill:none;stroke:#3bbded;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                <path
                        sodipodi:nodetypes="cccc"
                        inkscape:connector-curvature="0"
                        d="m 8.6611408,119.10133 h -9.991816 l -1e-6,-3.00678 h 9.991816"
                        style="fill:none;fill-opacity:1;stroke:#3bbded;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                        id="path1454-3-0" />
                <path
                        sodipodi:nodetypes="ccccccccccccccccccc"
                        inkscape:connector-curvature="0"
                        d="m 161.4178,116.79613 h 11.35631 v -3.00678 H 58.169066 l -4.382181,2.3052 H 42.66114 m 118.75666,0.70158 v 28.33007 l 1.24469,1.24469 h 9.96386 v 2.49759 h -10.82441 l -3.40769,-3.77519 v -10.12283 l -0.78643,-0.23436 V 116.79613 H 58.169066 l -4.334934,2.3052 H 42.66114"
                        style="fill:none;stroke:#3bbded;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                        id="path1685" />
                <rect
                        y="110.01418"
                        x="-1.1881109"
                        height="4.1104908"
                        width="4.3231025"
                        id="rect1689"
                        style="opacity:1;fill:#000000;fill-opacity:1;stroke:#000000;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                <path
                        sodipodi:nodetypes="cc"
                        inkscape:connector-curvature="0"
                        id="path1691"
                        d="M 3.1349908,110.36496 H 287.66796"
                        style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                <path
                        sodipodi:nodetypes="cc"
                        inkscape:connector-curvature="0"
                        id="path1693"
                        d="M 3.1349908,112.72027 H 287.66796"
                        style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                <rect
                        y="110.01418"
                        x="87.478554"
                        height="4.1104908"
                        width="4.3231025"
                        id="rect1689-7"
                        style="opacity:1;fill:#000000;fill-opacity:1;stroke:#000000;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                <rect
                        y="110.01418"
                        x="199.02826"
                        height="4.1104908"
                        width="4.3231025"
                        id="rect1689-7-3"
                        style="opacity:1;fill:#000000;fill-opacity:1;stroke:#000000;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                <rect
                        y="110.01418"
                        x="287.66797"
                        height="4.1104908"
                        width="4.3231025"
                        id="rect1689-7-3-2"
                        style="opacity:1;fill:#000000;fill-opacity:1;stroke:#000000;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                <rect
                        transform="scale(1,-1)"
                        y="-169.00131"
                        x="-1.1881109"
                        height="4.1104908"
                        width="4.3231025"
                        id="rect1689-71"
                        style="opacity:1;fill:#000000;fill-opacity:1;stroke:#000000;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                <path
                        sodipodi:nodetypes="cc"
                        inkscape:connector-curvature="0"
                        id="path1691-2"
                        d="M 3.1349948,168.65055 H 287.66796"
                        style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                <path
                        sodipodi:nodetypes="cc"
                        inkscape:connector-curvature="0"
                        id="path1693-5"
                        d="M 3.1349948,166.29523 H 287.66796"
                        style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                <rect
                        transform="scale(1,-1)"
                        y="-169.00131"
                        x="87.478554"
                        height="4.1104908"
                        width="4.3231025"
                        id="rect1689-7-39"
                        style="opacity:1;fill:#000000;fill-opacity:1;stroke:#000000;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                <rect
                        transform="scale(1,-1)"
                        y="-169.00131"
                        x="199.02826"
                        height="4.1104908"
                        width="4.3231025"
                        id="rect1689-7-3-25"
                        style="opacity:1;fill:#000000;fill-opacity:1;stroke:#000000;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                <rect
                        transform="scale(1,-1)"
                        y="-169.00131"
                        x="287.66794"
                        height="4.1104908"
                        width="4.3231025"
                        id="rect1689-7-3-2-9"
                        style="opacity:1;fill:#000000;fill-opacity:1;stroke:#000000;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                <path
                        inkscape:connector-curvature="0"
                        id="path1802"
                        d="M -0.5857122,164.89082 V 114.12467"
                        style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                <path
                        inkscape:connector-curvature="0"
                        id="path1802-7"
                        d="M 1.9774398,164.89081 V 114.12467"
                        style="fill:none;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                <rect
                        ry="0"
                        y="148.21631"
                        x="128.95886"
                        height="8"
                        width="8"
                        id="rect1428-4"
                        style="opacity:1;fill:none;fill-opacity:1;stroke:#3bbded;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                <path
                        sodipodi:nodetypes="cccccccc"
                        inkscape:connector-curvature="0"
                        d="m 128.95886,152.58248 h -3.56276 l -3.8275,4.10752 v 12.73133 h 3.00678 V 156.69 l 0.82072,-1.10074 h 3.56276"
                        style="fill:none;fill-opacity:1;stroke:#3bbded;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                        id="path1454-3-0-3" />
                <path
                        sodipodi:nodetypes="cccccccccccccc"
                        inkscape:connector-curvature="0"
                        d="m 136.95886,152.78432 h 3.56277 l 3.82749,4.10752 v 0.86052 3.96366 l 1.05236,0.8401 h 12.73133 v 3.00678 h -12.73133 l -4.05914,-3.99974 v -2.23984 -2.43148 l -0.82071,-1.10074 h -3.56277"
                        style="fill:none;fill-opacity:1;stroke:#3bbded;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                        id="path1454-3-0-3-8-8" />
                <path
                        status="0"
                        style="fill:#a2a2a2;fill-opacity:1;stroke:none;stroke-width:0.26458332;stroke-opacity:1"
                        inkscape:connector-curvature="0"
                        d="m 38.655427,117.58942 a 0.38095238,0.38095238 0 0 0 -0.380951,0.38095 0.38095238,0.38095238 0 0 0 0.380951,0.38095 0.38095238,0.38095238 0 0 0 0.38095,-0.38095 0.38095238,0.38095238 0 0 0 -0.38095,-0.38095 m 0.19048,-3.42857 c 1.71428,0 1.75619,1.36 0.85714,1.80952 -0.37714,0.18667 -0.54476,0.58667 -0.61714,0.94095 0.18285,0.0762 0.34285,0.19429 0.46476,0.34667 1.40952,-0.7619 2.92571,-0.46095 2.92571,0.90286 0,1.71428 -1.36,1.75238 -1.80952,0.84952 -0.19048,-0.37714 -0.59429,-0.54476 -0.94857,-0.61714 -0.0762,0.18285 -0.19429,0.33905 -0.34667,0.46857 0.7581,1.40571 0.45714,2.91809 -0.906667,2.91809 -1.714286,0 -1.748571,-1.3638 -0.849524,-1.81333 0.373334,-0.18667 0.540953,-0.58286 0.617143,-0.93333 -0.186666,-0.0762 -0.350475,-0.1981 -0.472381,-0.35048 -1.405713,0.75429 -2.914285,0.45714 -2.914285,-0.90286 0,-1.71428 1.35619,-1.75619 1.805715,-0.85333 0.190475,0.37714 0.590476,0.54095 0.944761,0.61333 0.07238,-0.18285 0.194286,-0.34285 0.350476,-0.46476 -0.758095,-1.40571 -0.457143,-2.91428 0.899052,-2.91428 z"
                        id="DC1.Ventilation.Fan1.Status"
                        active="true"
                        inkscape:label="#path1417-9-4" />
                <path
                        status="0"
                        style="fill:#a2a2a2;fill-opacity:1;stroke:none;stroke-width:0.27813733;stroke-opacity:1"
                        inkscape:connector-curvature="0"
                        d="m 132.94912,152.02769 a 0.42098274,0.38095238 0 0 0 -0.42098,0.38095 0.42098274,0.38095238 0 0 0 0.42098,0.38095 0.42098274,0.38095238 0 0 0 0.42099,-0.38095 0.42098274,0.38095238 0 0 0 -0.42099,-0.38095 m 0.2105,-3.42857 c 1.89442,0 1.94073,1.36 0.94721,1.80952 -0.41678,0.18667 -0.60201,0.58667 -0.682,0.94095 0.20208,0.0762 0.37889,0.19429 0.5136,0.34667 1.55764,-0.7619 3.23315,-0.46095 3.23315,0.90286 0,1.71428 -1.50291,1.75238 -1.99967,0.84952 -0.21049,-0.37714 -0.65673,-0.54476 -1.04824,-0.61714 -0.0842,0.18285 -0.21471,0.33905 -0.3831,0.46857 0.83776,1.40571 0.50518,2.91809 -1.00194,2.91809 -1.89442,0 -1.93231,-1.3638 -0.93879,-1.81333 0.41256,-0.18667 0.5978,-0.58286 0.68199,-0.93333 -0.20628,-0.0762 -0.3873,-0.1981 -0.52202,-0.35048 -1.55342,0.75429 -3.22051,0.45714 -3.22051,-0.90286 0,-1.71428 1.49869,-1.75619 1.99546,-0.85333 0.21049,0.37714 0.65252,0.54095 1.04403,0.61333 0.08,-0.18285 0.2147,-0.34285 0.38731,-0.46476 -0.83776,-1.40571 -0.50518,-2.91428 0.99352,-2.91428 z"
                        id="DC1.Ventilation.Fan2.Status"
                        active="true"
                        inkscape:label="#path1417-9-4-8"
                        inkscape:transform-center-x="-2.6726953"
                        inkscape:transform-center-y="0.80180859">
                    <title
                            id="title4051">active</title>
                </path>
                <path
                        status="0"
                        id="DC1.Ventilation.Heater1.Status"
                        active="true"
                        d="m 13.545975,119.18544 c 0,-0.14612 -0.11846,-0.26458 -0.26458,-0.26458 h -0.52917 c -0.14613,0 -0.26458,0.11846 -0.26458,0.26458 0,0.14613 0.11845,0.26459 0.26458,0.26459 h 0.52917 c 0.14612,0 0.26458,-0.11846 0.26458,-0.26459 z m 0,-1.05833 c 0,-0.14613 -0.11846,-0.26458 -0.26458,-0.26458 h -0.52917 c -0.14613,0 -0.26458,0.11845 -0.26458,0.26458 0,0.14613 0.11845,0.26458 0.26458,0.26458 h 0.52917 c 0.14612,0 0.26458,-0.11845 0.26458,-0.26458 m 0,-1.05833 c 0,-0.14613 -0.11846,-0.26459 -0.26458,-0.26459 h -0.52917 c -0.14613,0 -0.26458,0.11846 -0.26458,0.26459 0,0.14612 0.11845,0.26458 0.26458,0.26458 h 0.52917 c 0.14612,0 0.26458,-0.11846 0.26458,-0.26458 m 0,-1.05834 c 0,-0.14612 -0.11846,-0.26458 -0.26458,-0.26458 h -0.52917 c -0.14613,0 -0.26458,0.11846 -0.26458,0.26458 0,0.14613 0.11845,0.26459 0.26458,0.26459 h 0.52917 c 0.14612,0 0.26458,-0.11846 0.26458,-0.26459 m 0.52917,-1.05833 v 5.29167 h -2.11667 c 0,-1.81898 0,-3.72326 0,-5.29167 m 4.42495,4.59989 -0.58472,-0.381 -0.58209,0.381 h -0.003 l -0.81757,-0.52916 0.23813,-0.46302 0.58208,0.37571 0.58209,-0.37571 0.8202,0.52916 -0.23548,0.46302 m 0,-1.60072 -0.58472,-0.37571 -0.58209,0.37571 -0.003,-0.003 -0.81757,-0.52652 0.23813,-0.45773 0.58208,0.37571 0.58209,-0.37571 0.8202,0.52917 -0.23548,0.45773 m -0.0291,-1.5875 -0.57944,-0.37571 -0.58473,0.37571 v -0.003 l -0.8202,-0.52652 0.23812,-0.45773 0.58473,0.37571 0.58208,-0.37571 0.82021,0.52917 -0.24077,0.45773"
                        inkscape:connector-curvature="0"
                        style="fill:#a2a2a2;fill-opacity:1;stroke-width:0.26458332"
                        inkscape:label="#path2-4" />
                <text
                        xml:space="preserve"
                        style="font-style:normal;font-variant:normal;font-weight:400;font-size:3.86953115px;line-height:125%;font-family:Calibri;text-align:start;letter-spacing:0px;word-spacing:0px;text-anchor:start;fill:#000000;fill-opacity:1;stroke:none;stroke-width:0.26458332"
                        x="24.546215"
                        y="118.88091"
                        id="DC1.Ventilation.Heater1.Temperature1"
                        inkscape:label="#text1047"><tspan
                            style="stroke-width:0.26458332"
                            sodipodi:role="line"
                            x="24.546215"
                            y="118.88091"
                            id="tspan1045"><tspan
                                dx="0"
                                dy="0"
                                style="font-style:normal;font-variant:normal;font-weight:400;font-size:3.86953115px;font-family:Calibri;fill:#000000;stroke-width:0.26458332"
                                id="tspan1041">°C</tspan></tspan></text>
                <path
                        status="0"
                        d="m 25.692195,159.40124 -0.515235,0.79462 0.515235,0.80188 h -0.0036 l -0.722055,1.12481 -0.627715,-0.32656 0.515235,-0.80188 -0.515235,-0.79825 0.725682,-1.1248 0.627716,0.33018 m 2.177048,-0.0399 -0.515235,0.80188 0.515235,0.79825 -0.0036,0.004 -0.722055,1.12118 -0.627715,-0.32656 0.515235,-0.79825 -0.515235,-0.79825 0.725683,-1.12481 0.627715,0.32293 m 2.19519,0 -0.522491,0.80188 0.522491,0.79825 v 0.004 l -0.725682,1.12118 -0.634973,-0.32656 0.515235,-0.79825 -0.515235,-0.79825 0.725683,-1.12481 0.634972,0.32293 m -6.531144,6.9339 v -2.90273 a 0.7256827,0.7256827 0 0 1 0.725683,-0.72569 h 5.805461 a 0.7256827,0.7256827 0 0 1 0.725683,0.72569 v 2.90273 h -0.725683 v -0.72569 h -5.805461 v 0.72569 h -0.725683 m 1.451365,-2.90273 a 0.36284135,0.36284135 0 0 0 -0.362841,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.362841,0.36284 0.36284135,0.36284135 0 0 0 0.362842,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.362842,-0.36284 m 1.451366,0 a 0.36284135,0.36284135 0 0 0 -0.362842,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.362842,0.36284 0.36284135,0.36284135 0 0 0 0.362841,-0.36284 v -0.72568 A 0.36284135,0.36284135 0 0 0 26.43602,163.3925 m 1.451365,0 a 0.36284135,0.36284135 0 0 0 -0.362841,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.362841,0.36284 0.36284135,0.36284135 0 0 0 0.362842,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.362842,-0.36284 m 1.451366,0 a 0.36284135,0.36284135 0 0 0 -0.362842,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.362842,0.36284 0.36284135,0.36284135 0 0 0 0.362841,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.362841,-0.36284 z"
                        id="DC1.Heating.Heater2.Status"
                        active="true"
                        style="fill:#a2a2a2;fill-opacity:1;stroke-width:0.36284134"
                        inkscape:connector-curvature="0"
                        inkscape:label="#path2" />
                <path
                        status="0"
                        d="m 154.9775,120.91719 -0.79462,-0.51524 -0.80188,0.51524 v -0.004 l -1.12481,-0.72206 0.32656,-0.62771 0.80188,0.51523 0.79825,-0.51523 1.1248,0.72568 -0.33018,0.62772 m 0.0399,2.17704 -0.80188,-0.51523 -0.79825,0.51523 -0.004,-0.004 -1.12118,-0.72206 0.32656,-0.62771 0.79825,0.51523 0.79825,-0.51523 1.12481,0.72568 -0.32293,0.62771 m 0,2.19519 -0.80188,-0.52249 -0.79825,0.52249 h -0.004 l -1.12118,-0.72568 0.32656,-0.63497 0.79825,0.51523 0.79825,-0.51523 1.12481,0.72568 -0.32293,0.63497 m -6.9339,-6.53114 h 2.90273 a 0.7256827,0.7256827 0 0 1 0.72569,0.72568 v 5.80546 a 0.7256827,0.7256827 0 0 1 -0.72569,0.72569 h -2.90273 v -0.72569 h 0.72569 v -5.80546 h -0.72569 v -0.72568 m 2.90273,1.45137 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36285 h -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,0.36285 0.36284135,0.36284135 0 0 0 0.36284,0.36284 h 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 m 0,1.45136 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 h -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 0.36284135,0.36284135 0 0 0 0.36284,0.36284 h 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 m 0,1.45137 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36285 h -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,0.36285 0.36284135,0.36284135 0 0 0 0.36284,0.36284 h 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 m 0,1.45136 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 h -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 0.36284135,0.36284135 0 0 0 0.36284,0.36284 h 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 z"
                        id="DC1.Heating.Heater3.Status"
                        active="true"
                        style="fill:#a2a2a2;fill-opacity:1;stroke-width:0.36284134"
                        inkscape:connector-curvature="0"
                        inkscape:label="#path2-78" />
                <path
                        status="0"
                        d="m 187.54089,159.40124 -0.51524,0.79462 0.51524,0.80188 h -0.004 l -0.72206,1.12481 -0.62771,-0.32656 0.51523,-0.80188 -0.51523,-0.79825 0.72568,-1.1248 0.62772,0.33018 m 2.17704,-0.0399 -0.51523,0.80188 0.51523,0.79825 -0.004,0.004 -0.72206,1.12118 -0.62771,-0.32656 0.51523,-0.79825 -0.51523,-0.79825 0.72568,-1.12481 0.62771,0.32293 m 2.19519,0 -0.52249,0.80188 0.52249,0.79825 v 0.004 l -0.72568,1.12118 -0.63497,-0.32656 0.51523,-0.79825 -0.51523,-0.79825 0.72568,-1.12481 0.63497,0.32293 m -6.53114,6.9339 v -2.90273 a 0.7256827,0.7256827 0 0 1 0.72568,-0.72569 h 5.80546 a 0.7256827,0.7256827 0 0 1 0.72569,0.72569 v 2.90273 h -0.72569 v -0.72569 h -5.80546 v 0.72569 h -0.72568 m 1.45137,-2.90273 a 0.36284135,0.36284135 0 0 0 -0.36285,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36285,0.36284 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 m 1.45136,0 a 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,0.36284 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 m 1.45137,0 a 0.36284135,0.36284135 0 0 0 -0.36285,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36285,0.36284 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 m 1.45136,0 a 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,0.36284 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 z"
                        id="DC1.Heating.Heater4.Status"
                        active="true"
                        style="fill:#a2a2a2;fill-opacity:1;stroke-width:0.36284134"
                        inkscape:connector-curvature="0"
                        inkscape:label="#path2-54" />
                <path
                        status="0"
                        d="m 248.84244,159.40124 -0.51524,0.79462 0.51524,0.80188 h -0.004 l -0.72206,1.12481 -0.62771,-0.32656 0.51523,-0.80188 -0.51523,-0.79825 0.72568,-1.1248 0.62772,0.33018 m 2.17704,-0.0399 -0.51523,0.80188 0.51523,0.79825 -0.004,0.004 -0.72206,1.12118 -0.62771,-0.32656 0.51523,-0.79825 -0.51523,-0.79825 0.72568,-1.12481 0.62771,0.32293 m 2.19519,0 -0.52249,0.80188 0.52249,0.79825 v 0.004 l -0.72568,1.12118 -0.63497,-0.32656 0.51523,-0.79825 -0.51523,-0.79825 0.72568,-1.12481 0.63497,0.32293 m -6.53114,6.9339 v -2.90273 a 0.7256827,0.7256827 0 0 1 0.72568,-0.72569 h 5.80546 a 0.7256827,0.7256827 0 0 1 0.72569,0.72569 v 2.90273 h -0.72569 v -0.72569 h -5.80546 v 0.72569 h -0.72568 m 1.45137,-2.90273 a 0.36284135,0.36284135 0 0 0 -0.36285,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36285,0.36284 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 m 1.45136,0 a 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,0.36284 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 m 1.45137,0 a 0.36284135,0.36284135 0 0 0 -0.36285,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36285,0.36284 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 m 1.45136,0 a 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,0.36284 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 z"
                        id="DC1.Heating.Heater5.Status"
                        active="true"
                        style="fill:#a2a2a2;fill-opacity:1;stroke-width:0.36284134"
                        inkscape:connector-curvature="0"
                        inkscape:label="#path2-54-0" />
                <path
                        status="0"
                        d="m 190.46226,119.89494 0.51524,-0.79462 -0.51524,-0.80188 h 0.004 l 0.72206,-1.12481 0.62771,0.32656 -0.51523,0.80188 0.51523,0.79825 -0.72568,1.1248 -0.62772,-0.33018 m -2.17704,0.0399 0.51523,-0.80188 -0.51523,-0.79825 0.004,-0.004 0.72206,-1.12118 0.62771,0.32656 -0.51523,0.79825 0.51523,0.79825 -0.72568,1.12481 -0.62771,-0.32293 m -2.19519,0 0.52249,-0.80188 -0.52249,-0.79825 v -0.004 l 0.72568,-1.12118 0.63497,0.32656 -0.51523,0.79825 0.51523,0.79825 -0.72568,1.12481 -0.63497,-0.32293 m 6.53114,-6.9339 v 2.90273 a 0.7256827,0.7256827 0 0 1 -0.72568,0.72569 h -5.80546 a 0.7256827,0.7256827 0 0 1 -0.72569,-0.72569 v -2.90273 h 0.72569 v 0.72569 h 5.80546 v -0.72569 h 0.72568 m -1.45137,2.90273 a 0.36284135,0.36284135 0 0 0 0.36285,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36285,-0.36284 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,0.36284 m -1.45136,0 a 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,0.36284 m -1.45137,0 a 0.36284135,0.36284135 0 0 0 0.36285,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36285,-0.36284 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,0.36284 m -1.45136,0 a 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,0.36284 z"
                        id="DC1.Heating.Heater6.Status"
                        active="true"
                        style="fill:#a2a2a2;fill-opacity:1;stroke-width:0.36284134"
                        inkscape:connector-curvature="0"
                        inkscape:label="#path2-54-9" />
                <path
                        status="0"
                        d="m 251.5982,119.89494 0.51524,-0.79462 -0.51524,-0.80188 h 0.004 l 0.72206,-1.12481 0.62771,0.32656 -0.51523,0.80188 0.51523,0.79825 -0.72568,1.1248 -0.62772,-0.33018 m -2.17704,0.0399 0.51523,-0.80188 -0.51523,-0.79825 0.004,-0.004 0.72206,-1.12118 0.62771,0.32656 -0.51523,0.79825 0.51523,0.79825 -0.72568,1.12481 -0.62771,-0.32293 m -2.19519,0 0.52249,-0.80188 -0.52249,-0.79825 v -0.004 l 0.72568,-1.12118 0.63497,0.32656 -0.51523,0.79825 0.51523,0.79825 -0.72568,1.12481 -0.63497,-0.32293 m 6.53114,-6.9339 v 2.90273 a 0.7256827,0.7256827 0 0 1 -0.72568,0.72569 h -5.80546 a 0.7256827,0.7256827 0 0 1 -0.72569,-0.72569 v -2.90273 h 0.72569 v 0.72569 h 5.80546 v -0.72569 h 0.72568 m -1.45137,2.90273 a 0.36284135,0.36284135 0 0 0 0.36285,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36285,-0.36284 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,0.36284 m -1.45136,0 a 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,0.36284 m -1.45137,0 a 0.36284135,0.36284135 0 0 0 0.36285,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36285,-0.36284 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,0.36284 m -1.45136,0 a 0.36284135,0.36284135 0 0 0 0.36284,-0.36284 v -0.72568 a 0.36284135,0.36284135 0 0 0 -0.36284,-0.36284 0.36284135,0.36284135 0 0 0 -0.36284,0.36284 v 0.72568 a 0.36284135,0.36284135 0 0 0 0.36284,0.36284 z"
                        id="DC1.Heating.Heater7.Status"
                        active="true"
                        style="fill:#a2a2a2;fill-opacity:1;stroke-width:0.36284134"
                        inkscape:connector-curvature="0"
                        inkscape:label="#path2-54-0-1" />
                <text
                        xml:space="preserve"
                        style="font-style:normal;font-variant:normal;font-weight:400;font-size:3.86953115px;line-height:125%;font-family:Calibri;text-align:start;letter-spacing:0px;word-spacing:0px;text-anchor:start;fill:#000000;fill-opacity:1;stroke:none;stroke-width:0.26458332"
                        x="232.22052"
                        y="126.46344"
                        id="DC1.IT.Main.Temperature3"
                        inkscape:label="#text1047-6-7"><tspan
                            style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:8.46666622px;font-family:Calibri;-inkscape-font-specification:'Calibri, Normal';font-variant-ligatures:normal;font-variant-caps:normal;font-variant-numeric:normal;font-feature-settings:normal;text-align:start;writing-mode:lr-tb;text-anchor:start;stroke-width:0.26458332"
                            sodipodi:role="line"
                            x="232.22052"
                            y="126.46344"
                            id="tspan1045-6-5"><tspan
                                dx="0 0 0"
                                dy="0 0 0"
                                style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:8.46666622px;font-family:Calibri;-inkscape-font-specification:'Calibri, Normal';font-variant-ligatures:normal;font-variant-caps:normal;font-variant-numeric:normal;font-feature-settings:normal;text-align:start;writing-mode:lr-tb;text-anchor:start;fill:#000000;stroke-width:0.26458332"
                                id="tspan1039-3-9">°C </tspan></tspan></text>
                <rect
                        y="131.70056"
                        x="2.9696229"
                        height="26.458332"
                        width="14.363094"
                        id="rect1319"
                        style="opacity:0.98999999;fill:none;stroke:#000000;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                <rect
                        y="131.70056"
                        x="17.332718"
                        height="26.458334"
                        width="14.363095"
                        id="rect1319-3"
                        style="opacity:0.98999999;fill:none;stroke:#000000;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                <rect
                        y="131.70056"
                        x="31.695812"
                        height="26.458334"
                        width="14.363095"
                        id="rect1319-2"
                        style="opacity:0.98999999;fill:none;stroke:#000000;stroke-width:0.5;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                <rect
                        y="131.7368"
                        x="46.095139"
                        height="21.944651"
                        width="22.700602"
                        id="rect1319-2-8"
                        style="opacity:0.98999999;fill:none;stroke:#000000;stroke-width:0.57246345;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />
                <rect
                        ry="0"
                        y="119.10133"
                        x="48.708691"
                        height="8"
                        width="5.1254406"
                        id="rect1428-8"
                        style="opacity:1;fill:none;fill-opacity:1;stroke:#3bbded;stroke-width:0.40021247;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1" />
                <g
                        active="1"
                        id="DC1.Ventilation.Valve1.Status"
                        transform="matrix(0.26458333,0,0,0.26458333,58.84698,112.92014)">
                    <g
                            status="0"
                            id="g934">
                        <path
                                inkscape:connector-curvature="0"
                                style="fill:none;stroke:#a2a2a2;stroke-width:1.88976383;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                                d="m -20.075703,43.305618 v -19"
                                id="path870" />
                        <path
                                inkscape:connector-curvature="0"
                                style="fill:none;stroke:#a2a2a2;stroke-width:1.88976383;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                                d="m -39.075703,43.305618 v -19"
                                id="path829-3" />
                    </g>
                    <g
                            transform="translate(-26.411699,-26.319696)"
                            status="1"
                            id="g930">
                        <rect
                                style="opacity:1;fill:#3bbded;fill-opacity:1;stroke:none;stroke-width:1.51181102;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                                id="rect825-8"
                                width="4.5762711"
                                height="4.5762711"
                                x="-46.99852"
                                y="37.94767"
                                transform="rotate(-45)" />
                        <path
                                style="fill:none;stroke:#3bbded;stroke-width:1.88976383;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                                d="m -2.9346567,69.395967 v -19"
                                id="path827-1"
                                inkscape:connector-curvature="0" />
                        <path
                                inkscape:connector-curvature="0"
                                style="fill:none;stroke:#3bbded;stroke-width:1.88976383;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                                d="m 6.3359971,69.625314 v -19"
                                id="path866-3" />
                        <path
                                inkscape:connector-curvature="0"
                                style="fill:none;stroke:#3bbded;stroke-width:1.88976383;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                                d="m -12.664004,69.625314 v -19"
                                id="path829-3-7-1" />
                    </g>
                    <g
                            transform="translate(-39.694348,0.17005954)"
                            status="2"
                            id="g924">
                        <rect
                                style="opacity:1;fill:#3bbded;fill-opacity:1;stroke:none;stroke-width:1.51181102;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                                id="rect825"
                                width="4.5762711"
                                height="4.5762711"
                                x="28.608835"
                                y="14.298912"
                                transform="rotate(45)" />
                        <path
                                style="fill:none;stroke:#3bbded;stroke-width:1.88976383;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                                d="M 0.78870392,33.805618 H 19.788704"
                                id="path827"
                                inkscape:connector-curvature="0" />
                        <path
                                inkscape:connector-curvature="0"
                                style="fill:none;stroke:#3bbded;stroke-width:1.88976383;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                                d="m 19.788705,43.305618 v -19"
                                id="path866" />
                        <path
                                inkscape:connector-curvature="0"
                                style="fill:none;stroke:#3bbded;stroke-width:1.88976383;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                                d="m 0.78870392,43.305618 v -19"
                                id="path829-3-7" />
                    </g>
                    <g
                            status="16"
                            id="g1007-0"
                            transform="rotate(-90,-15.620346,83.061023)">
                        <path
                                style="fill:none;stroke:#a2a2a2;stroke-width:1.88976383;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                                d="m 29.329836,60.930363 9.500001,16.454481"
                                id="path969-4"
                                inkscape:connector-curvature="0" />
                        <path
                                style="fill:none;stroke:#a2a2a2;stroke-width:1.88976383;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:3.77952766, 3.77952766;stroke-dashoffset:0;stroke-opacity:1"
                                d="M 24.364406,69.335013 H 43.364407"
                                id="path971-2"
                                inkscape:connector-curvature="0" />
                        <path
                                id="path985-4"
                                style="fill:#a2a2a2;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                                d="m 27.319738,64.411955 h 4.020196 l -2.010098,-3.481592 z"
                                inkscape:connector-curvature="0"
                                sodipodi:nodetypes="cccc" />
                        <path
                                id="path985-1-0"
                                style="fill:#a2a2a2;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                                d="m 40.839935,73.903252 -2.010098,3.481592 -2.010098,-3.481592 z"
                                inkscape:connector-curvature="0"
                                sodipodi:nodetypes="cccc" />
                    </g>
                    <g
                            transform="translate(-63.388172,-35.974173)"
                            status="32"
                            id="g1007">
                        <path
                                style="fill:none;stroke:#a2a2a2;stroke-width:1.88976383;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
                                d="m 29.329836,60.930363 9.500001,16.454481"
                                id="path969"
                                inkscape:connector-curvature="0" />
                        <path
                                style="fill:none;stroke:#a2a2a2;stroke-width:1.88976383;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:3.77952766, 3.77952766;stroke-dashoffset:0;stroke-opacity:1"
                                d="M 24.364406,69.335013 H 43.364407"
                                id="path971"
                                inkscape:connector-curvature="0" />
                        <path
                                id="path985"
                                style="fill:#a2a2a2;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                                d="m 27.319738,64.411955 h 4.020196 l -2.010098,-3.481592 z"
                                inkscape:connector-curvature="0"
                                sodipodi:nodetypes="cccc" />
                        <path
                                id="path985-1"
                                style="fill:#a2a2a2;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                                d="m 40.839935,73.903252 -2.010098,3.481592 -2.010098,-3.481592 z"
                                inkscape:connector-curvature="0"
                                sodipodi:nodetypes="cccc" />
                    </g>
                    <path
                            style="fill:#ff0000;fill-opacity:1;stroke-width:0.528992"
                            id="path2-72"
                            d="m -28.850931,33.975678 h -1.057984 V 31.85971 h 1.057984 m 0,4.231936 h -1.057984 v -1.057984 h 1.057984 m -6.347904,2.644959 h 11.637823 l -5.818911,-10.050847 z"
                            inkscape:connector-curvature="0"
                            status="4" />
                </g>
                <text
                        xml:space="preserve"
                        style="font-style:normal;font-variant:normal;font-weight:400;font-size:3.86953115px;line-height:125%;font-family:Calibri;text-align:start;letter-spacing:0px;word-spacing:0px;text-anchor:start;fill:#000000;fill-opacity:1;stroke:none;stroke-width:0.26458332"
                        x="79.63298"
                        y="126.59428"
                        id="DC1.Ventilation.Valve1.Position"
                        inkscape:label="#text1047-6-0"><tspan
                            style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:8.46666622px;font-family:Calibri;-inkscape-font-specification:'Calibri, Normal';font-variant-ligatures:normal;font-variant-caps:normal;font-variant-numeric:normal;font-feature-settings:normal;text-align:start;writing-mode:lr-tb;text-anchor:start;stroke-width:0.26458332"
                            sodipodi:role="line"
                            x="79.63298"
                            y="126.59428"
                            id="tspan1045-6-4"><tspan
                                dx="0 0"
                                dy="0 0"
                                style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:8.46666622px;font-family:Calibri;-inkscape-font-specification:'Calibri, Normal';font-variant-ligatures:normal;font-variant-caps:normal;font-variant-numeric:normal;font-feature-settings:normal;text-align:start;writing-mode:lr-tb;text-anchor:start;fill:#000000;stroke-width:0.26458332"
                                id="tspan1043-9-76">% </tspan></tspan></text>
                <text
                        xml:space="preserve"
                        style="font-style:normal;font-variant:normal;font-weight:400;font-size:3.86953115px;line-height:125%;font-family:Calibri;text-align:start;letter-spacing:0px;word-spacing:0px;text-anchor:start;fill:#000000;fill-opacity:1;stroke:none;stroke-width:0.26458332"
                        x="231.5927"
                        y="158.83154"
                        id="DC1.IT.Main.Temperature4"
                        inkscape:label="#text1047-6-7-6"><tspan
                            style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:8.46666622px;font-family:Calibri;-inkscape-font-specification:'Calibri, Normal';font-variant-ligatures:normal;font-variant-caps:normal;font-variant-numeric:normal;font-feature-settings:normal;text-align:start;writing-mode:lr-tb;text-anchor:start;stroke-width:0.26458332"
                            sodipodi:role="line"
                            x="231.5927"
                            y="158.83154"
                            id="tspan1045-6-5-5"><tspan
                                dx="0 0 0"
                                dy="0 0 0"
                                style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:8.46666622px;font-family:Calibri;-inkscape-font-specification:'Calibri, Normal';font-variant-ligatures:normal;font-variant-caps:normal;font-variant-numeric:normal;font-feature-settings:normal;text-align:start;writing-mode:lr-tb;text-anchor:start;fill:#000000;stroke-width:0.26458332"
                                id="tspan1039-3-9-6">°C </tspan></tspan></text>
                <text
                        xml:space="preserve"
                        style="font-style:normal;font-variant:normal;font-weight:400;font-size:3.86953115px;line-height:125%;font-family:Calibri;text-align:start;letter-spacing:0px;word-spacing:0px;text-anchor:start;fill:#000000;fill-opacity:1;stroke:none;stroke-width:0.26458332"
                        x="97.707527"
                        y="153.51204"
                        id="DC1.Engineering.Main.Temperature2"
                        inkscape:label="#text1047-6-7-6-6"><tspan
                            style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:8.46666622px;font-family:Calibri;-inkscape-font-specification:'Calibri, Normal';font-variant-ligatures:normal;font-variant-caps:normal;font-variant-numeric:normal;font-feature-settings:normal;text-align:start;writing-mode:lr-tb;text-anchor:start;stroke-width:0.26458332"
                            sodipodi:role="line"
                            x="97.707527"
                            y="153.51204"
                            id="tspan1045-6-5-5-7"><tspan
                                dx="0 0 0"
                                dy="0 0 0"
                                style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:8.46666622px;font-family:Calibri;-inkscape-font-specification:'Calibri, Normal';font-variant-ligatures:normal;font-variant-caps:normal;font-variant-numeric:normal;font-feature-settings:normal;text-align:start;writing-mode:lr-tb;text-anchor:start;fill:#000000;stroke-width:0.26458332"
                                id="tspan1039-3-9-6-2">°C </tspan></tspan></text>
            </g>
        </g>
    </svg>
    <!--svg-->
<!--     <div id="about_elem">
        <input class="modal__check" type="checkbox" id="modal"/>
        <div class="modal">
            <label class="modal__closetwo" for="modal"></label>
            <div class="modal__info">
                <label class="modal__close" for="modal">&times;</label>
                <svg id="show_elem" width="100" height="100">
                    <use id="use_svg" x="0" y="0"/>
                </svg>
                <h1>Обозначение:</h1>
                <h3 id="li_designation" ></h3>
                <h1>Имя:</h1>
                <h3 id="li_name" ></h3>
                <h1>Значение:</h1>
                <h3 id="li_value" >Нет данных</h3>
                <h1>Вкл/Выкл</h1>
                <label class="switch">
                    <input type="checkbox">
                    <span class="slider round"></span>
                </label>
            </div>
        </div>

        <label style="opacity: 0;" for="modal" class="button">Open modal</label>
    </div> -->
</div>
<script src="//cdnjs.cloudflare.com/ajax/libs/sockjs-client/0.3.4/sockjs.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/stomp.js/2.3.3/stomp.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js" type="text/javascript"></script>
<script>
    // mqttConnect('172.17.0.3');
</script>
</body>
</html>
<?php
    // echo "
    //     <script>
    //     window.all_elems = ". json_encode(getFromXlsx()[0]) .";
    //     </script>";
?>