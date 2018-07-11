<?php
$tLogin =  $modx->getService('tLogin', 'tLogin',  MODX_CORE_PATH . 'components/tlogin/model/tlogin/');
if(!$tLogin){
    $modx->log(1, '[tLogin] Could not load tLogin class');
    return;
}

$pdo = $modx->getService('pdoTools');
$loginTpl = $modx->getOption('tplLogin', $scriptProperties, 'tlogin_login');
$logoutTpl = $modx->getOption('tpllogout', $scriptProperties, 'tlogin_logout');
$register = $modx->getOption('tlogin_register',null,0);

//Обработка ссылки Выход
if(isset($_GET['logout'])){
    if($register){
        $response = $modx->runProcessor('/security/logout');
        if ($response->isError()) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[tLogin] Logout error. Message: '.$response->getMessage());
        }
        $url = $modx->makeUrl($modx->getOption('site_start'));
        $modx->sendRedirect($url);
    }else{
        $session = $_COOKIE['PHPSESSID'];
        $options = array(
            xPDO::OPT_CACHE_KEY => 'tLogin',
        );
        $modx->cacheManager->delete($session, $options);
    }
    if(intval($_GET['logout']) > 0){
        $url = $modx->makeUrl(intval($_GET['logout']));
    }else{
        $url = $modx->makeUrl($modx->resource->id);
    }
    $modx->sendRedirect($url);
}

//Проверяю текущую авторизацию
if($user_data = $tLogin->getTelegramUserData()){
    return $pdo->getChunk($logoutTpl, $user_data);
}

//Авторизация
if(isset($_GET) && !empty($_GET['hash'])){
    $uri = $modx->resource->get('uri');
    $tLogin->checkUser($_GET, $uri);
}

return $pdo->getChunk($loginTpl);