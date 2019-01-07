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

class HideMailtoRole extends DataExtension
{

    //member link

    public function HideMailtoLink()
    {
        return "mailto/" . $this->owner->ID;
    }
}
