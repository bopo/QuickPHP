<?php require_once dirname(__FILE__).'/../../bootstrap.php';

/**
 * @package System.Caching
 */
class TValidTest extends PHPUnit_Framework_TestCase
{
    protected static $runtimePath = null;
    protected static $archive = null;

    protected function setUp()
    {
        // $valid = valid::factory($_ENV);
        // var_dump(get_class_methods($valid));
    }

    protected function tearDown()
    {
    }

    public function testNotEmpty()
    {
        $this->assertTrue(valid::not_empty('111'));
        $this->assertFalse(valid::not_empty(''));
    }

    public function testRegex()
    {
        $this->assertTrue(valid::regex('adfad', '/^a/'));
    }

    public function testMinLength()
    {
        $this->assertTrue(valid::min_length('111',2));
        $this->assertFalse(valid::min_length('232323',10));
    }

    public function testMaxLength()
    {
        $this->assertTrue(valid::max_length('111',10));
        $this->assertFalse(valid::max_length('232323',3));
    }

    public function testExactLength()
    {
        $this->assertTrue(valid::exact_length('111',3));
        $this->assertFalse(valid::exact_length('232323',10));
    }

    public function testEmail()
    {
        $this->assertTrue(valid::email('ibopo@126.com', true));
        $this->assertFalse(valid::email('232323'));
    }

    public function testEmailDomain()
    {
        // $this->assertTrue(valid::email_domain('ibopo@126.com'));
        // $this->assertFalse(valid::email_domain('ibopo@mi-tang.com'));
    }

    public function testUrl()
    {
        $this->assertTrue(valid::url('http://www.mi-tang.com'));
        $this->assertFalse(valid::url('ibopo@mi-tang.com'));
    }

    public function testIP()
    {
        $this->assertTrue(valid::ip('127.0.0.1'));
        $this->assertTrue(valid::ip('127.0.0.1', true));
        $this->assertFalse(valid::ip('ibopo@mi-tang.com'));
    }
    public function testDate()
    {
        $this->assertTrue(valid::date('1982-04-28'));
        $this->assertFalse(valid::ip('ibopo@mi-tang.com'));
    }
    public function testAlpha()
    {
        $this->assertTrue(valid::alpha('ibopo'));
        $this->assertFalse(valid::alpha('127.0.0.1'));
    }

    public function testAlphaDash()
    {
        $this->assertTrue(valid::alpha_dash('ibopo'));
        $this->assertFalse(valid::alpha_dash('127.0.0.1'));
    }
    public function testDigit()
    {
        $this->assertFalse(valid::digit('ibo1._-po'));
        $this->assertTrue(valid::digit('12'));
    }
    public function testRange()
    {
        $this->assertTrue(valid::range(10,0,11));
        $this->assertFalse(valid::range(10,0,1));
    }
    public function testNumeric()
    {
        $this->assertTrue(valid::numeric(10));
        $this->assertFalse(valid::numeric('1a'));
    }
    public function testDecimal()
    {
        $this->assertTrue(valid::decimal('10.998',3));
        $this->assertTrue(valid::decimal('10.998',3,2));
        $this->assertFalse(valid::decimal('10.998',5,4));
    }

    public function testColor()
    {
        $this->assertTrue(valid::color('#fff'));
        $this->assertFalse(valid::color('dfsdf'));
    }

}
