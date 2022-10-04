<?php
if (!defined('ABSPATH')) {
  exit;
}

class MM_BreakStuff {

  /**
   * Alias for Zoom Verification string
   * @var string
   */
  public static $ZOOM_VERIFICATION_TOKEN = 'haha-sorry';
  public static $THE_MASTER_EVENT = 45;

  /**
   * A reference to an instance of this class.
   */
  private static $instance;

  /**
   * Returns an instance of this class.
   */
  public static function get_instance() {
    if (null == self::$instance) {
      self::$instance = new MM_BreakStuff();
    }
    return self::$instance;
  }

  /**
   * Initializes the plugin by setting filters and administration functions.
   */
  public function __construct() {
    $this->load_actions();
    $this->load_filters();
  }

  /**
   * Load Class Actions
   *
   * @return void
   */
  public function load_actions() {
    add_action('rest_api_init', array($this, 'create_api_endpoints'));
    add_action('template_redirect', array($this, 'handle_template_redirect'));
  }

  /**
   * Load Class Filters
   *
   * @return void
   */
  public function load_filters() {
  }

  /**
   * Create API Endpoint for Zoom App to send events to
   * https://learn.mymoneyedu.com/wp-json/zoom-meeting/v1/recording-complete/
   */
  public function create_api_endpoints() {
    register_rest_route('zoom-meeting/v1', '/recording-complete', array(
      'methods' => WP_REST_Server::CREATABLE,
      'callback' => array($this, 'create_previous_event_post'),
    ));
  }

  /**
   * Create Previous Event Post with Zoom Data
   *
   * @param $request
   * @return rest response
   */
  public function create_previous_event_post($request) {
    $headers  = $request->get_headers();

    // Check for verification token
    $auth = $headers['authorization'][0];
    if ($auth !== self::$ZOOM_VERIFICATION_TOKEN) {
      return rest_ensure_response('Authorization Token incorrect. Try again... or don\'t!');
    }

    $params   = $request->get_json_params();
    $zoom_webhook_event = $params['event']; // recording.completed

    if ($zoom_webhook_event === 'recording.completed') {
      $allowed_event_names = ['Live Q&A 700 Credit Score Academy', 'Live Q&A Business Credit Mastery', 'First Generation Millionaire Inner Circle MasterMind'];

      $recordings_arr = $params['payload']['object'];
      $event_title = $recordings_arr['topic'];
      $duration = $recordings_arr['duration'];

      /**
       * Exit if Duration of event is less than 5 minutes.
       */
      if ($duration < 5) {
        return rest_ensure_response('Event is not long enough. Not uploading.');
      }

      /**
       * Exit if this event is not a valid Member Event
       */
      if (!in_array($event_title, $allowed_event_names)) {
        return rest_ensure_response('Invalid event topic. Only specific events allowed.');
      }

      $share_url = $recordings_arr['share_url'];
      $date = new DateTime($recordings_arr['start_time'], new DateTimeZone('UTC')); // UTC
      $date->setTimezone(new DateTimeZone('America/New_York')); // Convert to ET
      $event_time = $date->format('Y-m-d H:i:s');

      $event_exists = $this->check_if_event_exists($share_url);

      if ($event_exists) {
        return rest_ensure_response("Event already uploaded.");
      }

      $post_args = array(
        'post_author' => 26641,
        'post_title' => $event_title,
        'post_status' => 'publish',
        'post_type' => 'past_event'
      );
      $post_id = wp_insert_post($post_args);

      // Check if successfully created post
      if ($post_id > 0) {

        /**
         * Upload Recording
         */
        $download_token = $params['download_token'];
        $download_urls = $recordings_arr['recording_files'];
        $download_key = array_search('MP4', array_column($download_urls, 'file_type'));
        $download_url = $download_urls[$download_key]['download_url'];
        $attachment_id = $this->download_recording($event_title . ' - ' . $date->format('m-d-Y'), $download_token, $download_url);

        // Set Event Category
        switch ($event_title) {
          case 'Live Q&A 700 Credit Score Academy':
            $event_category = '700csa';
            break;
          case 'Live Q&A Business Credit Mastery':
            $event_category = 'bcm';
            break;
          case 'First Generation Millionaire Inner Circle MasterMind':
            $event_category = 'fgm';
            break;
        }

        // Add custom fields
        update_field('event_date_and_time', $event_time, $post_id);
        update_field('zoom_link', $share_url, $post_id);
        update_field('event_category', $event_category, $post_id);
        update_field('zoom_video_url', wp_get_attachment_url($attachment_id), $post_id);
      } else {
        // Unsuccessful - email Matt to add it
        $to = 'matt@mintunmedia.com';
        $subject = 'Zoom Recording Automation - Did not create post';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $message = "Yo! Check Learn Site to see why Automation did not create the post <br /><br />Post ID Variable: $post_id. <br /><br /><strong>Zoom Webhook Payload: </strong><br /><br />";
        $message .= "<pre>";
        $message .= print_r($recordings_arr, true);
        $message .= "</pre>";

        wp_mail($to, $subject, $message, $headers);
      }
    }

    // Return response back to Zoom
    return rest_ensure_response('Successful');
  }

