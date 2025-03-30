<?php

return [

        //Añadir nuevamente en produccion
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=dbchat',
        'username' => 'root',
        //Comento la contraseña para tener acceso de manera local a la BD, quitar antes de enviar.
        //'password' => 'Unse#2024',
        'password'=>'',
        'charset' => 'utf8',

    /*Añadir nuevamente en produccion
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=dbchat',
    'username' => 'root',
    'password' => 'Unse#2024',
    'charset' => 'utf8',*/

    //para trabajar aqui en mi maquina
//    'class' => 'yii\db\Connection',
//     'dsn' => 'mysql:host=localhost;dbname=dbchatdos',
//     'username' => 'root',
//     'password' => '',
//     'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
