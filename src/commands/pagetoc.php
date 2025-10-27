<?php
/**
    Computes table of contents (toc) of html page(s) from h2 and h3 tags.
    
    
    @license    GPL
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
        'action' => 'save',
        'file' => '',
        'file' => '',
        'toc-css-class' => 'pagetoc',
        'toc-tab-length' => 4,
        'insert-after' => '<body>',
        'backup' => false,
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
                        Default: ['h2', 'h3']
                    - 'file' string (optional)
                        File to process.
                        If 
                        Default: ''
                    - 'excludes' array (optional)
                        Array of strings containing relative paths of html files to exclude from dir (only basenames)
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
                    - 'backup' bool (optional)
                        If true, original files are backed up in the same directory (backup files end with ~)
                        Default: 'false'
                        
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
//echo "\n<pre>"; print_r(self::$params); echo "</pre>\n"; exit;
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
            self::$toc .= '<div class="' . self::$params['command']['toc-css-class'] . '">' . "\n";
            self::compute(
                level: 0,
                text: file_get_contents($file),
                prefix: '',
            );
            self::$toc .= "</div>\n";
echo "============================ toc\n"; echo self::$toc . "\n";
echo "============================ text\n"; echo self::$text . "\n";
exit;
        }
    }
    
    /**
        Recursive
    **/
    public static function compute(int $level, string $text, string $prefix): void {
//echo "======\ncompute(level: $level, prefix: $prefix)\n";
//echo "text = $text\n";
        $curTag = self::$params['command']['tags'][$level];
//echo "curTag = $curTag\n";
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
//print_r($m1); exit;
        self::$text .= $m1['begin'] . "\n";
        //
        // catch the blocks delimited by tags of current level
        //
        $p2 = '/<' . $curTag . '(?<tag_attributes>.*?)>(?<tag_contents>.*?)<\/' . $curTag . '>(?<end>.*?)(?=<' . $curTag . '|\Z)/s';
        preg_match_all($p2, $m1['end'], $m2);
//echo "\n<pre>"; print_r($m2); echo "</pre>\n"; exit;
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
                }
                self::$toc .= "$tabs2</li>\n";
            }
            self::$toc .= $tabs1 . "</ul>\n";
        }
    }
    
    /**
        @param  $html       String like '<h2 class="myclass" name="myname" id="myid">Paragraph title</h2>'
        @param  $tagName    String like 'h2'
        @param  $slug       String like 'paragraph-title'
        @param  $prefix     String like '2-'
    **/
    public static function handleTag(string $html, string $tagName, string $slug, string $prefix): array {
//echo "handleTag(\n    html: $html,\n    tagName: $tagName,\n    slug: $slug,\n    prefix: $prefix)\n";
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $tag = $dom->getElementsByTagName($tagName)->item(0);
        $newHtml = '<' . $tagName;
        if($tag){
            $anchor = $prefix . $slug;
            $anchorFound = false;
            foreach ($tag->attributes as $attr) {
                if($attr->nodeName == 'name') {
                    $newHtml .= ' name="' . $anchor . '"';
                    $anchorFound = true;
                } else {
                    $newHtml .= ' ' . $attr->nodeName . '="' . $attr->nodeValue . '"';
                $attributes[$attr->nodeName] = $attr->nodeValue;
                }
            }
            if(!$anchorFound){
                $newHtml .= ' name="' . $anchor . '"';
            }
        }
        $newHtml .= ">\n";
        return [$newHtml, $anchor];
    }
    
}// end class
