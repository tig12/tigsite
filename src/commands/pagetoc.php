<?php
/**
    Computes table of contents (toc) of html page(s) from h2 and h3 tags.
    
    @license    GPL - conforms to file LICENCE located in the root directory of current repository.
    @copyright  Thierry Graff
    @history    2024-02-23 23:38:48+02:00, Thierry Graff : Creation
**/

namespace tigsite\commands;

use tigsite\commands\shared\SiteConfig;
use tigsite\commands\shared\PageConfig;
use tigsite\commands\shared\CheckParams;
use tigsite\commands\shared\ExpandVariables;
use tiglib\patterns\command\Command;
use tiglib\filesystem\rscandir;
use tiglib\strings\slugify;

class pagetoc implements Command {
    
    /** Default values for $params['command'] passed to execute() **/
    const DEFAULT_PARAMS = [
        'action'            => 'save',
        'tags'              => ['h2', 'h3'],
        'file'              => '',
        'toc-css-class'     => 'pagetoc',
        'toc-tab-length'    => 4,
        'insert-after'      => '<body>',
        'create-backup'            => false,
        'backup-extension'  => '.bck',
    ];
    
    const POSSIBLE_ACTIONS = ['save', 'print-toc', 'print-full'];
    
    /** Cleaned version of parameter $params passed to execute() **/
    public static array $params = [];
    
    /** Stores the resulting page **/
    private static string $text = '';
    
    /** Stores the resulting table of contents **/
    private static string $toc = '';
    
    /** Shortcut tfor tab used when generating toc **/
    private static string $toctab = '';
    
    /** 
        @param  $params Associative array that MUST contain the following keys :
            - 'site' (required) :
                Associative array corresponding to global site configuration
                See format in docs/
            - 'command' (required) :
                Associative array with the following keys :
                    - 'action' string (optional)
                        Action to execute ; can be :
                            'save'          : the concerned file(s) are overwritten with the new toc.
                            'print-toc'     : Prints the new toc without overriding the files.
                            'print-full'    : Prints the whole file(s) without overriding the files.
                        Default: 'save'
                    - 'tags' array (optional)
                        Array of strings designating the html tags used to build the toc.
                        Default: ['h2', 'h3']
                    - 'file' string (optional)
                        File to process.
                        If not specified, all the files specified in the site configuration (in <code>config.yml</code>) are processed.
                        Default: ''
                    - 'excludes' array (optional)
                        Array of strings containing relative paths of html files to exclude from site root-dir
                        Default: []
                    - 'toc-css-class' string (optional)
                        CSS class of the div containing the generated toc
                        Default: 'pagetoc'
                    - 'toc-tab-length' int (optional)
                        Number of white spaces used to indent the pagetoc list.
                        Default: 4
                    - 'insert-after' string (optional)
                        In the resulting page, the toc is inserted after this html fragment of the original page
                        Default: '<body>'
                    - 'create-backup' bool (optional)
                        If true, original files are backed up in the same directory
                        Used only if parameter 'action' = 'save'
                        Default: false
                    - 'backup-extension' string (optional)
                        String appended to the original file name when creating the backup files
                        Used only if parameter 'create-backup' = true
                        Default: '.bck'
                        
        @throws \Exception in case of bad parameter
    **/
    public static function execute($params=[]
    ){
        //
        // handle parameters
        //
        CheckParams::check($params);
        $params['site'] = SiteConfig::compute($params['site']);
        $params['command'] = array_replace(self::DEFAULT_PARAMS, $params['command']);
        // global vars
        self::$params = $params;
        self::$toctab = str_repeat(' ', self::$params['command']['toc-tab-length']);
        //
        // compute files to process
        //
        $files = $params['command']['file'] == ''
            ? SiteConfig::computeFiles(siteConfig: $params['site'], command: $params['command'])
            : [ $params['site']['site-root'] . DS . $params['command']['file'] ];
        //
        // compute the toc and the resulting text
        //
        foreach($files as $file){
            self::$toc = '';
            self::$text = '';
            self::$toc .= "\n" . '<nav class="' . self::$params['command']['toc-css-class'] . '">' . "\n";
            $text = @file_get_contents($file);
            if($text === false){
                echo "ERROR: impossible to read file $file\n";
                continue;
            }
            self::compute(
                level: 0,
                text: file_get_contents($file),
                prefix: '',
            );
            self::$toc .= "</nav>";
            //
            // generate output
            //
            if(self::$params['command']['action'] == 'print-toc'){
                echo self::$toc . "\n";
                continue;
            }
            // here, action = 'save' or 'print-full'
            // Add the toc in the original contents
            $pattern = '#'
                . '('
                . self::$params['command']['insert-after']
                . '\s*'
                . '(?:<nav class="' . self::$params['command']['toc-css-class'] . '">.*?</nav>)?'
                . ')'
                . '#s';
            preg_match($pattern, self::$text, $m);
            if(!isset($m[1])){
                echo "ERROR: unable to insert new table of contents in $file\n"
                    . "Check that the file contains " . self::$params['command']['insert-after'];
                continue;
            }
            $insertNewline = (trim($m[1]) == self::$params['command']['insert-after']) ? true : false; //  true if the page does not already contain a pagetoc.
/* 
            // for unknown reason, preg_replace was greedy, so used str_replace instead
            $pattern = '#'
                . self::$params['command']['insert-after']
                . '\s*'
                . '(?:<nav class="' . self::$params['command']['toc-css-class'] . '">.*?</nav>)?'
                . '#s';
            $replace = self::$params['command']['insert-after'] . self::$toc;
            self::$text = preg_replace($pattern, $replace, self::$text);
*/
            self::$text = str_replace(
                $m[1],
                self::$params['command']['insert-after'] . "\n" . self::$toc . ($insertNewline ? "\n\n" : ''),
                self::$text,
            );
            if(self::$params['command']['action'] == 'print-full'){ 
                echo self::$text . "\n";
                continue;
            }
            // here, action = 'save'
            if(self::$params['command']['create-backup']) {
                $newfile = $file . self::$params['command']['backup-extension'];
                copy($file, $newfile);
                echo "Generated backup file $newfile\n";
            }
            file_put_contents($file, self::$text);
            echo "Wrote TOC in file $file\n";
        }
    }
    
