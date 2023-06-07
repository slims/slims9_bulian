<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-06 05:39:26
 * @modify date 2023-06-06 15:59:35
 * @license GPLv3
 * @desc [description]
 */
namespace SLiMS;

use Closure;

class Jquery
{
    private static ?object $instance = null;
    private string $mainSelector = '';
    private string $result = '';
    private string $contents = '';
    public string $position = 'top.';

    private function __construct(){}

    public static function getInstance(string $mainSelector)
    {
        if (is_null(self::$instance)) self::$instance = new Jquery;
        self::$instance->mainSelector = $mainSelector;
        return self::$instance;
    }

    public static function raw(string $content)
    {
        $instance = self::getInstance('');
        $instance->contents = $instance->position . 'jQuery.' . $content;
        echo $instance;
        $instance->contents = '';
    }

    public function writeIfExists(string $contents):void
    {
        $position = $this->position;
        $message = isDev() ? "alert('jQuery not exists!')" : "console.log('jQuery not exists!')";
        $this->result = <<<HTML
        <script type="text/javascript">
            if (typeof {$position}\$ === 'function') {
                {$position}\$(document).ready(function(){
                    {$contents}
                })
            } else { {$message} }
        </script>
        HTML;
    }

    public function delayIn(int $timeout)
    {
        $contents = $this->contents;
        $this->contents = '';
        $this->setContent(<<<HTML
            setTimeout(() => {$contents}, {$timeout})
        HTML);
        return $this;
    }

    public function on(string $selector, string $eventListener, Closure|string $contents)
    {
        $position = $this->position;
        $mainSelector = $this->mainSelector;
        $this->setContent(<<<HTML
        {$position}\$('{$mainSelector}').on('{$eventListener}', '{$selector}', function(e){
            {$contents}
        })
        HTML);
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setPosition(string $position)
    {
        $this->position = $position;
        return $this;
    }

    private function setContent(string $content)
    {
        $this->contents .= $content . PHP_EOL;
    }
    
    
    public function __call($method, $parameters)
    {
        if (!method_exists($this, $method)) {
            $mainSelector = $this->mainSelector;
            $position = $this->position;
            
            if (count($parameters) === 1 && is_callable($parameters[0]))
            {
                $contents = <<<HTML
                function(e) {
                    {$parameters[0]()}
                }
                HTML;
            } else if (count($parameters) === 1 && substr($parameters[0], 0,1) === "'") {
                $contents = $parameters[0];
            } else { 
                $contents = "'" . implode(',', $parameters) . "'"; 
            }

            $this->setContent(<<<HTML
            {$position}\$('{$mainSelector}').{$method}({$contents})
            HTML);

            return $this;
        }
    }

    public function __toString():String
    {
        $this->writeIfExists($this->contents);
        return $this->getResult();
    }
}