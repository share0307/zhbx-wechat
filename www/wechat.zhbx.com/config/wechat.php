<?php

return array(
    'app_id' => 'wxcb3eb74cf1c40af7',
    'secret' => '539cec82c0a571a69fa85ad3bdccfcca',
    
    // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
    'response_type' => 'array',
    
    'log' => [
        'level' => 'debug',
        'file' => storage_path('logs/wechat.log'),
    ],
);
