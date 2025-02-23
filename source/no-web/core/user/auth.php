<?PHP
class auth
{
    var $container;
    var $options;
    var $user;

    function auth($container = "adodb",$container_options =array(),$options = null,$instance = null)
    {
        switch ($container)
        {
            case "something":
            print 'Negative';
            break;
            default:
            require_once(dirname(__FILE__) .'/containers/adodb.php');
            $this->container = new user_container_adodb($container_options,$instance);
            break;
        }

        $this->options = $options;
       
	   	if (empty($this->options['gc_prob']))
			$this->options['gc_prob'] = false;
	    
        $this->garbageCleanup($this->options['gc_prob']);
        
        if(isset($_COOKIE[$this->options['sess_name']]))
        {
            $this->verifyLogin($_COOKIE[$this->options['sess_name']]);
        }
    }
    
    function preparePassword($password)
    {
        switch ($this->options['pass_cypher'])
        {
            case "md5":
            $password = md5($password);
            break;
            default:
            $password = $password;
            break;
        }
        
        return $password;
    }
    
    function login($username,$password,$duration = null)
    {
        $password = $this->preparePassword($password);

        $user = $this->container->getUser($username);

        if(!$user)
        {
            return false;
        }
        elseif($user['password'] != $password)
        {
            return false;
        }
        else
        {
            $session_id = $this->makeSession($user['user_id'],$duration);
            $this->user = $user;
            return true;
        }
    }

	function getUser ($user_id)
	{
		return $this->container->getUser(false, $user_id);
	}

    function makeSession($user_id = 0, $duration = null)
    {
        $session_id = md5(uniqid(rand(), true));
        $last_activity = mktime();
        $duration = (is_null($duration) ? $this->options['sess_duration'] : $duration);
        
        $this->container->makeSession($session_id,$user_id,$last_activity,$duration);
        
        $this->setCookie($session_id,$duration);
        
        return $session_id;
    }

    function setCookie($session_id,$duration,$duration_mod = 'add')
    {
        $duration 	= ($duration_mod=='add' ? mktime()+$duration : mktime()-$duration);
     	$secure 	= (@$_SERVER['HTTPS'] == 'on') ? true : false;
	 
	    
        setcookie(
        $this->options['sess_name'],
        $session_id,
        $duration,
        $this->options['cookie_path'],
		false,
		$secure
        );
    }
    
    function verifyLogin($session_id)
    {
        if(!$session = $this->container->getSession($session_id))
        {
            $this->logout();
            return false;
        }
        
        if($session['last_activity']+$session['duration'] < mktime())
        {
            $this->logout();
            return false;
        }
        else
        {
            $user = $this->container->getUser('',$session['user_id']);
            $this->user = $user;
            $this->container->updateSession($session['session_id'],mktime());
            $this->setCookie($session['session_id'],$session['duration']);
            return true;
        }
        
    }
    
    function logout()
    {
		if(isset($_COOKIE[$this->options['sess_name']]))
		{
			$this->container->deleteSession($_COOKIE[$this->options['sess_name']]);
		}

        $this->setCookie(0,300,'minus');
    }
    
    function garbageCleanup($num)
    {
        if($num > 10)
        {
            $num = 10;
        }
        
        if(rand(0,10) == $num)
        {
            $this->container->gc();
        }
    }
    
    function setDuration($duration)
    {
        $this->container->updateSession($session['session_id'],'',$duration);
    }
    
    function addUser($username,$password)
    {
        $password = $this->preparePassword($password);
        return $this->container->addUser($username,$password);
    }
  
  	function addGroup ($user_id, $group_id)
	{
		return $this->container->addGroup($user_id, $group_id);
	}
   
   	function getGroup ($group_id)
	{
		return $this->container->getGroup($group_id);
	}
    
    function removeUser($user_id)
    {
        $this->container->removeUser($user_id);
        return true;
    }
    
    function changePassword($user_id,$password)
    {
        $password = $this->preparePassword($password);
        $this->container->changePassword($user_id,$password);
        return true;
    }
}
?>
