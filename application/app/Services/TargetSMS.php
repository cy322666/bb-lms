<?php

namespace App\Services;

use Exception;

class TargetSMS
{

    private string $apiurl = 'https://apiagent.ru/password_generation/api.php';

    /**
     * Создание подключения.
     *
     * @param string $login    логин в системе
     * @param string $password пароль в системе
     */
    public function __construct(string $login, string $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * Генерация кода авторизации
     * @param string $phone номер телефона получателя
     * @param string $sender подпись отправителя
     * @param string $text текст персонификации
     * @return array
     * @throws Exception
     */
    public function sendSMS(string $phone, string $sender, string $text = ''): array
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
                <request>
                 <security>
                     <login>'.$this->login.'</login>
                     <password>'.$this->password.'</password>
                 </security>
                 <phone>'.$phone.'</phone>
                 <sender>'.$sender.'</sender>
                 <text>'.$text.'</text>
                </request>';
        return $this->send($xml);
    }

    /**
     * Отправка xml на сервер
     * @param $data
     * @return array
     * @throws Exception
     */
    private function send($data): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml; charset=utf-8'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CRLF, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $this->apiurl);

        $result = curl_exec($ch);
        $error  = curl_error($ch);
        $info   = curl_getinfo($ch);
        curl_close($ch);

        if (!isset($info['http_code']) || $info['http_code'] >= 400)
            throw new Exception('Ошибка запроса к серверу авторизации. Код: '.
                $info['http_code']. '. Ошибка: '.$error);

        $xml = @simplexml_load_string($result);

        if (!$xml)
            throw new Exception('Неверный формат ответ от сервера.');

        if (isset($xml->error))
            throw new Exception($xml->error);

        return [
            'xml'    => $xml,
            'info'   => $info,
            'result' => $result,
            'error'  => $error,
        ];
    }

    public static function parsingResponse($result) : array
    {
        $str = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
        }, $result['result']);

        $response = xml_parser_create();
        xml_parse_into_struct($response, $str, $values, $index);
        xml_parser_free($response);

        return $values;
    }
}
