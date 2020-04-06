<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Параметры соединения с базой данных
$db_server = 'localhost';
$db_name = '*****';
$db_user = '*****';
$db_passwd = '*****';

// Вычисление разницы дат
function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
{
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);   
    $interval = date_diff($datetime1, $datetime2);   
    return $interval->format($differenceFormat);   
}

// Подключение к базе данных
$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);
if (!$db_connection || !@mysql_select_db($db_name, $db_connection))
{
        echo "Error during SQL connection";
}
mysql_query("set names utf8");

// Если запрошены текущие данные
if(isset($_GET['last'])) {
        $result = mysql_query("
                        SELECT DATE_FORMAT(DATE_ADD(datatime, INTERVAL 1 HOUR),'%d.%m.%Y %H:%i:%s') datatime,t1 t3,t2 t2,t3 t4,t4 t1 FROM temperatures 
                        ORDER BY datatime DESC
                        LIMIT 1");
        $data = [];
        while($row = mysql_fetch_assoc($result)) {      
                        $data[] = $row;
        }
        // Выводим только их и заканчиваем на этом
        die(json_encode($data ));
}

$intervalMinutes = 30;
$datastart = date_add(date_create(date('Y-m-d H:i:s')), date_interval_create_from_date_string('-1 days'));
$dataend = date_add(date_create(date('Y-m-d H:i:s')), date_interval_create_from_date_string('1 hour'));
if(isset($_GET['intervalMinutes']))
        $intervalMinutes = intval($_GET['intervalMinutes']);

// Если слишком большой временной промежуток, то не будем наружать сервер большим объемом данных, округлим побольше
$difHours = date_diff($datastart, $dataend);
if($difHours->format('%days')>3) 
        $intervalMinutes = 60;
if($difHours->format('%days')>10) 
        $intervalMinutes = 180;
if(isset($_GET['datastart']) && $_GET['datastart']!="")
        $datastart = date_create($_GET['datastart']);
if(isset($_GET['dataend']) && $_GET['dataend']!="")
        $dataend = date_create($_GET['dataend']);
        


$result = mysql_query("
        SELECT roundeddatetime datatime, AVG(t1) t3,AVG(t2) t2,AVG(t3) t4,AVG(t4) t1 
        FROM
        (
                SELECT DATE_FORMAT(DATE_ADD(DATE_ADD(datatime, INTERVAL (".$intervalMinutes."-MINUTE(datatime)%".$intervalMinutes.") MINUTE), INTERVAL 1 HOUR),'%Y-%m-%d %H:%i:00') roundeddatetime,t1,t2,t3,t4 FROM temperatures 
                WHERE datatime>DATE_ADD('".$datastart->format('Y-m-d H:i:s')."', INTERVAL -1 HOUR) AND datatime<DATE_ADD('".$dataend->format('Y-m-d H:i:s')."', INTERVAL -1 HOUR)
        ) t1
        GROUP BY roundeddatetime
        LIMIT 3440");
$data = [];
while($row = mysql_fetch_assoc($result)) {      
        $data[] = $row;
}

echo json_encode($data );
?>
