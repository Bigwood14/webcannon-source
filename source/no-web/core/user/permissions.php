<?PHP
class permissions
{
    var $auth;

    function permissions(&$auth)
    {
        $this->auth = &$auth;
    }

    function hasPermission($key,$level = 1, $user_id = null)
    {
        if(is_null($user_id))
        {
            $user_id = $this->auth->user['user_id'];
        }

        $rw = $this->auth->container->getPermission($key,$user_id);

		if (empty($rw['has']))
			$rw['has'] = false;

        if($rw['has'] == 'y' && $level >= $level)
        {
            return true;
        }
        elseif($rw['has'] == 'n')
        {
            return false;
        }
        else
        {
            $rw = $this->auth->container->getGroupPermission($key,$this->getGroups($user_id));

            if($rw['has'] == 'y' && $level >= $level)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    function getGroups($user_id)
    {
        $groups = $this->auth->container->getGroups($user_id);
        return $groups;
    }
    
    function addPermission($key,$user_id,$level = 0,$has = 'y')
    {
        return $this->auth->container->addPermission($key,$user_id,$level,'u',$has);
    }
    
    function addGroupPermission($key,$group_id,$level = 0)
    {
        return $this->auth->container->addPermission($key,$group_id,$level,'g',$has);
    }
}
?>
