<?php

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Класс контроллера GeoIpSearchController.
 */
class GeoIpSearchController extends Controller
{
    private const HL_BLOCK_ID = 2;
    private const API_KEY = "0e70e0cdd83a225044125b8f9f501db8";
    private const SITE_ID = "s1";

    private $hlEntityDataClass;


    public function __construct()
    {
        parent::__construct();
        $this->hlEntityDataClass = $this->getEntityDataClass(self::HL_BLOCK_ID);
    }

    /**
     * Возвращает класс данных сущности highload-блока.
     *
     * @param int $hlBlockId Идентификатор highload-блока.
     * @return DataManager|string
     * @throws LoaderException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getEntityDataClass(int $hlBlockId)
    {
        Loader::includeModule("highloadblock");
        $hlBlock = HighloadBlockTable::getById($hlBlockId)->fetch();

        return HighloadBlockTable::compileEntity($hlBlock)->getDataClass();
    }

    /**
     * Конфигурирует доступные действия контроллера.
     *
     * @return array Массив с настройками действий контроллера.
     */
    public function configureActions(): array
    {
        return [
            'getGeoIpInfo' => [],
        ];
    }

    /**
     * Действие для получения информации о географическом расположении по IP.
     *
     * @param string $ip IP-адрес.
     * @return AjaxJson Объект AjaxJson с результатом запроса.
     */
    public function getGeoIpInfoAction(string $ip): AjaxJson
    {
        if ($this->isValidIpAddress($ip) === false) {
            return $this->sendErrorResponse('Invalid IP address');
        }

        $ipInfo = $this->getIpInfoFromHl($ip);
        if (!$ipInfo) {
            $ipInfo = $this->getIpInfoFromApi($ip);
        }

        return AjaxJson::createSuccess([
            'ipInfo' => $ipInfo
        ]);
    }

    /**
     * Проверяет, является ли IP-адрес допустимым.
     *
     * @param string $ip IP-адрес.
     * @return bool
     */
    private function isValidIpAddress(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Отправляет AjaxJson с ошибкой.
     *
     * @param string $message Сообщение об ошибке.
     * @return AjaxJson
     */
    private function sendErrorResponse(string $message): AjaxJson
    {
        $result = [new \Bitrix\Main\Error($message)];
        return AjaxJson::createError(new Bitrix\Main\ErrorCollection, $result);
    }

    /**
     * Возвращает информацию о географическом расположении из highload-блока.
     *
     * @param mixed $ip IP-адрес.
     * @return array|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws JsonException
     */
    private function getIpInfoFromHl($ip): ?array
    {
        $entityDataClass = $this->hlEntityDataClass;
        $ipInfo = $entityDataClass::getList([
            "select" => ["UF_IP_INFO"],
            "filter" => ["UF_IP" => $ip],
            'order' => ['ID' => 'DESC'],
            'cache' => [
                'ttl' => 3600,
            ],
        ])->fetch();

        return json_decode($ipInfo['UF_IP_INFO'], true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Получает информацию о географическом расположении из внешнего API.
     *
     * @param string $ip IP-адрес.
     * @return array|null
     */
    private function getIpInfoFromApi(string $ip): ?array
    {
        $apiKey = self::API_KEY;
        $url = "http://api.ipstack.com/{$ip}?access_key={$apiKey}&format=1";

        $httpClient = new \Bitrix\Main\Web\HttpClient();
        $jsonResponse = $httpClient->get($url);

        if ($httpClient->getStatus() === 200) {

            try {
                $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);

            } catch (JsonException $e) {
                $this->sendErrorEmail($ip, $e->getMessage());
            }

            if ($response['success'] === false) {
                $this->sendErrorEmail($ip, $response['error']['info']);
                return null;
            }

            $this->storeDataInHl($ip, $jsonResponse);
            return $response;
        }

        $this->sendErrorEmail($ip, $httpClient->getStatus());
        return null;
    }

    /**
     * Отправляет электронное письмо об ошибке.
     *
     * @param string $ip IP-адрес.
     * @param string $error Ошибка.
     * @return void
     */
    private function sendErrorEmail(string $ip, string $error): void
    {
        Event::send([
            "EVENT_NAME" => "IPSTACK_COM_ERROR",
            "LID" => static::SITE_ID,
            "C_FIELDS" => [
                "IP" => $ip,
                "ERROR" => $error,
            ]
        ]);
    }

    /**
     * Сохраняет данные в highload-блоке.
     *
     * @param string $ip IP-адрес.
     * @param mixed $response Ответ от API.
     * @return void
     */
    private function storeDataInHl(string $ip, $response): void
    {
        $this->hlEntityDataClass::add([
            'UF_IP' => $ip,
            'UF_IP_INFO' => $response
        ]);
    }
}
