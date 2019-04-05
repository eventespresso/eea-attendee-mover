/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function EE_Attendee_Mover_Event_Select2( data_interface_args ) {
	this.default_query_params = data_interface_args.default_query_params || {};
	this.items_per_page = parseInt( this.default_query_params.limit ) || 10;
	this.nonce = data_interface_args.nonce;
	this.locale = data_interface_args.locale;
	this.now = moment();
	this.ISO_8601 = moment.ISO_8601;
	this.sold_out_notice = eei18n.attendee_mover_sold_out_datetime;
	;

	/**
	 * Changes the request params set by select2 and prepares them for an EE4
	 * REST request
	 * @param {object} params
	 * @returns object
	 */
	this.prepData = function( params ) {
		params.page = params.page || 1;
		var new_params = this.default_query_params;
		new_params.limit = [
			( params.page - 1 ) * this.items_per_page,
			this.items_per_page,
		];
		if ( typeof new_params.where === 'undefined' ) {
			new_params.where = {};
		}
		var search_term = params.term || '';
		new_params.where.EVT_name = [ 'like', '%' + search_term + '%' ];
		new_params.include = 'EVT_ID, EVT_name, Datetime.DTT_name,' +
			' Datetime.DTT_EVT_start, Datetime.DTT_EVT_end,' +
			' Datetime.DTT_is_primary, Datetime.DTT_reg_limit,' +
			' Datetime.DTT_sold';
		new_params._wpnonce = this.nonce;
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
	 * @param {moment} startDate
	 * @param {moment} endDate
	 * @returns {boolean}
	 */
	this.isActive = function( startDate, endDate ) {
		return startDate.diff( this.now, 'seconds' ) < 0 &&
			endDate.diff( this.now, 'seconds' ) > 0;
	};

	/**
	 * @param {moment} startDate
	 * @returns {boolean}
	 */
	this.isUpcoming = function( startDate ) {
		return startDate.diff( this.now, 'seconds' ) > 0;
	};

	/**
	 * Takes incoming EE4 REST API response
	 * and turns into a data format select2 can handle
	 *
	 * @param {object} data
	 * @param {object} params
	 * @returns object
	 */
	this.processResults = function( data, params ) {
		var event = null;
		var event_date = null;
		var start_date = null;
		var end_date = null;
		var formatted_results = [];
		var option_text = '';
		var datetime_details = '';
		moment.locale( this.locale );
		for ( var i = 0; i < data.length; i++ ) {
			event = data[ i ];
			option_text = '';
			datetime_details = '';
			for ( var j = 0; j < event.datetimes.length; j++ ) {
				event_date = event.datetimes[ j ];
				start_date = moment( event_date.DTT_EVT_start, this.ISO_8601 );
				end_date = moment( event_date.DTT_EVT_end, this.ISO_8601 );
				if (
					datetime_details === '' &&
					(
						this.isActive( start_date, end_date ) ||
						this.isUpcoming( start_date )
					)
				) {
					if ( event_date.DTT_name !== '' ) {
						datetime_details = event_date.DTT_name + ' •• ';
					}
					datetime_details += start_date.format( 'MMM DD YYYY' );
					if ( start_date.diff( end_date, 'seconds' ) !== 0 ) {
						datetime_details += ' - ' +
							end_date.format( 'MMM DD YYYY' );
					}
					if (
						parseInt( event_date.DTT_reg_limit, )
						<= parseInt( event_date.DTT_sold, )
					) {
						datetime_details += ' •• ' + this.sold_out_notice;
					}
				}
			}
			option_text = '# ' + event.EVT_ID + ' •• ';
			option_text += event.EVT_name + ' •• ' + datetime_details;
			formatted_results.push(
				{
					id: event.EVT_ID,
					text: option_text,
				},
			);
		}
		params.page = params.page || 1;
		return {
			results: formatted_results,
			pagination: {
				more: data.length === this.items_per_page,
			},
		};
	};
}

// scripts/attendee-mover-event-selector.js:86
