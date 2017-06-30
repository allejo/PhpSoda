<?php

use allejo\Socrata\SodaClient;
use PHPUnit\Framework\TestCase;

class SodaClientTest extends TestCase
{
    private $domain;
    private $token;

    public function setUp ()
    {
        $this->domain = 'opendata.socrata.com';
        $this->token  = 'khpKCi1wMz2bwXyMIHfb6ux73';
    }

    public static function domainURLs()
    {
        return [
            ['http://opendata.socrata.com'],
            ['https://opendata.socrata.com'],
            ['opendata.socrata.com'],
        ];
    }

    /**
     * @dataProvider domainURLs
     *
     * @param string $domain
     */
    public function testDomainsTrimming($domain)
    {
        $sc = new SodaClient($domain);

        $this->assertEquals($this->domain, $sc->getDomain());
    }

    public function testDisablingAssociativeArrays()
    {
        $sc = new SodaClient($this->domain);
        $sc->disableAssociativeArrays();

        $this->assertFalse($sc->associativeArrayEnabled());
    }

    public function testEnablingAssociativeArrays()
    {
        $sc = new SodaClient($this->domain);
        $sc->enableAssociativeArrays();

        $this->assertTrue($sc->associativeArrayEnabled());
    }

    public function testAppToken()
    {
        $token = md5("hello world");
        $sc    = new SodaClient($this->domain, $token);

        $this->assertEquals($token, $sc->getToken());
    }

    public function testEmail()
    {
        $email = "email@example.org";
        $sc    = new SodaClient($this->domain, "", $email);

        $this->assertEquals($email, $sc->getEmail());
    }

    public function testPassword()
    {
        $password = "my super secret password";
        $sc       = new SodaClient($this->domain, "", "", $password);

        $this->assertEquals($password, $sc->getPassword());
    }

    public function testWarningTriggeredWithEmailOnly()
    {
        $this->expectException(PHPUnit_Framework_Error_Warning::class);

        $sc = new SodaClient($this->domain, '', 'email@domain.com');
        $sc->getGuzzleClient();
    }

    public function testWarningTriggeredWithPasswordOnly()
    {
        $this->expectException(PHPUnit_Framework_Error_Warning::class);

        $sc = new SodaClient($this->domain, '', '', 'my password');
        $sc->getGuzzleClient();
    }

    public function testGuzzleClientAutoAuthentication()
    {
        $sc = new SodaClient($this->domain, '', 'email@domain.com', 'my password');
        $config = $sc->getGuzzleClient()->getConfig();

        $this->assertArrayHasKey('auth', $config);
    }
}
