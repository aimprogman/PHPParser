<?php

require_once("../vendor/php-query.php");  // Библиотека для работы с DOM документам

/* Функция getUrlParameters для генерации url с параметрами
* $type - название элементов формы
* $value  - значение элементов формы
*/

function getUrlParameters($type, $value) // Смог бы вывести эту функцию в другой файл как service
{
    $result = "";

    switch ($type) {
        case "price_from":
            $result = "&price[from]=$value";
            break;
        case "price_to":
            $result = "&price[to]=$value";
            break;
        case "rooms":
            foreach ($value as $room) {
                $result = $result . "&rooms[]=$room";
            }
            break;
        case "only_photo":
            $result = $value == "on" ? 1 : 0;
            $result = "&only_photo=$result";
            break;
    }
    return $result;
}

//// Объявление и инициализация переменных
$property = "";

$urlParameters = array(); // Массив используется для хранение параметров url

$viewModel = new stdClass(); // Переменная Object используется для передачи данных в View

$viewModel->price_from = "";

$viewModel->price_to = "";

$viewModel->only_photo = "";

$viewModel->rooms = array(
    1 => ['value' => "1-комнатная", 'isSelected' => ""],
    2 => ['value' => "2-комнатная", 'isSelected' => ""],
    3 => ['value' => "3-комнатная", 'isSelected' => ""],
    4 => ['value' => "4-комнатная", 'isSelected' => ""],
    5 => ['value' => "5-комнатная", 'isSelected' => ""]
);

$viewModel->property = array(
    'city/flats' => ['value' => "квартиры (вторичка)", 'isSelected' => ""],
    'city/rooms' => ['value' => "комнаты", 'isSelected' => ""],
    'city/elite' => ['value' => "Элитная недвижимость:", 'isSelected' => ""],
    'city/newflats' => ['value' => "Новостройки", 'isSelected' => ""],
    'country/houses' => ['value' => "дома", 'isSelected' => ""],
    'country/cottages' => ['value' => "коттеджи", 'isSelected' => ""],
    'commerce/offices' => ['value' => "офисы", 'isSelected' => ""],
    'commerce/comm_new' => ['value' => "помещения в строящихся домах", 'isSelected' => ""],
    'commerce/comm_lands' => ['value' => "земельные участки", 'isSelected' => ""]
);




// Получение входных данных от клиента и генерация url

if (isset($_GET['search'])) {
    if (isset($_GET['property'])) {
        $property = $_GET['property'];
        $viewModel->property[$property]['isSelected'] = "selected";
    }
    if (isset($_GET['price_from'])) {
        $urlParameters[] = getUrlParameters('price_from', $_GET['price_from']);
        $viewModel->price_from = $_GET['price_from'];
    }
    if (isset($_GET['price_to'])) {
        $urlParameters[] = getUrlParameters('price_to', $_GET['price_to']);
        $viewModel->price_to = $_GET['price_to'];
    }
    if (isset($_GET['rooms'])) {
        $urlParameters[] = getUrlParameters('rooms', $_GET['rooms']);
        foreach ($_GET['rooms'] as $item) {
            $viewModel->rooms[$item]['isSelected'] = "selected";
        }
    }
    if (isset($_GET['only_photo'])) {
        $urlParameters[] = getUrlParameters('only_photo', $_GET['only_photo']);
        $viewModel->only_photo = "checked";
    }
}

/// Соединение в одну строку всех значении массива, чтобы получился url
$urlParameters = implode('', $urlParameters);
$url = "http://www.50.bn.ru/sale/$property/?sort=price&sortorder=ASC$urlParameters";

/// Использована curl для отправки Http get - запроса и получение данных
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_ENCODING, "");

$curl_html = curl_exec($ch);
$info = curl_getinfo($ch);

if ($info['http_code'] != 200 && $info['http_code'] != 404) {
    $content = "Упс, технические неполадки, поробуйте еще раз!";
} else {
    $pq = phpQuery::newDocumentHTML($curl_html, 'UTF-8');
    $content = $pq->find('div.result > table')->htmlOuter();
}
curl_close($ch);

///Подключение view
include('../view/main.html');

?>