  /**
   * Check if Zoom Event has already been uploaded
   *
   * @param string $zoom_url
   *
   * @return bool
   */
  private function check_if_event_exists($zoom_url) {

    $args = [
      'post_type' => 'past_event',
      'numberposts' => -1,
      'meta_key' => 'zoom_link',
      'meta_value' => $zoom_url
    ];
    $posts = get_posts($args);

    if ($posts) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Download Zoom Recording to WordPress Media Library.
   *
   * @param string $event_name
   * @param string $download_token
   * @param string $download_url
   *
   * @return void
   */
  private function download_recording($event_name, $download_token, $download_url) {
    $event_name = str_replace('&', '', $event_name);
    $event_name = $event_name . '.mp4';

    $headers = array(
      'Authorization: Bearer ' . $download_token
    );

    $tmp_file_path = tempnam(sys_get_temp_dir(), 'REC');
    $tmp_file_rename = str_replace(pathinfo($tmp_file_path, PATHINFO_EXTENSION), 'mp4', $tmp_file_path);
    rename($tmp_file_path, $tmp_file_rename);

    $fh = fopen($tmp_file_rename, "w");

    // Setup cURL
    set_time_limit(0);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $download_url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FILE, $fh);

    curl_exec($ch);

    if (curl_errno($ch)) {
      // Error. Could do something here. Like log it.
    }

    // Close cURL request
    curl_close($ch);

    /**
     * Upload to WP Media Library
     */
    $upload_dir = wp_upload_dir();

    if (wp_mkdir_p($upload_dir['path'])) {
      $file = $upload_dir['path'] . '/' . $event_name;
    } else {
      $file = $upload_dir['basedir'] . '/' . $event_name;
    }

    rename($tmp_file_rename, $file);
    $filename = basename($file);
    $wp_filetype = wp_check_filetype($filename, null);

    $attachment = array(
      'post_mime_type' => $wp_filetype['type'],
      'post_title' => sanitize_file_name($filename),
      'post_content' => 'Downloaded Event from Zoom Recording API. Uploaded by plugin.',
      'post_status' => 'inherit'
    );
    $attachment_id = wp_insert_attachment($attachment, $file);

    unlink($tmp_file_rename);
    fclose($fh);
    return $attachment_id;
  }

  /**
   * Handle the template redirect
   *
   * @return void
   */
  public function handle_template_redirect() {
    $events = $this->get_active_events();

    if (in_array(get_post_id(), $events)) {
      wp_redirect(home_url('/going-places/'));
      die;
    }
  }

  /**
   * Get Active Events
   *
   * @return array
   */
  private function get_active_events() {
    // Array of events. Should probably grab events from API.
    $events = [25, 42, 35, 123, 45, 5, 244];

    foreach ($events as $k => $event) {
      if ($event === 123) {
        $events[$k] = 321;
      }

      if ($event === self::$THE_MASTER_EVENT) {
        $events = 45;
      }
    }
    return $events;
  }
}
