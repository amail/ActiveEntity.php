<?php

	/*
	* This file is part of the ActiveEntity package.
	*
	* (c) Robin Orheden <robin@comfirm.se>, Comfirm AB
	*
	* For the full copyright and license information, please view the LICENSE
	* file that was distributed with this source code.
	*/

	abstract class EntityBase {
	    protected function formatPropertyName($segments){
	    	return implode("_", $segments);
	    }

	    protected function splitIntoWords($text, $lowercase = true){
			// Break the text into segments where case determines each word
			preg_match_all('/((?:^|[A-Z])[a-z0-9]*)/', ucfirst($text), $segments);

			$segments = $segments[0];
			array_shift($segments);

			return $lowercase ? array_map('strtolower', $segments) : $segments;
	    }

	    protected function convertPropertyNameToTypeName($property_name, $singularify = false){
	    	if($singularify){
	    		// It's not always as simple as this.. But currently works out 99.9% of the cases.
	    		// Could implement support for this http://www.kavoir.com/2011/04/php-class-converting-plural-to-singular-or-vice-versa-in-english.html
	    		if(substr($property_name, -1) == 's'){
	    			$property_name = substr($property_name, 0, -1);
	    		}
	    	}

	    	return implode("", array_map("ucfirst", explode("_", $property_name)));
	    }
	}

?>