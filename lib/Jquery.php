<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-06 05:39:26
 * @modify date 2023-06-06 06:32:18
 * @license GPLv3
 * @desc [description]
 */
namespace SLiMS;

use Closure;

class Jquery
{
    private string $mainSelector = '';

    public function __construct(string $mainSelector)
    {
        $this->mainSelector = $mainSelector;
    }

    public function writeIfExists(string $contents):void
    {
        echo <<<HTML
        <script type="text/javascript">
            if ($ !== null || jQuery !== null) {
                $(document).ready(function(){
                    {$content}
                })
            } else { console.log('jQuery not exists!') }
        </script>
        HTML;
    }

    public function writeWithTimeOut(int $timeout, string $contents)
    {
        $mainSelector = $this->mainSelector;
        $this->writeIfExists(<<<HTML
        
        HTML);
    }

    public function on(string $selector, string $eventListener, Closure|string $contents)
    {
        $mainSelector = $this->mainSelector;
        $this->writeIfExists(<<<HTML
        $('{$mainSelector}').on('{$eventListener}', '{$selector}', function(e){
            {$contents}
        })
        HTML);
    }

    /**
     * Redirect html content via Simbio AJAX
     *
     * @param string $url
     * @param string $data
     * @param string $position
     * @param string $selector
     * @return void
     */
    public function simbioAJAX(string $url, string $data = '', string $position = 'top.', string $selector = '#mainContent', int $timeout = 0)
    {
        $params = empty($data) ? "'$url'" : "'$url', {method: 'post', addData: '$data'}";
        exit(<<<HTML
        <script>setTimeout(() => {$position}\$('{$selector}').simbioAJAX({$params}), {$timeout})</script>
        HTML);
    }

    
    public function __call($method, $parameter)
    {
        if (!method_exists($this, $method)) {
            $mainSelector = $this->mainSelector;
            $this->writeIfExists(<<<HTML
            $('{$mainSelector}').{$method}(function(e){
                {$content}
            })
            HTML);
        }
    }
}