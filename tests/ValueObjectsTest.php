<?php
use Webravo\Common\Exception\ValueObjectException;
use Webravo\Common\ValueObject\GuidObject;
use Webravo\Common\ValueObject\UrlObject;
use Webpatser\Uuid\Uuid;

class ValueObjectsTest extends TestCase
{
    public function testGuidValidation()
    {

        $guid = (string) Uuid::generate();

        $guid_value = (new GuidObject($guid))->getValue();

        $this->assertTrue($guid == $guid_value);

        $this->expectException(ValueObjectException::class);

        $guid = new GuidObject('bad guid');
    }

    public function testUrlValidation()
    {
        $url = new UrlObject('https://www.zanox-affiliate.de/ppc/?45416728C59644888&zpar0=bravocheck-20181217134156&zpar9=[[7522C574A3B95F9CB331]]');

        $url = new UrlObject('http://www.bravosconto.it');

        $url = new UrlObject('https://pippo.com');

        $url = new UrlObject('https://partners.webmasterplan.com/click.asp?ref=805173&subid=bravocheck-20180522112757&site=16952&type=text&tnb=6');

        $url = new UrlObject('https://www.awin1.com/awclick.php?gid=325539&clickref=bravocheck-20181217165834&zpar0=bravocheck-20181217165834&mid=9342&awinaffid=477141&linkid=2066109');

        $url = new UrlObject('http://clk.tradedoubler.com/click?a(2946877)p(42700)ttid(13)url(https://www.lastminute.de/reiseangebote/deals-der-woche.html)epi(bravocheck-20181217134105)');

        $this->expectException(ValueObjectException::class);

        $url = new UrlObject('bad url');
    }

}
