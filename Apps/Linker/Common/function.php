<?php

function getELUser($id)
{
    $arr = M('user')->find($id);
    return $arr['usernickname'];
}