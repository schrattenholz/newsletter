<?php

namespace Schrattenholz\Newsletter;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;

class NewsletterRecipient extends DataObject{
	private static $table_name="newsletterrecepient";
	private static $db=array(
		"Email"=>"Text",
		"Confirmed"=>"Boolean",
		"Hash"=>"Text"
		
	);	
	private static $field_labels = array(
      'Email' => 'Email' 
   );
	private static $summary_fields = array(
      'Email',
	  'Confirmed'
   );

}