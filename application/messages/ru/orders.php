<?php

return [
    // Заголовки и общие элементы
    'navbar.title' => 'Заказы',
    'table.header.column.id' => 'ID',
    'table.header.column.user' => 'Пользователь',
    'table.header.column.link' => 'Ссылка',
    'table.header.column.quantity' => 'Количество',
    'table.header.column.service' => 'Услуга',
    'table.header.column.status' => 'Статус',
    'table.header.column.mode' => 'Режим',
    'table.header.column.created' => 'Создан',
    'search.button' => 'Поиск',
    'search.placeholder' => 'Поиск заказов',
    'search.type.order_id' => 'ID заказа',
    'search.type.username' => 'Имя пользователя',
    'navbar.toggle' => 'Переключить навигацию',

    // Статусы заказов
    'status.pending' => 'В ожидании',
    'status.in_progress' => 'В процессе',
    'status.completed' => 'Завершено',
    'status.canceled' => 'Отменено',
    'status.failed' => 'Ошибка',

    // Режимы
    'mode.auto' => 'Авто',
    'mode.manual' => 'Вручную',

    // Услуги (из dropdown-меню)
    'service.real_views' => 'Реальные просмотры',
    'service.page_likes' => 'Лайки страницы',
    'service.followers' => 'Подписчики',
    'service.groups_join' => 'Вступление в группы',
    'service.website_likes' => 'Лайки сайта',
    'service.likes' => 'Лайки',
    'service.views' => 'Просмотры',
    'service.comment' => 'Комментарий',

    // Фильтры и dropdown-меню
    'filter.all' => 'Все',
    'filter.all_orders' => 'Все заказы',
    'filter.all_services' => 'Все услуги',
    'filter.all_statuses' => 'Все статусы',
    'filter.all_modes' => 'Все режимы',

    // Пагинация
    'pagination.summary' => '{start}-{end} из {total}',

    // Валидация
    'validator.search_type.invalid' => 'Неверный тип поиска.',
    'validator.order_id.not_numeric' => 'ID заказа должен быть числом.',
    'validator.order_id.positive' => 'ID заказа должен быть больше 0.',
    'validator.link.invalid' => 'Введите корректную ссылку.',
    'validator.username.too_short' => 'Имя пользователя должно содержать минимум 2 символа.',
    'validator.username.too_long' => 'Имя пользователя не должно превышать 50 символов.',
];
