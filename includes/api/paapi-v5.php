<?php

class AmazonPAApi
{

	private $region    = "us-east-1";
	private $accessUrl = "webservices.amazon.com";
	private $service = 'ProductAdvertisingAPI';
	private $accessKey = null;
	private $secretKey = null;
	private $partnerTag = null;
	private $items = [];
	private $awsHeaders = [];

	private $canonicalURL = '';
	private $HMACAlgorithm = 'AWS4-HMAC-SHA256';
    private $aws4Request = 'aws4_request';
    private $strSignedHeader = null;
    private $amzTimestamp = null;
    private $amzDate = null;
    private $payLoad = null;

	function __construct()
	{
		$this->amzTimestamp = $this->getTimeStamp();
		$this->amzDate = $this->getDate();
	}

	public function setAccessKey ( $accessKey )
	{
		if ( empty( trim($accessKey) ) || !is_string( $accessKey ) )
			throw new Exception('You must entered an access key.');

		$this->accessKey = $accessKey;
	}

	public function setSecretKey ( $secretKey )
	{
		if ( empty( trim($secretKey) ) || !is_string( $secretKey ) )
			throw new Exception('You must entered a secret key.');

		$this->secretKey = $secretKey;
	}

	public function setPartnerTag ( $partnerTag )
	{
		if ( empty( trim($partnerTag) ) || !is_string( $partnerTag ) )
			throw new Exception('You must entered a partner tag.');

		$this->partnerTag = $partnerTag;
	}

	protected function generateHex( $data )
	{
		return hash('sha256', $data);
	}

    /**
     * Funtion to generate AWS signature key.
     * @param key
     * @param date
     * @param region
     * @param service
     * @return - signature key
     * @reference - http://docs.aws.amazon.com/general/latest/gr/signature-v4-examples.html#signature-v4-examples-java
     */
    private function getSignatureKey($key, $date, $region, $service)
    {
        $kSecret  = 'AWS4' . $key;
        $kDate    = hash_hmac('sha256', $date, $kSecret, true);
        $kRegion  = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', $this->aws4Request, $kService, true);

        return $kSigning;
    }

    /**
     * Build string for Authorization header.
     * @param strSignature
     * @return
     */
    private function buildAuthorizationString($strSignature)
    {
        return $this->HMACAlgorithm . ' ' . 'Credential=' . $this->accessKey . '/' . $this->amzDate . '/' . $this->region . '/' . $this->service . '/' . $this->aws4Request . ',' . 'SignedHeaders=' . $this->strSignedHeader . ',' . 'Signature=' . $strSignature;
    }

	public function setItem ( $items = [] )
	{
		$this->items = (array) $items;
	}

	protected function preparePayload ()
	{
		$payload = [
		    "ItemIds" => (array) $this->items,
		    "PartnerTag" => $this->partnerTag,
		    "PartnerType" => "Associates",
		    "Resources" => [
		        "ItemInfo.Title",
		        "Offers.Listings.Price",
		        "Images.Primary.Medium",
		        "Images.Primary.Large"
		    ]
		];
        $this->payLoad = json_encode($payload, JSON_PRETTY_PRINT);
		return $this->payLoad;
	}

	protected function prepareHeaders()
	{
		$this->awsHeaders = [
		    'Content-Encoding' => 'amz-1.0',
		    'Content-Type' => 'application/json',
		    'Host' => 'webservices.amazon.com',
		    'X-Amz-Date' => $this->amzTimestamp,
		    'X-Amz-Target' => 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems',
		];

		/* Sort headers */
		ksort($this->awsHeaders);

		return $this->awsHeaders;

	}

