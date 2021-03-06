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

class UQLQueryPath extends UQLBase {
	
	public $uql_abstract_entity;
	// reference to the abstract table's data
	public $uql_query_object;
	public $uql_filter_engine;
	
	public function __construct(&$database_handle, &$abstract_entity) {
		
		if ($abstract_entity instanceof UQLAbstractEntity)
			$this->uql_abstract_entity = $abstract_entity;
		else
			$this->error ( 'You must provide a appropriate value for abstract_entity parameter' );
		
		$this->uql_query_object = new UQLQuery ( $database_handle );
		$filter_object = UQLFilter::the_uql_find_filter_object ( $this->uql_abstract_entity->the_uql_get_entity_name () );
		$this->uql_filter_engine = new UQLFilterEngine ( $filter_object, UQL_FILTER_OUT );
	}
	
	public function the_uql_execute_query($query) {
		
		if ($this->uql_query_object->the_uql_execute_query ( $query )) {
			if ($this->uql_query_object->the_uql_get_count () > 0) {
				$this->the_uql_get_next ();
				return true;
			}
		}
		
		return false;
	
	}
	
	public function the_uql_get_next() {
		return $this->uql_query_object->the_uql_fetch_row ();
	}
	
	public function the_uql_get_count() {
		return $this->uql_query_object->the_uql_get_count ();
	}
	
	public function the_uql_get_query_object() {
		return $this->uql_query_object;
	}
	
	public function the_uql_get_abstract_entity() {
		return $this->uql_abstract_entity;
	}
	
	public function __get($key) {
		
		if (! $this->uql_abstract_entity->the_uql_is_field_exist ( $key ))
			$this->the_uql_error ( "Unknown field [$key]" );
		
		$local_current_query_fields = $this->uql_query_object->the_uql_get_current_query_fields ();
		if ($local_current_query_fields == null)
			return "Unknown";
		
		foreach ( $local_current_query_fields as $local_field_name ) {
			if (strcmp ( $key, $local_field_name ) == 0) {
				$local_current_row = $this->uql_query_object->the_uql_get_current_row ();
				if ($local_current_row == null)
					return "Unknown";
				else {
					return $this->uql_filter_engine->the_uql_apply_filter ( $key, $local_current_row->$key );
				}
			}
		}
		
		return "Unknown";
	}
	
	public function __destruct() {
		
		$this->uql_abstract_entity = null;
		$this->uql_query_object = null;
	
		//$this->plugin = null;
	}

}
?>