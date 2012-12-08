<?php

/****************************************************************************************
 * Copyright (c) 2012, Abdullah E. Almehmadi - www.abdullaheid.net                      *
 * All rights reserved.                                                                 *
 ****************************************************************************************
   Redistribution and use in source and binary forms, with or without modification,     
 are permitted provided that the following conditions are met:                         
 
   Redistributions of source code must retain the above copyright notice, this list of 
 conditions and the following disclaimer.
 
   Redistributions in binary form must reproduce the above copyright notice, this list 
 of conditions and the following disclaimer in the documentation and/or other materials
 provided with the distribution.

   Neither the name of the underQL nor the names of its contributors may be used to
 endorse or promote products derived from this software without specific prior written 
 permission.

   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL
 THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
 OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *****************************************************************************************/

class UQLFilter extends UQLBase {
	
	private $uql_entity_name;
	private $uql_filters_map;
	
	public function __construct($entity_name) {
		//wefwewewe
		$this->uql_entity_name = $entity_name;
		$this->uql_filters_map = new UQLMap ();
	}
	
	public function __call($function_name, $parameters) {
		$local_params_count = count ( $parameters );
		if ($local_params_count < 1 /*filter_name [AT LEAST]*/)
            $this->the_uql_error ( $function_name . ' filter must have 1 parameter at least' );
		
		if ($local_params_count == 1)
			$this->the_uql_add_filter ( $function_name, array ($parameters [0], 'inout' ) );
		else
			$this->the_uql_add_filter ( $function_name, $parameters );
		
		return $this;
	}
	
	protected function the_uql_add_filter($field, $filter) {
		if (! $this->uql_filters_map->the_uql_is_element_exist ( $field ))
			$this->uql_filters_map->the_uql_add_element ( $field, new UQLMap () );
		
		$local_filter = $this->uql_filters_map->the_uql_find_element ( $field );
		$local_filter->the_uql_add_element ( $local_filter->the_uql_get_count (), array ('filter' => $filter, 'is_active' => true ) );
		$this->uql_filters_map->the_uql_add_element ( $field, $local_filter );
	}
	
	protected function the_uql_set_filter_activation($field_name, $filter_name, $activation) {
		$local_filter = $this->uql_filters_map->the_uql_find_element ( $field_name );
		if (! $local_filter)
			$this->the_uql_error ( 'You can not stop a filter for unknown field (' . $field_name . ')' );
			
		/* $target_filter = $local_filter->the_uql_find_element($filter_name);
        if(!$target_filter)
            $this->the_uql_error('You can not stop unknown filter ('.$filter_name.')'); */
		
		for($i = 0; $i < $local_filter->the_uql_get_count (); $i ++) {
			$target_filter = $local_filter->the_uql_find_element ( $i );
			if (strcmp ( $target_filter ['filter'] [0], $filter_name ) == 0) {
				$target_filter ['is_active'] = $activation;
				$local_filter->the_uql_add_element ( $i, array ('filter' => $target_filter ['filter'], 'is_active' => $activation ) );
				$this->uql_filters_map->the_uql_add_element ( $field_name, $local_filter );
			}
		}
	
	}
	
	public function the_uql_start_filters(/*$field_name,$filter_name*/)
    {
		$params_count = func_num_args ();
		if ($params_count < 2)
			$this->the_uql_error ( 'start_filters needs 2 parameters at least' );
		
		$filters_counts = $params_count - 1; // remove field name
		$parameters = func_get_args ();
		if ($filters_counts == 1) {
			$this->the_uql_set_filter_activation ( $parameters [0], $parameters [1], true );
			return;
		} else {
			for($i = 0; $i < $filters_counts - 1; $i ++)
				$this->the_uql_set_filter_activation ( $parameters [0], $parameters [$i + 1], true );
		}
	}
	
	public function the_uql_stop_filters(/*$field_name,$filter_name*/)
    {
		$params_count = func_num_args ();
		if ($params_count < 2)
			$this->the_uql_error ( 'stop_filters needs 2 parameters at least' );
		
		$filters_counts = $params_count - 1; // remove field name
		$parameters = func_get_args ();
		if ($filters_counts == 1) {
			$this->the_uql_set_filter_activation ( $parameters [0], $parameters [1], false );
			return;
		} else {
			for($i = 0; $i < $filters_counts - 1; $i ++)
				$this->the_uql_set_filter_activation ( $parameters [0], $parameters [$i + 1], false );
		}
	}
	
	public function the_uql_get_filters_by_field_name($field_name) {
		return $this->uql_filters_map->the_uql_find_element ( $field_name );
	}
	
	public function the_uql_get_filters() {
		return $this->uql_filters_map;
	}
	
	public function the_uql_get_entity_name() {
		return $this->uql_entity_name;
	}
	
	public static function the_uql_find_filter_object($entity) {
		$filter_object_name = sprintf ( UQL_FILTER_OBJECT_SYNTAX, $entity );
		if (isset ( $GLOBALS [$filter_object_name] ))
			$filter_object = $GLOBALS [$filter_object_name];
		else
			$filter_object = null;
		
		return $filter_object;
	}
	
	public function __destruct() {
		$this->uql_entity_name = null;
		$this->uql_filters_map = null;
	}
}

?>