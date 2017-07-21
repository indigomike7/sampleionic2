<?php

class Ynlog
{

    /**
     * @var string
     */
    static protected $_code = NULL;

    /**
     * write log to file when debug mode
     */
    static public function write($message, $level)
    {
        $filename = PHPFOX_DIR_FILE . '/log/mobile-phpfox-' . date('Y-m-d') . '.log';

        $logChunkSize = Phpfox::getParam('mfox.log_chunk_size', 100);

        if (file_exists($filename) && filesize($filename) > $logChunkSize * 1024 * 1024 ) {
            $newName = PHPFOX_DIR_FILE . '/log/mobile-phpfox-' . date('Y-m-d-H-i-s') . '.log';
            rename($filename, $newName);
            return;
        }

        if (!is_string($message))
        {
            $message = var_export($message, 1);
        }

        if ($fp = fopen($filename, 'a+'))
        {
            fwrite($fp, date('Y-m-d H:i:s') . ':' . self::getCode() . PHP_EOL . $message . PHP_EOL);
            fclose($fp);
        }
    }

    /**
     * @param Exception $exception
     */
    static function handleException($e)
    {
        exit('caused');
        $message = implode(PHP_EOL, array(
            'message: ' . $e->getMessage() . ' (' . $e->getCode() . ')',
            'file: ' . $e->getFile() . ' (' . $e->getLine() . ')',
            'trace: ',
            'trace:',
            self::getTrace($e->getTrace())
                ));
        self::write($message, $level = 'ERROR');
        return TRUE;
    }

    /**
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @param string $errcontext
     */
    static public function handleError($errno, $errstr = NULL, $errfile = NULL, $errline = NULL, $errcontext = NULL)
    {
        $level = 'NOTICE';
        $fatal = FALSE;

        switch ($errno)
        {
            case E_ERROR :
            case E_CORE_ERROR :
            case E_USER_ERROR :
            case E_COMPILE_ERROR :
            case E_RECOVERABLE_ERROR :
            case E_PARSE :
                $level = 'ERROR';
                $fatal = TRUE;
                break;
            case E_WARNING :
            case E_CORE_WARNING :
            case E_USER_WARNING :
            case E_COMPILE_WARNING :
                $level = 'WARNING';
                break;
        }
        $message = implode(PHP_EOL, array(
            'error: ' . $errstr . ' (' . $errno . ')',
            'file: ' . $errfile . ' (' . $errline . ')',
            'trace: ',
            self::getTrace()
                ));

        self::write($message, $level);

        if ($fatal)
        {
            self::handleFatalError();
        }
        return TRUE;
    }

    static function handleFatalError()
    {
        // Clean any previous output from buffer
        while (ob_get_level() > 0)
        {
            ob_end_clean();
        }
        echo "fatal error!";
        exit;
    }

    /**
     * @return string
     */
    static function getCode()
    {
        if (NULL == self::$_code)
        {
            self::$_code = self::getRandomString(8);
        }
        return self::$_code;
    }

    /**
     * @param   mixed   $var,...    variable to debug
     * @return  string
     */
    public static function vars()
    {
        if (func_num_args() === 0)
            return;

        // Get all passed variables
        $variables = func_get_args();

        $output = array();
        foreach ($variables as $var)
        {
            $output[] = self::_dump($var, 1024);
        }

        return '<pre class="debug">' . implode("\n", $output) . '</pre>';
    }

    /**
     * @param   mixed   $value              variable to dump
     * @param   integer $length             maximum length of strings
     * @param   integer $level_recursion    recursion limit
     * @return  string
     */
    public static function dump($value, $length = 128, $level_recursion = 10)
    {
        return self::_dump($value, $length, $level_recursion);
    }

