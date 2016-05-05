<?php
namespace AttendeeMover\steps\progress;

use EventEspresso\core\services\progress_steps\ProgressStep;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class VerifyChanges
 * Description
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         $VID:$
 */
class VerifyChanges extends ProgressStep {


	/**
	 * VerifyChanges constructor.
	 *
	 * @throws \EventEspresso\Core\Exceptions\InvalidDataTypeException
	 */
	public function __construct() {
		$this->setId( 3 );
		$this->setHtmlClass( 'verify_changes' );
		$this->setText( __( 'Verify Changes', 'event_espresso' ) );
	}



}
// End of file VerifyChanges.php
// Location: /VerifyChanges.php