<?php

namespace Schrattenholz\Newsletter;

use PageController;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ThemeResourceLoader;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\FieldType\DBField;
use Schrattenholz\Order\OrderConfig;
class NewsletterController extends PageController {

	/**
	 * An array of actions that can be accessed via a request. Each array element should be an action name, and the
	 * permissions or conditions required to allow the user to access it.
	 *
	 * <code>
	 * array (
	 *     'action', // anyone can access this action
	 *     'action' => true, // same as above
	 *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
	 *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
	 * );
	 * </code>
	 *
	 * @var array
	 */
	 	private static $allowed_actions = array (
		'edit',
		'signin'
	);
	    private static $casting = [
        'edit' => 'HTMLText' 
    ];
	 private static $url_handlers = array(        
	 	'' => 'AnmeldeformularNewsletter',
        '$Action/$NewsletterRecipientID' => 'edit'
    );
	public function clear(){
		
		$delRecipients=DataList::create('NewsletterRecipient')->where('Confirmed=0');
		$text="";
		foreach($delRecipients as $r){	
			$d = new DateTime('', new DateTimeZone('Europe/Berlin')); 
			$d->sub(new DateInterval('P3D'));
			$heute = $d->format('Y-m-d');
			if($r->Created<$heute){	
				$text.=$r->Email;	
				$r->delete();	
			}
		}
		return $text;
	}
	public function edit($request) {
		
			$params = $request->latestParams();
			$ID = $params['NewsletterRecipientID'];
			$Action=$params['Action'];
			$newsletterR=NewsletterRecipient::get()->filter('Hash',$ID)->First();
			$newletterConfig=NewsletterConfig::get()->First();
			if($Action=="clear"){
				return $this->clear();
				//return "clear";
			}else{
				
				
				$doc=new ArrayData(array('Content'=>""));
				if($newsletterR){
					$doc->Email=$newsletterR->Email;
					if($Action=='confirm'){
						$newsletterR->Confirmed=true;					
						$doc->Content= DBField::create_field('HTMLText',$newletterConfig->ContentRegistrationConfirmation);
						$newsletterR->write();
					}else if($Action=='signout'){				
						$newsletterR->delete();
						$doc->Content= DBField::create_field('HTMLText',$newletterConfig->ContentSignOff);
					}else{
						$doc->Content= DBField::create_field('HTMLText',$newletterConfig->ContentError);
					}
				}else{
					if($Action=='confirm'){
						$doc->Content=  DBField::create_field('HTMLText',$newletterConfig->ContentSignOffNotPossible);
					}else if($Action=='signout'){	
						$doc->Content=  DBField::create_field('HTMLText',$newletterConfig->ContentSignOffNotPossible);
					}
				}
				return $doc->renderWith("Page"); 
			}
	}
public function signin($data) {
		
			if($data['Abmeldung']){	
				foreach(DataList::create('NewsletterRecipient') as $r){
					if($data['Email']==$r->Email){
						$recipient=$r;
					}					
				}			
				if($recipient){					
					$recipient->delete();
					$msg="<div class='msg'><p>ERFOLGREICHE NEWSLETTERABMELDUNG</p><p>Ihre E-Mail-Adresse wurde gelöscht.</p><p  >Vielen Dank für Ihr Interesse!</p></div>";
					$delay=3500;
					$error=false;
				}else{
					$msg="<div class='msg'><p>KEINE ABMELDUNG MÖGLICH</p><p >Sie sind nicht für den Newsletter angemeldet.</p></div>";
					$error=true;
					$delay=4000;	
				}			
			}else{
				
				$added=$this->addNewsletterRecipient($data['Email']);
						
				$msg=$added['msg'];
				$error=$added['error'];
				$delay=$added['delay'];
			}

			if($error)return $msg."___1"."___".$delay;	
			
			return  $msg."___0"."___".$delay;				
			
					
	}
	function addNewsletterRecipient($eMail){
		$tmpAr=array();
			foreach(DataList::create('NewsletterRecipient') as $r){
					if($eMail==$r->Email){
						$recipient=$r;
					}					
				}
				if(!$recipient){
					if($this->validateEmail($eMail)){
						$newsletterR=new NewsletterRecipient();
						$newsletterR->Email=$eMail;
						$newsletterR->Confirmed=false;
						$newsletterR->write();
						$tmpAr['msg']= "<div class='msg'><p>ERFOLGREICHE NEWSLETTERANMELDUNG</p><p>Sie erhalten eine E-Mail mit einem Bestätigungslink an Ihre eingetragene E-Mail-Adresse.</p></div>";
						$tmpAr['error']=false;
						$tmpAr['delay']=8000;
						$email = new Email("system@amp-bayern.com", $newsletterR->Email, "Newsletterbestätigung für amp-bayern.com", "");
						$email->setTemplate('NewsletterApplyTemplate');
 
						// You can call this multiple times or bundle everything into an array, including DataSetObjects
						$email->populateTemplate($newsletterR);
						 
						
						 
						$email->populateTemplate(array(
							'BaseHref' => $_SERVER['DOCUMENT_ROOT'], 
							'OrderConfig'=>OrderConfig::get()->First()// Accessible in template via $WelcomeMessage
						));
						$email->send();
					}else{
						$tmpAr['msg']= "<div class='msg'><p>E-Mail-Adresse ist nicht korrekt!</p></div>";
						$tmpAr['error']=true;
						$tmpAr['delay']=2500;	
					}
				}else{
					$tmpAr['msg']= "<div class='msg'><p>Sie sind bereits für den Newsletter angemeldet.</p></div>";
					$tmpAr['error']=false;
					$tmpAr['delay']=3500;		
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
	public function init() {
		parent::init();
	
		// Note: you should use SS template require tags inside your templates 
		// instead of putting Requirements calls here.  However these are 
		// included so that our older themes still work
		/*Requirements::themedCSS('reset');
		Requirements::themedCSS('layout'); */
		//Requirements::themedCSS('typography'); 
		//Requirements::themedCSS('form'); 
	}

}