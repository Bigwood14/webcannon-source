<?php
define('SERVER_ID', '2');
define('SERVER_DB', '1');
/**
* Config is the class for storing configuration values most should
* be written directly to this file although it does supply some functions
* for gathering from other sources.
*
* @author      
* @version     1.0
* @package Core
*/
class config
{
    var $values;
    
    /**
    * Set up the options
    *
    * @return          void
    * @since           1.0
    */
    function config()
    {
        // Path to the main installed directory
        // Orignal vars are stated to the right in comment for setup they will be {var} er celibero.thestone.
    	$this->values['site']['domain']        = 'celibero.thestone'; // domain
    	$this->values['site']['just_domain']   = 'celibero.thestone'; // domain
    	$this->values['site']['path']          = '/media/disk/www/celibero/trunk/'; // root_path
    	$this->values['site']['core_path']     = '/media/disk/www/celibero/trunk/no-web/core/'; // core_path
    	$this->values['site']['upload_patch']  = '/home/upload/'; // upload_path
    	$this->values['site']['installed']     = '1'; // installed
    	
    	$this->values['template']['directory']     = $this->values['site']['path']."no-web/templates/";
        
        $this->values['db']['type']         = 'mysql';
        $this->values['db']['hostname']     = 'localhost'; // hostname
        $this->values['db']['username']     = 'celibero'; // username
        $this->values['db']['password']     = 'a1376aba41'; // password
        $this->values['db']['database']     = 'celibero'; // database
        
        $this->values['mm_field_hide'] = array('id','mask','import_id','list_id');
    }
}
 ?>