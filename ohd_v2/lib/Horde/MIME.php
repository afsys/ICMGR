<?php
	
if (!class_exists('HordeMIME')) {

/* We need to (unfortunately) hard code these constants because they reside in
 * the imap module, which is not required for Horde.
 * These constants are found in the UW-imap c-client distribution:
 *   ftp://ftp.cac.washington.edu/imap/
 * The constants appear in the file include/mail.h */
require_once 'NLS.php'; 
require_once 'Util.php';
require_once 'String.php';

if (!Util::extensionExists('imap')) {
    /* Primary body types */
    define('TYPETEXT', 0);
    define('TYPEMULTIPART', 1);
    define('TYPEMESSAGE', 2);
    define('TYPEAPPLICATION', 3);
    define('TYPEAUDIO', 4);
    define('TYPEIMAGE', 5);
    define('TYPEVIDEO', 6);
    define('TYPEOTHER', 8);

    /* Body encodings */
    define('ENC7BIT', 0);
    define('ENC8BIT', 1);
    define('ENCBINARY', 2);
    define('ENCBASE64', 3);
    define('ENCQUOTEDPRINTABLE', 4);
    define('ENCOTHER', 5);
}

/**
 * Older versions of PHP's imap extension don't define TYPEMODEL.
 */
if (!defined('TYPEMODEL')) {
    define('TYPEMODEL', 7);
}

/**
 * Return a code for type()/encoding().
 */
define('MIME_CODE', 1);

/**
 * Return a string for type()/encoding().
 */
define('MIME_STRING', 2);


/**
 * The MIME:: class provides methods for dealing with MIME standards.
 *
 * $Horde: framework/MIME/MIME.php,v 1.139.4.20 2006/03/24 04:29:52 chuck Exp $
 *
 * Copyright 1999-2006 Chuck Hagenbuch <chuck@horde.org>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @since   Horde 1.3
 * @package Horde_MIME
 */
class HordeMIME {

    /**
     * A listing of the allowed MIME types.
     *
     * @var array
     */
    var $mime_types = array(
        TYPETEXT => 'text',
        TYPEMULTIPART => 'multipart',
        TYPEMESSAGE => 'message',
        TYPEAPPLICATION => 'application',
        TYPEAUDIO => 'audio',
        TYPEIMAGE => 'image',
        TYPEVIDEO => 'video',
        TYPEMODEL => 'model',
        TYPEOTHER => 'other'
    );

    /**
     * A listing of the allowed MIME encodings.
     *
     * @var array
     */
    var $mime_encodings = array(
        ENC7BIT => '7bit',
        ENC8BIT => '8bit',
        ENCBINARY => 'binary',
        ENCBASE64 => 'base64',
        ENCQUOTEDPRINTABLE => 'quoted-printable',
        ENCOTHER => 'unknown'
    );

    /**
     * Filter for RFC822.
     *
     * @var string
     */
    var $rfc822_filter = "()<>@,;:\\\"[]\1\2\3\4\5\6\7\10\11\12\13\14\15\16\17\20\21\22\23\24\25\26\27\30\31\32\33\34\35\36\37\177";

    /**
     * Determines if a string contains 8-bit characters.
     *
     * @param string $string  The string to check.
     *
     * @return boolean  True if it does, false if it doesn't.
     */
    function is8bit($string)
    {
        return (is_string($string) && preg_match('/[\x80-\xff]/', $string));
    }

    /**
     * Encodes a string containing non-ASCII characters according to RFC 2047.
     *
     * @param string $text     The text to encode.
     * @param string $charset  The character set of the text.
     *
     * @return string  The text, encoded only if it contains non-ASCII
     *                 characters.
     */
    function encode($text, $charset = null)
    {
        /* Return if nothing needs to be encoded. */
        if (!HordeMIME::is8bit($text)) {
            return $text;
        }

        if (is_null($charset)) {
            $charset = NLS::getCharset();
        }

        $charset = String::lower($charset);
        $line = '';

        /* Get the list of elements in the string. */
        $size = preg_match_all('/([^\s]+)([\s]*)/', $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $key => $val) {
            if (HordeMIME::is8bit($val[1])) {
                if ((($key + 1) < $size) &&
                    HordeMIME::is8bit($matches[$key + 1][1])) {
                    $line .= HordeMIME::_encode($val[1] . $val[2], $charset) . ' ';
                } else {
                    $line .= HordeMIME::_encode($val[1], $charset) . $val[2];
                }
            } else {
                $line .= $val[1] . $val[2];
            }
        }

        return rtrim($line);
    }

    /**
     * Internal recursive function to RFC 2047 encode a string.
     *
     * @access private
     *
     * @param string $text     The text to encode.
     * @param string $charset  The character set of the text.
     *
     * @return string  The text, encoded only if it contains non-ASCII
     *                 characters.
     */
    function _encode($text, $charset)
    {
        $char_len = strlen($charset);
        $txt_len = strlen($text) * 2;

        /* RFC 2047 [2] states that no encoded word can be more than 75
           characters long. If longer, you must split the word. */
        if (($txt_len + $char_len + 7) > 75) {
            $pos = intval((68 - $char_len) / 2);
            return HordeMIME::_encode(substr($text, 0, $pos), $charset) . ' ' . HordeMIME::_encode(substr($text, $pos), $charset);
        } else {
            return '=?' . $charset . '?b?' . trim(base64_encode($text)) . '?=';
        }
    }

    /**
     * Encodes a line via quoted-printable encoding.
     * Wraps lines at 76 characters.
     *
     * @param string $text  The text to encode.
     * @param string $eol   The EOL sequence to use.
     *
     * @return string  The quoted-printable encoded string.
     */
    function quotedPrintableEncode($text, $eol)
    {
        $output = '';

        foreach (preg_split("/\r?\n/", $text) as $line) {
            /* We need to go character by character through the line. */
            $length = strlen($line);
            $current_line = '';
            $current_length = 0;

            for ($i = 0; $i < $length; $i++) {
                $char = substr($line, $i, 1);
                $ascii = ord($char);

                /* Spaces or tabs at the end of the line are NOT allowed.
                 * Also, Characters in ASCII below 32 or above 126 AND 61
                 * must be encoded. */
                if (((($ascii === 9) || ($ascii === 32)) && ($i == ($length - 1))) ||
                    (($ascii < 32) || ($ascii > 126) || ($ascii === 61))) {
                    $char = '=' . String::upper(sprintf('%02s', dechex($ascii)));
                }

                /* Lines must be 76 characters or less. */
                $char_length = strlen($char);
                $current_length += $char_length;
                if ($current_length > 76) {
                    $output .= $current_line . '=' . $eol;
                    $current_line = '';
                    $current_length = $char_length;
                }
                $current_line .= $char;
            }
            $output .= $current_line . $eol;
        }

        return $output;
    }

    /**
     * Encodes a string containing email addresses according to RFC 2047.
     *
     * This differs from HordeMIME::encode() because it keeps email addresses legal,
     * only encoding the personal information.
     *
     * @param string $addresses  The email addresses to encode.
     * @param string $charset    The character set of the text.
     * @param string $defserver  The default domain to append to mailboxes.
     *
     * @return string  The text, encoded only if it contains non-ascii
     *                 characters.
     */
    function encodeAddress($addresses, $charset = null, $defserver = null)
    {
        if (is_array($addresses)) {
            $addr_arr = $addresses;
        } else {
            /* parseAddressList() does not process the null entry
             * 'undisclosed-recipients:;' correctly. */
            if (preg_match('/undisclosed-recipients:\s*;/i', trim($addresses))) {
                return $addresses;
            }

            require_once 'Mail/RFC822.php';
            $parser = &new Mail_RFC822();
            $addr_arr = $parser->parseAddressList($addresses, $defserver, true, false);
            if (is_a($addr_arr, 'PEAR_Error')) {
                return $addr_arr;
            }
        }

        $text = '';
        if (is_array($addr_arr)) {
            foreach ($addr_arr as $addr) {
                // Check for groups.
                if (!empty($addr->groupname)) {
                    $text .= HordeMIME::encode($addr->groupname, $charset) . ': ' . HordeMIME::encodeAddress($addr->addresses) . ';';
                    continue;
                }

                if (empty($addr->personal)) {
                    $personal = '';
                } else {
                    if ((substr($addr->personal, 0, 1) == '"') &&
                        (substr($addr->personal, -1) == '"')) {
                        $addr->personal = substr($addr->personal, 1, -1);
                    }
                    $personal = HordeMIME::encode($addr->personal, $charset);
                }
                if (strlen($text) != 0) {
                    $text .= ', ';
                }
                $text .= HordeMIME::trimEmailAddress(HordeMIME::rfc822WriteAddress($addr->mailbox, $addr->host, $personal));
            }
        }

        return $text;
    }

    /**
     * Decodes an RFC 2047-encoded string.
     *
     * @param string $string      The text to decode.
     * @param string $to_charset  The charset that the text should be decoded
     *                            to.
     *
     * @return string  The decoded text.
     */
    function decode($string, $to_charset = null)
    {
        if (($pos = strpos($string, '=?')) === false) {
            return $string;
        }

        /* Take out any spaces between multiple encoded words. */
        $string = preg_replace('|\?=\s=\?|', '?==?', $string);

        /* Save any preceding text. */
        $preceding = substr($string, 0, $pos);

        $search = substr($string, $pos + 2);
        $d1 = strpos($search, '?');
        if ($d1 === false) {
            return $string;
        }

        $charset = substr($string, $pos + 2, $d1);
        $search = substr($search, $d1 + 1);

        $d2 = strpos($search, '?');
        if ($d2 === false) {
            return $string;
        }

        $encoding = substr($search, 0, $d2);
        $search = substr($search, $d2 + 1);

        $end = strpos($search, '?=');
        if ($end === false) {
            $end = strlen($search);
        }

        $encoded_text = substr($search, 0, $end);
        $rest = substr($string, (strlen($preceding . $charset . $encoding . $encoded_text) + 6));

        if (!isset($to_charset)) {
            $to_charset = NLS::getCharset();
        }

        switch ($encoding) {
        case 'Q':
        case 'q':
            $encoded_text = str_replace('_', ' ', $encoded_text);
            $decoded = preg_replace('/=([0-9a-f]{2})/ie', 'chr(0x\1)', $encoded_text);
            $decoded = String::convertCharset($decoded, $charset, $to_charset);
            break;

        case 'B':
        case 'b':
            $decoded = base64_decode($encoded_text);
            $decoded = String::convertCharset($decoded, $charset, $to_charset);
            break;

        default:
            $decoded = '=?' . $charset . '?' . $encoding . '?' . $encoded_text . '?=';
            break;
        }

        return $preceding . $decoded . HordeMIME::decode($rest, $to_charset);
    }

    /**
     * Decodes an RFC 2231-encoded string.
     *
     * @param string $string   The entire string to decode, including the
     *                         parameter name.
     * @param string $charset  The charset that the text should be decoded to.
     *
     * @return array  The decoded text, or the original string if it was not
     *                encoded.
     */
    function decodeRFC2231($string, $to_charset = null)
    {
        if (($pos = strpos($string, '*')) === false) {
            return false;
        }

        if (!isset($to_charset)) {
            $to_charset = NLS::getCharset();
        }

        $attribute = substr($string, 0, $pos);
        $charset = $lang = null;
        $output = '';

        /* Get the character set and language used in the encoding, if
         * any. */
        if (preg_match("/^[^=]+\*\=([^']*)'([^']*)'/", $string, $matches)) {
            $charset = $matches[1];
            $lang = $matches[2];
            $string = str_replace($charset . "'" . $lang . "'", '', $string);
        }

        $lines = preg_split('/' . preg_quote($attribute) . '(?:\*\d)*/', $string);
        foreach ($lines as $line) {
            $pos = strpos($line, '*=');
            if ($pos === 0) {
                $line = substr($line, 2);
                $line = str_replace('_', '%20', $line);
                $line = str_replace('=', '%', $line);
                $output .= urldecode($line);
            } else {
                $line = substr($line, 1);
                $output .= $line;
            }
        }

        /* RFC 2231 uses quoted printable encoding. */
        if (!is_null($charset)) {
            $output = String::convertCharset($output, $charset, $to_charset);
        }

        return array(
            'attribute' => $attribute,
            'value' => $output
        );
    }

    /**
     * If an email address has no personal information, get rid of any angle
     * brackets (<>) around it.
     *
     * @param string $address  The address to trim.
     *
     * @return string  The trimmed address.
     */
    function trimEmailAddress($address)
    {
        $address = trim($address);

        if ((substr($address, 0, 1) == '<') && (substr($address, -1) == '>')) {
            $address = substr($address, 1, -1);
        }

        return $address;
    }

    /**
     * Builds an RFC 822 compliant email address.
     *
     * @param string $mailbox   Mailbox name.
     * @param string $host      Domain name of mailbox's host.
     * @param string $personal  Personal name phrase.
     *
     * @return string  The correctly escaped and quoted
     *                 "$personal <$mailbox@$host>" string.
     */
    function rfc822WriteAddress($mailbox, $host = null, $personal = '')
    {
        $address = '';

        if (!empty($personal)) {
            $vars = get_class_vars('HordeMIME');
            $address .= HordeMIME::_rfc822Encode($personal, $vars['rfc822_filter'] . '.');
            $address .= ' <';
        }

        if (!is_null($host)) {
            $address .= HordeMIME::_rfc822Encode($mailbox);
            if (substr($host, 0, 1) != '@') {
                $address .= '@' . $host;
            }
        }

        if (!empty($personal)) {
            $address .= '>';
        }

        return $address;
    }

    /**
     * Explodes a RFC 822 string, ignoring a delimiter if preceded by a "\"
     * character, or if delimiter is inside single or double quotes.
     *
     * @param string $str        The RFC 822 string.
     * @param string $delimiter  The delimter.
     *
     * @return array  The exploded string in an array.
     */
    function rfc822Explode($str, $delimiter)
    {
        $arr = array();
        $match = 0;
        $quotes = array('"', "'");
        $in_quote = null;
        $in_group = false;
        $prev = null;

        if (empty($str)) {
            return array($str);
        }

        if (in_array($str{0}, $quotes)) {
            $in_quote = $str{0};
        } elseif ($str{0} == ':') {
            $in_group = true;
        } elseif ($str{0} == $delimiter) {
            $arr[] = '';
            $match = 1;
        }

        for ($i = 1; $i < strlen($str); $i++) {
            $char = $str{$i};
            if (in_array($char, $quotes)) {
                if ($prev !== '\\') {
                    if ($in_quote === $char) {
                        $in_quote = null;
                    } elseif (is_null($in_quote)) {
                        $in_quote = $char;
                    }
                }
            } elseif ($in_group) {
                if ($char == ';') {
                    $arr[] = substr($str, $match, $i - $match + 1);
                    $match = $i + 1;
                    $in_group = false;
                }
            } elseif ($char == ':') {
                $in_group = true;
            } elseif ($char == $delimiter &&
                      $prev !== '\\' &&
                      is_null($in_quote)) {
                $arr[] = substr($str, $match, $i - $match);
                $match = $i + 1;
            }
            $prev = $char;
        }

        if ($match != $i) {
            /* The string ended without a $delimiter. */
            $arr[] = substr($str, $match, $i - $match);
        }

        return $arr;
    }

    /**
     * Takes an address object, as returned by imap_header() for example, and
     * formats it as a string.
     *
     * Object format for the address "John Doe <john_doe@example.com>" is:
     * <pre>
     *   $object->personal = Personal name ("John Doe")
     *   $object->mailbox  = The user's mailbox ("john_doe")
     *   $object->host     = The host the mailbox is on ("example.com")
     * </pre>
     *
     * @param stdClass $ob   The address object to be turned into a string.
     * @param mixed $filter  A user@example.com style bare address to ignore.
     *                       Either single string or an array of strings.  If
     *                       the address matches $filter, an empty string will
     *                       be returned.
     *
     * @return string  The formatted address (Example: John Doe
     *                 <john_doe@example.com>).
     */
    function addrObject2String($ob, $filter = '')
    {
        /* If the personal name is set, decode it. */
        $ob->personal = isset($ob->personal) ? HordeMIME::decode($ob->personal) : '';

        /* If both the mailbox and the host are empty, return an empty
           string.  If we just let this case fall through, the call to
           HordeMIME::rfc822WriteAddress() will end up return just a '@', which
           is undesirable. */
        if (empty($ob->mailbox) && empty($ob->host)) {
            return '';
        }

        /* Make sure these two variables have some sort of value. */
        if (!isset($ob->mailbox)) {
            $ob->mailbox = '';
        } elseif ($ob->mailbox == 'undisclosed-recipients') {
            return '';
        }
        if (!isset($ob->host)) {
            $ob->host = '';
        }

        /* Filter out unwanted addresses based on the $filter string. */
        if ($filter) {
            if (!is_array($filter)) {
                $filter = array($filter);
            }
            foreach ($filter as $f) {
                if (strcasecmp($f, $ob->mailbox . '@' . $ob->host) == 0) {
                    return '';
                }
            }
        }

        /* Return the trimmed, formatted email address. */
        return HordeMIME::trimEmailAddress(HordeMIME::rfc822WriteAddress($ob->mailbox, $ob->host, $ob->personal));
    }

    /**
     * Takes an array of address objects, as returned by imap_headerinfo(),
     * for example, and passes each of them through HordeMIME::addrObject2String().
     *
     * @param array $addresses  The array of address objects.
     * @param mixed $filter     A user@example.com style bare address to
     *                          ignore.  If any address matches $filter, it
     *                          will not be included in the final string.
     *
     * @return string  All of the addresses in a comma-delimited string.
     *                 Returns the empty string on error/no addresses found.
     */
    function addrArray2String($addresses, $filter = '')
    {
        $addrList = array();

        if (!is_array($addresses)) {
            return '';
        }

        foreach ($addresses as $addr) {
            $val = HordeMIME::addrObject2String($addr, $filter);
            if (!empty($val)) {
                $bareAddr = String::lower(HordeMIME::bareAddress($val));
                if (!isset($addrList[$bareAddr])) {
                    $addrList[$bareAddr] = $val;
                }
            }
        }

        if (empty($addrList)) {
            return '';
        } else {
            return implode(', ', $addrList);
        }
    }

    /**
     * Returns the bare address.
     *
     * @param string $address    The address string.
     * @param string $defserver  The default domain to append to mailboxes.
     * @param boolean $multiple  Should we return multiple results?
     *
     * @return mixed  If $multiple is false, returns the mailbox@host e-mail
     *                address.  If $multiple is true, returns an array of
     *                these addresses.
     */
    function bareAddress($address, $defserver = null, $multiple = false)
    {
        $addressList = array();

        /* Use built-in IMAP function only if available and if not parsing
         * distribution lists because it doesn't parse distribution lists
         * properly. */
        if (Util::extensionExists('imap') && strpos($address, ':') === false) {
            $from = imap_rfc822_parse_adrlist($address, $defserver);
        } else {
            require_once 'Mail/RFC822.php';
            $parser = new Mail_RFC822();
            $from = $parser->parseAddressList($address, $defserver, false, false);
            if (is_a($from, 'PEAR_Error')) {
                return $multiple ? array() : '';
            }
        }

        foreach ($from as $entry) {
            if (isset($entry->mailbox) &&
                $entry->mailbox != 'undisclosed-recipients' &&
                $entry->mailbox != 'UNEXPECTED_DATA_AFTER_ADDRESS') {
                if (isset($entry->host)) {
                    $addressList[] = $entry->mailbox . '@' . $entry->host;
                } else {
                    $addressList[] = $entry->mailbox;
                }
            }
        }

        return $multiple ? $addressList : array_pop($addressList);
    }

    /**
     * Quotes and escapes the given string if necessary.
     *
     * @access private
     *
     * @param string $str     The string to be quoted and escaped.
     * @param string $filter  A list of characters that make it necessary to
     *                        quote the string if they occur.
     *
     * @return string  The correctly quoted and escaped string.
     */
    function _rfc822Encode($str, $filter = '')
    {
        if (empty($filter)) {
            $vars = get_class_vars('HordeMIME');
            $filter = $vars['rfc822_filter'] . ' ';
        }

        // Strip double quotes if they are around the string already.
        if (substr($str, 0, 1) == '"' && substr($str, -1) == '"') {
            $str = substr($str, 1, -1);
        }

        if (strcspn($str, $filter) != strlen($str)) {
            $str = str_replace('\"', '"', $str);
            return '"' . str_replace('"', '\\"', str_replace('\\', '\\\\', $str)) . '"';
        } else {
            return $str;
        }
    }

    /**
     * Returns the HordeMIME type for the given input.
     *
     * @param mixed $input     Either the HordeMIME code or type string.
     * @param integer $format  If MIME_CODE, return code.
     *                         If MIME_STRING, returns lowercase string.
     *
     * @return mixed  See above.
     */
    function type($input, $format = null)
    {
        return HordeMIME::_getCode($input, $format, 'mime_types');
    }

    /**
     * Returns the HordeMIME encoding for the given input.
     *
     * @param mixed $input     Either the MIME code or encoding string.
     * @param integer $format  If MIME_CODE, return code.
     *                         If MIME_STRING, returns lowercase string.
     *                         If not set, returns the opposite value.
     *
     * @return mixed  See above.
     */
    function encoding($input, $format = null)
    {
        return HordeMIME::_getCode($input, $format, 'mime_encodings');
    }

    /**
     * Retrieves MIME encoding/type data from the internal arrays.
     *
     * @access private
     *
     * @param mixed $input    Either the MIME code or encoding string.
     * @param string $format  If MIME_CODE, returns code.
     *                        If MIME_STRING, returns lowercase string.
     *                        If null, returns the oppposite value.
     * @param string $type    The name of the internal array.
     *
     * @return mixed  See above.
     */
    function _getCode($input, $format, $type)
    {
        $numeric = is_numeric($input);
        if (!$numeric) {
            $input = String::lower($input);
        }

        switch ($format) {
        case MIME_CODE:
            if ($numeric) return $input;
            break;

        case MIME_STRING:
            if (!$numeric) return $input;
            break;
        }

        $vars = get_class_vars('HordeMIME');

        if ($numeric) {
            if (isset($vars[$type][$input])) {
                return $vars[$type][$input];
            }
        } else {
            if (($search = array_search($input, $vars[$type]))) {
                return $search;
            }
        }

        return null;
    }

    /**
     * Generates a Message-ID string conforming to RFC 2822 [3.6.4] and the
     * standards outlined in 'draft-ietf-usefor-message-id-01.txt'.
     *
     * @param string  A message ID string.
     */
    function generateMessageID()
    {
        return '<' . date('YmdHis') . '.' .
            substr(base_convert(microtime() . mt_rand(), 10, 36), -16) .
            '@' . $_SERVER['SERVER_NAME'] . '>';
    }

    /**
     * Adds proper linebreaks to a header string.
     * RFC 2822 [2.2.3] says that headers SHOULD be wrapped at 78 characters.
     * However, since we may be dealing with quoted text that contains spaces,
     * strict wrapping at 78 characters may add unwanted newlines and tabs to
     * the quoted parameter.  The correct way to get around this is to encode
     * paraeters according to RFC 2231.  However, since most mailers do not
     * seem to support this, we will instead simply use long lines.  RFC 2822
     * says headers SHOULD only be 78 characters a line, but also says that
     * a header line MUST not be more than 998 characters.  Therefore, the
     * compromise is to put each parameter on a separate line and chop it
     * only if it is longer than 998 characters.
     *
     * @param string $header  The header name.
     * @param string $text    The text of the header field.
     * @param string $eol     The EOL string to use.
     *
     * @return string  The header text, with linebreaks inserted.
     */
    function wrapHeaders($header, $text, $eol = "\r\n")
    {
        $header = rtrim($header);

        /* Remove any existing linebreaks. */
        $text = preg_replace("/\r?\n\s?/", ' ', $text);
        $text = $header . ': ' . rtrim($text);

        $eollength = strlen($eol);
        $header_lower = strtolower($header);

        if (($header_lower != 'content-type') &&
            ($header_lower != 'content-disposition')) {
            /* Wrap the line. */
            $line = wordwrap($text, 75, $eol . "\t");

            /* Make sure there are no empty lines. */
            $line = preg_replace('/' . $eol . "\t\s*" . $eol . "\t/", '/' . $eol . "\t/", $line);

            return substr($line, strlen($header) + 2);
        }

        /* Split the line by the RFC parameter separator ';'. */
        $params = preg_split("/\s*;\s*/", $text);

        $line = '';
        $length = 1000 - $eollength;
        $paramcount = count($params);

        foreach ($params as $count => $val) {
            /* If longer than RFC allows, then simply chop off the excess. */
            $moreparams = (($count + 1) != $paramcount);
            $maxlength = $length - (!empty($line) ? 1 : 0) - (($moreparams) ? 1 : 0);
            if (strlen($val) > $maxlength) {
                $val = substr($val, 0, $maxlength);

                /* If we have an opening quote, add a closing quote after
                 * chopping the rest of the text. */
                if (strpos($val, '"') !== false) {
                    $val = substr($val, 0, -1);
                    $val .= '"';
                }

            }

            if (!empty($line)) {
                $line .= "\t";
            }
            $line .= $val . (($moreparams) ? ';' : '') . $eol;
        }

        return substr($line, strlen($header) + 2, ($eollength * -1));
    }

}

}

?>