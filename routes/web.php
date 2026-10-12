<?php

declare(strict_types=1);

return [
    'GET' => [
        '/' => 'DashboardController@index',
        '/employees' => 'EmployeeController@index',
        '/employees/create' => 'EmployeeController@create',
        '/departments' => 'DepartmentController@index',
        '/login' => 'AuthController@login',
    ],
    'POST' => [
        '/employees' => 'EmployeeController@store',
        '/login' => 'AuthController@authenticate',
    ],
];
