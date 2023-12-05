<?php
namespace SLiMS\Auth;

use SLiMS\Auth\Exception;
use SLiMS\Auth\Methods\Contract;

class Validator
{
    private string $error = '';

    /**
     * Store authentication instance
     */
    private ?object $methodInstance = null;

    public function __construct(string $methodClass)
    {
        $this->methodInstance = new $methodClass;
    }

    /**
     * Undocumented function
     *
     * @param string $methodClass
     * @return Validator
     */
    public static function use(string $methodClass):Validator
    {
        return new static($methodClass);
    }

    /**
     * call authenticate method
     * @param string $type
     * @return boolean
     */
    public function process(string $type):bool
    {
        try {

            if (!$this->methodInstance instanceof Contract) throw new Exception('Method ' . get_class($this->methodInstance) . ' is not instace of Auth Contract');

            $authenticate = $this->methodInstance->$type();

            if ($authenticate->has2Fa()) {
                # redirect to f2a page
                $_SESSION['user'] = $authenticate->getData();
                redirect('index.php?p=2fa');
            }

            $authenticate->generateSession();

            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getMethodInstance()
    {
        return $this->methodInstance;
    }

    public function getData()
    {
        return $this->methodInstance?->getData(...func_get_args())??[];
    }

    public function getHook()
    {
        $this->methodInstance->hookProcess();
    }

    public function getError()
    {
        return $this->error;
    }
}