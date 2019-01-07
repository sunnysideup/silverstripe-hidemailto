<?php

namespace Sunnysideup\HideMailto;

use SilverStripe\Control\Email\Email;
use SilverStripe\View\ViewableData;
use SilverStripe\Core\Convert;
use SilverStripe\View\Requirements;
use SilverStripe\CMS\Model\SiteTreeExtension;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Member;
use Sunnysideup\HideMailto\HideMailto;
use SilverStripe\Control\Director;
use SilverStripe\CMS\Controllers\ContentController;

class HideMailto extends SiteTreeExtension
{
    private static $email_field = Email::class;

    private static $default_subject = "enquiry";

    private static $replace_characters = array(
        "." => "&#x2e;",
        "@" => "&#x40;",
        "a" => "&#x61;",
        "b" => "&#x62;",
        "c" => "&#x63;",
        "d" => "&#x64;",
        "e" => "&#x65;",
        "f" => "&#x66;",
        "g" => "&#x67;",
        "h" => "&#x68;",
        "i" => "&#x69;"
    );

    /**
     *
     * @param String $email
     * @param String $subject
     * @return Obj (MailTo, Text, Original, Subject)
     */
    public static function convert_email($email, $subject = '')
    {
        $obj = new ViewableData();
        if (!$subject) {
            $subject = self::$default_subject;
        }
        //mailto part
        $mailTo = "mailto:".$email."?subject=".Convert::raw2mailto($subject);
        $mailToConverted = self::string_encoder($mailTo);
        $convertedEmail = self::string_encoder($email);
        $obj->MailTo = $mailToConverted;
        $obj->Text = $convertedEmail;
        $obj->Original = $email;
        $obj->Subject = $subject;
        //$obj->OnClick = "jQuery(this).attr('href', HideMailto2Email('".self::get_dot_replacer()."', '".$array[0]."', '".$array[1]."', '".Convert::raw2mailto($subject)."')); return true;";
        //TO DO: add a JS function that puts the
        Requirements::javascript('silverstripe/admin: thirdparty/jquery/jquery.js');
        //Requirements::javascript("sunnysideup/hidemailto: hidemailto/javascript/HideMailto2Email.js");
        return $obj;
    }


    /**
     * encodes a string - randomly
     * @param String $string
     * @return String
     */
    private static function string_encoder($string)
    {
        $encodedString = '';
        $nowCodeString = '';
        $originalLength = strlen($string);
        for ($i = 0; $i < $originalLength; $i++) {
            $encodeMode = rand(1, 2);
            switch ($encodeMode) {
                case 1: // Decimal code
                    $nowCodeString = '&#' . ord($string[$i]) . ';';
                    break;
                case 2: // Hexadecimal code
                    $nowCodeString = '&#x' . dechex(ord($string[$i])) . ';';
                    break;
                default:
                    return 'ERROR: wrong encoding mode.';
            }
            $encodedString .= $nowCodeString;
        }
        return $encodedString;
    }

    public function HideMailToObject()
    {
        if ($email = $this->getHiddenEmailData()) {
            $obj = self::convert_email($email);
            return $obj;
        }
    }

    private function getHiddenEmailData()
    {
        if ($field = self::$email_field) {
            if ($email = $this->owner->$field) {
                return $this->isEmail($email);
            }
        }
    }

    private function isEmail($email)
    {
        if (!preg_match("/^([A-Za-z0-9._-])+\@(([A-Za-z0-9-])+\.)+([A-Za-z0-9])+$/", trim($email))) {
            return "";
        } else {
            return $email;
        }
    }
}
