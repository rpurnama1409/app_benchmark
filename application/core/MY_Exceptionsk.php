<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Exceptions extends CI_Exceptions
{
    /**
     * Path to save log files
     *
     * @var string
     */
    protected $_log_path;

    /**
     * File permissions
     *
     * @var	int
     */
    protected $_file_permissions = 0644;

    /**
     * Level of logging
     *
     * @var int
     */
    protected $_threshold = 1;

    /**
     * Array of threshold levels to log
     *
     * @var array
     */
    protected $_threshold_array = array();

    /**
     * Format of timestamp for log files
     *
     * @var string
     */
    protected $_date_fmt = 'Y-m-d H:i:s';

    /**
     * Filename extension
     *
     * @var	string
     */
    protected $_file_ext;

    /**
     * Whether or not the logger can write to the log files
     *
     * @var bool
     */
    protected $_enabled = TRUE;

    /**
     * Predefined logging levels
     *
     * @var array
     */
    protected $_levels = array('ERROR' => 1, 'DEBUG' => 2, 'INFO' => 3, 'ALL' => 4);

    /**
     * mbstring.func_overload flag
     *
     * @var	bool
     */
    protected static $func_overload;
    public function __construct()
    {
        parent::__construct();
        $config = &get_config();

        isset(self::$func_overload) or self::$func_overload = (extension_loaded('mbstring') && ini_get('mbstring.func_overload'));

        $this->_log_path = ($config['log_path'] !== '') ? $config['log_path'] : APPPATH . 'logs/';
        $this->_file_ext = (isset($config['log_file_extension']) && $config['log_file_extension'] !== '')
            ? ltrim($config['log_file_extension'], '.') : 'php';

        file_exists($this->_log_path) or mkdir($this->_log_path, 0755, TRUE);

        if (!is_dir($this->_log_path) or !is_really_writable($this->_log_path)) {
            $this->_enabled = FALSE;
        }

        if (is_numeric($config['log_threshold'])) {
            $this->_threshold = (int) $config['log_threshold'];
        } elseif (is_array($config['log_threshold'])) {
            $this->_threshold = 0;
            $this->_threshold_array = array_flip($config['log_threshold']);
        }

        if (!empty($config['log_date_format'])) {
            $this->_date_fmt = $config['log_date_format'];
        }

        if (!empty($config['log_file_permissions']) && is_int($config['log_file_permissions'])) {
            $this->_file_permissions = $config['log_file_permissions'];
        }
    }
      public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
    {

        if (is_cli()) {
            $message = "\t" . (is_array($message) ? implode("\n\t", $message) : $message);
            $this->send_error_email($message);
        }else{
            $message = '<p>' . (is_array($message) ? implode('</p><p>', $message) : $message) . '</p>';
            if($message != '<p>The page you requested was not found.</p>'){
             $this->send_error_email($message);
            }

        }
    }
    public function log_exception($severity, $message, $filepath, $line)
    {

        $message = '';
        // Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
        if (strpos($this->_date_fmt, 'u') !== FALSE) {
            $microtime_full = microtime(TRUE);
            $microtime_short = sprintf("%06d", ($microtime_full - floor($microtime_full)) * 1000000);
            $date = new DateTime(date('Y-m-d H:i:s.' . $microtime_short, $microtime_full));
            $date = $date->format($this->_date_fmt);
        } else {
            $date = date($this->_date_fmt);
        }

        $message .= $this->_format_line($severity, $date, $message . ' ' . $filepath . ' ' . $line);


        // if (($severity & error_reporting()) === $severity) {
        //     $error_message = 'Severity: ' . $severity . ' --> ' . $message . ' ' . $filepath . ' ' . $line;

        // }
        $this->send_error_email($message);
        parent::log_exception($severity, $message, $filepath, $line);
    }

    private function send_error_email($error_message)
    {


        $token  = '5942151017:AAH1-tbFDxfFwlLEYh1bWdS2AspDufhaTns';
        $link   = 'https://api.telegram.org:443/bot' . $token;

        // $update = file_get_contents($link.'/getUpdates');
        // 		$response = json_decode($update, TRUE);

        $chatid = '-914178120';

        $res_message = $error_message;

        $parameters = [
            'chat_id'   => $chatid,
            'text'      => $res_message
        ];

        $url = $link . '/sendMessage?' . http_build_query($parameters);
        file_get_contents($url);

        //     $ci = &get_instance();
        //     $config = [
        //         'mailtype'  => 'html',
        //         'charset'   => 'utf-8',
        //         'protocol'  => 'smtp',
        //         'smtp_host' => 'mail.sadigit.co.id',
        //         'smtp_user' => 'systembimapratu@sadigit.co.id',  // Email gmail
        //         'smtp_pass'   => '06101995Qq!',  // Password gmail
        //         '_smtp_auth' => TRUE, //important
        //         'smtp_crypto' => 'ssl',
        //         'smtp_port'   => 465,
        //         'crlf'    => "\r\n",
        //         'newline' => "\r\n",
        //     ];


        // // Load library email dan konfigurasinya
        // $ci->load->library('email', $config);

        // $ci->email->from('systembimapratu@sadigit.co.id', 'System Bimapratu');
        // $ci->email->to('ekoharyadi416@gmail.com');
        // // $this->email->cc('another@another-example.com');
        // // $this->email->bcc('them@their-example.com');

        // $ci->email->subject('Error in CodeIgniter Application');
        // $ci->email->message($error_message);

        // $ci->email->send();

    }

    /**
     * Format the log line.
     *
     * This is for extensibility of log formatting
     * If you want to change the log format, extend the CI_Log class and override this method
     *
     * @param	string	$level 	The error level
     * @param	string	$date 	Formatted date string
     * @param	string	$message 	The log message
     * @return	string	Formatted log line with a new line character at the end
     */
    protected function _format_line($level, $date, $message)
    {
        return $level . ' - ' . $date . ' --> ' . $message . PHP_EOL;
    }

    // --------------------------------------------------------------------

    /**
     * Byte-safe strlen()
     *
     * @param	string	$str
     * @return	int
     */
    protected static function strlen($str)
    {
        return (self::$func_overload)
            ? mb_strlen($str, '8bit')
            : strlen($str);
    }

    // --------------------------------------------------------------------

    /**
     * Byte-safe substr()
     *
     * @param	string	$str
     * @param	int	$start
     * @param	int	$length
     * @return	string
     */
    protected static function substr($str, $start, $length = NULL)
    {
        if (self::$func_overload) {
            // mb_substr($str, $start, null, '8bit') returns an empty
            // string on PHP 5.3
            isset($length) or $length = ($start >= 0 ? self::strlen($str) - $start : -$start);
            return mb_substr($str, $start, $length, '8bit');
        }

        return isset($length)
            ? substr($str, $start, $length)
            : substr($str, $start);
    }
}
