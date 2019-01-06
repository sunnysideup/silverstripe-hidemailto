<?php

namespace Sunnysideup\HideMailto;

use Director;


//===================---------------- START hidemailto MODULE ----------------===================
//HideMailto_Controller::set_allowed_domains(array('app.co.nz'));
//DataObject::add_extension('Member', 'HideMailto_Role');
//DataObject::add_extension('SiteTree', 'HideMailto');
//HideMailto::set_email_field("Email");
//HideMailto::set_default_subject("email from our website");
//===================---------------- END hidemailto MODULE ----------------===================



Director::addRules(100, array(
    'mailto/$Name/$URL/$Subject' => 'HideMailto_Controller'
));
