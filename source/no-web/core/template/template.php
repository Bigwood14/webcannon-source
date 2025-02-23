<?PHP
class template
{
    var $directory;
    var $file;
    
    function template()
    {
    }
    
    function display($file = false)
    {
        if($file === false)
        {
            $file = $this->file;
        }
        $template = &$this;
        include $template->directory.$file;
    }
}
?>