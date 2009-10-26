<?php
/**
 * Preparser
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package    Preparser
 * @copyright  Copyright (c) 2009 Valentin Golev (http://va1en0k.net/)
 * @license    New BSD License
 * @version    $Id$
 */
 
namespace Preparser;

function includePreparsed($context, $file = null) { return Preparser::includePreparsed($context, $file); }
function requirePreparsed($context, $file = null) { return Preparser::includePreparsed($context, $file); }
function includePreparsed_once($context, $file = null) { return Preparser::includePreparsed_once($context, $file); }
function requirePreparsed_once($context, $file = null) { return Preparser::includePreparsed_once($context, $file); }
function getValue($tmp, $id) { return $tmp[$id]; }

function setCachePath($path) { Preparser::setCachePath($path); }
 
function getContext() { return Preparser::getContext(); }
function getFileName() { return Preparser::getFileName(); }
 
class Preparser {

    private static $_cachePath = null;
    private static $_tmp = null;
    private static $_contexts = array();
    private static $_fileName = array();
    //public static function setTmp($tmp) { self::$_tmp = $tmp; }
    //public static function getTmp($id) { return self::$_tmp[$id]; }
    
    public static function getFileName() {
        return self::$_fileName;
    }
    public static function setFileName($filename) {
        self::$_fileName = $filename;
    }
    
    public static function getCachePath() {
        return self::$_cachePath;
    }
    public static function setCachePath($path) {
        self::$_cachePath = realpath($path);
    }
    
    public static function getContext() {
        return self::$_contexts[ sizeof(self::$_contexts) - 1 ];
    }
    public static function setContext($context) {
        self::$_contexts[] = $context;
    }
    public static function releaseContext() {
        array_pop(self::$_contexts);
    }
    
