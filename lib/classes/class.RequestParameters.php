<?php
/*
Class of methods to populate and retrieve request parameters
Copyright (C) 2019-2020 CMS Made Simple Foundation <foundation@cmsmadesimple.org>

This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>

CMS Made Simple is free software; you can redistribute it and/or modify it
under the terms of the GNU General Public License as published by the
Free Software Foundation, either version 2 of that license, or (at your option)
any later version.

CMS Made Simple is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of that license along with CMS Made Simple.
If not, see <https://www.gnu.org/licenses/>.
*/
namespace CMSMS;

use CMSMS\AppParams;
use CMSMS\AppSingle;
use CMSMS\Crypto;
use const CMS_JOB_KEY;

/**
 * Class of static methods to populate get-parameters for use in an URL,
 * and retrieve parameter-values from $_REQUEST | $_GET | $_POST.
 * @see also get_parameter_value() which can revert to a $_SESSION value,
 * for any requested parameter that is not found.
 * @since 2.99
 */
class RequestParameters
{
    private const KEYPREF = '\\V^^V/'; //something extremely unlikely to be the prefix of any site-parameter key

    private const JOBID = 'aj_'; //something distinctive for job-URL's

    private const JOBKEY = '_jr_'; // parameter name for a secure repeatable job

    private const JOBONCEKEY = '_jo_'; // parameter name for a secure one-time job

    /**
     * $return 2-member array: [0] = separator char(s), [1] = encoding wanted
     */
    protected static function modes(int $format) : array
    {
        switch ($format) {
            case 2:
                return ['&', true];
            case 3:
                return ['&', false];
            default:
                return ['&amp;', true];
        }
    }

    /**
     * Generate get-parameter for a job-type
     *
     * @param int $type job-type value (0..2)
     * @param bool $first Optional flag whether this is the first get-parameter. Default false
     * @param int $format Optional format enumerator. Default 2
     *  See RequestParameters::create_action_params()
     * @return string
     */
    protected static function create_jobtype(int $type, bool $first = false, int $format = 2) : string
    {
        [$sep, $enc] = self::modes($format);
        $text = ($first) ? '' : $sep;
        if ($enc) {
            $text .= rawurlencode(CMS_JOB_KEY).'='.$type;
        } else {
            $text .= CMS_JOB_KEY.'='.$type;
        }
        return $text;
    }

    /**
     * Generate get-parameters for use in a job-URL
     *
     * @param array $parms URL get-parameters. See RequestParameters::create_action_params()
     * @param bool $onetime Optional flag whether the URL is for one-time use. Default false.
     * @param int $format Optional format enumerator. Default 2
     *  See RequestParameters::create_action_params()
     * @return string
     */
    public static function create_job_params(array $parms, bool $onetime = false, int $format = 2) : string
    {
        $parms['id'] = self::JOBID;
        $str = $parms['action'] ?? 'job';
        $str .= AppSingle::App()->GetUUID();
        if ($onetime) {
            $chars = Crypto::random_string(12, true);
            while (1) {
                $key = str_shuffle($chars);
                $subkey = substr($key, 0, 6);
                $val = hash('tiger128,3', $subkey.$str); // 32-hexits
                $savekey = self::KEYPREF.$subkey;
                if (!AppParams::exists($savekey)) {
                    AppParams::set($savekey, $val); // a bit racy!
                    $parms[self::JOBONCEKEY] = $subkey;
                    break;
                }
            }
        } else {
            $parms[self::JOBKEY] = hash('tiger128,3', $str);
        }
        $parms[CMS_JOB_KEY] = 2;

        return self::create_action_params($parms, $format);
    }

    /**
     * Get an URL query-string corresponding to the supplied value, which is
     * probably non-scalar.
     * This allows (among other things) generation of URL content that replicates
     * parameter arrays like $_POST'd parameter values, for passing around and
     * re-use without [de]serialization.
     * It behaves better than PHP http_build_query(), but only interprets 1-D arrays.
     *
     * @param string $key parameter name/key
     * @param mixed  $val Generally an array, but may be some other non-scalar or a scalar
     * @param int $format Optional format enumerator. Default 2
     * @see create_action_params()
     * @return string (No leading $sep for arrays)
     */
    public static function build_query(string $key, $val, int $format = 2) : string
    {
        [$sep, $enc] = self::modes($format);
        $multi = false;
        $eq = ($enc) ? '~~~' : '=';
        $sp = ($enc) ? '___' : $sep;
        if (is_array($val)) {
            $out = '';
            $first = true;
            foreach ($val as $k => $v) {
                if ($first) {
                    $out .= $key.'['.$k.']'.$eq;
                    $first = false;
                } else {
                    $out .= $sp.$key.'['.$k.']'.$eq;
                    $multi = true;
                }
                if (!is_scalar($v)) {
                    try {
                        $v = json_encode($v);
                    } catch (Throwable $t) {
                        $v = 'UNKNOWNOBJECT';
                    }
                }
                $out .= $v;
            }
        } elseif (!is_scalar($val)) {
            try {
                $val = json_encode($val);
            } catch (Throwable $t) {
                $val = 'UNKNOWNOBJECT';
            }
            $out = $key.$eq.$val;
        } else { //just in case, also handle scalars
            $out = $key.$eq.$val;
        }

        if ($enc) {
            $out = str_replace($eq, '=', rawurlencode($out));
            if ($multi) {
                $out = str_replace($sp, $sep, $out);
            }
        }
        return $out;
    }

