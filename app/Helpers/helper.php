<?php


/**
 * @return mixed|null
 */
function getRemoteIPAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null;
}


/**
 * @return string
 */
function getClientIp(): string
{
    $ips = getRemoteIPAddress();
    $ips = explode(',', $ips);
    return !empty($ips[0]) ? $ips[0] : \Request::getClientIp();
}


/**
 * @param $list
 * @return array|null
 */
function getVar($list): ?array
{
    $file = resource_path('var/' . $list . '.json');
    return (\File::exists($file)) ? json_decode(file_get_contents($file), true) : [];
}

/**
 * @param $params
 * @param $key
 * @param null $default
 * @return mixed|null
 */
function gv($params, $key, $default = null) {
    return (isset($params[$key]) && $params[$key]) ? $params[$key] : $default;
}

/**
 * @param $params
 * @param $key
 * @return bool
 */
function gbv($params, $key): bool
{
    return isset($params[$key]) && $params[$key];
}



/**
 * @param null $prop
 * @return mixed|null
 */
function getDefaultCurrency($prop = null) {
    $default_currency_key = array_search(config('config.currency'), array_column(getVar('currency'), 'name'));
    $currency = ($default_currency_key !== false) ? getVar('currency')[$default_currency_key] : null;

    if (!$prop) {
        return $currency;
    }

    return ($currency && isset($currency[$prop])) ? $currency[$prop] : null;
}

/**
 * @return float|int
 */
function getPostMaxSize() {
    if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
        return (int) $postMaxSize;
    }

    $metric = strtoupper(substr($postMaxSize, -1));
    $postMaxSize = (int) $postMaxSize;

    switch ($metric) {
        case 'K':
            return $postMaxSize * 1024;
        case 'M':
            return $postMaxSize * 1048576;
        case 'G':
            return $postMaxSize * 1073741824;
        default:
            return $postMaxSize;
    }
}


/**
 * @param string $time
 * @return false|string|void
 */
function showTime($time = '') {
    if (!$time) {
        return;
    }

    if (config('config.time_format') === 'H:mm') {
        return date('H:i', strtotime($time));
    } else {
        return date('h:i a', strtotime($time));
    }
}


/**
 * @param $date
 * @return string
 */
function getStartOfDate($date): string
{
    return date('Y-m-d', strtotime($date)) . ' 00:00';
}


/**
 * @param $date
 * @return string
 */
function getEndOfDate($date): string
{
    return date('Y-m-d', strtotime($date)) . ' 23:59';
}