	public function getResults ()
	{

        if ( empty( trim($this->accessKey) ) || !is_string( $this->accessKey ) )
			throw new Exception('You must entered an access key.');

		if ( empty( trim($this->secretKey) ) || !is_string( $this->secretKey ) )
			throw new Exception('You must entered a secret key.');

		if ( empty( trim($this->partnerTag) ) || !is_string( $this->partnerTag ) )
			throw new Exception('You must entered a partner tag.');

		if ( empty( $this->items ) || !is_array( $this->items ) )
			throw new Exception('You must entered a item id.');

		/* Prepare Headers for Signature Version 4. */
		$this->prepareHeaders();

		/* Create a Canonical Request for Signature Version 4. */
        $canonicalURL = $this->prepareCanonicalRequest();

        /* Create a String to Sign for Signature Version 4. */
        $stringToSign = $this->prepareStringToSign($canonicalURL);

        /* Calculate the AWS Signature Version 4. */
        $signature = $this->calculateSignature($stringToSign);
        if ($signature) {
            $this->awsHeaders['Authorization'] = $this->buildAuthorizationString($signature);
        }

        /* Making Request */
        $request = wp_remote_post( 'https://webservices.amazon.com/paapi5/getitems', array( 
            'timeout' => 120, 
            'httpversion' => '1.1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.87 Safari/537.36',
            'Origin' => 'https://webservices.amazon.com',
            'Referer' => 'https://webservices.amazon.com',
            'Connection' => 'close',
            'headers' => $this->awsHeaders,
            'body'    => $this->payLoad,
        ) );

        if (is_wp_error( $request )){
            return $this->sendOutput(false, $request->get_error_message());
        }

        /* Getting Body of Request */
        $body    = json_decode( wp_remote_retrieve_body( $request ) );

        /* Request have some error */
        if ( isset($body->Errors) ){
            return $this->sendOutput(false, sprintf("<strong>%s</strong>: %s", $body->Errors[0]->Code, $body->Errors[0]->Message) );
        }

        return $this->sendOutput(true, $this->prepareItems($body));


	}

    private function sendOutput($success=true, $data='')
    {
        $output = ["success" => $success];
        if ( $success ){
            $output['data']    = $data;
        }else{
            $output['message'] = $data;
        }
        return $output;
    }

	private function prepareCanonicalRequest()
    {
        $canonicalURL = '';

        /* Start with the HTTP request method (GET, PUT, POST, etc.), followed by a newline character. */
        $canonicalURL .= "POST\n";

        /* Add the canonical URI parameter, followed by a newline character. */
        $canonicalURL .= "/paapi5/getitems\n" . "\n";

        /* Add the canonical headers, followed by a newline character. */
        $signedHeaders = '';
        foreach ($this->prepareHeaders() as $key => $value) {
            $signedHeaders .=  strtolower($key) . ';';
            $canonicalURL .= strtolower($key) . ':' . $value . "\n";
        }

        $canonicalURL .= "\n";

        /* Add the signed headers, followed by a newline character. */
        $this->strSignedHeader = substr($signedHeaders, 0, -1);
        $canonicalURL .= $this->strSignedHeader . "\n";

        /* Use a hash (digest) function like SHA256 to create a hashed value from the payload in the body of the HTTP or HTTPS. */
        $canonicalURL .= $this->generateHex($this->preparePayload());
        $this->canonicalURL = $canonicalURL;

        return $canonicalURL;
    }

    private function prepareStringToSign($canonicalURL)
    {
        $stringToSign = '';

        /* Add algorithm designation, followed by a newline character. */
        $stringToSign .= $this->HMACAlgorithm . "\n";

        /* Append the request date value, followed by a newline character. */
        $stringToSign .= $this->amzTimestamp . "\n";

        /* Append the credential scope value, followed by a newline character. */
        $stringToSign .= $this->amzDate . '/' . $this->region . '/' . $this->service . '/' . $this->aws4Request . "\n";
        /* Append the hash of the canonical request */
        $stringToSign .= $this->generateHex($canonicalURL);

        return $stringToSign;
    }

    private function calculateSignature($stringToSign)
    {
        /* Derive signing key */
        $signatureKey = $this->getSignatureKey($this->secretKey, $this->amzDate, $this->region, $this->service);

        /* Calculate the signature. */
        $signature = hash_hmac('sha256', $stringToSign, $signatureKey, true);

        /* Encode signature (byte[]) to Hex */
        $strHexSignature = strtolower(bin2hex($signature));

        return $strHexSignature;
    }

    private function prepareItems($body){
        $item = $body->ItemsResult->Items[0];
        return [
            'ASIN' => (string) $item->ASIN,
            'Title'         => (string) $item->ItemInfo->Title->DisplayValue,
            'DetailPageURL' => (string) $item->DetailPageURL,
            'ListPrice' => (isset($item->Offers)?(string) $item->Offers->Listings[0]->Price->DisplayAmount:''),
            'Image' => (isset($item->Images->Primary->Large->URL)?$item->Images->Primary->Large->URL:$item->Images->Primary->Medium->URL),
        ];
    }


    /**
     * Get timestamp. yyyyMMdd'T'HHmmss'Z'
     * @return - timestamp in required firmat
     */
    private function getTimeStamp()
    {
        return gmdate('Ymd\THis\Z');
    }

    /**
     * Get date. yyyyMMdd
     * @return - GMT date
     */
    private function getDate()
    {
        return gmdate('Ymd');
    }
}