    /**
        Recursive
    **/
    public static function compute(int $level, string $text, string $prefix): void {
        $curTag = self::$params['command']['tags'][$level];
        //
        // catch the beginning of the text
        //
        $p1 = '/(?<begin>.*?)(?<end><' . $curTag . '.*)/si';
        preg_match($p1, $text, $m1);
        if(!isset($m1['begin'])){
            // end of file reached
            self::$text .= $text;
            return;
        }
        self::$text .= $m1['begin'];
        //
        // catch the blocks delimited by tags of current level
        //
        $p2 = '/\s*<' . $curTag . '(?<tag_attributes>.*?)>(?<tag_contents>.*?)<\/' . $curTag . '>(?<end>.*?)(?=<' . $curTag . '|\z)/si';
        preg_match_all($p2, $m1['end'], $m2);
        if(count($m2[0]) > 0) {
            $tabs1 = str_repeat(self::$toctab, 2*$level + 1);
            $tabs2 = str_repeat(self::$toctab, 2*$level + 2);
            $tabs3 = str_repeat(self::$toctab, 2*$level + 3);
            self::$toc .= $tabs1 . "<ul>\n";
            for($i=0; $i < count($m2[0]); $i++){
                self::$toc .= $tabs2 . "<li>\n";
                $slug = slugify::compute($m2['tag_contents'][$i]);
                $newPrefix = $prefix . ($i + 1) . '-';
                // reconstitute tag string -- TODO use nested capturing pattern
                $html = '<' . $curTag . $m2['tag_attributes'][$i] . '>' . $m2['tag_contents'][$i] . '</' . $curTag . '>';
                [$newHtml, $anchor] = self::handleTag($html, $curTag, $slug, $newPrefix);
                self::$toc .= $tabs3 . '<a href="#' . $anchor . '">' . $m2['tag_contents'][$i] . "</a>\n";
                self::$text .= $newHtml;
                if(count(self::$params['command']['tags']) > $level + 1){
                    self::compute($level + 1, $m2['end'][$i], $newPrefix); //           recursive here
                } else {
                    self::$text .= $m2['end'][$i];
                }
                self::$toc .= "$tabs2</li>\n";
            }
            self::$toc .= $tabs1 . "</ul>\n";
        }
    }
    
    /**
        @param  $html       String like '<h2 class="myclass" id="myid">Paragraph title</h2>'
        @param  $tagName    String like 'h2'
        @param  $slug       String like 'paragraph-title'
        @param  $prefix     String like '2-'
        @return Array containing 2 elements :
                    - A string containing the original tag with an attribute id added or replaced
                      Ex: '<h2 class="myclass" id="2-paragraph-title">Paragraph title</h2>'
                    - The new value of tag id
                      Ex: '2-paragraph-title'
    **/
    public static function handleTag(string $html, string $tagName, string $slug, string $prefix): array {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $tag = $dom->getElementsByTagName($tagName)->item(0);
        $newHtml = '<' . $tagName;
        if($tag){
            $id = $prefix . $slug;
            $idFound = false;
            foreach ($tag->attributes as $attr) {
                if($attr->nodeName == 'id') {
                    $newHtml .= ' id="' . $id . '"';
                    $idFound = true;
                } else {
                    $newHtml .= ' ' . $attr->nodeName . '="' . $attr->nodeValue . '"';
                $attributes[$attr->nodeName] = $attr->nodeValue;
                }
            }
            if(!$idFound){
                $newHtml .= ' id="' . $id . '"';
            }
        }
        $newHtml .= '>' . $tag->textContent . '</' . $tagName . '>';
        return [$newHtml, $id];
    }
    
}// end class
