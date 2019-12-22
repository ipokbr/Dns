<?php
/*
Esta classe eh utilizada somente para a classe e métodos de parser da resolucao de DNS

*/

namespace App;

use App\Aux_Func;


class Dns_Parser
{
        private static function ParsePath($str)
        {
		$parse=array();
                preg_match_all("/;; Received.*from (?<path_addr>.+)#53\((?<path_name>.+)\) in (?<path_time>.+)/",$str,$path);
                $parse['path_addr']=$path['path_addr'];
                $parse['path_name']=$path['path_name'];
                $parse['path_time']=$path['path_time'];
                return $parse;
        }

	private static function ParseStatus($str)
	{
		preg_match("/, status: (.+),/",$str,$status);
		return $status[1];
	}

	private static function ParseFlags($str)
	{
		$parse=array();
		preg_match("/;; flags: (.+);/",$str,$flags);
		$t_flags=explode(' ',$flags[1]); 

		foreach( $t_flags as $key => $value)
			$parse[$value] = '1';

		return $parse;
	}

	private static function ParseHeader($str)
	{
		$parse=array();
		preg_match('/flags: (.+); query: (.+), answer: (.+), authority: (.+), additional: (.+)/i', $str, $count);
		$parse['answer_count']=(int)trim($count[3]);
		$parse['authority_count']=(int)trim($count[4]);
		$parse['additional_count']=(int)trim($count[5]);
		return $parse;
	}

	private static function ParseResponse($section,$str)
	{
		$parse=array();
		$pos_start = strpos($str,$section);
		if($pos_start !== FALSE) 
		{
			$pos_start += strlen($section)+1; //adiciona a string buscada e o \n
			$pos_fim = strpos($str,"\n\n",$pos_start);
			$saida_answer = substr($str,$pos_start,$pos_fim-$pos_start);
			$saida_answer = explode("\n",$saida_answer);
			if(count($saida_answer)) 
				foreach($saida_answer as $key => $value) 
					$parse[$key] = Aux_Func::fill_dns_array($value);
		}
		
		return $parse;
	}


	public static function Parse($text)
	{
		$auth = ";; AUTHORITY SECTION:";
		$answer = ";; ANSWER SECTION:";
		$add = ";; ADDITIONAL SECTION:";
		$srv = ";; SERVER: ";
		$time = ";; Query time:";


		//Server responsible for the query
		$response['path']=self::ParsePath($text);

		$str = ";; Got answer:";
		$pos = Aux_Func::reverse_strpos($text,$str);

		//If $str was not found, there is an error
		if( $pos === FALSE) 
		{
			//It's almost impossible to reach this block
                        $response['status'] = "UNKNOWN";
                        $response['err'] = Aux_Func::dnserr($response['status']);
			return $response;
		}

		$text_parsed = substr($text,$pos+1);

		//Parse the sections
		$response['status']=self::ParseStatus($text_parsed);
		$response['flags']=self::ParseFlags($text_parsed);
		$response['header']=self::ParseHeader($text_parsed);

		if($response['status'] != "NOERROR") 
		{
			$response['err'] = Aux_Func::dnserr($response['status']);
			return $response;
        	}

		$response['answer']=self::ParseResponse($answer,$text_parsed);
		$response['authority']=self::ParseResponse($auth,$text_parsed);
		$response['additional']=self::ParseResponse($add,$text_parsed);

		//If 'answer' array is empty, then it flags an specific status to facilitate future checks
                if (count($response['answer']) == 0)
                {
                        $response['status'] = "NOANSWER";
                        $response['err'] = Aux_Func::dnserr($response['status']);
                }

		return $response;
	}
}
