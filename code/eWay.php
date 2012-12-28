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
		
		//prepare to send data to gateway
		$cart = ShoppingCart::singleton();
		$order = $cart->current();
		SS_Log::log( New Exception('Amount is :'.$this->Amount->Nice()), SS_Log::NOTICE );
		static $ewayurl;
		$ewayurl.="?CustomerID=91369113";
		$ewayurl.="&UserName=admin@scubapro.co.nzsand";
		$ewayurl.="&Amount=1809.00";
		$ewayurl.="&Currency=AUD";
		$ewayurl.="&PageTitle=MyeWayTitle";
	    $ewayurl.="&PageDescription=MyeWayDescription";
		$ewayurl.="&PageFooter=myeWayFooter";	
		$ewayurl.="&Language=EN";
		$ewayurl.="&CompanyName=myeWayCompanyName";
		$ewayurl.="&CustomerFirstName=".$data['FirstName'];
	    $ewayurl.="&CustomerLastName=".$data['Surname'];		
		$ewayurl.="&CustomerAddress=".$data['ShippingAddress'];
		$ewayurl.="&CustomerCity=".$data['ShippingCity'];
		$ewayurl.="&CustomerState=".$data['ShippingState'];
		$ewayurl.="&CustomerPostCode=".$data['ShippingPostalCode'];
		$ewayurl.="&CustomerCountry=".$data['ShippingCountry'];		
		$ewayurl.="&CustomerEmail=".$data['Email'];
		$ewayurl.="&CustomerPhone=".$data['ShippingPhone'];		
		$ewayurl.="&InvoiceDescription=".$data['Reference'];
		$ewayurl.="&CancelURL=mysite.com/ewaysharedpage.php";
		$ewayurl.="&ReturnUrl=mysite.com/ewayresponse.php";
		$ewayurl.="&CompanyLogo=myCompanyLogo";
		$ewayurl.="&PageBanner=myPageBanner";
		$ewayurl.="&MerchantReference=".$data['Reference'];
		$ewayurl.="&MerchantInvoice=".$data['Reference'];
		$ewayurl.="&MerchantOption1="; 
		$ewayurl.="&MerchantOption2=";
		$ewayurl.="&MerchantOption3=";
		$ewayurl.="&ModifiableCustomerDetails=ModDetails";
			
		SS_Log::log( New Exception('eWayURL is :'.$ewayrul), SS_Log::NOTICE );
			
	    $spacereplace = str_replace(" ", "%20", $ewayurl);	
	    $posturl="https://au.ewaygateway.com/Request/$spacereplace";
		SS_Log::log( New Exception('PostURL is :'.$posturl), SS_Log::NOTICE );
		
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		$response = curl_exec($ch);
		SS_Log::log( New Exception($response), SS_Log::NOTICE );
		
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
	
	function success(){
		
		//get payment object, based on some passed parameter
		$payment = $this->payment();
		
		if($payment && $obj = $payment->PaidObject()){
			//redirect to PaymentObject link (eg $order->Link())
			Director::redirect($obj->Link());
			return;
		}
		
		//else redirect home
		Director::redirect(Director::absoluteURL('home',true));
		return;
	}
	
	

}

