<?php

namespace Schrattenholz\Newsletter;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;

use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

use SilverStripe\Forms\Form;
use Terraformers\RichFilterHeader\Form\GridField\RichFilterHeader;

class NewsletterAdmin extends ModelAdmin {
    private static $menu_title = 'Newsletter';
	private static $url_segment = 'newsletter';
  private static $managed_models = [
      NewsletterRecipient::class,
	  NewsletterConfig::class
  ];
private static $field_labels = [
      'NewsletterRecipient' => 'Empfänger'
   ];
	/*public function getSearchContext() {
        $context = parent::getSearchContext();
        if($this->modelClass == 'NewsletterRecipient') {
            $context->getFields()->push(new CheckboxField('q[Confirmed]', 'Nur bestätigte Anmeldungen'));
        }
        return $context;
    }*/

 
}