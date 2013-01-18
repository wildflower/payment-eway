<?php

/**
 * eWay payment class
 * 
 * @author your name here haydn [at] email.web>
 */

class eWay extends Payment{
	
	protected static $eway_CustomerID;
	
	public static function set_eway_CustomerID($account) {
		self::$eway_CustomerID = $account;
	}
	public static function eway_CustomerID() {
		return self::$eway_CustomerID;
	}
	protected static $eway_Username;
	
	public static function set_eway_Username($username) {
		self::$eway_Username = $username;
	}
	public static function eway_Username() {
		return self::$eway_Username;
	}
	static $db = array(
		//it's a good idea to save transaction details that are returned from the gateway
		'TrxnNumber' => 'Varchar',
		'AuthCode' => 'Varchar'
	);
	public static $summary_fields = array(
	'TrxnNumber' => 'Txn Number'
	);
	
	static $eWay_Url = "https://www.ewaygateway.com/gateway_CVN/payment.asp";
	static $eWay_Sandbox_API_Key = "C3AB9CRwb7s70t3pKEaWxV8/CZnpt51SM0CmglO6Q0L3y6+r0yIidynMnHMpfzk1ug5YyH";

	public static function fetch_data($string, $start_tag, $end_tag)
		{
			$position = stripos($string, $start_tag);  
			$str = substr($string, $position);  		
			$str_second = substr($str, strlen($start_tag));  		
			$second_positon = stripos($str_second, $end_tag);  		
			$str_third = substr($str_second, 0, $second_positon);  		
			$fetch_data = trim($str_third);		
			return $fetch_data; 
		}
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
		$ewayurl.="?CustomerID=".$this->eway_CustomerID();
		$ewayurl.="&UserName=".$this->eway_Username();
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
		$ewayurl.="&CancelURL=".Director::absoluteBaseURL()."/ewayctl/cancel/".$data['Reference'];
		$ewayurl.="&ReturnUrl=".Director::absoluteBaseURL()."/ewayctl/success/".$data['Reference'];
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
		
		$responsemode = eWay::fetch_data($response, '<result>', '</result>');
	    $responseurl = eWay::fetch_data($response, '<uri>', '</uri>');
		   
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
		SS_Log::log( New Exception('In payment'), SS_Log::NOTICE );
		$id = $this->request->param('ID');
	
		if($this->payment){
			return $this->payment;
		}

		if($id){
			$p =  DataObject::get_one('payment',"\"OrderID\" = '$id'");
			$this->payment = $p;
			return $p;
		}
		return null;
	}
	
	function order(){
		SS_Log::log( New Exception('In Order'), SS_Log::NOTICE );
		$id = $this->request->param('ID');
	
		if($this->order){
		return $this->order;
		}
		
		if($id){
			$o =  DataObject::get_one('order',"\"ID\" = '$id'");
			$this->order = $o;
			return $o;
		}
		
		return null;
	}
	
	function failure(){
		echo "failure in here";
	}
	function getAccessPaymentCode()
	{
		//return 'XAl9yWwl7aWYBY7rPCdAhzOQToMhCcLhodvkrCCmlGmSi7nQqC';
		return $this->request->postVar('AccessPaymentCode');
	}
	
	function success(){
	
	
	if(eWay::eway_CustomerID()){
		$CustomerID = eWay::eway_CustomerID();
		}
	if(eWay::eway_Username()){
		$Username = eWay::eway_Username();
		}
	
	//curl to the result URL https://au.ewaygateway.com/Result/?CustomerID=87654321&UserName=TestAccount &AccessPaymentCode=611a5cabc19330f5
	static $ewayurl;
		$ewayurl.="?CustomerID=".$CustomerID;
		$ewayurl.="&UserName=".$Username;
		$ewayurl.="&AccessPaymentCode=".$this->getAccessPaymentCode();
		
		 $spacereplace = str_replace(" ", "%20", $ewayurl);	
	    $posturl="https://au.ewaygateway.com/Result/$spacereplace";
		SS_Log::log( New Exception('PostURL is :'.$posturl), SS_Log::NOTICE );
		
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		$response = curl_exec($ch);
		//SS_Log::log( New Exception($response), SS_Log::NOTICE );			
		
		//get payment/order object, based on some passed URL parameter
		$payment = $this->payment();
		$order = $this->order();
		
		//get status from response and stick it into $this->payment->status		
		$responsestatus = eWay::fetch_data($response, '<ResponseCode>', '</ResponseCode>');
		if($responsestatus =='00'){
			$this->payment->Status = "Success";
			$this->order->Status = "Paid";
			$this->order->Paid = date('Y-m-d H:i:s');
		}elseif ($responsestatus =='08'||$responsestatus =='10'||$responsestatus =='11'||$responsestatus =='16'){
			$this->payment->Status ="Incomplete";
		}else{
			$this->payment->Status ="Failure";
			}
		$responsetxn = eWay::fetch_data($response, '<TrxnNumber>', '</TrxnNumber>');
			$this->payment->TrxnNumber = $responsetxn;
			
		$responsemessage = eWay::fetch_data($response, '<TrxnResponseMessage>', '</TrxnResponseMessage>');
		$this->payment->Message = $responsemessage;

		$responseamount = eWay::fetch_data($response, '<ReturnAmount>', '</ReturnAmount>');
		//get message from response and stick it into $this->payment->message
		if ($responseamount != $this->payment->AmountAmount){
			$this->payment->Status = "Failure";
			$this->payment->Message = "Amount is different!";
			$this->order->Status = "Unpaid";
		}
		
		$this->payment->write();
		$this->order->write();
		//SS_Log::log( New Exception(var_export($payment,TRUE)), SS_Log::NOTICE );
		
		if($payment && $obj = $payment->PaidObject()){
			//redirect to PaymentObject link (eg $order->Link())
			$link = $obj->Link();
			$this->redirect($link);
			return;
		}
		
		//else redirect home
		$this->redirect(Director::absoluteURL('home',true));
		return;
	}
	
	

}

