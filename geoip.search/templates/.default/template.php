<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var \CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var array $templateData */
/** @var \CBitrixComponent $component */
$this->setFrameMode(true);

$headStrings = [
    '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">',
    '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>'
];

foreach ($headStrings as $headString) {
    Asset::getInstance()->addString($headString);  // Добавление строк в заголовок страницы
}
?>

<div class="container">
    <form class="geoip-form row">
        <input class="geoip-form__input form-control col" type="text" name="ip" value="" placeholder="IP">
        <button type="submit" class="geoip-form__submit-button btn btn-primary col"><?= Loc::getMessage('GEOIP_SEND_BUTTON') ?></button>
    </form>
    <div class="geoip-result row"></div>

<script>
    BX.ready(function () {  // Выполнение кода после загрузки Bitrix
        new GeoIpSearchComponent(<?= CUtil::PhpToJSObject($arResult['JS_PARAMS']) ?>);  // Инициализация объекта GeoIpSearchComponent с передачей параметров из PHP в JavaScript
    });
</script>




