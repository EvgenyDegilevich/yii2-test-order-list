<?php

return [
    // Заголовки и общие элементы
    'title' => 'Orders',
    'table.header.column.id' => 'ID',
    'table.header.column.user' => 'User',
    'table.header.column.link' => 'Link',
    'table.header.column.quantity' => 'Quantity',
    'table.header.column.service' => 'Service',
    'table.header.column.status' => 'Status',
    'table.header.column.mode' => 'Mode',
    'table.header.column.created' => 'Created',
    'search.button' => 'Search',
    'search.placeholder' => 'Search orders',
    'search.type.order_id' => 'Order ID',
    'search.type.link' => 'Link',
    'search.type.username' => 'Username',
    'navbar.toggle' => 'Toggle navigation',
    'export.button' => 'Save Result',

    // Статусы заказов
    'status.pending' => 'Pending',
    'status.in_progress' => 'In progress',
    'status.completed' => 'Completed',
    'status.canceled' => 'Canceled',
    'status.failed' => 'Error',

    // Режимы
    'mode.auto' => 'Auto',
    'mode.manual' => 'Manual',

    // Услуги (из dropdown-меню)
    'service.real_views' => 'Real Views',
    'service.page_likes' => 'Page Likes',
    'service.followers' => 'Followers',
    'service.groups_join' => 'Groups Join',
    'service.website_likes' => 'Website Likes',
    'service.likes' => 'Likes',
    'service.views' => 'Views',
    'service.comment' => 'Comment',

    // Фильтры и dropdown-меню
    'filter.all' => 'All',
    'filter.all_orders' => 'All orders',
    'filter.all_services' => 'All services',
    'filter.all_statuses' => 'All statuses',
    'filter.all_modes' => 'All modes',

    // Пагинация
    'pagination.summary' => '{start} to {end} of {total}',

    // Валидация
    'validator.search_type.invalid' => 'Invalid search type.',
    'validator.order_id.not_numeric' => 'Order ID must be a number.',
    'validator.order_id.positive' => 'Order ID must be greater than 0.',
    'validator.link.invalid' => 'Please enter a valid URL.',
    'validator.username.too_short' => 'Username must be at least 2 characters long.',
    'validator.username.too_long' => 'Username must not exceed 50 characters.',
];
