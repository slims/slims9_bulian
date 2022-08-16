<?php
/**
 * @composedBy Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-08-16 09:07:12
 * @modify date 2022-08-16 16:18:26
 * @license GPLv3
 * @desc modify from SLiMS Index.php
 */

namespace SLiMS;

use Closure,Exception,Content,utility;

class Opac
{
    /**
     * Opac property
     *
     * @var boolean
     */
    private bool $matchPath = false;
    private string $path = '';
    private array $definedVariable = [];
    private array $sysconf = [];
    private $dbs = null;
    
    /**
     * Opac constructor
     *
     * @param array $definedVariable
     * @param array $sysconf
     * @param [type] $dbs
     */
    public function __construct(array $definedVariable, array $sysconf, $dbs)
    {
        $this->definedVariable = $definedVariable;
        $this->sysconf = $sysconf;
        $this->dbs = $dbs;
    }

    /**
     * Hook process before content
     *
     * @param Closure $callback
     * @return void
     */
    public function hookBeforeContent(Closure $callback)
    {
        $callback($this);
        return $this;
    }

    /**
     * Hook process after content
     *
     * @param Closure $callback
     * @return void
     */
    public function hookAfterContent(Closure $callback)
    {
        $callback($this);
        return $this;
    }

    /**
     * Filter path string
     *
     * @param string $path
     * @return void
     */
    public static function filterPath(string $path)
    {
        $path = utility::filterData('p', 'get', false, true, true);
        // some extra checking
        $path = preg_replace('@^(http|https|ftp|sftp|file|smb):@i', '', $path);
        $path = preg_replace('@\/@i','',$path);

        return $path;
    }

    /**
     * Load coontent based on plugin
     *
     * @return void
     */
    private function loadPluginPath()
    {
        if (isset(($menu = Plugins::getInstance()->getMenus('opac'))[$this->path])) {
            if (file_exists($menu[$this->path][3])) {
                // extract defined variable
                extract($this->definedVariable);
                
                // Default variable for this method
                $path = $this->path;
                $sysconf = $this->sysconf;
                $dbs = $this->dbs;
                $page_title = $menu[$this->path][0];

                // Include plugin file
                include $menu[$this->path][3];
                $this->matchPath = true;
            }
            else
            {
                throw new Exception("Plugin for path {$this->path} is not found!");
            }
        }
        return $this;
    }

    /**
     * Load content from static file 
     * in lib/contents/
     *
     * @return void
     */
    private function loadFileContent()
    {
        if (file_exists(LIB.'contents/'.$this->path.'.inc.php') && !$this->matchPath) {
            // extract defined variable
            extract($this->definedVariable);
            
            if ($this->path != 'show_detail') {
                $this->definedVariable['metadata'] = '<meta name="robots" content="noindex, follow">';
            }

            // Default variable for this method
            $path = $this->path;
            $sysconf = $this->sysconf;
            $dbs = $this->dbs;
            
            include LIB.'contents/'.$this->path.'.inc.php';
            $this->matchPath = true;
        }
        return $this;
    }

    /**
     * Load content from database like
     * news, content etc
     *
     * @return void
     */
    private function loadDbContent()
    {
        if (!$this->matchPath)
        {
            // get content data from database
            $this->definedVariable['metadata'] = '<meta name="robots" content="index, follow">';
            include LIB.'content.inc.php';
            $content = new Content();
            $content_data = $content->get($this->dbs, $this->path);
            if ($content_data) {
                $page_title = $content_data['Title'];
                echo $content_data['Content'];
                unset($content_data);
                $this->matchPath = true;
            }
            else
            {
                $this->loadApi();
            }
        }

        return $this;
    }

    /**
     * Fetch route process
     *
     * @return void
     */
    private function loadApi()
    {
        $sysconf = $this->sysconf;
        $dbs = $this->dbs;
        require SB . 'api/v'.$this->sysconf['api']['version'].'/routes.php';
    }

    /**
     * Parse content to template
     *
     * @return void
     */
    public function parseToTemplate()
    {
        include LIB.'contents/default.inc.php';
        // extract defined variable
        extract($this->definedVariable);
        $sysconf = $this->sysconf;
        $main_content = ob_get_clean();

        // parse into template
        require $this->sysconf['template']['dir'].'/'.$this->sysconf['template']['theme'].'/index_template.inc.php';
        exit;
    }

    /**
     * Handle request based on $_GET['p'];
     *
     * @param string $path
     * @return void
     */
    public function handle(string $path)
    {
        // not isset then by pass to Opac::class
        if (!isset($_GET[$path])) return $this;

        // fiiltering inputed path
        $this->path = self::filterPath($_GET[$path]);

        try {
            // start buffering
            ob_start();

            $this
                ->loadPluginPath() // Plugin first
                ->loadFileContent() // or from lib/contents/
                ->loadDbContent() // or from database
                ->parseToTemplate(); // then parse to template
            
        } catch (Exception $e) {
            // Clear buffer
            ob_get_flush();

            // send as json
            $this->toJson(['status' => false, 'message' => $e->getMessage()]);

            // output buffer
            ob_start();
            echo "<div class=\"alert alert-danger\">{$e->getMessage()}</div>";
            $this->parseToTemplate();
        }
    }

    /**
     * Parse default content
     *
     * @return void
     */
    public function orWelcome()
    {
        $filteredQuery = array_intersect(['keywords','page','title','author','subject','location'], array_keys($_GET));

        ob_start();
        if (empty($filteredQuery)) {
            $this->defineVariable['metadata'] = '<meta name="robots" content="index, follow">';
            // get content data from database
            include LIB.'content.inc.php';
            $content = new Content();
            $content_data = $content->get($this->dbs, 'headerinfo');
            if ($content_data) {
                //$header_info .= '<div id="headerInfo">'.$content_data['Content'].'</div>';
                unset($content_data);
            }
        }

        return $this;
    }

    /**
     * Output content as JSON with header
     *
     * @param [type] $data
     * @return void
     */
    public function toJson($data)
    {
        if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') 
            die(Json::stringify($data)->withHeader());
    }
}