    /**
     * Generate get-parameters for use in an URL (not necessarily one which runs a module-action)
     *
     * @param array $parms URL get-parameters. Should include mact-components
     *  and action-parameters (if any), and generic-parameters (if any)
     * @param int $format Optional format enumerator
     *  0 = (pre-2.99 default, back-compatible) rawurlencoded parameter keys and values
     *      other than the value for key 'mact', '&amp;' for parameter separators
     *  1 = proper: as for 0, but also encode the 'mact' value
     *  2 = default: as for 1, except '&' for parameter separators - e.g. for use in get-URL, js
     *  3 = displayable: no encoding, all html_entitized, probably not usable as-is
     *   BUT the output must be entitized upstream, it's not done here
     * @return string
     */
    public static function create_action_params(array $parms, int $format = 2) : string
    {
        [$sep, $enc] = self::modes($format);

        if (isset($parms[CMS_JOB_KEY])) {
            $type = $parms[CMS_JOB_KEY];
            unset($parms[CMS_JOB_KEY]);
        } else {
            $type = -1;
        }
        ksort($parms); //security key(s) lead

        if (isset($parms['module']) && isset($parms['id']) && isset($parms['action'])) {
            $module = trim($parms['module']);
            $id = trim($parms['id']);
            $action = trim($parms['action']);
            $inline = !empty($parms['inline']) ? 1 : 0;
            unset($parms['module'], $parms['id'], $parms['action'], $parms['inline']);
            $parms = ['mact' => "$module,$id,$action,$inline"] + $parms;
        }

        $text = '';
        $first = true;
        foreach ($parms as $key => $val) {
            if (is_scalar($val)) {
                if ($enc) {
                    $key = rawurlencode($key);
                }
                if ($enc && ($format != 0 || $key != 'mact')) {
                    $val = rawurlencode($val);
                }
                if ($first) {
                    $text .= $key.'='.$val;
                    $first = false;
                } else {
                    $text .= $sep.$key.'='.$val;
                }
            } else {
                if ($first) {
                    $first = false;
                } else {
                    $text .= $sep;
                }
                $text .= self::build_query($key, $val, $format);
            }
        }

        if ($type != -1) {
            $text .= self::create_jobtype($type, false, $format);
        }
        return $text;
    }

    /**
     * Validate security parameters in $parms
     *
     * @param array $parms Some/all current-request parameters
     * @return boolean indicating validity
     */
    public static function check_secure_params(array $parms)
    {
        if (isset($parms[self::JOBKEY])) {
            $str = $parms['action'] ?? 'job';
            $str .= AppSingle::App()->GetUUID();
            return $parms[self::JOBKEY] == hash('tiger128,3', $str);
        }
        if (isset($parms[self::JOBONCEKEY])) {
            $key = $parms[self::JOBONCEKEY];
            $savekey = self::KEYPREF.$key;
            if (AppParams::exists($savekey)) {
                $val = AppParams::get($savekey);
                AppParams::remove($savekey);
                $str = $parms['action'] ?? 'job';
                $hash = hash('tiger128,3', $key.$str.AppSingle::App()->GetUUID());
                return $hash == $val;
            }
            return false;
        }
        return (!isset($parms[CMS_JOB_KEY]) || $parms[CMS_JOB_KEY] != 2);
    }

    /**
     * Return array of request parameters, $_REQUEST or else merged $_POST, $_GET
     *
     * @return array
     */
    protected static function get_request_params() : array
    {
        if (!empty($_REQUEST)) {
            return $_REQUEST;
        }
        return array_merge($_POST, $_GET);
    }

