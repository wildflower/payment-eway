<?php

/**
 * eWay payment class
 * 
 * @author your name here haydn [at] email.web>
 */

class eWay extends Payment{
	
	static $db = array(
		//it's a good idea to save transaction details that are returned from the gateway
		//'TransactionID' => 'Varchar'
	);
	
	static $eWay_Url = "https://www.ewaygateway.com/gateway_CVN/payment.asp";
	static $eWay_Sandbox_API_Key = "C3AB9CRwb7s70t3pKEaWxV8/CZnpt51SM0CmglO6Q0L3y6+r0yIidynMnHMpfzk1ug5YyH";

	
	//store logo url, api_endpoint, and config settings up here
	
	/**
	 * 
	 * This function is required
	 */
	function processPayment($data, $form) {
		//var_dump($form);
		//var_dump($data);
		$options = array('format' => '#0.00'); //Zend Number:Locale format
		//prepare to send data to gateway
		$cart = ShoppingCart::singleton();
		$order = $cart->current();
	//	SS_Log::log( New Exception('nice currency Amount is :'.$this->Amount->Nice($options). ' amount amount is:'.$this->Amount->Amount), SS_Log::NOTICE );
		
		static $ewayurl;
		$ewayurl.="?CustomerID=91369113";
		$ewayurl.="&UserName=admin@scubapro.co.nzsand";
		$ewayurl.="&Amount=".$this->Amount->Nice($options);
		$ewayurl.="&Currency=".Payment::site_currency();
		$ewayurl.="&PageTitle=MyeWayTitle";
	    $ewayurl.="&PageDescription=MyeWayDescription";
		$ewayurl.="&PageFooter=myeWayFooter";	
		$ewayurl.="&Language=EN";
		$ewayurl.="&CompanyName=myeWayCompanyName";
		$ewayurl.="&CustomerFirstName=".$data['FirstName'];
	    $ewayurl.="&CustomerLastName=".$data['Surname'];		
		$ewayurl.="&CustomerAddress=".$data['ShippingAddress']." ".$data['ShippingAddressLine2'];
		$ewayurl.="&CustomerCity=".$data['ShippingCity'];
		$ewayurl.="&CustomerState=".$data['ShippingState'];
		$ewayurl.="&CustomerPostCode=".$data['ShippingPostalCode'];
		$ewayurl.="&CustomerCountry=".$data['ShippingCountry'];		
		$ewayurl.="&CustomerEmail=".$data['Email'];
		$ewayurl.="&CustomerPhone=".$data['ShippingPhone'];		
		$ewayurl.="&InvoiceDescription=".$data['Reference'];
		$ewayurl.="&CancelURL=http://silverstripe/ewayctl/cancel/".$data['Reference'];
		$ewayurl.="&ReturnUrl=http://silverstripe/ewayctl/success/".$data['Reference'];
		$ewayurl.="&CompanyLogo=myCompanyLogo";
		$ewayurl.="&PageBanner=myPageBanner";
		$ewayurl.="&MerchantReference=".$data['Reference'];
		$ewayurl.="&MerchantInvoice=".$data['Reference'];
		$ewayurl.="&MerchantOption1="; 
		$ewayurl.="&MerchantOption2=";
		$ewayurl.="&MerchantOption3=";
		$ewayurl.="&ModifiableCustomerDetails=ModDetails";
			
		//SS_Log::log( New Exception('eWayURL is :'.$ewayurl), SS_Log::NOTICE );
			
	    $spacereplace = str_replace(" ", "%20", $ewayurl);	
	    $posturl="https://au.ewaygateway.com/Request/$spacereplace";
		//SS_Log::log( New Exception('PostURL is :'.$posturl), SS_Log::NOTICE );
		
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		$response = curl_exec($ch);
		//SS_Log::log( New Exception($response), SS_Log::NOTICE );
		
		function fetch_data($string, $start_tag, $end_tag)
		{
			$position = stripos($string, $start_tag);  
			$str = substr($string, $position);  		
			$str_second = substr($str, strlen($start_tag));  		
			$second_positon = stripos($str_second, $end_tag);  		
			$str_third = substr($str_second, 0, $second_positon);  		
			$fetch_data = trim($str_third);		
			return $fetch_data; 
		}
		
		
		$responsemode = fetch_data($response, '<result>', '</result>');
	    $responseurl = fetch_data($response, '<uri>', '</uri>');
		   
		if($responsemode=="True")
		{ 			  	  	
		  header("location: ".$responseurl);
		  exit;
		}
		else
		{
			//this needs to store the response and go back to checkout failure to show the answer(?) and be processed
		  header("location: eway-failure");
		  exit;
		}
		
		
		/*//send response
		return new Payment_Success();
		or
		return new Payment_Processing();
		or
		return new Payment_Failure($reason);
		*/
		
		return new Payment_Processing();
	}
	
	/**
	 * 
	 * This function is required
	 */
	function getPaymentFormFields() {
		
		return new FieldSet();	
	}
	
}


 
//only needed if you are connecting to an external gateway that will return to your site
class eWay_Controller extends Controller{
	
	protected $payment = null; //only need to get this once
	static $URLSegment = "ewayctl";
	
	static $allowed_actions = array(
		'success',
		'failure',
		'cancel'
	);
	
	
	function payment(){
		if($this->payment){
			return $this->payment;
		}
		
		if($id = Controller::getRequest()->getVar('paymentid')){
			$p =  DataObject::get_one('MyPayment',"\"ID\" = '$id'");
			$this->payment = $p;
			return $p;
		}
		return null;
	}
	
	function failure(){
		echo "failure in here";
	}
	function getAccessPaymentCode()
	{
		if($AccessPaymentCode = $this->request->postVar('AccessPaymentCode')){
			SS_Log::log( New Exception('in getAccessPaymentCode is :'.$AccessPaymentCode), SS_Log::NOTICE );
		}
	
	}
	
	function success(){
	SS_Log::log( New Exception('In Success AccessPaymentCode is :'.$AccessPaymentCode), SS_Log::NOTICE );
		if($AccessPaymentCode = $this->request->postVar('AccessPaymentCode')){
			SS_Log::log( New Exception('AccessPaymentCode is :'.$AccessPaymentCode), SS_Log::NOTICE );
		}
		//get payment object, based on some passed parameter AccessPaymentCode
		$this->getAccessPaymentCode();
		//curl to the result URL https://au.ewaygateway.com/Result/?CustomerID=87654321&UserName=TestAccount &AccessPaymentCode=611a5cabc19330f52f9db09e4549c225dda64a71aa8775f53 cafce75c0acff0b611a5cabc19330f52f9db09e4549c225dda64a71aa8775f5asdfalkji323jlJS
		$payment = $this->payment();
		
		if($payment && $obj = $payment->PaidObject()){
			//redirect to PaymentObject link (eg $order->Link())
			Controller->redirect($obj->Link());
			return;
		}
		
		//else redirect home
		Controller->redirect(Director::absoluteURL('home',true));
		return;
	}
	
	

}

