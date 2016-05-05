<?php
namespace AttendeeMover\steps\progress;

use EventEspresso\core\services\progress_steps\ProgressStep;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class SelectEvent
 * Description
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         $VID:$
 */
class SelectEvent extends ProgressStep {

	/**
	 * SelectEvent constructor.
	 *
	 * @throws \EventEspresso\Core\Exceptions\InvalidDataTypeException
	 */
	public function __construct() {
		$this->setId( 1 );
		$this->setHtmlClass( 'select_event' );
		$this->setText( __( 'Select Event', 'event_espresso' ) );
	}

}
// End of file SelectEvent.php
// Location: /SelectEvent.php