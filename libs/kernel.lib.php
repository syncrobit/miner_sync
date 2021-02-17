<?php
/**
 * Created by SyncroBit.
 * Author: George Partene
 * Version: 0.1
 */

 class SB_KERNEL{
    public static function generateSnapShot(){
        $time           = time();
        $expiry         = time() + 1800;
        $file_name      = "snapshot_".$time.".bin";
        $miner_release  = self::getMinerRelease();
        $chain_height   = self::getBockChainHeight();

        if(file_exists(SNAPSHOT_DIR.$file_name)){
            $check_sum_md5 = hash_file("md5", SNAPSHOT_DIR.$file_name);
            $check_sum_sha1 = hash_file("sha1", SNAPSHOT_DIR.$file_name);
            $check_sum_sha256 = $sha256 = hash_file("sha256", SNAPSHOT_DIR.$file_name);
            
        }else{
            exec("sudo docker exec miner miner snapshot take /var/data/".$file_name, $out, $e_status);
            if($e_status == 0){
                exec("sudo docker cp miner:/var/data/".$file_name." ".SNAPSHOT_DIR.$file_name, $m_out, $m_status);
                if($m_status == 0){
                    exec("sudo docker exec miner rm -rf /var/data/".$file_name, $r_out, $r_status);
                    exec("sudo chown www-data:www-data ".SNAPSHOT_DIR.$file_name, $c_out, $c_status);

                    $check_sum_md5 = hash_file("md5", SNAPSHOT_DIR.$file_name);
                    $check_sum_sha1 = hash_file("sha1", SNAPSHOT_DIR.$file_name);
                    $check_sum_sha256 = hash_file("sha256", SNAPSHOT_DIR.$file_name);
                }else{
                    return array("error" => "cannot move file");
                }
            }else{
                return array("error" => "cannot generate snapshot");
            }
        }

        self::insertGeneratedSnapShots($chain_height, $miner_release);
        return array(
            "fileUri"           => SNAPSHOT_URI.$file_name,
            "checkSum"          => array(
                "md5"           => $check_sum_md5,
                "sha1"          => $check_sum_sha1,
                "sha256"        => $check_sum_sha256
            ),
            "minerRelease"      => $miner_release,
            "blockHeight"       => $chain_height,
            "timestamp"         => gmdate("Y-m-d\TH:i:s\Z", $time),
            "expires"           => gmdate("Y-m-d\TH:i:s\Z", $expiry)
            
        );
    }

    public static function getMinerRelease(){
        exec("sudo docker container inspect -f '{{.Config.Image}}' miner | awk -F: '{print $2}'", $output, $retval);
        return $output[0];
    }

    public static function getBockChainHeight(){
        exec("sudo docker exec miner miner info height | awk -F\" \" '{print $2}'", $output, $retval);
        return $output[0];
    }

    public static function cleanUpfiles(){
        $files = scandir(SNAPSHOT_DIR);
        $now   = time();
      
        foreach ($files as $file) {
          if (is_file(SNAPSHOT_DIR.$file)) {
            if ($now - filemtime(SNAPSHOT_DIR.$file) >= 60 * 30) { // 30 minutes
                unlink(SNAPSHOT_DIR.$file);
            }
          }
        }
    }

    public static function getUserIPAddress(){
        $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])){
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    }else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else if(isset($_SERVER['HTTP_X_FORWARDED'])){
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    }else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])){
        $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    }else if(isset($_SERVER['HTTP_FORWARDED_FOR'])){
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    }else if(isset($_SERVER['HTTP_FORWARDED'])){
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    }else if(isset($_SERVER['REMOTE_ADDR'])){
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    }else{
        $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
    } 

    public static function insertGeneratedSnapShots($height, $miner_v){
        $date   = time();
        $ip     = self::getUserIPAddress();

        try {
            $sql = "INSERT INTO `sb_msync` (`date`, `ip`, `height`, `miner_v`) VALUES (:date, :ip, :height, :miner_v)";
            $db = new PDO("mysql:host=".SB_DB_HOST.";dbname=".SB_DB_DATABASE, SB_DB_USER, SB_DB_PASSWORD);
            $statement = $db->prepare($sql);
            $statement->bindValue(":date", $date);
            $statement->bindValue(":ip", $ip);
            $statement->bindValue(":height", $height);
            $statement->bindValue(":miner_v", $miner_v);

            return ($statement->execute());
            

        } catch (PDOException $e) {
           echo $e->getMessage();
        }

        return false;

    }

 }