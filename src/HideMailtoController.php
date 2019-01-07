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

/**
 * Generates obfusticated links, and also holds the method called when /mailto/
 * is called via the URL. As noted above, take a look at the _config.php file to
 * see how mailto/ maps to this class.
 */
class HideMailtoController extends ContentController
{
    /**
     * The list of allowed domains to create a mailto: link to. By default, allow
     * all domains.
     *
     * TODO Maybe the default should be to allow the current domain only?
     */
    private static $allowed_domains = '*';

    public function __construct($dataRecord = null)
    {
        parent::__construct($dataRecord);
        return $this->index();
    }

    public function defaultAction($action)
    {
        return $this->index();
    }

    public $url = '';

    /**
     * This is called by default when this controller is executed.
     */
    public function index()
    {
        $member = null;
        $user = '';
        $domain = '';
        $subject = '';
        // We have two situations to deal with, where urlParams['Action'] is an int (assume Member ID), or a string (assume username)
        if (is_numeric($this->getRequest()->param('Name'))) {
            // Action is numeric, assume it's a member ID and optional ID is the email subject
            $member = Member::get()->byID($this->getRequest()->param('Name'));
            if (!$member) {
                user_error("No member found with ID #" . $this->getRequest()->param('Name'), E_USER_ERROR); // No member found with this ID, perhaps we could redirect a user back instead of giving them a 500 error?
            }
            list($user, $domain) = explode('@', $member->Email);
            $subject = $this->getRequest()->param('ID');
        } else {
            // Action is not numeric, assume that Action is the username, ID is the domain and optional OtherID is the email subject
            $user = urldecode($this->getRequest()->param('Name'));
            $domain = urldecode($this->getRequest()->param('URL'));
            $subject = $this->getRequest()->param('Subject');
        }
        $emailString = "mailto: $user@$domain?subject=".$subject;
        // Make sure the domain is in the allowed domains
        if ((is_string(self::$allowed_domains) && self::$allowed_domains == '*') || in_array($domain, self::$allowed_domains)) {
            // Create the redirect
            header("Location: " . $emailString);
            header("Refresh: 0; url=". $emailString);
            echo $this->customise(array("RedirectBackURL" => $this->RedirectBackURL(), "Email" => $this->makeMailtoString($user, $domain, $subject)))->renderWith(HideMailto::class);
            $emailString = $this->makeMailtoString($user, $domain, $subject);
        } else {
            user_error("We're not allowed to redirect to the domain '$domain', because it's not listed in the _config.php file", E_USER_ERROR);
        }
    }

    public function RedirectBackURL()
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->redirectBackURL = $_SERVER['HTTP_REFERER'];
        }
        if (!$this->redirectBackURL) {
            $this->redirectBackURL = Director::absoluteBaseURL();
        }
        return $this->redirectBackURL;
    }


    protected function makeMailtoString($user, $domain, $subject = '')
    {
        $target = 'mailto:' . $user . '@' . $domain;
        if ($subject) {
            $target .= '?subject=' . Convert::raw2mailto($subject);
        }
        $target = str_replace(".", "&x2e;", $target);
        $target = str_replace("@", "&x40;", $target);
        return $target;
    }
}
