 <?php
  /**
   * This file contains reusable helper functions for the application.
   */

  /**
   * Redirects the user to a specified page.
   *
   * @param string $url The URL to redirect to.
   * @return void
   */
  function redirect($url) {
    header("Location: " . $url);
    exit();
  }

  /**
   * Escapes HTML output to prevent Cross-Site Scripting (XSS) attacks.
   *
   * @param string $string The string to sanitize.
   * @return string The sanitized string.
   */
  function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
  }

  /**
   * Displays session messages (like success or error notifications) and then clears them.
   *
   * @return string The HTML for the message alert, or an empty string if no message exists.
   */
  function display_message() {
    $output = '';
    if (isset($_SESSION['message'])) {
        $output .= '<div class="alert alert-success">' . escape($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }
    if (isset($_SESSION['error'])) {
        $output .= '<div class="alert alert-danger">' . escape($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    return $output;
  }

  /**
   * Formats a date string into a more readable format.
   *
   * @param string $date The date string (e.g., from the database).
   * @param string $format The desired output format (defaults to 'M d, Y').
   * @return string The formatted date.
   */
  function format_date($date, $format = 'M d, Y') {
    if (empty($date)) {
        return 'N/A';
    }
    return date($format, strtotime($date));
  }

?>

