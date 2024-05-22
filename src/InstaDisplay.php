<?php

namespace Instagramclient;

class InstaDisplay
{

    private $appId;
    private $appSecret;
    private $redirectUri;
    private $pdo = null;
    private $dbname = __DIR__ . '/database.sqlite';
    
    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->appId = $_ENV['INSTAGRAM_APP_ID'];
        $this->appSecret = $_ENV['INSTAGRAM_APP_SECRET'];
        $this->redirectUri = $_ENV['INSTAGRAM_REDIRECT_URI'];
        try{
            if($this->pdo==null){
              $this->pdo =new \PDO("sqlite:$this->dbname","","",array(
                    \PDO::ATTR_PERSISTENT => true
                ));
            }            
        }catch(\PDOException $e){
            print "Error in openhrsedb ".$e->getMessage();
        }
    }

    public function getShorterLivedAccessToken($code)
    {        
        $url = 'https://api.instagram.com/oauth/access_token';
        $data = [
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
            'code' => $code
        ];
        $response = $this->postRequest($url, $data);        
        if(isset($response['access_token'])){            
            return $response['access_token'];
        }else{
            return null;
        }
    }

    public function getLongerLivedAccessToken($accessToken)
    {
        $url = 'https://graph.instagram.com/access_token';
        $data = [
            'client_secret' => $this->appSecret,
            'grant_type' => 'ig_exchange_token',
            'access_token' => $accessToken
        ];
        $response = $this->getRequest($url, $data);
        echo json_encode($response);
        echo "<br>";
        echo json_encode($data);
        return $response['access_token'];
    }

    public function saveAccessToken($accessToken)
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS instagram (access_token TEXT)');
        $this->pdo->exec('DELETE FROM instagram');
        $stmt = $this->pdo->prepare('INSERT INTO instagram (access_token) VALUES (:access_token)');        
        $stmt->execute(['access_token' => $accessToken]);
    }

    public function testAccessToken()
    {
        try {
            $accessToken = $this->getAccessToken();
            if(!$accessToken) return false;
            $url = 'https://graph.instagram.com/me?fields=id&access_token=' . $accessToken;
            $response = file_get_contents($url);
            $response = json_decode($response, true);            
            return isset($response['id']);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getAccessToken()
    {
        try {
            $stmt = $this->pdo->query('SELECT access_token FROM instagram');            
            $row = $stmt->fetch();            
            return $row['access_token'];
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function getUserMidiaApi() {
        $accessToken = $this->getAccessToken();
        $url = 'https://graph.instagram.com/me/media?fields=id,caption,media_type,media_url,thumbnail_url,permalink,timestamp&access_token=' . $accessToken;
        $response = file_get_contents($url);
        $response = json_decode($response, true);
        echo json_encode($response);
        $this->insertDB('media','media',json_encode($response['data']));
        return $this->getDB('media','media');
    }



    public static function getLinkAuthorizationCode()
    {
        $url = 'https://api.instagram.com/oauth/authorize';
        $url .= '?client_id=' . $_ENV['INSTAGRAM_APP_ID'];
        $url .= '&redirect_uri=' . $_ENV['INSTAGRAM_REDIRECT_URI'];
        $url .= '&scope=user_profile,user_media';
        $url .= '&response_type=code';
        return $url;
    }

    public function getRequest($url, $data)
    {
        $url .= '?' . http_build_query($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function postRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function getAtributos()
    {
        return [
            'appId' => $this->appId,
            'appSecret' => $this->appSecret,
            'redirectUri' => $this->redirectUri
        ];
    }

    function insertDB($table, $coluna, $data) {
        try {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS '.$table.' ('.$coluna.' TEXT)');
            $this->pdo->exec('DELETE FROM '.$table);
            $stmt = $this->pdo->prepare('INSERT INTO '.$table.' ('.$coluna.') VALUES (:data)');
            $stmt->bindValue(':data', $data, SQLITE3_TEXT);
            $stmt->execute();
        } catch (\PDOException $e) {
            // handle the exception error
            echo "Error in insertDB: ".$e->getMessage();
        }
    }

    function getDB($table, $coluna) {
        try {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS '.$table.' ('.$coluna.' TEXT)');
            
            $stmt = $this->pdo->prepare('SELECT '.$coluna.' FROM '.$table);
            $stmt->execute();
            
            // verifica se há resultados
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch();
                return $row[$coluna];
            } else {
                return null;
            }
        } catch (\PDOException $e) {
            // trata o erro de exceção
            echo "Error in getDB: ".$e->getMessage();
            return null;
        }
    }


}