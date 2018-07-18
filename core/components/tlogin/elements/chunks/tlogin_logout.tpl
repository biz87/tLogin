<p>Вы  вошли как
    {$first_name}
    {$last_name}
    {if $username}(@{$username}){/if}
    {if $photo_url}<img src="{$photo_url}" alt="{$first_name} {$last_name}" height="50">{/if}
</p>
<div>
    <a  href="{{$_modx->makeUrl($logout_id, ['logout' => 1])}}">Выйти</a>
</div>

<p>Для  очистки виджета, нажмите кнопку выхода в  своем  Телеграм</p>