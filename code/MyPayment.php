<?php

/**
 * MyPayment example payment class
 * 
 * @author your name here <name [at] email.web>
 */

class MyPayment extends Payment{
	
	static $db = array(
		//it's a good idea to save transaction details that are returned from the gateway
		//'TransactionID' => 'Varchar'
	);
	
	//store logo url, api_endpoint, and config settings up here
	
	/**
	 * 
	 * This function is required
	 */
	function processPayment($data, $form) {
		
		//prepare to send data to gateway
		
		/*//send response
		return new Payment_Success();
		or
		return new Payment_Processing();
		or
		return new Payment_Failure($reason);
		*/
	}
	
	/**
	 * 
	 * This function is required
	 */
	function getPaymentFormFields() {
		
		return new FieldSet();	
	}
	
}


/* 
//only needed if you are connecting to an external gateway that will return to your site
class MyPayment_Controller extends Controller{
	
	protected $payment = null; //only need to get this once
	
	static $allowed_actions = array(
		'success',
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

*/