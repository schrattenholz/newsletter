<?php
namespace Schrattenholz\Newsletter;

use SilverStripe\ORM\DataObject;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Assets\Image;

class NewsletterConfig extends DataObject{
	  private static $db = array(
		'EmailSignature' => 'HTMLText',
		'ReceiptSubject' => 'Varchar',
		'ReceiptBody' => 'HTMLText',
		'ReceiptFrom' => 'Varchar',
		'NotificationSubject' => 'Varchar',
		'NotificationBody' => 'HTMLText',
		'NotificationTo' => 'Varchar',
		'ContentRegistrationConfirmation'=>'HTMLText',
		'ContentSignOff'=>'HTMLText',
		'ContentSignOffNotPossible'=>'HTMLText',
		'ContentError'=>'HTMLText',
		
	  );
	  private static $table_name="newsletterconfig";
	 /* public function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if(!self::current_shop_config()) {
		  $shopConfig = new NewsletterConfig();
		  $shopConfig->write();
		  DB::alteration_message('Added default newsletter config', 'created');
		}
	  }*/
    /*public static function current_newsletter_config() {

  	//TODO: lazy load this

    return NewsletterConfig::get()->First();
  }*/
}
