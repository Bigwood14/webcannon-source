<?PHP
class installer
{
    var $file;

	function isFileWritable ($file)
	{
		return is_writable($file);
	}

    function importSQLFile($filename,$prefix = '',$db = ''){
        //first we need to open the file and read the content
        $handle=fopen($filename,"rb");
        $contents=fread($handle, filesize($filename));
        fclose($handle);

        //now we need to remove all comments (lines starting with //, #, or -- and content between /* */).. all other text will be sent to dbms so uncommented text will cause errors
        $content=preg_replace("!( /\* .*? \*/ )!sx", "", $contents); //removes multi-line c style comments (/* */)
        $content=preg_replace("!(--|//|#).*$!m", "", $content); //removes single-line comments (//,#,--)

        //next we break the remaining content into an array (of queries)
        $queries=explode(";",$content);

        //now we need to perform all of the queries
        for($i=0;$i<sizeof($queries)-1;$i++){
            //firstly, we need to add the table prefix to all of the queries
            $query = trim($queries[$i]);
            $query = preg_replace("/^((?:create|drop|alter)\s+table|insert\s+into|delete\s*from|update)\s+(`?)((?<=`).+?|\w+)\\2/i", "$1 ".$prefix."$2$3$2", trim($queries[$i]));


            //before we can perform the query, we need to check if it is a CREATE TABLE statement - if it is we need to see if the table already exists and if so drop it
            if(strpos($query,"CREATE TABLE")!==false && strpos($query,"CREATE TABLE")==0){
                //here we have established that the statment STARTS with CREATE TABLE (having the words CREATE TABLE in a value part of the query won't error out)
                preg_match("/create\s*table\s*(`?)((?<=`).+?|\w+)\\1/i",$query, $table); //grab table name from query

                //check if table exists already, if so drop it
                /*if($this->find_table($table[2],'')){
                mysql_query('DROP TABLE '.$table[2]);
                }*/
            }
//print $query;
            //Perform the actual query
            if($db == '')
            {
                if(!(mysql_query($query)))
                {
                    return false;
                }
            }
            else 
            {
                if(!($db->Execute($query)))
                {
                    print $db->ErrorMsg();
                    return false;
                }
            }
        }

        return true;
    }

    function checkPHPVersion($minVersion)
    {
        if(phpversion() < $minVersion)
        {
            return false;
        }
        else
        {
            return phpversion();
        }
    }

    function getFileVarValue($var)
    {
        $read  = fopen($file, "r");

        while (!feof ($read))
        {
            $buffer  = fgets($read, 4096);
            if(preg_match($var."\S*=\S*();",$buffer))
            {
            }
        }
    }

    function replaceFileVarRE($var)
    {
        $file = file($this->file);

        $contents = implode('',$file);

        if(is_array($var))
        {
            foreach($var AS $value)
            {

                echo $pattern = "/".$value['var']."\S*=\S*([^;]*);/i";
                $replacement = $value['var']." = '".$an['value']."';";
                echo preg_replace($pattern, $replacement, $contents);

            }
        }
    }

    function replaceFileVar($var,$file = '')
    {
        if($file == '')
        {
            $file = file($this->file);
        }

        $contents = implode('',$file);

        if(is_array($var))
        {
            foreach($var AS $value)
            {
                $contents = str_replace($value['var'],$value['val'],$contents);
            }
        }

        $f = fopen($this->file,'w');
        if(!fwrite($f,$contents))
        {
            fclose($f);
            return false;
        }
        fclose($f);

        return true;

    }
}
?>
