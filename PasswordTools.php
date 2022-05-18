<?php
namespace Core\Lib;

final class PasswordTools {

    private $weak;
    // Use api from pwnedpasswords.com ?
    private $pownedPasswordDatabase=true;

    /**
     * Constructor of the class
     * @param bool|boolean $weak                   [if password can be weak. For test only.]
     * @param bool|boolean $pownedPasswordDatabase [True if you want to use pwnedpasswords.com api]
     */
    public function __construct(bool $weak=false, bool $pownedPasswordDatabase=true)
    {
        $this->weak = $weak;
        $this->pownedPasswordDatabase = $pownedPasswordDatabase;
    }

    /**
     * To avoid $var to be changed because object should be immuable
     */
    public function __set($var,$value)
    {
        return null;
    }
    public function __call($name, $arguments)
    {
        return null;
    }
    public function __isset($name)
    {
        return null;
    }
    public function __unset($name)
    {
        return $name;
    }
    public function __get($var)
    {
        return $var;
    }


    /**
     * Options for creating hash
     * @return [array] [array of options]
     */
    private function options():array
    {
        if (version_compare(PHP_VERSION, 7.0, '<')) {
            return array(
                'cost' => $this->getOptimalBcryptCostParameter(),
                'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM)
            );
        } else {
            return array(
                'cost' => $this->getOptimalBcryptCostParameter(),
            );
        }
    }

    /**
     * This code will benchmark your server to determine how high of a cost you can
     * afford. You want to set the highest cost that you can without slowing down
     * you server too much. 8-10 is a good baseline, and more is good if your servers
     * are fast enough. The code below aims for ≤ 50 milliseconds stretching time,
     * which is a good baseline for systems handling interactive logins.
     * @param float $min_ms Minimum amount of time in milliseconds that it should take
     * to calculate the hashes
     * @return int         [the optimal cost for server]
     */
    private function getOptimalBcryptCostParameter($timeTarget = 0.25):int
    {// 250 milliseconds
        $cost = 8;
        do {
            $cost++;
            if (version_compare(PHP_VERSION, 7.0, '<') ) {
                $options = array(
                    'cost' => $cost,
                    'salt' => 'usesomesillystringforsalt'
                );
            } else {
                $options = array('cost' => $cost);
            }
            $start = microtime(true);
            \password_hash("rasmuslerdorf", PASSWORD_DEFAULT, $options);
            $end = microtime(true);
        } while (($end - $start) < $timeTarget);

        return $cost;
    }

    /**
     * Note that the salt here is randomly generated.
     * Never use a static salt or one that is not randomly generated.
     *
     * For the VAST majority of use-cases, let password_hash generate the salt randomly for you
     * @param string $password [the password to hash]
     * @return string           [the hash]
     */
    public function create_hash(string $password):string
    {
        return \password_hash($password, PASSWORD_DEFAULT, $this->options());
    }

    /**
     * Validate the given password compare to good hash in base
     * @param  string $password  [the password to validate]
     * @param  string $good_hash [the hash of the initial password]
     * @return [bool]            [True if password is good. False otherwise]
     */
    public function validate_password(string $password, string $good_hash):bool
    {
        if (\password_verify($password, $good_hash)) {
            return true;
        }
        return false;
    }

    /**
     * If hash is old, the given password is re-hashed. This function is used for password hashed with old password_hash function
     * @param  string  $password [the given password]
     * @param  string  $hash     [the hash in base]
     * @return mixed           [the new hash or false]
     */
    public function isPasswordNeedsRehash(string $password, string $hash)
    {
        if (\password_needs_rehash($hash, PASSWORD_DEFAULT, $this->options())) {
            return $this->create_hash($password);
        }
        return false;
    }

    /**
     * Use pwnedpasswords.com api to know if the password have been powned
     * @param  string  $pw [description]
     * @return bool|boolean     [True if the password have been powned. False otherwise or if api is unavailable]
     */
    public function isPasswordHaveBeenPowned(string $pw):bool
    {
        $upperCase = strtoupper(hash('sha1',$pw));
        $prefix = substr($upperCase,0,5);
        $response = explode("\n",$this->file_curl_contents("https://api.pwnedpasswords.com/range/$prefix",true));
        foreach ($response as $key => $value) {
            if (substr($value,0,35) == substr($upperCase,5) ) {
                // echo 'Password breached !!!';
                return true;
            }
        }
        return false;
    }

    /**
     * Check is the password given to the function is strong enough
     * @param  string  $pw      [the password in clear before hash]
     * @param  int     $length  [the minimum of lenght to be accepted]
     * @param  string  $compare [the last password used. This param should be used when password needs to be changed. Application have to ask the old password, hash it to be sure it is the good one and compare it to the new one to be sure is different enough]
     * @return bool|boolean          [True if is strong enough. False otherwise]
     */
    public function isPasswordStrong(string $pw,int $length,string $compare=''):bool
    {
        $compare = mb_strtolower($compare,'UTF-8');
        $p = mb_strtolower($pw,'UTF-8');
        similar_text($compare, $p, $percent);
        if ($percent > 50) return false;
        if ($this->pownedPasswordDatabase === true) {
            if (true === $this->isPasswordHaveBeenPowned($pw)) {
                return false;
            }
        }

        if (mb_strlen($pw,'UTF-8') < $length) return false;
        $regex = '/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)+,\/,°,`,£,€,\',§,ç,é,è,ù,à,=,:,;, ]/';
        $r = false;
        // // If the password length is greater than $length and contain any lowercase alphabet or any number or any special character
        $r = (preg_match('/[a-z]/',$pw) || preg_match('/[A-Z]/',$pw) || preg_match('/\d+/',$pw) || preg_match($regex,$pw) );
        // // If the password length is greater than $length and contain alphabet,number,special character respectively
        $r = (( ((preg_match('/[a-z]/',$pw) || preg_match('/[A-Z]/',$pw)) && preg_match('/\d+/',$pw)) || (preg_match('/\d+/',$pw) && preg_match($regex,$pw)) || ((preg_match('/[a-z]/',$pw) || preg_match('/[A-Z]/',$pw)) && preg_match($regex,$pw)) ));
        // // If the password length is greater than $length and must contain alphabets,numbers and special characters
        if ($this->weak == false) {
            $r =(preg_match('/[a-z]/',$pw) && preg_match('/[A-Z]/',$pw) && preg_match('/\d+/',$pw) && preg_match($regex,$pw));
        }
        return (bool) $r;
    }

    /**
    * BoZoN core part
    * @author: Bronco (bronco@warriordudimanche.net)
    * @param string $url    [the url of website to grab]
    * @param bool|boolean $pretend  [True to pretend to be Firefox. False otherwise]
    * @return  bool|boolean   [data return by url]
    **/
    private function file_curl_contents(string $url,bool $pretend=true)
    {
        # distant version of file_get_contents
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-charset: UTF-8'));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!ini_get('safe_mode') && !ini_get('open_basedir') ) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        if ($pretend){
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64; rv:40.0) Gecko/20100101 Firefox/40.0');
        }
        curl_setopt($ch, CURLOPT_REFERER, 'http://noreferer.com');// notez le referer "custom"
        $data = curl_exec($ch);
        $response_headers = curl_getinfo($ch);
        curl_close($ch);
        return $data;
    }
}
