/**
 * Отправка сообщений в RabbitMQ Ajax
 *
 * */
function ajax(data, url)
{
    var xhr = new XMLHttpRequest();
    xhr.withCredentials = true;
    xhr.open( "POST", url , true );
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    var dataArray = {};
    dataArray.ip = data;
    xhr.send(JSON.stringify(data));
    //Обработчик добавлен из-за неизвестной (на 25.11.2018) для меня ошибки потери данных в xhr
    xhr.addEventListener("load", transferComplete);
    function transferComplete(evt) 
    {
        console.log(xhr.status);
        // 4. Если код ответа сервера не 200, то это ошибка
        if (xhr.status != 200) 
        {
            // обработать ошибку
            alert(xhr.status + ': ' + xhr.statusText); // пример вывода: 404: Not Found
        } else 
        {
            console.log(xhr.responseText); // responseText -- текст ответа.
            ajax(xhr.responseText, 'http://localhost:8080/monitoring_zabbix_plugins.php');
        }
    }
}

/**
 * WebSocket соединеие с RabbitMQ нужно для вывода информации во frontend
 *
 * */
function mqttConnect(rabbitMQ_IP)
{
    getActiveObjects();
    //generateDivAndOl();
    var server_data = document.getElementById('temp_value');
    var wsbroker = rabbitMQ_IP;//location.hostname;  // mqtt websocket enabled broker
    var wsport = 15675; // port for above
    var client = new Paho.MQTT.Client(wsbroker, wsport, "/ws",
        '_dc_temp_frontend_', 10);
    client.onConnectionLost = function (responseObject) {
        console.log("CONNECTION LOST - " + responseObject.errorMessage);
    };
    client.onMessageArrived = function (message) {
        //console.log("RECEIVE ON " + message.destinationName + " PAYLOAD " + message.payloadString);
        var boof = JSON.parse(message.payloadString);
        //console.log(boof[0]);
            setDivConsumer(boof[0]);
        //renderLineFromMessage(boof[0]);
    };
    var options = {
        timeout: 10,
        keepAliveInterval: 30,
        onSuccess: function () {
            console.log("CONNECTION SUCCESS");
            client.subscribe('#', {qos: 1});
        },
        onFailure: function (message) {
            console.log("CONNECTION FAILURE - " + message.errorMessage);
        }
    };
    if (location.protocol == "https:") {
        options.useSSL = true;
    }
    console.log("CONNECT TO " + wsbroker + ":" + wsport);
    client.connect(options);
}

/**
 * Выводит сообщения по каждому элементу списком, !!!ОБЯЗАТЕЛЬНО!!! сначала создать ol элемент, затем записать
 * в него все li только потом ol записать в div, иначе пропускаются почти все элементы, не понятно почему
 *
 * */
function renderLineFromMessage(array) {
    var myDiv = document.getElementById('divMessageOut');
    var myList = document.createElement('ol');
    myList.setAttribute('id','olMessageOut');
    var render_flag = false;
    array.forEach(function (item, i, arr) {
        var li = document.getElementById(getKeyFromItem(item));
        if(li == null){
            li = document.createElement('li');
            li.setAttribute('id',getKeyFromItem(item));
            li.innerText = getKeyFromItem(item) + ' = ' + item[7];
            myList.appendChild(li);
            render_flag = true;
        }
        else
            li.innerText = getKeyFromItem(item) + ' = ' + item[7];
        //console.log(li);
        //console.log(getKeyFromItem(item));
    });
    if(render_flag == true)
        myDiv.appendChild(myList);
}

/**
 * Создание ключа из входного сообщения
 *
 * */
function getKeyFromItem(item) {
    return key = item[0].replace(/\.[\s]/g,'') + item[1].replace(/\.[\s]/g,'') + item[2].replace(/\.[\s]/g,'')
        + item[3].replace(/\.[\s]/g,'');
}

/**
 * Создание нужного div'a при загрузке страницы
 *
 * */
function generateDivAndOl() {
    var myDiv = document.createElement('div');
    myDiv.setAttribute('id','divMessageOut');
    document.body.appendChild(myDiv);
}

function getActiveObjects() {
    var g_array = document.getElementsByTagName("g");
    var path_array = document.getElementsByTagName("path");
    //console.log(path_array);
    for(var i = 0; i < g_array.length; i++){
        if(g_array[i].getAttribute("active") == true){
            g_array[i].style.cursor = 'pointer';
            g_array[i].onclick = renderThisControlPanel;
            g_array[i].setAttribute('class','button');
        }
    }
    for(var j = 0; j < path_array.length; j++){
        if(path_array[j].getAttribute("active") == "true"){
            path_array[j].style.cursor = 'pointer';
            path_array[j].onclick = renderThisControlPanel;
            path_array[j].setAttribute('class','button');
        }
    }
}

