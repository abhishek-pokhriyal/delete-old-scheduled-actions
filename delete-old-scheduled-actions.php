<?php
/**
 * Plugin Name:       Delete Old Scheduled Actions
 * Plugin URI:        https://github.com/coloredcow-admin/megafitmeals/
 * Description:       Delete old scheduled actions.
 * Version:           0.0.1
 * Author:            ColoredCow
 * Author URI:        https://coloredcow.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       delete-old-scheduled-actions
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'DOSA_VER' ) )
	define( 'DOSA_VER', '0.0.1' );

class Delete_Old_Scheduled_Actions {
	/**
	 * Static property to hold our singleton instance
	 *
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return void
	 */
	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ), 10 );
		add_filter( 'page_template', array( $this, 'template_loader' ) );
		add_action( 'wp_ajax_actionscheduler_purging', array( $this, 'do_actionscheduler_purging' ) );
		add_action( 'wp_ajax_nopriv_actionscheduler_purging', array( $this, 'do_actionscheduler_purging' ) );
	}

	public function template_loader( $page_template )
	{
		if ( is_page( 'delete-actions' ) ) {
			$page_template = dirname( __FILE__ ) . '/delete-actions.php';
		}

		return $page_template;
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return Delete_Old_Scheduled_Actions
	 */

	public static function getInstance() {
		if ( ! self::$instance )
			self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Call front-end JS
	 *
	 * @return void
	 */
	public function front_scripts() {
		if ( is_page( 'delete-actions' ) ) {
			wp_enqueue_script( 'dos-actions', plugins_url( 'assets/dos-actions.js', __FILE__ ), array( 'jquery' ), DOSA_VER, true );
		}
	}

	public function authorize_request() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'You are not authorized.' ) );
			exit;
		}

		return true;
	}

	public function do_actionscheduler_purging() {
		global $wpdb;

		$this->authorize_request();

		$actions_table = $wpdb->prefix . 'actionscheduler_actions';
		$logs_table    = $wpdb->prefix . 'actionscheduler_logs';

		$offset            = 0;
		$limit             = absint( $this->get_post_parameter( 'limit', 100 ) );
		$actions_query     = sprintf(
								"
								SELECT	action_id
								FROM	%s
								WHERE	status = 'complete'
									AND	hook = 'sfn_followup_emails'
								LIMIT	%s
								",
								$actions_table,
								$limit
							);
		$action_ids        = $wpdb->get_col( $actions_query );

		if ( empty( $action_ids ) ) {
			$response = array(
				'success'        => false,
				'current_offset' => $offset,
				'current_limit'  => $limit,
			);

			wp_send_json( $response );
			exit;
		}

		$ids             = implode( ',', array_map( 'absint', $action_ids ) );
		$deleted_logs    = $wpdb->query( "DELETE FROM $logs_table WHERE action_id IN ($ids)" );
		$deleted_actions = $wpdb->query( "DELETE FROM $actions_table WHERE action_id IN ($ids)" );

		if ( false === $deleted_logs && false === $deleted_actions ) {
			$response = array(
				'success'        => false,
				'current_offset' => $offset,
				'current_limit'  => $limit,
			);

			wp_send_json( $response );
			exit;
		}

		$response = array(
			'success'         => true,
			'deleted_logs'    => $deleted_logs,
			'deleted_actions' => $deleted_actions,
			'limit'           => $limit,
		);
		wp_send_json( $response );
		exit;
	}

	public function get_post_parameter( $key, $default = false ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			return $default;
		}

		return $_POST[ $key ];
	}
}

$delete_old_scheduled_actions = Delete_Old_Scheduled_Actions::getInstance();
