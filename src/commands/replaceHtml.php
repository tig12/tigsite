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
            - 'site' (required) : associative array corresponding to global site configuration
                See format in docs/
            - 'command' (required) : associative aray with the following keys :
                - 'before' and 'after' (required) : html pieces of code surrounding the html replaced by this function.
                
                - 'replacement-file' : relative path to the file containing
                   the new html code to insert between 'before' and 'after'.
                - 'replacement-string' : string containing the new html code
                   to insert between 'before' and 'after'.
                - 'replacement-directive' :  string containing the directive of a page configuration.
                   This directive indicates the path to a file containing
                   the new html code to insert between 'before' and 'after'.
                NOTE : 'command' must contain one and only one of
                        'replacement-file' or 'replacement-string' or 'replacement-directive'
                
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
        $params['site'] = SiteConfig::compute($params['site']);
        
        if(!isset($params['command']['before'])){
            throw new Exception("Missing \$params['command']['before']");
        }
        if(!isset($params['command']['after'])){
            throw new Exception("Missing \$params['command']['after']");
        }
        $b1 = isset($params['command']['replacement-file']);
        $b2 = isset($params['command']['replacement-string']);
        $b3 = isset($params['command']['replacement-directive']);
        if(!$b1 && !$b2 && !$b3){
            throw new Exception("\$params['command'] must contain either 'replacement-file' or 'replacement-string' or 'replacement-directive'");
        }
        if($b1 && $b2){
            throw new Exception("\$params['command'] cannot contain both 'replacement-file' and 'replacement-string'");
        }
        if($b1 && $b3){
            throw new Exception("\$params['command'] cannot contain both 'replacement-file' and 'replacement-directive'");
        }
        if($b2 && $b3){
            throw new Exception("\$params['command'] cannot contain both 'replacement-string' and 'replacement-directive'");
        }
        if(!isset($params['command']['exclude'])){
            $params['command']['exclude'] = [];
        }
        //
        // prepare variables
        //
        if($b1){
            $replace = file_get_contents($params['site']['location'] . DS . $params['command']['replacement-file']);
        }
        else if($b2){
            $replace = $params['command']['replacement-string'];
        }
        if(!$b3){
            $replace = $params['command']['before'] . $replace . $params['command']['after'];
        }
        
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
        
        //
        // perform replacement
        //
        $pattern = '#' . preg_quote($params['command']['before']) . '(.*?)' . preg_quote($params['command']['after']) . '#sm';
        foreach($files as $file){
            echo "processing $file\n";
            $subject = file_get_contents($file);
            if($b3){
                // $replace must be computed for each page
                $pageConfig = PageConfig::compute($file);
                if(!isset($pageConfig[$params['command']['replacement-directive']])){
                    // replacement-directive does not exist for this page, no need to replace
                    continue;
                }
                $replacementDirective = $pageConfig[$params['command']['replacement-directive']];
                $replacementFile = $params['site']['location'] . DS . $replacementDirective;
                if(!is_file($replacementFile)){
                    $msg = "Bad value for '$replacementDirective' : \"$replacementFile\""
                        . "\n in file $replacementFile";
                    throw new Exception($msg);
                }
                $replace = file_get_contents($replacementFile);
                $replace = $params['command']['before'] . $replace . $params['command']['after'];
            }
            $replace2 = expandVariables::expand($replace, ['root-dir' => $params['site']['location'], 'current-file' => $file]);
// echo "\n"; print_r($replace2); echo "\n";
// continue;
            $new = preg_replace($pattern, $replace2, $subject, -1, $count);
            if($count == 0){
                continue;
            }
            file_put_contents($file, $new);
        }
        
    }
    
}// end class
