/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function EE_Attendee_Mover_Event_Select2( data_interface_args ) {
	this.default_query_params = data_interface_args.default_query_params || {};
	this.items_per_page = this.default_query_params.limit || 10;
	this.nonce = data_interface_args.nonce;
	this.locale = data_interface_args.locale;

	/**
	 * Changes the request params set by select2 and prepares them for an EE4 REST request
	 * @param {object} params
	 * @returns object
	 */
	this.prepData = function ( params ) {
		params.page = params.page || 1;
		var new_params =  this.default_query_params;
		new_params.limit = [
			( params.page - 1 ) * this.items_per_page,
			this.items_per_page
		];
		if( typeof new_params.where === 'undefined' ) {
			new_params.where = {};
		}
		var search_term = params.term || '';
		new_params.where.EVT_name= [ 'like', '%' + search_term + '%' ];
		new_params.include='EVT_ID, EVT_name, Datetime.DTT_name, Datetime.DTT_EVT_start, Datetime.DTT_EVT_end, Datetime.DTT_is_primary, Datetime.DTT_reg_limit, Datetime.DTT_sold';
		new_params._wpnonce = this.nonce;
		// console_log_object( 'new_params', new_params, 0 );
		return new_params;
	};

	/**
	 * Sets the wp nonce header for authentication
	 * @param {object} xhr
	 * @returns void
	 */
	this.beforeSend = function( xhr ) {
		 xhr.setRequestHeader( 'X-WP-Nonce', this.nonce );
	};

	/**
	 * Takes incoming EE4 REST API response and turns into a data format select2 can handle
	 * @param {object} data
	 * @param {object} params
	 * @returns object
	 */
	this.processResults = function ( data, params ){
		// console_log( 'processResults', '', true );
		// console_log_object( 'data', data, 0 );
		// console_log_object( 'params', params, 0 );
		var formatted_results = [];
		for( var i=0; i<data.length; i++ ) {
			//find the primary datetime's name
			var preferred_datetime_text = '';
			var secs       = 0;
			var start_date = null;
			var start_date_string = '';
			var end_date = null;
			var end_date_string = '';
			var reg_limit = 999999;
			var sold = 0;
			moment.locale( this.locale );

			for( var j=0; j<data[i].datetimes.length; j++) {
				if( data[i].datetimes[j].DTT_is_primary || preferred_datetime_text == '' ) {
					if( data[i].datetimes[j].DTT_name !== '' ) {
						preferred_datetime_text = data[ i ].datetimes[ j ].DTT_name + ': ';
					}
					// console_log_object( 'DTT_EVT_start', data[ i ].datetimes[ j ].DTT_EVT_start, 0 );
					// console_log_object( 'DTT_EVT_end', data[ i ].datetimes[ j ].DTT_EVT_end, 0 );
					secs = Date.parse( data[i].datetimes[j].DTT_EVT_start );
					// start date
					start_date        = moment( new Date( secs ) );
					start_date_string = start_date.format( "MMM YYYY" );
					preferred_datetime_text += start_date_string;
					// end date
					secs     = Date.parse( data[ i ].datetimes[ j ].DTT_EVT_end );
					end_date        = moment( new Date( secs ) );
					end_date_string = end_date.format( "MMM YYYY" );
					if ( end_date_string !== start_date_string ) {
						preferred_datetime_text += ' - ' + end_date_string;
					}
					reg_limit = parseInt( data[ i ].datetimes[ j ].DTT_reg_limit  );
					sold = parseInt( data[ i ].datetimes[ j ].DTT_sold  );
					if ( reg_limit <= sold ) {
						preferred_datetime_text += ' : ' + eei18n.attendee_mover_sold_out_datetime;
					}

				}
			}
			formatted_results.push(
				{
					id: data[i]['EVT_ID'],
					text: data[i]['EVT_name'] + ' ( ' + preferred_datetime_text + ' )'
				}
			);
		}
		params.page = params.page || 1;

		return {
		  results: formatted_results,
		  pagination: {
			more: data.length == this.items_per_page
		  }
		}
	};
}

// scripts/attendee-mover-event-selector.js:86