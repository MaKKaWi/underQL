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

class UQLEntity extends UQLBase {
	
	private $uql_abstract_entity;
	private $uql_database_handle;
	private $uql_path;
	private $uql_change;
	private $uql_delete;
	
	public function __construct($entity_name, &$database_handle) {
		
		$this->uql_abstract_entity = new UQLAbstractEntity ( $entity_name, $database_handle );
		$this->uql_database_handle = $database_handle;
		$this->uql_path = null;
		$this->uql_change = new UQLChangeQuery ( $database_handle, $this->uql_abstract_entity );
		$this->uql_delete = new UQLDeleteQuery ( $database_handle, $this->uql_abstract_entity );
	}
	
	public function __set($name, $value) {
		$this->uql_change->$name = $value;
		return $this;
	}
	
	public function __get($name) {
		return $this->uql_change->$name;
	}
	
	public function the_uql_insert() {
		return $this->uql_change->the_uql_insert ();
	}
	
	public function the_uql_insert_or_update_from_array($the_array, $extra = '', $is_save = true) {
		//$array_count = @count($the_array);
		foreach ( $the_array as $key => $value ) {
			if ($this->uql_abstract_entity->the_uql_is_field_exist ( $key ))
				$this->$key = $value;
		}
		
		if ($is_save)
			return $this->the_uql_insert ();
		else
			return $this->the_uql_update ( $extra );
	}
	
	public function the_uql_insert_from_array($the_array) {
		return $this->the_uql_insert_or_update_from_array ( $the_array, null );
	}
	
	public function the_uql_update_from_array($the_array, $extra = '') {
		return $this->the_uql_insert_or_update_from_array ( $the_array, $extra, false );
	}
	
	public function the_uql_update_from_array_where_id($the_array, $id, $id_name = 'id') {
		return $this->the_uql_insert_or_update_from_array ( $the_array, "WHERE `$id_name` = $id", false );
	}
	
	public function the_uql_update($extra = '') {
		return $this->uql_change->the_uql_update ( $extra );
	}
	
	public function the_uql_update_where_id($id, $id_name = 'id') {
		return $this->uql_change->the_uql_update_where_id ( $id, $id_name );
	}
	
	public function the_uql_delete($extra = '') {
		return $this->uql_delete->the_uql_delete ( $extra );
	}
	
	public function the_uql_delete_where_id($id, $id_name = 'id') {
		return $this->uql_delete->the_uql_delete_where_id ( $id, $id_name );
	}
	
	public function the_uql_query($query) {
		
		$this->uql_path = new UQLQueryPath ( $this->uql_database_handle, $this->uql_abstract_entity );
		if ($this->uql_path->the_uql_execute_query ( $query ))
			return $this->uql_path;
		
		return false;
	}
	
	public function the_uql_select($fields = '*', $extra = '') {
		$query = sprintf ( "SELECT %s FROM `%s` %s", $fields, $this->uql_abstract_entity->the_uql_get_entity_name (), $extra );
		
		return $this->the_uql_query ( $query );
	}
	
	public function the_uql_select_where_id($fields, $id, $id_name = 'id') {
		return $this->the_uql_select ( $fields, "WHERE `$id_name` = $id" );
	}
	
	public function the_uql_are_rules_passed() {
		return $this->uql_change->the_uql_are_rules_passed ();
	}
	
	public function the_uql_get_messages_list() {
		return $this->uql_change->the_uql_get_messages_list ();
	}
	
	public function the_uql_get_abstract_entity() {
		return $this->uql_abstract_entity;
	}
	
	public function __destruct() {
		$this->uql_abstract_entity = null;
		$this->uql_database_handle = null;
		$this->uql_path = null;
		$this->uql_change = null;
		$this->uql_delete = null;
	}

}
?>