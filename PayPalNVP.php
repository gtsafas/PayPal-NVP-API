<?php

/////////////////////////////////////////////////////////////////////////////////////
// Developed by George Tsafas 
// Last Modified: 2011-06-04
//
// You may do whatever you like with this lib
//
////////////////////////////////////////////////////////////////////////////////////

class PayPalNVP 
{
    private $Debug	= 0;
    private $UserName	= '';
    private $Password	= '';
    private $Signature	= '';
    private $EndPoint	= 'https://api-3t.sandbox.paypal.com/nvp';
    private $ECEndPoint	= 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout';
    private $APIVersion = '51.0';

    /*******************************************
    * Function PayPalNVP
    *
    * UserName  (Required)
    * Password  (Required)
    * Signature (Required)
    * EndPoint  (Optional)
    * Debug	(Optional) Note: Not very useful if not run from shell
    *
    *******************************************/
    public function PayPalNVP($credentials)
    {
	if ( (!is_array($credentials)) || (!isset($credentials)) )
	{
	    throw new Exception("You must give provide an array with credentials");
	}

	if (!isset($credentials['UserName']))
	{
	    throw new Exception("You must provide a UserName");
	}

	if (!isset($credentials['Password']))
	{
	    throw new Exception("You must provide a Password");
	}
	
	if (!isset($credentials['Signature']))
	{
	    throw new Exception("You must provide a Signature");
	}


	$this->UserName  = $credentials['UserName'];
	$this->Password	 = $credentials['Password'];
	$this->Signature = $credentials['Signature'];

	if (isset($credentials['EndPoint']))
	{
	    $this->EndPoint = $credentials['EndPoint'];
	}
	
	if (isset($credentials['APIVersion']))
	{
	    $this->APIVersion = $credentials['APIVersion'];
	}

	if (isset($credentials['ECEndPoint']))
	{
	    $this->ECEndPoint = $credentials['ECEndPoint'];
	}

	if (isset($credentials['Debug']))
	{
	    if (!is_int($credentials['Debug']))
	    {
		throw new Exception("Debug must be either a 1 or 0");
	    }
	    elseif ( ($credentials['Debug'] == 1) || ($credentials['Debug'] == 0) )
	    {
		$this->Debug = $credentials['Debug'];
	    }
	    else
	    {
		throw new Exception("Not a valid value for Debug, must be 1 or 0");
	    }
	}

    }

    public function SetAPIVersion($APIVersion)
    {
	if (isset($APIVersion))
	{
	    $this->APIVersion = $APIVersion;
	    return true;
	}
	else
	{
	    return false;
	}
    }

    public function SetEndPoint($EndPoint)
    {
	if (isset($EndPoint))
	{
	    $this->EndPoint = $EndPoint;
	    return true;
	}
	else
	{
	    return false;
	}
    }

    private function _FieldExists( $pattern, $input, $flags = 0 )
    {
	$keys = preg_grep( $pattern, array_keys( $input ), $flags );
	if (count($keys) > 0)
	{
	   return true;
	}
	else
	{
	   return false;
	}
	return false;
    }