    /**
     * Return parameters interpreted from parameters in the current request.
     * Non-action parameters are ignored.
     *
     * @return mixed array | null
     */
    public static function get_action_params()
    {
        $parms = [];
        $source = self::get_request_params();
        if (!empty($source['mact'])) {
            $parts = explode(',', $source['mact'], 4);
            $parms['module'] = trim($parts[0]);
            $parms['id'] = (isset($parts[1])) ? trim($parts[1]) : '';
            $parms['action'] = (isset($parts[2])) ? trim($parts[2]) : 'defaultadmin';
            $parms['inline'] = (!empty($parts[3])) ? 1 : 0;
        }

        if (isset($parms['id']) && $parms['id'] !== '') {
            $tmp = $source['mact'] ?? null;
            unset($source['mact']);

            $id = $parms['id'];
            $len = strlen($id);
            foreach ($source as $key => $val) {
                if (strncmp($key, $id, $len) == 0) {
                    $key2 = substr($key,$len);
                    if (is_numeric($val)) {
                        $parms[$key2] = $val + 0;
                    } elseif (is_scalar($val)) {
                        $parms[$key2] = $val; //TODO interpret json_encode()'d non-scalars
                    } else {
                        $parms[$key2] = $val;
                    }

                }
            }
            if ($tmp) $source['mact'] = $tmp;
        }

        if (isset($source[CMS_JOB_KEY])) {
            $parms[CMS_JOB_KEY] = filter_var($source[CMS_JOB_KEY], FILTER_SANITIZE_NUMBER_INT); //OR (int)
            if ($parms[CMS_JOB_KEY] == 2) {
                //TODO maybe a job-URL, check/process that
            }
        }

        return $parms;
    }

    /**
     * Return the non-action parameters in the current request.
     *
     * @param string $pref Optional, if non-empty, also ignore parameters whose
     *  key begins with this
     * @return array
     */
    public static function get_general_params($pref = '') : array
    {
        $source = self::get_request_params();
        $l = strlen(''.$pref);
        if ($l > 0) {
            $parms = [];
            foreach ($source as $key => $val) {
                if (strncmp($key, $pref, $l) != 0) {
                    $parms[$key] = $val;
                }
            }
        } else {
            $parms = $source;
        }

        if (isset($source['id'])) {
            $pref = $source['id'];
            $l = strlen($pref);
            if ($l > 0) {
                $tmp = [];
                foreach ($parms as $key => $val) {
                    if (strncmp($key, $pref, $l) != 0) {
                        $tmp[$key] = $val;
                    }
                }
                $parms = $tmp;
            }
        }

        return array_diff_key($parms, [
         'mact' => 1,
         'module' => 1,
         'id' => 1,
         'action' => 1,
         'inline' => 1,
        ]);
    }

    /**
     * Return values of specified parameter(s) (if they exist) in the current request
     * Null is returned for each parameter which doesn't exist.
     *
     * @param mixed $keys Optional wanted parameter-name(s) string | string[]
     *  String may be '' or '*', or array may be []. Default [], hence all parameters
     * @return mixed associative array | single value
     *   Values are verbatim i.e. not cleaned at all.
     *   Value is null for any parameter which doesn't exist.
     */
    public static function get_request_values($keys = [])
    {
        $multi = true;
        if ($keys) {
            if (!is_array($keys)) {
                if ($keys != '*') {
                    $multi = false;
                    $keys = [$keys];
                } else {
                    $keys = [];
                }
            }
        } else {
            $keys = [];
        }

        $source = self::get_request_params();
        if (!empty($source['mact'])) {
            $parts = explode(',', $source['mact'], 4);
            $source['module'] = trim($parts[0]);
            $source['id'] = $id = trim($parts[1]);
            $source['action'] = trim($parts[2]);
            $source['inline'] = (!empty($parts[3])) ? 1 : 0;
            $len = strlen($id);
            $strip = $len > 0;
        } else {
            $id = '';
            $strip = false;
        }

        if (!$keys) {
            $keys = array_keys($source);
        }

        $parms = array_fill_keys($keys, null);
        foreach ($keys as $key) {
            switch ($key) {
            case 'module':
            case 'id':
            case 'action':
                if (isset($source[$key])) {
                    $val = trim($source[$key]); break;
                } else {
                    continue 2;
                }
            case 'inline':
                if (isset($source[$key])) {
                    $val = (int)$source[$key]; break;
                } else {
                    continue 2;
                }
            default:
                if ($strip && isset($source[$id.$key])) {
                    $val = $source[$id.$key];
                    if (is_numeric($val)) {
                        $val += 0;
                    } elseif (($dec = self::get_json($val))) {
                        $val = $dec;
                    }
                } elseif (isset($source[$key])) {
                    $val = $source[$key];
                    if (is_numeric($val)) {
                        $val += 0;
                    } elseif (($dec = self::get_json($val))) {
                        $val = $dec;
                    }
                } else {
                    continue 2;
                }
            }
            $parms[$key] = $val;
        }
        return ($multi) ? $parms : reset($parms);
    }

    /**
     * Return json-decode()'d version of $val, if possible
     * @param mixed $val normally string
     * @return mixed decoded parameter | false
     */
    protected static function get_json($val)
    {
        if (!$val || !is_string($val) || is_numeric($val)) {
            return false;
        }

        $cleaned = ltrim($val);
        if (!$cleaned || !in_array($cleaned[0], ['{', '['])) {
            return false;
        }

        $dec = json_decode($cleaned, true);
        if ($dec && $dec != $cleaned && (json_last_error() == JSON_ERROR_NONE)) {
            return $dec;
        }
        return false;
    }
} // class
