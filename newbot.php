<?php header("HTTP/1.1 200 OK");
if (!isset($_REQUEST)) {
return;
}

include "pibyhelp.php";

//Строка для подтверждения адреса сервера из настроек Callback API
$confirmationToken = 'your con. token';

//Ключ доступа сообщества
$token = 'your token';

// Secret key
$secretKey = 'your secretKey';

//Получаем и декодируем уведомление.
$data = json_decode(file_get_contents('php://input'));

if (isset($data->secret)){
	// проверяем secretKey
	if(strcmp($data->secret, $secretKey) !== 0 && strcmp($data->type, 'confirmation') !== 0)
		return;

	//Проверяем, что находится в поле "type"
	switch ($data->type) {
		//Если это уведомление для подтверждения адреса сервера...
		case 'confirmation':
			//...отправляем строку для подтверждения адреса
			echo $confirmationToken;
			break;

		//Если это уведомление о новом сообщении...
		case 'message_new':
			//...получаем id его автора
			$userId = $data->object->user_id;
			$message = $data->object->body; // Само сообщение от пользователя
			//затем с помощью users.get получаем данные об авторе
			$userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&v=5.0"));

			//и извлекаем из ответа его имя.
			$user_name = $userInfo->response[0]->first_name;

			if ($message == "На завтра" or $message == "На неделю"){
				$keyboard = [
                    "one_time" => false,
                    "buttons" => [
					[
                    ["action" => [
                    "type" => "text",
                    "payload" => '{"button": "1"}',
                    "label" => "На завтра"],
                    "color" => "default"]
					],
					[
                    ["action" => [
                    "type" => "text",
                    "payload" => '{"button": "1"}',
                    "label" => "На неделю"],
                    "color" => "default"]
					]
					]];
				//С помощью messages.send и токена сообщества отправляем ответное сообщение
				$request_params = array(
					'message' => MessageSchedule ($message),
					'user_id' => $userId,
					'access_token' => $token,
					'read_state' => 1,
					'v' => '5.0',
					'keyboard' => json_encode($keyboard, JSON_UNESCAPED_UNICODE)
				);

				$get_params = http_build_query($request_params);

				file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);
			}
			else {
				$keyboard = [
                    "one_time" => false,
                    "buttons" => [
					[
                    ["action" => [
                    "type" => "text",
                    "payload" => '{"button": "1"}',
                    "label" => "На завтра"],
                    "color" => "default"]
					],
					[
                    ["action" => [
                    "type" => "text",
                    "payload" => '{"button": "1"}',
                    "label" => "На неделю"],
                    "color" => "default"]
					]
					]];
				//С помощью messages.send и токена сообщества отправляем ответное сообщение
				$request_params = array(
					'message' => "Выбери дату по соответствующей кнопке или впиши следующие: (На завтра) или (На неделю)",
					'user_id' => $userId,
					'access_token' => $token,
					'read_state' => 1,
					'v' => '5.0',
					'keyboard' => json_encode($keyboard, JSON_UNESCAPED_UNICODE)
				);

				$get_params = http_build_query($request_params);

				file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);
			}
			//Возвращаем "ok" серверу Callback API
			header("HTTP/1.1 200 OK");
			echo('ok');
			break;

		default:
			header("HTTP/1.1 200 OK");
			echo('ok');
		}
	}
?>
