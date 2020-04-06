<?php
// Параметры соединения с базой данных
$db_server = 'localhost';
$db_name = '*****';
$db_user = '*****';
$db_passwd = '******';

// Подключение к базе данных
$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);
if (!$db_connection || !@mysql_select_db($db_name, $db_connection))
{
        echo "Error during SQL connection";
}
mysql_select_db($db_name, $db_connection);

// Сохранение в базу переданных с контроллера данных
if(isset($_GET['t1']) && isset($_GET['t2']) && isset($_GET['t3']) && isset($_GET['t4'])) {
        $t1 = floatval($_GET['t1']);
        $t2 = floatval($_GET['t2']);
        $t3 = floatval($_GET['t3']);
        $t4 = floatval($_GET['t4']);
        if($t1>0 && $t2>0 && $t3>0&& $t4>0) {
                $result = mysql_query("
                        INSERT INTO `temperatures` (datatime,t1,t2,t3,t4) 
                        VALUES (now(), $t1, $t2, $t3, $t4)");
                echo mysql_error();     
                
                // Если температуры ниже минимума, отправим сообщение в Telegram
                if($t4<40 || $t2<35 || $t1<50 || $t3<50) {
                        include 'tele.php';
                        message_to_telegram("ГВС_П: $t4, ГВС_О: $t2, Котельная_П: $t1, Котельная_О: $t3");
                }
        }
        
        die();
}
?>

<html>
<head>
        <title>Температурные данные дома №*** по ул.*********</title>
        <link href="/temp/style.css" rel="stylesheet">
        <link href="/temp/jquery.datetimepicker.min.css" rel="stylesheet">
        <script type="text/javascript" src="/temp/jquery-3.3.1.min.js"></script>
        <script type="text/javascript" src="/temp/moment.js"></script>
        <script type="text/javascript" src="/temp/jquery.datetimepicker.full.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body> 
        <div class='current row'>
                <div class='col time'></div>
                <div class='t1 col'>ГВС, подача:
 <b></b></div>
                <div class='t2 col'>ГВС, обратка:
 <b></b></div>
                <div class='t3 col'>Котельная, подача:
 <b></b></div>
                <div class='t4 col'>Котельная, обратка:
 <b></b></div>
        </div> 
        <div class="row selectperiod">
                <div class="col"><input type="text" id="datastart" value="" class="form-control"/></div>
                <div class="col"><input type="text" id="dataend" value="" class="form-control"/></div>
                <div class="col">Точность: <select id="intervalMinutes">
                        <option value="1">1 мин</option>
                        <option value="5">5 мин</option>
                        <option value="15">15 мин</option>
                        <option value="30" selected>30 мин</option>
                        <option value="6">1 час</option>
                        </select>
                </div>
        </div>  
        <div id="chartContainer" style="position: relative; height:40vh; width:90vw;margin: 0 auto;">
                <canvas id="myChart"></canvas>
        </div>
        <script>
        // Создание и отрисовка графика
        function getChart(dataFromJSON) {
                var dates = [], t1arr = [], t2arr = [], t3arr = [], t4arr = [];
                dataFromJSON.forEach(function(item, i, arr) {
                        dates.push(item.datatime);
                        t1arr.push(item.t1);
                        t2arr.push(item.t2);
                        t3arr.push(item.t3);
                        t4arr.push(item.t4);
                });
        
                var ctx = document.getElementById("myChart").getContext('2d');
                window.myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                                labels: dates,
                                datasets: [{
                                        label: 'ГВС, подача',
                                        data: t1arr,
                                        borderColor: '#ffc700',
                                        backgroundColor: 'rgba(255,255,255,0)'
                                },{
                                        label: 'ГВС, обратка',
                                        data: t2arr,
                                        borderColor: '#3bd100',
                                        backgroundColor: 'rgba(255,255,255,0)'
                                },{
                                        label: 'Контур котельной, подача',
                                        data: t3arr,
                                        borderColor: '#ff0000',
                                        backgroundColor: 'rgba(255,255,255,0)'
                                },{
                                        label: 'Контур котельной, обратка',
                                        data: t4arr,
                                        borderColor: '#ee00ff',
                                        backgroundColor: 'rgba(255,255,255,0)'
                                }]
                        },
                        options: {
                                responsive: true,
                                //maintainAspectRatio: false,
                                legend: {
                                        display: false
                                },
                                scales: {
                                        xAxes: [{
                                                type: 'time',
                                                time: {
                                                        unit: 'minute'
                                                }
                                        }]
                                }
                        }
                });
        }
        
        // Обновление графика данными
        function updateChart(dataFromJSON) {
                var dates = [], t1arr = [], t2arr = [], t3arr = [], t4arr = [];
                dataFromJSON.forEach(function(item, i, arr) {
                        dates.push(item.datatime);
                        t1arr.push(item.t1);
                        t2arr.push(item.t2);
                        t3arr.push(item.t3);
                        t4arr.push(item.t4);
                });
                
                window.myChart.data.labels = dates;
                window.myChart.data.datasets[0].data = t1arr;
                window.myChart.data.datasets[1].data = t2arr;
                window.myChart.data.datasets[2].data = t3arr;
                window.myChart.data.datasets[3].data = t4arr;
                window.myChart.update();
        }
        
        // Получение данных для графика
        function doUpdateChart() {
                $.getJSON( "путь к файлу/json.php?datastart="+$("#datastart").val()+"&dataend="+$("#dataend").val()+"&intervalMinutes="+$("#intervalMinutes").val(),
                        function( data ) {
                                updateChart(data);
                        });
        }
        
        // Получение текущих данных
        function doUpdateLastValues() {
                $.getJSON( "путь к файлу/json.php?last=true",
                        function( data ) {
                                $(".time").html((data[0].datatime+'').replace(' ', '
'));
                                $(".t1 b").text(data[0].t1+"°C");
                                $(".t2 b").text(data[0].t2+"°C");
                                $(".t3 b").text(data[0].t3+"°C");
                                $(".t4 b").text(data[0].t4+"°C");
                        });
        }
                
        $(function() {
                // Выбор дат
                $.datetimepicker.setLocale('ru');
                $('#datastart,#dataend').datetimepicker({
                  format:'d.m.Y H:i',
                  lang:'ru',
                  minDate:'20.11.2018',
                  maxDate:Date.now(),
                  formatDate:'d.m.Y',
                  dayOfWeekStart: 1,
                  i18n:{
                          ru:{
                           months:[
                                'Январь','Февраль','Март','Апрель',
                                'Май','Июнь','Июль','Август',
                                'Сентябрь','Октябрь','Ноябрь','Декабрь',
                           ],
                           dayOfWeek:[
                                "Вс", "Пн", "Вт", "Ср", 
                                "Чт", "Пт", "Сб",
                           ]
                          }
                         },
                });
                                
                // Загрузка текущих данных
                doUpdateLastValues();
                // Заполнение графика
                $.getJSON( "путь к файлу/json.php?datastart="+$("#datastart").val()+"&dataend="+$("#dataend").val()+"&intervalMinutes="+$("#intervalMinutes").val(),
                        function( data ) {
                                getChart(data);
                        });
                                        
                window.lastDataStart = $("#datastart").val();
                window.lastDataEnd = $("#dataend").val();
                window.intervalMinutes = $("#intervalMinutes").val();
                var winHeight = $(window).height();
                setInterval(function() {
                        var changed = false;
                        if($("#datastart").val() != window.lastDataStart) {
                                changed = true;
                                window.lastDataStart = $("#datastart").val();
                        }
                        if($("#dataend").val() != window.lastDataEnd) {
                                changed = true;
                                window.lastDataEnd = $("#dataend").val();
                        }
                        if($("#intervalMinutes").val() != window.intervalMinutes) {
                                changed = true;
                                window.intervalMinutes = $("#intervalMinutes").val();
                        }
                        if(changed)
                                doUpdateChart();
                }, 1000);
                // Обновлять текущие данные раз в 30 сек
                setInterval(function() {
                        doUpdateLastValues();
                        doUpdateChart();
                }, 30000);
        });
        </script>

</body>
</html>
