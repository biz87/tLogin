<?php
$tLogin =  $modx->getService('tLogin', 'tLogin',  MODX_CORE_PATH . 'components/tlogin/model/tlogin/');
if(!$tLogin){
    $modx->log(1, '[tLogin] Could not load tLogin class');
    return;
}

$pdo = $modx->getService('pdoTools');
$loginTpl = $modx->getOption('tplLogin', $scriptProperties, 'tlogin_login');
$logoutTpl = $modx->getOption('tplLogout', $scriptProperties, 'tlogin_logout');
$logout_id = $modx->getOption('logout_id', $scriptProperties, $modx->resource->id);
$register = $modx->getOption('tlogin_register',null,0);
if(empty($logout_id)){
    $logout_id = $modx->resource->id;
}

//Обработка ссылки Выход
if(isset($_GET['logout'])){
    if($register){
        $response = $modx->runProcessor('/security/logout');
        if ($response->isError()) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[tLogin] Logout error. Message: '.$response->getMessage());
        }
    }else{
        $session = $_COOKIE['PHPSESSID'];
        $options = array(
            xPDO::OPT_CACHE_KEY => 'tLogin',
        );
        $modx->cacheManager->delete($session, $options);
    }

    $url = $modx->makeUrl($logout_id);
    $modx->sendRedirect($url);
}

//Проверяю текущую авторизацию
if($user_data = $tLogin->getTelegramUserData()){
    return $pdo->getChunk($logoutTpl, $user_data);
}

//Авторизация
if(isset($_GET) && !empty($_GET['hash'])){
    $id = $modx->resource->id;
    $tLogin->checkUser($_GET, $id);
}

return $pdo->getChunk($loginTpl);