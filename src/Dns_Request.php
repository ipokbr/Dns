<?php

namespace App;

use App\Aux_Func;
use App\Dns_Parser;
use Symfony\Component\Process\Process;


class Dns_Request
{
	protected $domain="";
	protected $trace=0;
	protected $record="a";
	protected $server="";
	protected $tcp=0;
	protected $recursive=1;
	protected $strict=1;
	protected $timeout=30;
	protected $types = [
        'a',
        'aaaa',
        'cname',
        'ns',
        'soa',
        'mx',
        'srv',
        'txt',
        'ptr'
	];

	public function setRequestType($record)
	{
		$valid_record = strtolower($record);
		if (! in_array($valid_record, $this->types))
			return FALSE;

		$this->record=$valid_record;
	}

	public function setServerRequest($server)
	{
		$server = escapeshellcmd($server);
		$this->server=$server;
	}

	public function unsetRecursive()
	{
		$this->recursive=0;
	}

	public function setTimeout($t)
	{
		$timeout=(int)$t;
		if($timeout > 0 && $timeout < 3600)
			$this->timeout=$timeout;
		else
			$this->timeout=30;
	}

	public function unsetStrict()
	{
		$this->strict=0;
	}

	/* Trace
	* This feature allows dig to follow the resolution path directly from root servers to 
	* the authoritative servers. This is cool because we can avoid local caches from 
	* recursive resolvers. Take care of CNAMEs resolution 
	*/
	public function setTrace()
	{
		$this->trace=1;
	}

	public function setTcp()
	{
		$this->tcp=1;
	}

	/*
	* This function removes from answer array the records that is not strict to those requested
	* It means: you request 'A' records from 'domain.com', sometimes dns answer includes the 'A'
	* record and also a CNAME record for performance purposes.
	* It is useless in many scenarios, but someone may need to analyze it when debugging. Also
	* it is important with +trace method, because we can follow cnames
	* This is why I prefer to remove it by default, but create a flag to enable send this data.
	*/
	private static function pStrict($answer="",$type="")
	{
		//TODO: strictly to the type, it doesn't analyze the label (that is different, of course)
		$ret=array();
		if( (is_array($answer)) && (count($answer) > 0) )
		{
			foreach($answer as $data) {
				if(strtolower($data['type']) == $type)
					$ret[]=$data;
			}
		}

		return $ret;
	}

	private function PrepareDig()
	{
		$hostname = "";
		if( ($this->record == "ptr") && (strpos($this->domain,"in-addr.arpa") === FALSE) )
			$hostname = Aux_Func::getReverseString($this->domain);
		else
			$hostname = $this->domain;

		//Main command - prepare dig to run
		$dig = ['/usr/bin/dig',
			'+time=5',
			'-4'
		];

		//Process query options
		if($this->trace)
			$dig[]='+trace';

		if($this->tcp)
			$dig[]='+tcp';

		if( $this->recursive == 0)
			$dig[]='+norec';

		$add= [ '+noall',
			'+answer',
			'+authority',
			'+additional',
			'+comments'
		];

		$dig = array_merge($dig,$add);

		if(! empty($this->server))
			$dig[]='@' . $this->server;

		$dig[]=$hostname;
		$dig[]=$this->record;

		return $dig;
	}
	private function fillRequest()
	{
		return [
			'trace' => $this->trace, //future
			'record' => $this->record,
			'server' => $this->server,
			'tcp'	=> $this->tcp,
			'strict'=> $this->strict,
			'recursive' => $this->recursive,
			'value' => $this->domain
		];
	}


	/*Main class method*/
	public function DnsRequest($value)
	{
		$value=escapeshellcmd($value);
		$this->domain=$value;

		//Prepare the string to run Dig
		$dig = $this->PrepareDig();

		//Set and execute process
		$process = new Process($dig);
		$process->setTimeout($this->timeout); //default 30s
		$process->run();

		//If error, return it
		if (!$process->isSuccessful()) 
		{
			$response['status'] = 'UNKNOWN';
			$response['err'] = Aux_Func::dnserr($response['status']);
		}
		else 
		{
			$result = $process->getOutput();

			$response = Dns_Parser::Parse($result);

			//Process Strict form (details above in the function)
			if( $this->strict && $response['status'] == 'NOERROR') {
				$response['answer'] = self::pStrict($response['answer'],$this->record);
				//Adjust the header count of answers
				$response['header']['answer_count'] = count($response['answer']);
			}
		}

		//Also send the request flags used
		$response['request']= $this->fillRequest();

		return $response;
	}
}
