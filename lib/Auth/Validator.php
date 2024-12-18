<?php
namespace SLiMS\Auth;

use SLiMS\Auth\Exception;
use SLiMS\Auth\Methods\Contract;
use SLiMS\DB;

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

            if (!$this->methodInstance instanceof Contract) throw new Exception('Method ' . get_class($this->methodInstance) . ' is not instance of Auth Contract');

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

    /**
     * Method to generate random token
     * 
     * @return array
     */
    function generateTokens(): array
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));

        return [$selector, $validator, $selector . ':' . $validator];
    }

    /**
     * Method to parse token
     * 
     * @return ?array
     */
    function parseToken(string $token): ?array
    {
        $parts = explode(':', $token);

        if ($parts && count($parts) == 2) {
            return [$parts[0], $parts[1]];
        }
        return null;
    }

    /**
     * Method to store user token
     * 
     * @return boolean
     */
    function setUserToken(int $user_id, string $selector, string $hashed_validator, string $expires_at): bool
    {
        $sql = 'INSERT INTO user_tokens(user_id, selector, hashed_validator, expires_at, created_at)
            VALUES(:user_id, :selector, :hashed_validator, :expires_at, now())';

        $statement = DB::getInstance()->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':selector', $selector);
        $statement->bindValue(':hashed_validator', $hashed_validator);
        $statement->bindValue(':expires_at', $expires_at);

        return $statement->execute();
    }

    /**
     * Method to get user token
     * 
     * @return array|bool
     */
    function getUserToken(string $selector)
    {

        $sql = 'SELECT id, selector, hashed_validator, user_id, expires_at
                FROM user_tokens
                WHERE selector = :selector AND
                    expires_at >= now()
                LIMIT 1';

        $statement = DB::getInstance()->prepare($sql);
        $statement->bindValue(':selector', $selector);

        $statement->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Method to delete user token
     * 
     * @return boolean
     */
    function deleteUserToken(int $user_id): bool
    {
        $sql = 'DELETE FROM user_tokens WHERE user_id = :user_id';
        $statement = DB::getInstance()->prepare($sql);
        $statement->bindValue(':user_id', $user_id);

        return $statement->execute();
    }

    /**
     * Method to get user by token
     * 
     * @return array
     */
    function findUserByToken(string $token): ?array
    {
        [$selector, $validator] = $this->parseToken($token);

        if (!$selector) {
            return null;
        }

        $sql = 'SELECT u.user_id AS uid, u.username AS uname, u.passwd, 
                       u.realname AS realname, u.groups, u.user_image AS upict, u.2fa
            FROM user AS u
            INNER JOIN user_tokens AS ut ON ut.user_id = u.user_id
            WHERE ut.selector = :selector AND ut.expires_at > now()
            LIMIT 1';

        $statement = DB::getInstance()->prepare($sql);
        $statement->bindValue(':selector', $selector);
        $statement->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Method to get user by username
     * 
     * @return array
     */
    function findUserByUsername($username): array
    {
        $sql = 'SELECT u.user_id AS uid, u.username AS uname, u.passwd, 
                u.realname AS realname, u.groups, u.user_image AS upict, u.2fa 
                FROM user AS u WHERE u.username = :username';

        $statement = DB::getInstance()->prepare($sql);
        $statement->bindValue(':username', $username);
        $statement->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Method to validate token
     * 
     * @return boolean
     */
    function isTokenValid($token): bool
    {
        [$selector, $validator] = $this->parseToken($token);

        $tokens = $this->getUserToken($selector);

        if (!$tokens) return false;

        return password_verify($validator, $tokens['hashed_validator']);
    }

    /**
     * Method to remember user login
     */
    function rememberMe(int $day = 30)
    {
        $user_id = $this->getData('uid');

        [$selector, $validator, $token] = $this->generateTokens();

        // remove all existing token associated with the user id
        $this->deleteUserToken($user_id);

        // set expiration date
        $expired_seconds = time() + 60 * 60 * 24 * $day;

        // insert a token to the database
        $hash_validator = password_hash($validator, PASSWORD_DEFAULT);
        $expires_at = date('Y-m-d H:i:s', $expired_seconds);

        if ($this->setUserToken($user_id, $selector, $hash_validator, $expires_at)) {
            setcookie('remember_me', $token, [
                'expires' => $expired_seconds,
                'path' => SWB,
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            setcookie('remember_me', $token, [
                'expires' => $expired_seconds,
                'path' => AWB,
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    /**
     * Method to check user already login
     * 
     * @return boolean
     */
    function isUserLoggedIn(): bool
    {
        // check the session
        if (isset($_SESSION['uname'])) {
            return true;
        }

        // check the remember_me in cookie
        $token = filter_input(INPUT_COOKIE, 'remember_me', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($token && $this->isTokenValid($token)) {

            $user = $this->findUserByToken($token);

            if ($user) {
                $this->methodInstance->setData($user);
                $this->methodInstance->setAdminData()->generateSession();
                return true;
            }
        }

        return false;
    }

    /**
     * Method to logout from session
     * 
     * @return void
     */
    function logout($destroy= true): void
    {
        if ($this->isUserLoggedIn()) {

            // delete the user token
            $this->deleteUserToken($_SESSION['uid']);

            // delete session
            $_SESSION = [];
        }

        // remove cookies
        $this->removeCookie('remember_me', AWB);
        $this->removeCookie('remember_me', SWB);
        $this->removeCookie('admin_logged_in', SWB);
    }

    /**
     * Method to remove cookie
     */
    function removeCookie($key, $path) : void {
        setcookie($key, FALSE, [
            'expires' => time()-86400,
            'path' => $path,
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}