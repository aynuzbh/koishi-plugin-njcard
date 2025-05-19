<?php
// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// 日志文件路径
define('ERROR_LOG_FILE', __DIR__ . '/error.log');

// 错误处理
set_error_handler(function($errno, $errstr, $errfile = '', $errline = 0) {
    $error_message = date('Y-m-d H:i:s') . " | Error: $errstr | File: $errfile | Line: $errline\n";
    file_put_contents(ERROR_LOG_FILE, $error_message, FILE_APPEND);

    http_response_code(500);
    echo json_encode([
        'code' => 500,
        'msg' => 'Internal Server Error',
        'error' => $errstr
    ]);
    exit;
});

// 日志接口 ?log=1 返回最近20条错误日志，允许所有人访问
if (isset($_GET['log']) && $_GET['log'] == 1) {
    header('Content-Type: text/plain; charset=utf-8');
    if (file_exists(ERROR_LOG_FILE)) {
        $lines = file(ERROR_LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice($lines, -20);
        echo implode("\n", $lines);
    } else {
        echo "No error log.";
    }
    exit;
}

// 定义概率配置
$probability_config = [
    '1' => 0.01,   // 1% 概率
    '3' => 0.03,   // 3% 概率
    '43' => 0.43,  // 43% 概率
    '53' => 0.53   // 53% 概率
];

// 获取图片列表函数
function getImagesFromDirectory($dir) {
    $images = [];
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) && $file !== '.' && $file !== '..') {
                $images[] = $file;
            }
        }
    }
    return $images;
}

// 随机选择目录
function selectRandomDirectory($probability_config) {
    $rand = mt_rand(1, 100) / 100;
    $current_probability = 0;
    
    foreach ($probability_config as $dir => $prob) {
        $current_probability += $prob;
        if ($rand <= $current_probability) {
            return $dir;
        }
    }
    
    return '53'; // 默认返回最后一个目录
}

// koishi segment image 格式化
function koishi_segment_image($imageUrl) {
    $fullUrl = (strpos($imageUrl, '//') === 0) ? 'https:' . $imageUrl : $imageUrl;
    return '[CQ:image,file=' . $fullUrl . ']';
}

// 获取随机图片路径
function getRandomImagePath($mode = 'single', $asKoishiSegment = false) {
    global $probability_config;
    
    // 获取当前域名
    $host = $_SERVER['HTTP_HOST'] ?: 'nj.aynu.asia';
    
    // 构建基础URL - 直接使用域名根目录，因为文件都在根目录下
    $base_url = "//{$host}/";
    
    $results = [];
    $used_images = [];
    
    $count = ($mode === 'multi') ? (isset($_GET['limit']) ? min(intval($_GET['limit']), 10) : 10) : 1;
    
    for ($i = 0; $i < $count; $i++) {
        $selected_dir = selectRandomDirectory($probability_config);
        $dir_path = __DIR__ . '/' . $selected_dir;
        $images = getImagesFromDirectory($dir_path);
        
        if (empty($images)) {
            $imageUrl = $base_url . $selected_dir . '/placeholder.png';
        } else {
            $attempts = 0;
            $found_unique = false;
            while ($attempts < 10 && !$found_unique) {
                $random_image = $images[array_rand($images)];
                $image_path = $selected_dir . '/' . $random_image;
                if (!in_array($image_path, $used_images)) {
                    $used_images[] = $image_path;
                    $imageUrl = $base_url . $image_path;
                    $found_unique = true;
                }
                $attempts++;
            }
            if (!$found_unique) {
                $imageUrl = $base_url . $selected_dir . '/' . $random_image;
            }
        }
        $results[] = $asKoishiSegment ? koishi_segment_image($imageUrl) : $imageUrl;
    }
    return $mode === 'multi' ? $results : $results[0];
}

// 处理请求
$mode = 'single';
$format = 'json';
$cache_control = true;
$pretty = false;
$returnKoishiSegment = false;

// 支持多种API风格
if (isset($_GET['wpon'])) {
    if ($_GET['wpon'] === 'url10') {
        $mode = 'multi';
    } elseif ($_GET['wpon'] === 'url') {
        $mode = 'single';
    }
} elseif (isset($_GET['mode'])) {
    $mode = strtolower($_GET['mode']);
    if (!in_array($mode, ['single', 'multi'])) {
        $mode = 'single';
    }
} elseif (isset($_GET['count'])) {
    $count = intval($_GET['count']);
    if ($count > 1) {
        $mode = 'multi';
        $_GET['limit'] = min($count, 10);
    } else {
        $mode = 'single';
    }
}

// 检查是否请求 koishi segment 格式
if (isset($_GET['format']) && strtolower($_GET['format']) === 'koishi') {
    $returnKoishiSegment = true;
    $format = 'text';
} elseif (isset($_GET['koishi']) && $_GET['koishi'] == '1') {
    $returnKoishiSegment = true;
    $format = 'text';
}

// 处理格式参数
if (isset($_GET['format']) && !$returnKoishiSegment) {
    $format = strtolower($_GET['format']);
    if (!in_array($format, ['json', 'text', 'html'])) {
        $format = 'json';
    }
}

// 处理缓存控制参数
if (isset($_GET['no_cache']) && $_GET['no_cache'] === '1') {
    $cache_control = false;
}

// 处理美化输出参数
if (isset($_GET['pretty']) && $_GET['pretty'] === '1') {
    $pretty = true;
}

// 设置缓存控制
if ($cache_control) {
    header('Cache-Control: public, max-age=60');
} else {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// 获取图片路径
$image_paths = getRandomImagePath($mode, $returnKoishiSegment);

// 根据格式返回响应
if ($format === 'text') {
    header('Content-Type: text/plain; charset=utf-8');
    if (is_array($image_paths)) {
        echo implode("\n", $image_paths);
    } else {
        echo $image_paths;
    }
} elseif ($format === 'html') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>随机图片</title><style>body{margin:0;padding:20px;font-family:Arial,sans-serif;} img{max-width:100%;height:auto;display:block;margin:10px 0;border:1px solid #ddd;}</style></head><body>';
    if (is_array($image_paths)) {
        foreach ($image_paths as $path) {
            echo '<img src="' . htmlspecialchars($path) . '" alt="随机图片">';
        }
    } else {
        echo '<img src="' . htmlspecialchars($image_paths) . '" alt="随机图片">';
    }
    echo '<p>由 <a href="http://nj.aynu.asia">随机图片API</a> 提供</p>';
    echo '</body></html>';
} else {
    $response = [
        'code' => 200,
        'msg' => 'success',
        'data' => $image_paths,
        'timestamp' => time()
    ];
    if (isset($_GET['info']) && $_GET['info'] === '1') {
        $response['info'] = [
            'total_images' => [
                '1' => count(getImagesFromDirectory(__DIR__ . '/1')),
                '3' => count(getImagesFromDirectory(__DIR__ . '/3')),
                '43' => count(getImagesFromDirectory(__DIR__ . '/43')),
                '53' => count(getImagesFromDirectory(__DIR__ . '/53'))
            ],
            'probabilities' => $probability_config
        ];
    }
    $json_options = JSON_UNESCAPED_SLASHES;
    if ($pretty) {
        $json_options |= JSON_PRETTY_PRINT;
    }
    echo json_encode($response, $json_options);
}
?>
