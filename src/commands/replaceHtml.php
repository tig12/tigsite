<?php
/******************************************************************************
    Replaces existing html code in a page by a new html string.
    
    @license    GPL
    @history    2019-02-02 02:47:43+01:00, Thierry Graff : Creation
********************************************************************************/
class replaceHtml implements Command {
    
    /** 
        Replaces existing html code in a page by a new html string.
        Restrictions : 
        - In the existing pages, the html code contained in $params['before'] and $params['after']
           must exist and be unique.
        
        @param  $params Associative array that MUST contain the following keys :
            - 'site' (required) : associative array ; see format in docs/
            - 'command' (required) : associative aray with the following keys :
                - 'before' and 'after' (required) : html piece of code surrounding the html replaced by this function.
                - 'replacement-file' : relative path to the file containing the a file containing
                    the new html code to insert between 'before' and 'after'.
                - 'replacement-string' : string containing the new html code to insert between 'before' and 'after'.
                NOTE : 'command' must contain 'replacement-file' or 'replacement-string' but not both.
                - 'exclude' : array of files that must not be concerned by replacement.
        @throws Exception in case of bad parameter
        
        @todo Add parameters "config-file" and "command-file" (only useful for messages in parameter checking)
        @todo Maybe add a parameter to specify the number of occurences replaced
        @todo Maybe add a parameter to specify which occurences are replaced
    **/
    public static function execute($params){
        //
        // check parameters
        //
        $params['site'] = checkSiteConfig::check($params['site']);
        
        if(!isset($params['command']['before'])){
            throw new Exception("Missing \$params['command']['before']");
        }
        if(!isset($params['command']['after'])){
            throw new Exception("Missing \$params['command']['after']");
        }
        if(!isset($params['command']['replacement-file']) && !isset($params['command']['replacement-string'])){
            throw new Exception("\$params['command'] must contain either 'replacement-file' or 'replacement-string'");
        }
        if(isset($params['command']['replacement-file']) && isset($params['command']['replacement-string'])){
            throw new Exception("\$params['command'] cannot contain both 'replacement-file' and 'replacement-string'");
        }
        if(!isset($params['command']['exclude'])){
            $params['command']['exclude'] = [];
        }
        //
        // do the job
        //
        if(isset($params['command']['replacement-file'])){
            $replace = file_get_contents($params['site']['location'] . DS . $params['command']['replacement-file']);
        }
        else{
            $replace = $params['command']['replacement-string'];
        }
        $replace = $params['command']['before'] . $replace . $params['command']['after'];
        
        $excludes = [];
        foreach($params['site']['exclude'] as $exclude){
            $excludes[] = $params['site']['location'] . DS . $exclude;
        }
        foreach($params['command']['exclude'] as $exclude){
            $excludes[] = $params['site']['location'] . DS . $exclude;
        }
        
        $rscandirParams = [
            'include'       => '*.html',
            'exclude'       => $excludes,
            'return-dirs'   => false,
            
        ];
        $files = jth_rscandir::rscandir($params['site']['location'], $rscandirParams);
        
        $pattern = '#' . preg_quote($params['command']['before']) . '(.*?)' . preg_quote($params['command']['after']) . '#sm';
        $N = count($files);
        for($i=0; $i < $N; $i++){
            echo "processing {$files[$i]}\n";
            $subject = file_get_contents($files[$i]);
            $replace2 = expandVariables::expand($replace, ['root-dir' => $params['site']['location'], 'current-file' => $files[$i]]);
            $new = preg_replace($pattern, $replace2, $subject, -1, $count);
            if($count == 0){
                continue;
            }
            file_put_contents($files[$i], $new);
        }
        
    }
    
}// end class