    /**
     * Helper for Debug::dump(), handles recursion in arrays and objects.
     *
     * @param   mixed   $var    variable to dump
     * @param   integer $length maximum length of strings
     * @param   integer $limit  recursion limit
     * @param   integer $level  current recursion level (internal usage only!)
     * @return  string
     */
    protected static function _dump(&$var, $length = 128, $limit = 10, $level = 0)
    {
        if ($var === NULL)
        {
            return '<small>NULL</small>';
        }
        elseif (is_bool($var))
        {
            return '<small>bool</small> ' . ($var ? 'TRUE' : 'FALSE');
        }
        elseif (is_float($var))
        {
            return '<small>float</small> ' . $var;
        }
        elseif (is_resource($var))
        {
            if (($type = get_resource_type($var)) === 'stream' AND $meta = stream_get_meta_data($var))
            {
                $meta = stream_get_meta_data($var);

                if (isset($meta['uri']))
                {
                    $file = $meta['uri'];

                    if (function_exists('stream_is_local'))
                    {
                        // Only exists on PHP >= 5.2.4
                        if (stream_is_local($file))
                        {
                            $file = self::getPath($file);
                        }
                    }

                    return '<small>resource</small><span>(' . $type . ')</span> ' . htmlspecialchars($file, ENT_NOQUOTES, APP_CHARSET);
                }
            }
            else
            {
                return '<small>resource</small><span>(' . $type . ')</span>';
            }
        }
        elseif (is_string($var))
        {
            return '<small>string</small><span>(' . strlen($var) . ')</span> "' . $str . '"';
        }
        elseif (is_array($var))
        {
            $output = array();

            // Indentation for this variable
            $space = str_repeat($s = '    ', $level);

            static $marker;

            if ($marker === NULL)
            {
                // Make a unique marker
                $marker = uniqid("\x00");
            }

            if (empty($var))
            {
                // Do nothing
            }
            elseif (isset($var[$marker]))
            {
                $output[] = "(\n$space$s*RECURSION*\n$space)";
            }
            elseif ($level < $limit)
            {
                $output[] = "<span>(";

                $var[$marker] = TRUE;
                foreach ($var as $key => &$val)
                {
                    if ($key === $marker)
                        continue;
                    if (!is_int($key))
                    {
                        $key = '"' . htmlspecialchars($key, ENT_NOQUOTES, 'UTF8') . '"';
                    }

                    $output[] = "$space$s$key => " . self::_dump($val, $length, $limit, $level + 1);
                }
                unset($var[$marker]);

                $output[] = "$space)</span>";
            }
            else
            {
                // Depth too great
                $output[] = "(\n$space$s...\n$space)";
            }

            return '<small>array</small><span>(' . count($var) . ')</span> ' . implode("\n", $output);
        }
        elseif (is_object($var))
        {
            // Copy the object as an array
            $array = (array) $var;

            $output = array();

            // Indentation for this variable
            $space = str_repeat($s = '    ', $level);

            $hash = spl_object_hash($var);

            // Objects that are being dumped
            static $objects = array();

            if (empty($var))
            {
                // Do nothing
            }
            elseif (isset($objects[$hash]))
            {
                $output[] = "{\n$space$s*RECURSION*\n$space}";
            }
            elseif ($level < $limit)
            {
                $output[] = "<code>{";

                $objects[$hash] = TRUE;
                foreach ($array as $key => &$val)
                {
                    if ($key[0] === "\x00")
                    {
                        // Determine if the access is protected or protected
                        $access = '<small>' . (($key[1] === '*') ? 'protected' : 'private') . '</small>';

                        // Remove the access level from the variable name
                        $key = substr($key, strrpos($key, "\x00") + 1);
                    }
                    else
                    {
                        $access = '<small>public</small>';
                    }

                    $output[] = "$space$s$access $key => " . self::_dump($val, $length, $limit, $level + 1);
                }
                unset($objects[$hash]);

                $output[] = "$space}</code>";
            }
            else
            {
                // Depth too great
                $output[] = "{\n$space$s...\n$space}";
            }

            return '<small>object</small> <span>' . get_class($var) . '(' . count($array) . ')</span> ' . implode("\n", $output);
        }
        else
        {
            return '<small>' . gettype($var) . '</small> ' . htmlspecialchars(print_r($var, TRUE), ENT_NOQUOTES, 'UTF8');
        }
    }

    /**
     * @param   string  $file   path to debug
     * @return  string
     */
    public static function getPath($file)
    {
        return str_replace(PHPFOX_DIR, '', $file);

        return $file;
    }

    /**
     * @param   string  $file           file to open
     * @param   integer $line_number    line number to highlight
     * @param   integer $padding        number of padding lines
     * @return  string   source of file
     * @return  FALSE    file is unreadable
     */
    public static function source($file, $line_number, $padding = 5)
    {
        if (!$file OR !is_readable($file))
        {
            // Continuing will cause errors
            return FALSE;
        }

        // Open the file and set the line position
        $file = fopen($file, 'r');
        $line = 0;

        // Set the reading range
        $range = array(
            'start' => $line_number - $padding,
            'end' => $line_number + $padding
        );

        // Set the zero-padding amount for line numbers
        $format = '% ' . strlen($range['end']) . 'd';

        $source = '';
        while (($row = fgets($file)) !== FALSE)
        {
            // Increment the line number
            if (++$line > $range['end'])
                break;

            if ($line >= $range['start'])
            {
                // Make the row safe for output
                $row = htmlspecialchars($row, ENT_NOQUOTES, 'UTF8');

                // Trim whitespace and sanitize the row
                $row = '<span class="number">' . sprintf($format, $line) . '</span> ' . $row;

                if ($line === $line_number)
                {
                    // Apply highlighting to this row
                    $row = '<span class="line highlight">' . $row . '</span>';
                }
                else
                {
                    $row = '<span class="line">' . $row . '</span>';
                }

                // Add to the captured source
                $source .= $row;
            }
        }

        // Close the file
        fclose($file);

        return '<pre class="source"><code>' . $source . '</code></pre>';
    }