    private static $includePaths = array();
    private static $included = array();
    private static $skipTokens = array(
        T_COMMENT, T_DOC_COMMENT, T_ML_COMMENT
    );
    private static function preparse($file) {
        $code = file_get_contents($file, FILE_USE_INCLUDE_PATH);
        
        $preparsedCode = '';
        
        $tokens = token_get_all($code);
        
        $was_brace = 0;
        
        $func_now = array('');
        
        $was_function = '';
        $last_tokens = array();
        
        $all_last_tokens = array();
        
        $figure_braces = 0;
        
        $in_class_since = false;
        
  //      echo '<pre>';
            
        foreach($tokens as $token) {
            if (is_array($token)) {
                if (in_array($token[0], self::$skipTokens))
                    continue;
                
                if ($token[0] == T_CLASS) {
                    $in_class_since = $figure_braces + 1;
                }
                
                if ($token[0] == T_CURLY_OPEN) $figure_braces++;
                if ($token[0] == T_DOLLAR_OPEN_CURLY_BRACES) $figure_braces++;
                
                if ($token[0] == T_REQUIRE || $token[0] == T_INCLUDE) {
                    $was_brace = 1;
                    $addMe = 'Preparser\includePreparsed(@$this, ';
                } else if ($token[0] == T_REQUIRE_ONCE || $token[0] == T_INCLUDE_ONCE) {
                    $was_brace = 1;
                    $addMe = 'Preparser\includePreparsed_once(@$this, ';
                } else if ($token[0] == T_VARIABLE && $token[1] == '$this' && (!$in_class_since || ($figure_braces < $in_class_since))) {
                    $addMe = 'Preparser\getContext()';
                } else {
                    
                    $addMe = $token[1];
                    
                    
                }
                $last_tokens[] = array($token[0], $addMe);
                if ($token[0] == T_WHITESPACE)
                    $was_function .= $token[1];
                    
            } else {
                if ($token == ';') {
                
                    //$was_end_brace = false;
                    
                    if ($was_brace) {
                        $was_brace = 0;
                        $preparsedCode .= ')';
                    }
                }
                
                if ($token == '{') $figure_braces++;
                if ($token == '}') $figure_braces--;
                
                if ($token == '(') {
                    if ($was_brace) $was_brace++;
                    $func_now[] = $last_tokens[ sizeof($last_tokens) - 1][1];
                }
                
                if ($token == ')') {
                    if ($was_brace) {
                        $was_brace--;
                        if (!$was_brace)
                            $preparsedCode .= ')';
                    }
                    $func_now[ sizeof($func_now) - 1 ] .= $token; 
                    
                    $was_function = array_pop($func_now);
                    
                    while ( $last_tokens[ sizeof($last_tokens) - 1][0] == T_WHITESPACE )
                        array_pop($last_tokens);
                    
                    array_pop($last_tokens);
                }
                
                if ($token == '[' ) {

                    while ( $last_tokens[ sizeof($last_tokens) - 1][0] == T_WHITESPACE )
                        array_pop($last_tokens);
                    
                    while ( $all_last_tokens[ sizeof($all_last_tokens) - 1][0] == T_WHITESPACE )
                                array_pop($all_last_tokens);
                    
                    if ( $all_last_tokens[ sizeof($all_last_tokens) - 1] == ')' ) {
                        
                        
                        if ($last_tokens[ sizeof($last_tokens) - 1][0] == T_OBJECT_OPERATOR ||
                            $last_tokens[ sizeof($last_tokens) - 1][0] == T_DOUBLE_COLON ||
                            $last_tokens[ sizeof($last_tokens) - 1][0] == T_NS_SEPARATOR
                            ) {
                            
                            while ( $all_last_tokens[ sizeof($all_last_tokens) - 1][0] == T_WHITESPACE )
                                array_pop($all_last_tokens);
                            
                            do {
                                $tok = array_pop($all_last_tokens);
                                if (!is_array($tok)) {
                                    if ($tok == ')')
                                        $skobs++;
                                    else if ($tok == '(')
                                        $skobs--;
                                }
                            } while ($skobs); // пропустили скобки
                            
                            while ( $all_last_tokens[ sizeof($all_last_tokens) - 1][0] == T_WHITESPACE )
                                array_pop($all_last_tokens);
                            
                            // пропускаем имя функции
                            array_pop($all_last_tokens);
                            
                            while ( $all_last_tokens[ sizeof($all_last_tokens) - 1][0] == T_WHITESPACE )
                                array_pop($all_last_tokens);
                            
                            // теперь там наверху -> или ::
                            //echo htmlspecialchars(print_r($all_last_tokens, true)); 
                            while ( $all_last_tokens[ sizeof($all_last_tokens) - 1][0] == T_OBJECT_OPERATOR ||
                                    $all_last_tokens[ sizeof($all_last_tokens) - 1][0] == T_DOUBLE_COLON ||
                                    $all_last_tokens[ sizeof($all_last_tokens) - 1][0] == T_NS_SEPARATOR ) {
                                $tok = array_pop($all_last_tokens);
                                $was_function = $tok[1] . $was_function;
                                
                                //
                                
                                $tok = array_pop($all_last_tokens); // var or smth
                                if ($tok == ')') {
                                    $was_function = $tok . $was_function;
                                    
                                    $skobs = 1;
                                    while ($skobs) {
                                        $tok = array_pop($all_last_tokens);
                                        if (is_array($tok)) {
                                            $was_function = $tok[1] . $was_function;
                                        } else {
                                            if ($tok == ')') {
                                                $skobs++;
                                            } else if ($tok == '(') {
                                                $skobs--;
                                            }
                                            $was_function = $tok . $was_function;
                                        }
                                        
                                    }
                                    $tok = array_pop($all_last_tokens);
                                }
                                //echo htmlspecialchars(print_r($all_last_tokens, true)); 
                                $was_function = $tok[1] . $was_function;
                            }
                            //die(":" .token_name( $all_last_tokens[ sizeof($all_last_tokens) - 1][0]));
                        }
                        
                        
                        $preparsedCode = substr($preparsedCode, 0,  - strlen($was_function) );
                        foreach($func_now as &$func)
                            $func = substr($func, 0,  - strlen($was_function));
                        
                        $addMe = 'Preparser\getValue(' . $was_function . ', ';
                        
                        $please_close_bracket++;
                    } else {
                        $addMe = $token;
                    }
                } else if ($token == ']' && $please_close_bracket) {
                    $please_close_bracket--;
                    
                    $addMe = ')';
                    
                } else {
                    $addMe = $token;
                }
            }
            $all_last_tokens[] = $token;
            
            foreach($func_now as &$func)
                $func .= $addMe; 
            
            $preparsedCode .= $addMe;
            
            
        }
        
    //    echo htmlspecialchars($preparsedCode);
        
        return $preparsedCode;
    }
    private static function getRealFileName($file) {
        if (is_file($file))
            return realpath($file);
                
        $paths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($paths as $path)
            if (is_file( $fileName = trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file))
                return realpath($fileName);

        return false;
    }
    private static function fixIncludePath($file = null) {
        if ($file) {
            $path = get_include_path();
            set_include_path(implode(PATH_SEPARATOR, array(
                realpath(dirname($file)),
                $path
            )));
            self::$includePaths[] = $path;
        } else {
            set_include_path(array_pop(self::$includePaths));
        }
    }
    private static function safeEval($code) {
        $f = function($code) {
            return eval( '?>' . $code );
        };
        return $f($code);
    }
    private static function safeInclude($filename) {
        self::setFileName($filename);
        $f = function() {
            return include(getFileName());
        };
        return $f($code);
    }
    public static function includePreparsed($context, $file = null, $once = false) {
        
        if ( is_string($context) && !is_string($file) ) {
            $file = $context;
            $context = null;
        }
        
        $fileName = self::getRealFileName($file);
        
        if (!$fileName) {
            throw new \Exception('Preparser::includePreparsed(' . htmlspecialchars($file) . '): failed to open stream: No such file or directory '.$context, E_USER_ERROR);
            die;
        }
        
        if ($once && in_array($fileName, self::$included))
            return;
        
        self::$included[] = $fileName;
        
//        echo $fileName, '<br/>';
        
        self::fixIncludePath($fileName);
        self::setContext($context);
        
        if (self::getCachePath() && is_file(self::getCachePath() . '/cache_' . md5($fileName) . '.php')
            && (filemtime($fileName) < filemtime(self::getCachePath() . '/cache_' . md5($fileName) . '.php'))) {
            $ret = self::safeInclude(self::getCachePath() . '/cache_' . md5($fileName) . '.php');
        } else {
            $code = self::preparse($fileName);
            $code = str_replace('__FILE__', "'$fileName'", $code);
            
            if (!self::getCachePath()) {
                $ret = self::safeEval( $code );
            } else {
                file_put_contents(self::getCachePath() . '/cache_' . md5($fileName) . '.php', $code);
                $ret = self::safeInclude(self::getCachePath() . '/cache_' . md5($fileName) . '.php');
            }
        }
        
        
        self::releaseContext();
        self::fixIncludePath();
        
        return $ret ? $ret : true;
    }
    public static function includePreparsed_once($context, $file = null) {
        return self::includePreparsed($file, $context, true);
    }
}