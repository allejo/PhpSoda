<?php

class TestsAuthentication
{
    private $username;
    private $password;

    public function __construct ($filePath)
    {
        if (file_exists($filePath))
        {
            $file = file_get_contents($filePath, true);
            $json = json_decode($file, true);

            $this->username = $json['username'];
            $this->password = $json['password'];
        }
        else if (getenv('username') !== false && getenv('password') !== false)
        {
            $this->username = getenv('username');
            $this->password = getenv('password');
        }
    }

    public function isAuthenticationSetup ()
    {
        return (isset($this->username) && isset($this->password));
    }

    public function getUsername ()
    {
        return $this->username;
    }

    public function getPassword ()
    {
        return $this->password;
    }
}
