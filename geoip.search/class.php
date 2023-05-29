<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}


/**
 * Класс компонента для поиска географического расположения по IP.
 */
class GeoIpSearchComponent extends CBitrixComponent
{
    /**
     * @param array $params Параметры компонента.
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        return $params;
    }

    /**
     * Выполняет основную логику компонента.
     *
     * @return void
     */
    public function executeComponent(): void
    {
        $this->arResult['JS_PARAMS'] = $this->getJsParams();

        $this->includeComponentTemplate();
    }

    /**
     * Возвращает параметры для передачи на клиентскую сторону в JavaScript.
     *
     * @return array
     */
    protected function getJsParams(): array
    {
        return [
            'componentName' => $this->getName(),
        ];
    }
}
