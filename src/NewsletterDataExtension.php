<?php 

namespace Schrattenholz\Newsletter;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\DataList;
use SilverStripe\Control\Email\Email;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\RequiredFields;
use Schrattenholz\Newsletter\NewsletterRecipient;
use SilverStripe\View\Requirements;
use Silverstripe\ORM\ArrayList;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;
use Silverstripe\SiteConfig\SiteConfig;
use Schrattenholz\Order\OrderConfig;
class NewsletterDataExtension extends DataExtension {
		private static $allowed_actions = array (
			'HandleNewsletter',
			'AnmeldeformularNewsletter'
		);
	 public function onAfterInit(){
		$vars = [
			"Link"=>$this->getOwner()->Link(),
			"ID"=>$this->owner->ID
		];
		Requirements::javascriptTemplate("schrattenholz/newsletter:javascript/newsletter.js",$vars);
		
	}
		public function AnmeldeformularNewsletter($hasCancelButton){
		if($hasCancelButton){
				$actions=new FieldList (
						new FormAction('ApplyNewsletter', _t('Newsletter.APPLYNOW','Absenden')),
						new FormAction('CancelNewsletter', _t('Newsletter.CANCEL','Abbrechen'))
					);
		}else{
			$actions=new FieldList (
						new FormAction('ApplyNewsletter', _t('Newsletter.APPLYNOW','Absenden'))
					);
		}
		$form = new Form (
					$this->getOwner(),
					"AnmeldeformularNewsletter",
					new FieldList (
						new TextField('Email', _t('Newsletter.EMAIL','Ihre E-Mail-Adresse*')),
						new CheckboxField('Abmeldung', _t('Newsletter.SIGNOFF','Vom Newsletter abmelden'))
					),
					$actions
					,new RequiredFields()
				);
				
				return $form;
	}
	public function HandleNewsletter($data) {
		$returnValues=new ArrayList(['Status'=>'error','Message'=>false,'Value'=>false]);
		Injector::inst()->get(LoggerInterface::class)->error('Abmeldung='.$data['Abmeldung']." Email=".$data['Email']);
		if(isset($data['Abmeldung'])){
			$recipient=NewsletterRecipient::get()->filter("Email",$data['Email'])->First();
			if($recipient){
				$recipient->delete();
				$returnValues->Status="good";
				$returnValues->Title="ERFOLGREICHE NEWSLETTERABMELDUNG";
				$returnValues->Message="<p>Ihre E-Mail-Adresse wurde gelöscht.</p><p  >Vielen Dank für Ihr Interesse!";
				$returnValues->Value=3500;
			}else{
				$returnValues->Status="good";
				$returnValues->Title="KEINE ABMELDUNG MÖGLICH";
				$returnValues->Message="<p >Sie sind nicht für den Newsletter angemeldet.</p>";
				$returnValues->Value=3500;
			}			
		}else{
		
			$returnValues=$this->addNewsletterRecipient($data['Email']);
		}
		return json_encode($returnValues);
	}
	function addNewsletterRecipient($eMail){

		$tmpAr=new ArrayList(['Status'=>'error','Message'=>false,'Value'=>false]);
			foreach(NewsletterRecipient::get() as $r){
					if($eMail==$r->Email){
						$recipient=$r;
					}					
				}
				if(isset($recipient)){
					$tmpAr->Title="BEREITS DABEI :)";
					$tmpAr->Message= "<p>Sie sind bereits angemeldet.<br/>Vielen Dank für Ihr Interesse!</p>";
					$tmpAr->Status="info";
					$tmpAr->Value=3500;
					
				}else{
					if($this->validateEmail($eMail)){
						$newsletterR=NewsletterRecipient::create();
						$newsletterR->Email=$eMail;
						$newsletterR->Confirmed=false;
						$newsletterR->Hash=hash("MD5",$eMail);
						$newsletterR->write();
						$tmpAr->Title="ERFOLGREICHE NEWSLETTERANMELDUNG";
						$tmpAr->Message= "<p>Sie erhalten eine E-Mail mit einem Bestätigungslink an Ihre eingetragene E-Mail-Adresse.</p>";
						$tmpAr->Status="good";
						$tmpAr->Value=8000;
						$config = SiteConfig::current_site_config();
						$email = Email::create()
						->setHTMLTemplate('Schrattenholz\\Newsletter\\Layout\\Email_Confirmation') 
						->setData([
							'BaseHref' => $_SERVER['DOCUMENT_ROOT'],
							'Recipient'=>$newsletterR,
							'OrderConfig'=>OrderConfig::get()->First()
						])
						->setFrom($config->Email)
						->setTo($newsletterR->Email)
						->setSubject("Ihre Newsletter Anmeldung | ".$config->Title);
						$email->send();
					}else{
						$tmpAr->Title="Falsche Eingabe";
						$tmpAr->Message= "<p>Die E-Mail-Adresse ist nicht korrekt</p>";
						$tmpAr->Status="error";
						$tmpAr->Value=8000;
					}
				}
				return $tmpAr;
	}
		public function validateEmail($value) {
        if($value!=""){
 
         $pcrePattern = '^[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*'
             . '@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$';
 
         // PHP uses forward slash (/) to delimit start/end of pattern, so it must be escaped
         $pregSafePattern = str_replace('/', '\\/', $pcrePattern);
 
         if($value && !preg_match('/' . $pregSafePattern . '/i', $value)){
             return false;
         } else{
             return true;
         }
		}else{
			return false;	
		}
     }
	
}