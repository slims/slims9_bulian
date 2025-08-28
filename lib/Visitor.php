<?php
/**
 * @compose by Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-09-18 19:39:17
 * @modify date 2023-01-26 15:04:20
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS;

class Visitor
{
    /**
     * Default visitor property
     *
     * @var array
     */
    protected array $allowedIp;
    protected string $visitTimeLimit;
    protected bool $accessAllow = false;
    protected array $data = [];
    protected bool $alreadyCheckIn = false;
    protected bool $member = false;
    protected bool $memberExpire = false;
    protected bool $institutionEmpty = false;
    protected string $error = '';
    protected string $image = 'person.png';
    protected bool $result = false;
    protected $opac;

    public function __construct(array $allowedIp, string $visitTimeLimit, Opac $opac)
    {
        $this->allowedIp = $allowedIp;
        $this->visitTimeLimit = $visitTimeLimit;
        $this->opac = $opac;
    }

    /**
     * Access check by ip
     *
     * @return Visitor
     */
    public function accessCheck()
    {
        foreach ($this->allowedIp as $ip) {
        // change wildcard
            $ip = preg_replace('@\*$@i', '.', $ip);
            if ($ip == ip() || $_SERVER['HTTP_HOST'] == 'localhost' || preg_match("@$ip@i", ip())) $this->accessAllow = true;
        }

        return $this;
    }

    /**
     * Record visitor counter
     *
     * @param int|string $memberId
     * @return Visitor
     */
    public function record($memberId)
    {
        $db = DB::getInstance();
        //DB::debug();

        try {
            $query = "SELECT member_id,member_name,member_image,inst_name, IF(TO_DAYS('".date('Y-m-d')."')>TO_DAYS(expire_date), 1, 0) AS is_expire FROM member WHERE member_id = ?";
            $statement = $db->prepare($query);
            $statement->execute([$memberId]);

            // room code check
            if (isset($_GET['room']) && strlen($_GET['room']) > 5) throw new \PDOException("Room code is not valid!");

            // Member
            if ($statement->rowCount() > 0)
            {
                $this->member = true;
                $data = $statement->fetch(\PDO::FETCH_NUM);
                
                // set image based on record data
                $this->image = $data[2]??'person.png';

                // set expire status
                if ($data[4] == 1) $this->memberExpire = true;
                
                // unset image and expire status
                unset($data[4]);
                unset($data[2]);

                $this->data = array_values($data);
                Plugins::getInstance()->execute('MEMBER_ON_VISIT', ['data' => $statement->fetch(\PDO::FETCH_ASSOC)]);
            }
            // Guest
            else
            {
                // institution check for guest
                if (empty(trim($_POST['institution'])))
                {
                    $this->institutionEmpty = true;
                    return $this;
                }

                // default non member photos
                $this->image = 'non_member.png';
                $this->data = [ null, $memberId,trim($_POST['institution'])];
                Plugins::getInstance()->execute('NON_MEMBER_ON_VISIT', ['data' => array_slice($this->data, 1)]);
            }

        
            $insertQuery = "INSERT INTO visitor_count (member_id, member_name, institution, room_code, checkin_date) VALUES (?,?,?,?,?)";
            $insertStatement = $db->prepare($insertQuery);

            if ($this->opac->enable_visitor_limitation && $this->alreadyCheckIn($memberId, $this->member))
            {
                $this->result = true;
                $this->alreadyCheckIn = true;
            }
            else
            {
                $this->result = $insertStatement->execute(array_merge($this->data, [$_GET['room']??null, date('Y-m-d H:i:s')]));
            }
        } catch (\PDOException $e) {
            // set error
            $this->error = $e->getMessage();
            $this->result = false;
        }

        return $this;
    }

    /**
     * Get last data to decide
     * ready checkin now or later
     *
     * @param int|string $memberIdOrName
     * @param boolean $isMember
     * @return bool
     */
    protected function alreadyCheckIn($memberIdOrName, $isMember = true)
    {
        $db = DB::getInstance();
        $criteria = 'member_name';
        if ($isMember) $criteria = 'member_id';
    
        $statement = $db->prepare('SELECT checkin_date FROM visitor_count WHERE '.$criteria.'=? ORDER BY checkin_date DESC LIMIT 1');
        $statement->execute([$memberIdOrName]);
        
        if ($statement->rowCount() > 0) {
            $data = $statement->fetchObject();
            $time = new \DateTime($data->checkin_date);
            $time->add(new \DateInterval('PT'.$this->visitTimeLimit.'M'));
            $timelimit = $time->format('Y-m-d H:i:s');
            $now = date('Y-m-d H:i:s');
            if ($now < $timelimit) {
                return true;
            }
        }
    
        return false;
    }

    public function isMember()
    {
        return $this->member;
    }

    public function isAlreadyCheckIn()
    {
        return $this->alreadyCheckIn;
    }

    public function isMemberExpire()
    {
        return $this->memberExpire;
    }

    public function isAccessAllow()
    {
        return $this->accessAllow;
    }

    public function isInstitutionEmpty()
    {
        return $this->institutionEmpty;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getData()
    {
        return array_merge($this->data, [$this->image]);
    }

    public function getError()
    {
        return $this->error;
    }
}