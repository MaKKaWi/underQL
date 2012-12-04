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

class UQLChangeQuery extends UQLBase {
	
	private $uql_the_query;
	private $uql_the_abstract_entity;
	/*
     used to save list of [field_name = value] that are coming
     from current insertion query
     */
	private $uql_the_values_map;
	private $uql_the_rule_engine;
	private $uql_the_rule_engine_results;
	
	public function __construct(&$database_handle, &$abstract_entity) {
		if ((! $database_handle instanceof UQLConnection) || (! $abstract_entity instanceof UQLAbstractEntity))
			$this->the_uql_error ( 'Bad database handle' );
		
		$this->uql_the_query = new UQLQuery ( $database_handle );
		$this->uql_the_abstract_entity = $abstract_entity;
		$this->uql_the_values_map = new UQLMap ();
		$this->uql_the_rule_engine = null;
		$this->uql_the_rule_engine_results = null;
	}
	
	public function __set($name, $value) {
		if (! $this->uql_the_abstract_entity->the_uql_is_field_exist ( $name ))
			$this->the_uql_error ( $name . ' is not a valid column name' );
		
		$this->uql_the_values_map->the_uql_add_element ( $name, $value );
	
		//echo '<pre>'; var_dump($this->uql_the_values_map); echo '</pre>';
	}
	
	public function __get($name) { //echo $name;
		//echo '<pre>'; var_dump($this->uql_the_abstract_entity); echo '</pre>';
		if (! $this->uql_the_abstract_entity->isFieldExist ( $name ))
			$this->the_uql_error ( $name . ' is not a valid column name' );
		
		if (! $this->uql_the_values_map->the_uql_is_element_exist ( $name ))
			return null;
		else
			return $this->uql_the_values_map->the_uql_find_element ( $name );
	
	}
	
	public function the_uql_are_rules_passed() {
		if ($this->uql_the_rule_engine != null)
			return $this->uql_the_rule_engine->the_uql_are_rules_passed ();
		
		return true;
	}
	
	public function the_uql_get_messages_list() {
		if (($this->uql_the_rule_engine != null) || ($this->uql_the_rule_engine_results == true))
			return $this->uql_the_rule_engine_results;
		
		return null;
	
	}
	
	protected function the_uql_format_insert_query() {
		$values_count = $this->uql_the_values_map->the_uql_get_count ();
		if ($values_count == 0)
			return "";
		
		$insert_query = 'INSERT INTO `' . $this->uql_the_abstract_entity->the_uql_get_entity_name () . '` (';
		
		$fields = '';
		$values = 'VALUES(';
		
		$all_values = $this->uql_the_values_map->the_uql_get_map ();
		$comma = 0;
		// for last comma in a string
		

		foreach ( $all_values as $key => $value ) {
			$fields .= "`$key`";
			$field_object = $this->uql_the_abstract_entity->the_uql_get_field_object ( $key );
			if ($field_object->numeric)
				$values .= $value;
			else // string quote
				$values .= "'$value'";
			
			$comma ++;
			
			if (($comma) < $values_count) {
				$fields .= ',';
				$values .= ',';
			}
		}
		
		$values .= ')';
		
		$insert_query .= $fields . ') ' . $values;
		return $insert_query;
	}
	
	protected function the_uql_insert_or_update($is_save = true, $extra = '') {
		$values_count = $this->uql_the_values_map->the_uql_get_count ();
		if ($values_count == 0)
			return false;
		
		$rule_object = UQLRule::the_uql_find_rule_object ( $this->uql_the_abstract_entity->the_uql_get_entity_name () );
		
		if ($rule_object != null) {
			$this->uql_the_rule_engine = new UQLRuleEngine ( $rule_object, $this->uql_the_values_map );
			
			$this->uql_the_rule_engine_results = $this->uql_the_rule_engine->the_uql_run_engine ();
			
			if (! $this->uql_the_rule_engine->the_uql_are_rules_passed ())
				return false;
		}
		
		$filter_object = UQLFilter::the_uql_find_filter_object ( $this->uql_the_abstract_entity->the_uql_get_entity_name () );
		
		if ($filter_object != null) {
			$fengine = new UQLFilterEngine ( $filter_object, UQL_FILTER_IN );
			$fengine->the_uql_set_values_map ( $this->uql_the_values_map );
			$this->uql_the_values_map = $fengine->the_uql_run_engine ();
		}
		
		if ($is_save)
			$query = $this->the_uql_format_insert_query ();
		else
			$query = $this->the_uql_format_update_query ( $extra );
		
		// clear values
		$this->uql_the_values_map = new UQLMap ();
		
		return $this->uql_the_query->the_uql_execute_query ( $query );
	}
	
	public function the_uql_insert() {
		return $this->the_uql_insert_or_update ();
	}
	
	protected function the_uql_format_update_query($extra = '') {
		$values_count = $this->uql_the_values_map->the_uql_get_count ();
		if ($values_count == 0)
			return "";
		
		$update_query = 'UPDATE `' . $this->uql_the_abstract_entity->the_uql_get_entity_name () . '` SET ';
		
		$fields = '';
		
		$all_values = $this->uql_the_values_map->the_uql_get_map ();
		$comma = 0;
		// for last comma in a string
		

		foreach ( $all_values as $key => $value ) {
			$fields .= "`$key` = ";
			$field_object = $this->uql_the_abstract_entity->the_uql_get_field_object ( $key );
			if ($field_object->numeric)
				$fields .= $value;
			else // string quote
				$fields .= "'$value'";
			
			$comma ++;
			
			if (($comma) < $values_count) {
				$fields .= ',';
			}
		}
		
		$update_query .= $fields . ' ' . $extra;
		
		return $update_query;
	}
	
	public function the_uql_update($extra = '') {
		return $this->the_uql_insert_or_update ( false, $extra );
	}
	
	public function the_uql_update_where_id($id, $id_name = 'id') {
		return $this->the_uql_update ( "WHERE `$id_name` = $id" );
	}
	
	public function __destruct() {
		$this->uql_the_query = null;
		$this->uql_the_abstract_entity = null;
		$this->uql_the_values_map = null;
		$this->uql_the_rule_engine = null;
		$this->uql_the_rule_engine_results = null;
	}

}
?>