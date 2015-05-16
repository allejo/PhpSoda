<?php

use allejo\Socrata\SodaClient;

class SodaClientTest extends PHPUnit_Framework_TestCase
{
    private $domain;
    private $token;

    public function setUp ()
    {
        $this->domain = "opendata.socrata.com";
        $this->token  = "khpKCi1wMz2bwXyMIHfb6ux73";
    }

    public function testDomainWithHttpPrefix ()
    {
        $sc = new SodaClient("http://opendata.socrata.com");

        $this->assertEquals($this->domain, $sc->getDomain());
    }

    public function testDomainWithHttpsPrefix ()
    {
        $sc = new SodaClient("https://opendata.socrata.com");

        $this->assertEquals($this->domain, $sc->getDomain());
    }

    public function testDomainWithoutPrefix ()
    {
        $sc = new SodaClient($this->domain);

        $this->assertEquals($this->domain, $sc->getDomain());
    }

    public function testDisablingAssociativeArrays ()
    {
        $sc = new SodaClient($this->domain);
        $sc->disableAssociativeArrays();

        $this->assertFalse($sc->associativeArrayEnabled());
    }

    public function testEnablingAssociativeArrays ()
    {
        $sc = new SodaClient($this->domain);
        $sc->enableAssociativeArrays();

        $this->assertTrue($sc->associativeArrayEnabled());
    }

    public function testAppToken ()
    {
        $token = md5("hello world");
        $sc    = new SodaClient($this->domain, $token);

        $this->assertEquals($token, $sc->getToken());
    }

    public function testEmail ()
    {
        $email = "email@example.org";
        $sc    = new SodaClient($this->domain, "", $email);

        $this->assertEquals($email, $sc->getEmail());
    }

    public function testPassword ()
    {
        $password = "my super secret password";
        $sc       = new SodaClient($this->domain, "", "", $password);

        $this->assertEquals($password, $sc->getPassword());
    }
}
