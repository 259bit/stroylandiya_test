function GeoIpSearchComponent(params) {
    // Конструктор компонента GeoIpSearchComponent
    // params - объект с параметрами компонента
    this.componentName = params.componentName; // Установка имени компонента из параметров
    this.getIpInfoActionName = params.getIpInfoActionName; // Установка имени действия получения информации по IP-адресу из параметров
    this.init(); // Инициализация компонента
}

GeoIpSearchComponent.prototype.init = function () {
    // Метод для инициализации компонента
    this.setupHandlers(); // Установка обработчиков событий
}

GeoIpSearchComponent.prototype.setupHandlers = function () {
    // Метод для установки обработчиков событий
    let geoIpForm = document.querySelector('.geoip-form'); // Получение элемента формы по классу
    geoIpForm.addEventListener('submit', this.getGeoIpInfo.bind(this)); // Установка обработчика события отправки формы на метод getGeoIpInfo с привязкой контекста к текущему объекту
}

GeoIpSearchComponent.prototype.getGeoIpInfo = function (event) {
    // Метод для получения информации о геоположении по IP-адресу
    event.preventDefault(); // Предотвращение отправки формы и перезагрузки страницы
    let ip = document.querySelector('.geoip-form__input').value; // Получение значения IP-адреса из поля ввода
    let resultContainer = document.querySelector('.geoip-result'); // Получение элемента контейнера для результатов

    // Отправка AJAX-запроса на сервер
    let request = BX.ajax.runComponentAction(this.componentName,
        'getGeoIpInfo',
        {
            data: {
                ip: ip
            }
        });

    request.then(function (response) {
        // Обработка успешного ответа на запрос
        let printData;

        if (response.status === 'success') {
            printData = response.data.ipInfo.city; // Получение информации о городе из данных ответа
        }

        resultContainer.innerHTML = printData; // Вывод информации в контейнер результатов

    }).catch(function (response) {
        // Обработка ошибок при выполнении запроса
        resultContainer.innerHTML = response.data[0].message; // Вывод сообщения об ошибке в контейнер результатов
    })
}