function renderThisControlPanel() {
    var show_elem = document.getElementById('show_elem');
    var use_svg = document.getElementById('use_svg');
    //use_svg.setAttributeNS('http://www.w3.org/1999/xlink', 'href', '#' + getSvgElem(this));
    use_svg.setAttribute('xmlns',"http://www.w3.org/2000/svg");
    use_svg.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
    use_svg.setAttribute('href','#' + getSvgElem(this));
    window.set_consumer = getSvgElem(this).replace(/\./g,'').toLowerCase();
    if(all_elems != undefined){
        var li_designation = document.getElementById('li_designation');
        var li_name = document.getElementById('li_name');
        for(var i = 0; i < all_elems.length; i++){
            if(getKey(all_elems[i]) == this.getAttribute('id')) {
                console.log(this.getAttribute('id'));
                li_designation.innerHTML = all_elems[i][5];
                li_name.innerHTML = all_elems[i][4];
            }
        }
    }
    document.getElementsByClassName('modal__check')[0].checked = true;
    //show_elem.innerHTML = "<use xlink:href='#" + this.getAttribute('id') + "' x='0' y='0'>";
}

// возвращает cookie с именем name, если есть, если нет, то undefined
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

function getSvgElem(elem) {
    if(elem.getElementsByTagName('g')[0] != undefined){
        elem = elem.getElementsByTagName('g')[0];
        //needElem = subGElem.getElementsByTagName('path')[0];
    }
    return elem.getAttribute('id');
}

function setDivConsumer(all_messages) {
    if(window.set_consumer  != undefined){
        var li_value = document.getElementById('li_value');
    }
    all_messages.forEach(function (item, i, arr) {
        if(getKeyFromItem(item).toLowerCase() == window.set_consumer ){
            // li_designation.innerHTML = item[5];
            // li_name.innerHTML = item[4];
            li_value.innerHTML = item[7];
        }
        setValueInMdcSvg(item);
        changeColorFromItem(item);
        elem = document.getElementById('DC1.Ventilation.Valve1.Status');
        if(elem.getAttribute('id').replace(/\./g,'').toLowerCase() == getKeyFromItem(item).toLowerCase()){
            showNeedElemStatus(item[7]);
        }

    });
}

function setValueInMdcSvg(message) {
    var all_text_places = document.getElementsByTagName('text');
    for(var i = 0; i < all_text_places.length; i++){
        if(all_text_places[i].getAttribute('id').replace(/\./g,'').toLowerCase() == getKeyFromItem(message).toLowerCase()){
            new_value = (message[8] == '%') ? message[7] + " " + message[8] : message[7] + " " + "°C";
            all_text_places[i].lastChild.lastChild.textContent = '';
            all_text_places[i].lastChild.lastChild.textContent = new_value;
        }
    }
}

function changeColorFromItem(message){
    elems = document.getElementsByTagName('path');
    for(var i = 0; i < elems.length; i++){
        if(elems[i].getAttribute('id').replace(/\./g,'').toLowerCase() == getKeyFromItem(message).toLowerCase()){
            if(message[7] == 0)
                elems[i].style.fill = "rgb(162, 162, 162)";
            if(message[7] == 1)
                elems[i].style.fill = "rgb(17, 232, 38)";
            if(message[7] == 2)
                elems[i].style.fill = "rgb(242, 242, 11)";
            if(message[7] == 4)
                elems[i].style.fill = "rgb(242, 57, 11)";
            if(message[7] == 16)
                elems[i].style.fill = "rgb(11, 57, 242)";
        }
    }
}

function showNeedElemStatus(status) {
    elem = document.getElementById('DC1.Ventilation.Valve1.Status');
    status = '00000011';
    for(var i = 0; i < elem.children.length; i++){
        if((status.toString(2) & elem.children[i].getAttribute('status').toString(2))
            == elem.children[i].getAttribute('status')){
            elem.children[i].style.display = "block";
        }
        else
            elem.children[i].style.display = "none";
    }
}

function getKey(item) {
    return item[0] + "." + item[1] + "." + item[2] + "." + item[3];
}

//Отобразить загрузку пока получаешь данные с сервера через Ajax
function loadServer(url) {
    // display loading image here...
    document.getElementById('loadingImg').visible = true;
    // request your data...
    var req = new XMLHttpRequest();
    req.open("POST", url, true);

    req.onreadystatechange = function () {
        if (req.readyState == 4 && req.status == 200) {
            // content is loaded...hide the gif and display the content...
            if (req.responseText) {
                document.getElementById('content').innerHTML = req.responseText;
                document.getElementById('loadingImg').visible = false;
            }
        }
    };
}

function test() {
    alert("test");
}