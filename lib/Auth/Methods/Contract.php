<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-12-05 15:26:50
 * @modify date 2023-12-05 15:26:50
 * @license GPL-3.0 
 * @descp
 */
namespace SLiMS\Auth\Methods;

use Countable;

abstract class Contract implements Countable
{
    // Static data for login type
    const LIBRARIAN_LOGIN = 1;
    const MEMBER_LOGIN = 2;

    /**
     * Login Type
     */
    protected int $type = 0;

    /**
     * @var string $username
     */
    protected string $username = '';

    /**
     * @var string $password
     */
    protected string $password = '';

    /**
     * @var array $data
     */
    protected array $data = [];

    public function __construct()
    {
        
    }

    /**
     * Authentication for member
     */
    abstract protected function memberAuthenticate();

    /**
     * Authentication for admin
     */
    abstract protected function adminAuthenticate();

    /**
     * Generating session based on data
     *
     * @return void
     */
    public function generateSession()
    {
        foreach($this->data as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    public function fetchRequest(array $keys)
    {
        $map = ['username','password'];
        foreach($keys as $order => $key) {
            $this->{$map[$order]} = $_POST[$key]??'';
        }
    }

    public function getData(string $key = '')
    {
        return $this->data[$key]??$this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function setType($type) : void {
        $this->type = $type;
    }

    public function has2Fa()
    {
        return isset($this->data['2fa']) && !empty($this->data['2fa']);
    }

    public function hookProcess(){}

    public function __call($method, $arguments)
    {
        if (method_exists($this, $fixMethod = $method . 'Authenticate')) {
            return $this->$fixMethod(...$arguments);
        }
    }

    public function count():int
    {
        return count($this->data);
    }
}