    private function _PPCallAPI($http_query)
    {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $this->EndPoint);
	curl_setopt($ch, CURLOPT_VERBOSE, $this->Debug);

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);

	$nvpreq = http_build_query(array(
	    'VERSION'   => $this->APIVersion,
	    'PWD'	=> $this->Password,
	    'USER'	=> $this->UserName,
	    'SIGNATURE' => $this->Signature
	));

	$nvpreq .= "&$http_query";

	curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

	$httpResponse = curl_exec($ch);

	if(!$httpResponse) {
	    throw new Exception("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
	}

	$httpResponseAr = explode("&", $httpResponse);
	$httpParsedResponseAr = array();

	foreach ($httpResponseAr as $i => $value) 
	{
	    $tmpAr = explode("=", $value);
	    if(sizeof($tmpAr) > 1) {
		$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
	    }
	}

	if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) 
	{
	    throw new Exception("Invalid HTTP Response for POST request($nvpreq) to $this->EndPoint.");
	}

	return $httpParsedResponseAr;
    }

    
    /*******************************************
    * Function DoDirectPayment
    *
    * CREDITCARDTYPE	(Required)
    * ACCT		(Required)
    * EXPDATE		(Required)
    * CVV2		(Required)
    * AMT		(Required)
    *
    * FIRSTNAME		(Required)
    * LASTNAME		(Required)
    * STREET		(Required)
    * CITY		(Required)
    * STATE		(Required)
    * COUNTRYCODE	(Required)
    * ZIP		(Required)
    *
    * PAYMENTACTION	(Optional)
    *******************************************/
    public function DoDirectPayment($params)
    {
	if ( (!is_array($params)) || (!isset($params)) )
	{
	    throw new Exception("You must give provide an array with required parameters!");
	}

	if (!isset($params['AMT']))
	{
	    throw new Exception("Missing required field: AMT");
	}
	if (!isset($params['ZIP']))
	{
	    throw new Exception("Missing required field: ZIP");
	}
	if (!isset($params['COUNTRYCODE']))
	{
	    throw new Exception("Missing required field: COUNTRYCODE");
	}
	if (!isset($params['STATE']))
	{
	    throw new Exception("Missing required field: STATE");
	}
	if (!isset($params['CITY']))
	{
	    throw new Exception("Missing required field: CITY");
	}
	if (!isset($params['STREET']))
	{
	    throw new Exception("Missing required field: STREET");
	}
	if (!isset($params['LASTNAME']))
	{
	    throw new Exception("Missing required field: LASTNAME");
	}
	if (!isset($params['FIRSTNAME']))
	{
	    throw new Exception("Missing required field: FIRSTNAME");
	}
	if (!isset($params['CVV2']))
	{
	    throw new Exception("Missing required field: CVV2");
	}
	if (!isset($params['EXPDATE']))
	{
	    throw new Exception("Missing required field: EXPDATE");
	}
	if (!isset($params['ACCT']))
	{
	    throw new Exception("Missing required field: ACCT");
	}
	if (!isset($params['CREDITCARDTYPE']))
	{
	    throw new Exception("Missing required field: CREDITCARDTYPE");
	}

	$params['METHOD'] = 'DoDirectPayment';
	$http_query = http_build_query($params);

	$response = $this->_PPCallAPI($http_query);
	
	return (object)$response;
    }
    
    /*******************************************
    * Function DoCapture
    *
    * AUTHORIZATIONID	(Required)
    * AMT		(Required)
    * COMPLETETYPE	(Required)
    * CURRENCYCODE	(Required)
    * NOTE		(Optional)
    * INVNUM		(Optional)
    *
    *******************************************/
    public function DoCapture($params)
    {
	if ( (!is_array($params)) || (!isset($params)) )
	{
	    throw new Exception("You must give provide an array with required parameters!");
	}
	if (!isset($params['CURRENCYCODE']))
	{
	    throw new Exeception("Missing required field: CURRENCYCODE");
	}
	if (!isset($params['COMPLETETYPE']))
	{
	    throw new Exeception("Missing required field: COMPLETETYPE");
	}
	if (!isset($params['AMT']))
	{
	    throw new Exeception("Missing required field: AMT");
	}
	if (!isset($params['AUTHORIZATIONID']))
	{
	    throw new Exeception("Missing required field: AUTHORIZATIONID");	
	}

	$params['METHOD'] = 'DoCapture';
	
	$http_query = http_build_query($params);

	$response = $this->_PPCallAPI($http_query);

	return (object)$response;

    }

    public function SetExpressCheckout($params)
    {
	if ( (!is_array($params)) || (!isset($params)) )
	{
	    throw new Exception("You must give provide an array with required parameters!");
	}

	if ( !($this->_FieldExists("/PAYMENTREQUEST_[0-9]_AMT/",$params)) )
	{
	    throw new Exception("Missing required field: PAYMENTREQUEST_n_AMT");	
	}

	if (!isset($params['RETURNURL']))
	{
	    throw new Exception("Missing required field: RETURNURL");	
	}

	if (!isset($params['CANCELURL']))
	{
	    throw new Exception("Missing required field: CANCELURL");	
	}
	$params['METHOD'] = 'SetExpressCheckout';


	$http_query = http_build_query($params);

	$response = $this->_PPCallAPI($http_query);
	if (isset($response['TOKEN']))
	{
	    $response['CLEAN_TOKEN'] = urldecode($response['TOKEN']);
	    $response['CHECKOUT_URL'] = $this->ECEndPoint . '&token=' . $response['TOKEN'];
	}

	return (object)$response;

    }

    public function GetExpressCheckout($token)
    {
	if ( (!is_string($token)) || (!isset($token)) )
	{
	    throw new Exception("You must provide a valid string with the token");
	}

	$params = array(
		'METHOD' => 'GetExpressCheckoutDetails',
		'TOKEN'  => $token
	    );
	
	$http_query = http_build_query($params);

	$response = $this->_PPCallAPI($http_query);

	if (isset($response['TOKEN']))
	{
	    $response['CLEAN_TOKEN'] = urldecode($response['TOKEN']);
	}

	return (object)$response;

    }

    public function DoExpressCheckout($params)
    {
	if ( (!is_array($params)) || (!isset($params)) )
	{
	    throw new Exception("You must give provide an array with required parameters!");
	}

	if ( (!isset($params['TOKEN'])) )
	{
	    throw new Exception("Missing required field: TOKEN");
	}
	
	if ( (!isset($params['PAYERID'])) )
	{
	    throw new Exception("Missing required field: PAYERID");
	}
	
	$params['METHOD'] = 'DoExpressCheckoutPayment';

	$http_query = http_build_query($params);

	$response = $this->_PPCallAPI($http_query);

	return (object)$response;




    }



}

?>


