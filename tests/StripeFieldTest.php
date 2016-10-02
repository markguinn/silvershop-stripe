<?php

class StripeFieldTest extends SapphireTest
{
    public function testHtmlDoesNotContainName()
    {
        $sut = new StripeField('number', 'Credit Card Number');
        $this->assertNotContains('name=', $sut->Field());
    }

    public function testHtmlDoesNotContainValue()
    {
        $sut = new StripeField('number', 'Credit Card Number');
        $sut->setValue('abc');
        $this->assertNotContains('value=', $sut->Field());
        $this->assertNotContains('abc', $sut->Field());
    }

    public function testHtmlContainsDataAttribute()
    {
        $sut = new StripeField('number', 'Credit Card Number');
        $this->assertContains('data-stripe="number"', $sut->Field());
    }

    public function testFieldHolder()
    {
        $sut = new StripeField('number', 'Credit Card Number');
        $this->assertNotContains('name=', $sut->FieldHolder());
        $this->assertContains('data-stripe="number"', $sut->Field());
    }

    public function testSmallFieldHolder()
    {
        $sut = new StripeField('number', 'Credit Card Number');
        $this->assertNotContains('name=', $sut->SmallFieldHolder());
        $this->assertContains('data-stripe="number"', $sut->Field());
    }

    public function testGetName()
    {
        $sut = new StripeField('number', 'Credit Card Number');
        $this->assertEquals('number', $sut->getName());
    }
}