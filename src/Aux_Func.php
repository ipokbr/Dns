<?php

namespace App;

class Aux_Func
{
	public static function getReverseString($s)
	{
		return implode('.',array_reverse(explode('.', $s))) . ".in-addr.arpa.";
	}

	public static function reverse_strpos($haystack,$needle)
	{
		$pos = strpos (strrev($haystack), strrev($needle));

		if ($pos === FALSE)
			return FALSE;

		return (strlen($haystack) - $pos);
	}

	public static function fill_dns_array($dns_line)
	{
		list($host, $ttl, $class, $type, $data) = preg_split('/[\s]+/', $dns_line, 5);

	        //if last digit of data string is (.), strip
        	if(substr($data,-1) == '.')
                	$data=substr($data,0,-1);

        	if(substr($host,-1) == '.')
                	$host=substr($host,0,-1);

		return  array( 'host' => $host, 'ttl' => $ttl, 'class' => $class, 'type' => $type, 'data' => $data);
	}

	public static function dnserr($status)
	{
        	switch($status) {
                	case 'SERVFAIL':
                        	$msg = "Server failed executing query (SERVFAIL).";
                        	break;
                	case 'NXDOMAIN':
                        	$msg = "No record found (NXDOMAIN).";
                        	break;
                	case 'REFUSED':
                        	$msg = "Query refused (REFUSED).";
                        	break;
                	case 'TIMEOUT':
                        	$msg = "Query timed out (TIMEOUT).";
                        	break;
	                case 'NOANSWER':
        	                $msg = "Invalid Answer (NOANSWER).";
                	        break;
                	default:
                        	$msg = "Unknown error (FAILED).";
        	}
        	return $msg;
	}
}
