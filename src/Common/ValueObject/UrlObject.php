<?php
namespace Webravo\Common\ValueObject;

class UrlObject extends AbstractValueObject
{
    public function isSatisfiedBy($value)
    {
        // Validate URL
        if (!empty($value)) {
            // https://stackoverflow.com/questions/30847/regex-to-validate-uris
            // http://snipplr.com/view/6889/regular-expressions-for-uri-validationparsing/
            // Added ... \[\] ...  to allow use of [] in URL parameters - <PN>
            if(preg_match( "/^([a-z][a-z0-9+.-]*):(?:\\/\\/((?:(?=((?:[a-z0-9-._~!$&'()*+,;=:]|%[0-9A-F]{2})*))(\\3)@)?(?=(\\[[0-9A-F:.]{2,}\\]|(?:[a-z0-9-._~!$&'()\[\]*+,;=]|%[0-9A-F]{2})*))\\5(?::(?=(\\d*))\\6)?)(\\/(?=((?:[a-z0-9-._~!$&'()\[\]*+,;=:@\\/]|%[0-9A-F]{2})*))\\8)?|(\\/?(?!\\/)(?=((?:[a-z0-9-._~!$&'()\[\]*+,;=:@\\/]|%[0-9A-F]{2})*))\\10)?)(?:\\?(?=((?:[a-z0-9-._~!$&'()\[\]*+,;=:@\\/?]|%[0-9A-F]{2})*))\\11)?(?:#(?=((?:[a-z0-9-._~!$&'()\[\]*+,;=:@\\/?]|%[0-9A-F]{2})*))\\12)?$/i", $value ) ) {
                return true;
            }
        }
        return false;
    }
}
