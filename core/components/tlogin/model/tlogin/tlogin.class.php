<?php

class tLogin
{

    public $modx;
    public $token;
    public $register;

    public function __construct(modX &$modx)
    {
        $corePath = MODX_CORE_PATH . 'components/tlogin/';
        $modelPath = $corePath . 'model/';
        $this->modx =& $modx;
        $this->token = $this->modx->getOption('tlogin_bot_token');
        $this->register = $this->modx->getOption('tlogin_register',null,0);
        $this->modx->addPackage('tlogin', $modelPath);
        $this->modx->lexicon->load('tlogin:default');
    }




    public function checkUser(array $data = array(), $uri)
    {
        if(count($data) == 0){return;}
        try {
            $auth_data = $this->checkTelegramAuthorization($data);
            $this->saveTelegramUserData($auth_data);
            $this->modx->sendRedirect($uri);
        } catch (Exception $e) {
            $this->modx->log(1,  '[tLogin] '.$e->getMessage());
        }
    }


    public function getTelegramUserData()
    {
        if($this->register){
            if($this->modx->user->hasSessionContext('web')){
                //Получаю пользователя
                $user = $this->modx->getUser();
                if($user->id > 0){
                    $profile = $user->Profile;
                    $userData = array_merge($user->toArray(), $profile->toArray());
                    return $userData;
                }
            }
        }else{
            //Читаю кэш
            $session = $_COOKIE['PHPSESSID'];
            $options = array(
                xPDO::OPT_CACHE_KEY => 'tLogin',
            );
            $auth_data = $this->modx->cacheManager->get($session, $options);
            return $auth_data;
        }


        return false;
    }


    private function checkTelegramAuthorization($auth_data)
    {
        $check_hash = $auth_data['hash'];
        $allow_key= array('username' , 'auth_date' ,'first_name', 'last_name' ,'photo_url' ,'id');
        unset($auth_data['hash']);
        $data_check_arr = [];
        foreach ($auth_data as $key => $value) {
            if( in_array( $key , $allow_key)){
                $data_check_arr[] = $key . '=' . $value;
            }
        }

        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', $this->token, true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);

        if (strcmp($hash, $check_hash) !== 0) {
            $this->modx->log(1, '[tLogin] Data is NOT from Telegram. Check bot  token');
            return;
        }

        if ((time() - $auth_data['auth_date']) > 86400) {
            $this->modx->log(1, '[tLogin] Data is outdated');
            return;
        }

        return $auth_data;
    }


    private function saveTelegramUserData($auth_data)
    {
        $auth_data_json = json_encode($auth_data);
        if($this->register){
            //Проверяю на существование
            $user = $this->modx->getObject('modUser', array('username' => $auth_data['username']));
            if($user && $user->get('id') > 0){
                $profile = $user->Profile;
                $userData = array_merge($user->toArray(), $profile->toArray());
                if(isset($userData['extended']['telegram']['id']) && $userData['extended']['telegram']['id'] == $auth_data['id']){
                    //Здесь нужно авторизовать  пользователя
                    $user->addSessionContext('web');
                }else{
                    $this->modx->log(1, '[tLogin] User with username '.$auth_data['username'].' already exists');
                    return;
                }
            }else{
                //Регистрируем

                $user = $this->modx->newObject('modUser');
                $user->set('username', $auth_data['username']);
                $user->save();

                $profile = $this->modx->newObject('modUserProfile');
                $profile->set('fullname', $auth_data['first_name'].' '.$auth_data['last_name']);
                $profile->set('email', $auth_data['username'].'@fakesite.ru');
                $profile->set('photo', $auth_data['photo_url']);
                $extended['telegram'] =  $auth_data;
                $profile->set('extended', $extended);
                $user->addOne($profile);
                $profile->save();
                $user->save();
                $user->addSessionContext('web');
                return;
            }



        }else{
            //Сохраняем в кэш
            $session = $_COOKIE['PHPSESSID'];
            $options = array(
                xPDO::OPT_CACHE_KEY => 'tLogin',
            );
            $this->modx->cacheManager->set($session, $auth_data, 0, $options);
        }
    }


}