<?php
namespace AttendeeMover\steps\progress;

use EventEspresso\core\services\progress_steps\ProgressStep;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class SelectTicket
 * Description
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         $VID:$
 */
class SelectTicket extends ProgressStep {



	/**
	 * SelectTicket constructor.
	 *
	 * @throws \EventEspresso\Core\Exceptions\InvalidDataTypeException
	 */
	public function __construct() {
		$this->setId( 2 );
		$this->setHtmlClass( 'select_ticket' );
		$this->setText( __( 'Select Ticket', 'event_espresso' ) );
	}



}
// End of file SelectTicket.php
// Location: /SelectTicket.php