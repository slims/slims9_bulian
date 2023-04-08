<?php
/**
 * @composedBy Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-08-16 09:07:12
 * @modify date 2023-04-08 10:35:20
 * @license GPLv3
 * @desc modify from SLiMS Index.php
 */

namespace SLiMS;

use Closure,Exception,Content,utility,simbio_security;

class Opac
{
    /**
     * Opac property
     *
     * @var boolean
     */
    private bool $matchPath = false;
    private string $path = '';
    private string $csrf_token = '';
    private bool $invalid_token = false;
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
     * Generate search keywords
     * into array javascript with Json::stringify
     *
     * @return void
     */
    public function generateKeywords(array $advanceSearch = [])
    {
        $result = [];
        $hasKeywords = isset($_GET['search']) && (isset($_GET['keywords'])) && ($_GET['keywords'] != '');
        $hasAdvanceSearch = [];

        foreach ($advanceSearch as $search) { $hasAdvanceSearch[] =  trim($_GET[$search]??''); }

        if ($hasKeywords || $hasAdvanceSearch) 
        {
            $result = preg_replace('@\b(exact|and|or|not)\b@i', '', $_GET['keywords']??implode(' ', $hasAdvanceSearch));
            $result = explode(' ', preg_replace('/[^A-Za-z0-9\s]/i', '', $result));
        }

        return Json::stringify($result);
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
            $opac = $this;
            
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
                $this->page_title = $content_data['Title'];
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
     * Set HTTP header
     *
     * @param string $headerName
     * @param string $headerValue
     * @return void
     */
    public function setHeader(string $headerName, string $headerValue)
    {
        header(trim($headerName) . ':' . trim($headerValue));
    }

    /**
     * Set csrf token
     *
     * @return void
     */
    private function setCsrf()
    {
        $this->csrf_token = \Slims\Opac\Security::getCsrfToken();
        $_SESSION['csrf_token'] = $this->csrf_token;
    }

    /**
     * Get CSRF token
     *
     * @return void
     */
    public function getCsrf()
    {
        return $this->csrf_token;
    }

    /**
     * Validate csrf token
     *
     * @return void
     */
    public function validateCsrf()
    {
        if ((isset($_SESSION['csrf_token'])) AND (isset($_GET['csrf_token'])) ) {
            if (!(\Slims\Opac\Security::checkCsrfToken($_SESSION['csrf_token'], $_GET['csrf_token']))) {
                $this->invalid_token = true;
            }
        }
    }

    /**
     * Set Content Security Policy
     *
     * @param array $additionalCsp
     * @return void
     */
    public function setCsp(array $additionalCsp = [])
    {
        $defaultCsp = config('csp', []);
        if (count($defaultCsp)) $this->setHeader('Content-Security-Policy', implode(';', array_merge($defaultCsp, $additionalCsp)));
    }

    /**
     * Parse content to template
     *
     * @return void
     */
    public function parseToTemplate()
    {
        // OPAC
        $opac = $this;
        
        // Validate incoming token and session
        $opac->validateCsrf();

        // csrf
        $opac->setCsrf();

        // extract defined variable
        extract($opac->definedVariable);
        $sysconf = $opac->sysconf;
        $dbs = $opac->dbs;

        // if (!$this->invalid_token)
        // {
            // load default contents
            include LIB.'contents/default.inc.php';
            
            // Sanitaze quote payload before content
            $sanitizer->quoteFree(exception: ['contentDesc','comment']);

            // Load common SLiMS variable
            require LIB.'contents/common.inc.php';
            $main_content = ob_get_clean();
        // }

        // parse into template
        require $opac->sysconf['template']['dir'].'/'.$opac->sysconf['template']['theme'].'/index_template.inc.php';
        exit;
    }

    public function onWeb(Closure $callback)
    {
        if (!isCli()) $callback($this);
        return $this;
    }

    public function onCli()
    {
        Cli\Console::getInstance()->run();
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
            echo $this->error(isDev() ? $e->getMessage() : 'something wrong.');
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
            $this->definedVariable['metadata'] = '<meta name="robots" content="index, follow">';
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

    private function error(string $message)
    {
        // Clear buffer
        ob_get_flush();

        // send as json
        $this->toJson(['status' => false, 'message' => $message]);

        // output buffer
        ob_start();
        $alertType = 'alert-danger';
        $alertTitle = 'Error';
        $alertMessage = $message;
        $sysconf = $this->sysconf;

        // load alert template
        require SB . 'template/alert.php';
        return ob_get_clean();
    }

    /**
     * Output content as JSON with header
     *
     * @param mix $data
     * @return void
     */
    public function toJson($data)
    {
        if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') 
            die(Json::stringify($data)->withHeader());
    }

    /**
     * Mutatate an existing key in sysconf
     * we don't want to add new key directly
     *
     * @param [type] $key
     * @param [type] $value
     * @return void
     */
    public function mutateConf($key, $value)
    {
        if (isset($this->sysconf[$key])) $this->sysconf[$key] = $value;
    }

    /**
     * Setter for definedVariable property
     *
     * @param string $key
     * @param string $value
     * @return @return Undocumented function
     */
    public function __set($key, $value)
    {
        $this->definedVariable[$key] = $value;
    }

    /**
     * Getter for sysconf & definedVariable property
     *
     * @param string $key
     * @return @return Undocumented function
     */
    public function __get($key)
    {
        if (isset($this->definedVariable[$key])) return $this->definedVariable[$key];
        if (isset($this->sysconf[$key])) return $this->sysconf[$key];
    }
}