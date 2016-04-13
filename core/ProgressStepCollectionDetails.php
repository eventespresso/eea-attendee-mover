<?php
namespace EspressoAttendeeMover\core;

use EventEspresso\core\services\collection_loaders\CollectionDetails;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class ProgressStepCollectionDetails
 * Description
 *
 * @package       Event Espresso
 * @subpackage    core
 * @author        Brent Christensen
 * @since         $VID:$
 */
class ProgressStepCollectionDetails extends CollectionDetails {

	/**
	 * ProgressStepCollectionDetails constructor.
	 */
	public function __construct() {
		$this->setCollectionInterface( 'ProgressStepInterface' );
		$this->setPathToCollection( plugin_dir_path( __FILE__ ) . DS . 'attendee_mover_steps' );
	}

}
// End of file ProgressStepCollectionDetails.php
// Location: /ProgressStepCollectionDetails.php