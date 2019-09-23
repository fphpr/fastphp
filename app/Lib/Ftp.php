<?php
namespace App;

class Ftp{

  private $ftp_server,$ftp_user_name,$ftp_user_pass;
  private $conn_id,$isLogin=false;


  public function login($array,$ssl=false)
  {
    $this->ftp_server=$array[0];
    $this->ftp_user_name=$array[1];
    $this->ftp_user_pass=$array[2];

    // set up basic connection
    if (!$ssl) {
      $this->conn_id = ftp_connect($this->ftp_server);
    }
    else {
      $this->conn_id = ftp_ssl_connect($this->ftp_server);
    }

    // login with username and password
    $this->isLogin = ftp_login($this->conn_id, $this->ftp_user_name, $this->ftp_user_pass);
    ftp_pasv($this->conn_id, true);
    return $this;
  }

  public function loginStatus()
  {
    return $this->isLogin;
  }

  public function mlsd($path='')
  {
    return ftp_mlsd($this->conn_id,$path);
  }

  public function nlist($path='')
  {
    return ftp_nlist($this->conn_id,$path);
  }


  /**
  * Creates a directory
  */
  public function mkdir($name)
  {
    return ftp_mkdir($this->conn_id,$name);
  }

  /**
  * Uploads a file to the FTP server
  *
  * @param $remote_file file path in ftp server
  * @param $file local file for upload
  */
  public function put($remote_file,$file,$nb=false)
  {
    if (!$nb) {
      return ftp_put($this->conn_id, $remote_file, $file, FTP_BINARY);
    }
    else {
      return ftp_nb_put($this->conn_id, $remote_file, $file, FTP_BINARY);
    }
  }

  /**
  * Stores a file on the FTP server (non-blocking)
  */
  public function nb_put($remote_file,$file)
  {
    return $this->put($remote_file,$file,true);
  }

  /**
  * Downloads a file from the FTP server
  */
  public function get($local_file,$server_file,$nb=false)
  {
    if (! $nb) {
      return ftp_get($this->conn_id, $local_file, $server_file, FTP_BINARY);
    }
    else {
      return ftp_nb_get($this->conn_id, $local_file, $server_file, FTP_BINARY);
    }
  }

  /**
  * Returns the current directory name
  */
  public function pwd()
  {
    return ftp_pwd($this->conn_id);
  }

  /**
  * Changes the current directory on a FTP server
  */
  public function chdir($dir)
  {
    return ftp_chdir($this->conn_id,$dir);
  }

  /**
  * Changes to the parent directory
  */
  public function cdup()
  {
    return ftp_cdup($this->conn_id);
  }

  /**
  * Set permissions on a file via FTP
  */
  public function chmod($file,$code)
  {
    return ftp_chmod($this->conn_id, $code, $file);
  }

  /**
  * Deletes a file on the FTP server
  */
  public function delete($path)
  {
    return ftp_delete($this->conn_id,$path);
  }

  /**
  * Removes a directory
  */
  public function rmdir($directory)
  {
    return ftp_rmdir($this->conn_id,$directory);
  }

  /**
  * Renames a file or a directory on the FTP server
  */
  public function rename($old,$new)
  {
    return ftp_rename($this->conn_id,$old,$new);
  }

  /**
  * Returns the size of the given file
  * returns the size of the given file in bytes.
  */
  public function size($remote_file)
  {
    return ftp_size($this->conn_id,$remote_file);
  }

  /**
  * Closes an FTP connection
  */
  public function close()
  {
    return ftp_close($this->conn_id);
  }

  public function get_ftp()
  {
    return $this->conn_id;
  }

}
