<?php

if (PHP_VERSION_ID < 80200) {

    // warning!
    header('HTTP/1.1 500 Internal Server Error');
    $message = 'require php_version above php8.2';
    echo $message;

    // trigger error message!
    trigger_error(message: $message, error_level: E_USER_ERROR);
}

// enums
require_once __DIR__ . '/enums/term_signal.php';
require_once __DIR__ . '/enums/http_status_code.php';
require_once __DIR__ . '/enums/http_method.php';
require_once __DIR__ . '/enums/path_sys.php';

// interfaces
require_once __DIR__ . '/interfaces/tag.php';
require_once __DIR__ . '/interfaces/file.php';
require_once __DIR__ . '/interfaces/virtualize/std_content.php';
require_once __DIR__ . '/interfaces/virtualize/std_file.php';
require_once __DIR__ . '/interfaces/virtualize/std_path_resolver.php';
require_once __DIR__ . '/interfaces/cabbage/values.php';
require_once __DIR__ . '/interfaces/cabbage/http_body_content.php';
require_once __DIR__ . '/interfaces/cabbage/http_file.php';
require_once __DIR__ . '/interfaces/cabbage/http_header.php';
require_once __DIR__ . '/interfaces/cabbage/http_info.php';
require_once __DIR__ . '/interfaces/cabbage/http_param.php';
require_once __DIR__ . '/interfaces/cabbage/http_info.php';
require_once __DIR__ . '/interfaces/cabbage/http_status_message.php';
require_once __DIR__ . '/interfaces/cabbage/http_request.php';
require_once __DIR__ . '/interfaces/cabbage/http_response.php';
require_once __DIR__ . '/interfaces/cabbage/middleware.php';
require_once __DIR__ . '/interfaces/cabbage/controllers/direct.php';
require_once __DIR__ . '/interfaces/cabbage/controllers/inspect.php';
require_once __DIR__ . '/interfaces/cabbage/app.php';

// tags
require_once __DIR__ . '/tags/tag.php';
require_once __DIR__ . '/tags/path.php';

// utils
require_once __DIR__ . '/utils.php';

// abstracts
require_once __DIR__ . '/abstracts/virtualize/std_content.php';
require_once __DIR__ . '/abstracts/cabbage/middleware.php';

// scripts
require_once __DIR__ . '/tags/tag.php';
require_once __DIR__ . '/virtualize/std_content.php';
require_once __DIR__ . '/virtualize/std_file.php';
require_once __DIR__ . '/virtualize/std_io.php';
require_once __DIR__ . '/virtualize/std_path_resolver.php';
require_once __DIR__ . '/cabbage/values.php';
require_once __DIR__ . '/cabbage/http_info.php';
require_once __DIR__ . '/cabbage/http_status_message.php';
require_once __DIR__ . '/cabbage/http_file.php';
require_once __DIR__ . '/cabbage/http_param.php';
require_once __DIR__ . '/cabbage/http_header.php';
require_once __DIR__ . '/cabbage/http_body_content.php';
require_once __DIR__ . '/cabbage/http_request.php';
require_once __DIR__ . '/cabbage/http_response.php';
require_once __DIR__ . '/cabbage/middlewares/data_assets_resources.php';
require_once __DIR__ . '/cabbage/controllers/direct.php';
require_once __DIR__ . '/cabbage/controllers/inspect.php';
require_once __DIR__ . '/cabbage/utils.php';
require_once __DIR__ . '/cabbage/app.php';