    /**
     *
     * @param   array   $trace
     * @return  string
     */
    public static function getTraceHtml(array $trace = NULL)
    {
        if ($trace === NULL)
        {
            // Start a new trace
            $trace = debug_backtrace();
        }

        // Non-standard function calls
        $statements = array(
            'include',
            'include_once',
            'require',
            'require_once'
        );

        $output = array();
        foreach ($trace as $step)
        {
            if (!isset($step['function']))
            {
                // Invalid trace step
                continue;
            }

            if (isset($step['file']) AND isset($step['line']))
            {
                // Include the source of this step
                $source = self::source($step['file'], $step['line']);
            }

            if (isset($step['file']))
            {
                $file = $step['file'];

                if (isset($step['line']))
                {
                    $line = $step['line'];
                }
            }

            // function()
            $function = $step['function'];

            if (in_array($step['function'], $statements))
            {
                if (empty($step['args']))
                {
                    // No arguments
                    $args = array();
                }
                else
                {
                    // Sanitize the file path
                    $args = array($step['args'][0]);
                }
            }
            elseif (isset($step['args']))
            {
                if (!function_exists($step['function']) OR strpos($step['function'], '{closure}') !== FALSE)
                {
                    // Introspection on closures or language constructs in a stack trace is impossible
                    $params = NULL;
                }
                else
                {
                    if (isset($step['class']))
                    {
                        if (method_exists($step['class'], $step['function']))
                        {
                            $reflection = new ReflectionMethod($step['class'], $step['function']);
                        }
                        else
                        {
                            $reflection = new ReflectionMethod($step['class'], '__call');
                        }
                    }
                    else
                    {
                        $reflection = new ReflectionFunction($step['function']);
                    }

                    // Get the function parameters
                    $params = $reflection->getParameters();
                }

                $args = array();

                foreach ($step['args'] as $i => $arg)
                {
                    if (isset($params[$i]))
                    {
                        // Assign the argument by the parameter name
                        $args[$params[$i]->name] = $arg;
                    }
                    else
                    {
                        // Assign the argument by number
                        $args[$i] = $arg;
                    }
                }
            }

            if (isset($step['class']))
            {
                // Class->method() or Class::method()
                $function = $step['class'] . $step['type'] . $step['function'];
            }

            $output[] = array(
                'function' => $function,
                'args' => isset($args) ? $args : NULL,
                'file' => isset($file) ? $file : NULL,
                'line' => isset($line) ? $line : NULL,
                'source' => isset($source) ? $source : NULL,
            );

            unset($function, $args, $file, $line, $source);
        }

        return var_export($output, 1);
    }

    /**
     * get debug trace back
     * @return string
     */
    static public function getTrace($backtrace = NULL)
    {
    	$index = 0;
		
        if (NULL == $backtrace)
        {
            $backtrace = array_slice(debug_backtrace(), 2);
        }
        $output = '';

        foreach ($backtrace as $index => $stack)
        {
            // Process args
            $args = array();
            if (!empty($stack['args']))
            {
                foreach ($stack['args'] as $argIndex => $argValue)
                {
                    if (is_object($argValue))
                    {
                        $args[$argIndex] = get_class($argValue);
                    }
                    else
                    if (is_array($argValue))
                    {
                        $args[$argIndex] = 'Array';
                        //substr(print_r($argValue, true), 0, 32);
                    }
                    else
                    if (is_string($argValue))
                    {
                        $args[$argIndex] = "'" . substr($argValue, 0, 100) . (strlen($argValue) > 100 ? '...' : '') . "'";
                    }
                    else
                    {
                        $args[$argIndex] = print_r($argValue, true);
                    }
                }
            }
            // Process message
            $output .= sprintf('#%1$d %2$s(%3$d): %4$s%5$s%6$s(%7$s)', $index, (!empty($stack['file']) ? self::getPath($stack['file']) : '(unknown)'), (!empty($stack['line']) ? $stack['line'] : '(unknown)'), (!empty($stack['class']) ? $stack['class'] : ''), (!empty($stack['type']) ? $stack['type'] : ''), $stack['function'], join(', ', $args)) . PHP_EOL;
        }

        // Throw main in there for the hell of it
        $output .= sprintf('#%1$d {main}', $index + 1);

        return $output;
    }

    /**
     * @param int $len OPTIONAL default = 8
     * @return string
     */
    static public function getRandomString($len = 8)
    {
        $seek = '0123456789AWETYUIOPASDFGHJKLZXCVBNMqwertyuioppasdfghjklzxcvbnm';
        $max = strlen($seek) - 1;
        $str = '';
        for ($i = 0; $i < $len; ++$i)
        {
            $str .= substr($seek, mt_rand(0, $max), 1);
        }
        return $str;
    }
    
    /**
     * Handle shutdown PHP script.
     * If there is a fatal error, this function will clear all buffer and return the error json.
     */
    static function handeShutdown()
    {
        if(function_exists('error_get_last'))
        {
            if (is_array($error = error_get_last()) && $error['type'] == 1)
            {
                $i = ob_get_level();
                while($i > 0)
                {
                    ob_clean();
                    $i--;
                }

                echo json_encode($error);

                die;
            }    
        }
    }

}
