<?PHP
class profile
{
    var $auth;
    var $user_id;

    function profile(&$auth)
    {
        $this->auth = &$auth;
        $this->setUser();
    }
    
    function setUser($user_id = null)
    {
        if(is_null($user_id))
        {
            $this->user_id = $this->auth->user['user_id'];
        }
        else
        {
            $this->user_id = $user_id;
        }
    }
    
    function getText($profile_key,$scroll = false)
    {
        if($scroll === false)
        {
            return $this->auth->container->getProfileText($profile_key,$this->user_id,0,0);
        }
    }
    
    function getBigText($profile_key,$scroll = false)
    {
        if($scroll === false)
        {
            return $this->auth->container->getProfileText($profile_key,$this->user_id,1,0);
        }
    }
    
    function addText($profile_key,$text)
    {
        return $this->auth->container->addProfileText($profile_key,$this->user_id,$text,0);
    }
    
    function addBigText($profile_key,$text)
    {
        return $this->auth->container->addProfileText($profile_key,$this->user_id,$text,1);
    }
}
?>