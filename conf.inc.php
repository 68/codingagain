<?php
/*
    rep2 - ��{�ݒ�t�@�C��

    ���̃t�@�C���́A���ɗ��R�̖�������ύX���Ȃ�����
*/

$_conf['p2version'] = '1.8.13'; // rep2�̃o�[�W����

$_conf['p2name'] = 'r e p 2';    // rep2�̖��O�B


//======================================================================
// ��{�ݒ菈��
//======================================================================
// �G���[�o�͐ݒ�iNOTICE�팸���B�܂��c���Ă���Ǝv���j
error_reporting(E_ALL ^ E_NOTICE);

// {{{ ��{�ϐ�

$_conf['p2web_url']             = 'http://akid.s17.xrea.com/';
$_conf['p2ime_url']             = 'http://akid.s17.xrea.com/p2ime.phtml';
$_conf['favrank_url']           = 'http://akid.s17.xrea.com/favrank/favrank.php';
$_conf['menu_php']              = 'menu.php';
$_conf['subject_php']           = 'subject.php';
$_conf['read_php']              = 'read.php';
$_conf['read_new_php']          = 'read_new.php';
$_conf['read_new_k_php']        = 'read_new_k.php';
$_conf['post_php']              = 'post.php';
$_conf['cookie_file_name']      = 'p2_cookie.txt';

// }}}
// {{{ �f�o�b�O

$debug = isset($_GET['debug']) ? $_GET['debug'] : 0;
if ($debug) {
    include_once 'Benchmark/Profiler.php';
    $profiler =& new Benchmark_Profiler(true);
    
    // printMemoryUsage();
    register_shutdown_function('printMemoryUsage');
}

// }}}
// {{{ ��������m�F

if (version_compare(phpversion(), '4.3.0', 'lt')) {
    die('<html><body><h3>p2 error: PHP�o�[�W����4.3.0�����ł͎g���܂���B</h3></body></html>');
}
if (ini_get('safe_mode')) {
    die('<html><body><h3>p2 error: �Z�[�t���[�h�œ��삷��PHP�ł͎g���܂���B</h3></body></html>');
}
if (!extension_loaded('mbstring')) {
    die('<html><body><h3>p2 error: PHP�̃C���X�g�[�����s�\���ł��BPHP��mbstring�g�����W���[�������[�h����Ă��܂���B</h3></body></html>');
}

// }}}
// {{{ ���ݒ�

// �^�C���]�[�����Z�b�g
if (function_exists('date_default_timezone_set')) { 
    date_default_timezone_set('Asia/Tokyo'); 
} else { 
    @putenv('TZ=JST-9'); 
}

@set_time_limit(60); // (60) �X�N���v�g���s��������(�b)

// �����t���b�V�����I�t�ɂ���
ob_implicit_flush(0);

// �N���C�A���g����ڑ���؂��Ă������𑱍s����
// ignore_user_abort(1);

// session.trans_sid�L���� �� output_add_rewrite_var(), http_build_query() ���Ő����E�ύX�����
// URL��GET�p�����[�^��؂蕶��(��)��"&amp;"�ɂ���B�i�f�t�H���g��"&"�j
ini_set('arg_separator.output', '&amp;');

// ���N�G�X�gID��ݒ�
define('P2_REQUEST_ID', substr($_SERVER['REQUEST_METHOD'], 0, 1) . md5(serialize($_REQUEST)));

// Windows �Ȃ�
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ';');
    defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '\\');
} else {
    defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ':');
    defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '/');
}

// }}}
// {{{ �����R�[�h�̎w��

// mb_detect_order("SJIS-win,eucJP-win,ASCII");
mb_internal_encoding('SJIS-win');
mb_http_output('pass');
mb_substitute_character(63); // �����R�[�h�ϊ��Ɏ��s���������� "?" �ɂȂ�
//mb_substitute_character(0x3013); // ��

ini_set('default_mimetype', 'text/html');
ini_set('default_charset', 'Shift_JIS');

// ob_start('mb_output_handler');

if (function_exists('mb_ereg_replace')) {
    define('P2_MBREGEX_AVAILABLE', 1);
    @mb_regex_encoding('SJIS-win');
} else {
    define('P2_MBREGEX_AVAILABLE', 0);
}

// }}}
// {{{ ���C�u�����ނ̃p�X�ݒ�

// ��{�I�ȋ@�\��񋟂��邷�郉�C�u����
define('P2_LIB_DIR', './lib');
define('P2_LIBRARY_DIR', P2_LIB_DIR); // 2006/11/24 ����݊��p�A�p�~�\��

// ���܂��I�ȋ@�\��񋟂��邷�郉�C�u����
define('P2EX_LIBRARY_DIR', './lib/expack');

// �X�^�C���V�[�g
define('P2_STYLE_DIR', './style');

// PEAR�C���X�g�[���f�B���N�g���A�����p�X�ɒǉ������
define('P2_PEAR_DIR', './includes');

// PEAR���n�b�N�����t�@�C���p�f�B���N�g���A�ʏ��PEAR���D��I�Ɍ����p�X�ɒǉ������
// Cache/Container/db.php(PEAR::Cache)��MySQL���肾�����̂ŁA�ėp�I�ɂ������̂�u���Ă���
define('P2_PEAR_HACK_DIR', './lib/pear_hack');

// �����p�X���Z�b�g
if (is_dir(P2_PEAR_DIR) || is_dir(P2_PEAR_HACK_DIR)) {
    $include_path = '.';
    if (is_dir(P2_PEAR_HACK_DIR)) {
        $include_path .= PATH_SEPARATOR . realpath(P2_PEAR_HACK_DIR);
    }
    $include_path .= PATH_SEPARATOR . ini_get('include_path');
    if (is_dir(P2_PEAR_DIR)) {
        $include_path .= PATH_SEPARATOR . realpath(P2_PEAR_DIR);
    }
    ini_set('include_path', $include_path);
}

// ���C�u������ǂݍ���
$pear_required = array(
    'File/Util.php'             => 'File',
    'Net/UserAgent/Mobile.php'  => 'Net_UserAgent_Mobile',
    'PHP/Compat.php'            => 'PHP_Compat',
    'HTTP/Request.php'          => 'HTTP_Request'
);
foreach ($pear_required as $pear_file => $pear_pkg) {
    if (!include_once($pear_file)) {
        $url = 'http://akid.s17.xrea.com/p2puki/pukiwiki.php?PEAR%A4%CE%A5%A4%A5%F3%A5%B9%A5%C8%A1%BC%A5%EB';
        $url_t = $_conf['p2ime_url'] . "?enc=1&amp;url=" . rawurlencode($url);
        $msg = '<html><body><h3>p2 error: PEAR �́u' . $pear_pkg . '�v���C���X�g�[������Ă��܂���</h3>
            <p><a href="' . $url_t . '" target="_blank">p2Wiki: PEAR�̃C���X�g�[��</a></p>
            </body></html>';
        die($msg);
    }
}

require_once P2_LIB_DIR . '/p2util.class.php';
require_once P2_LIB_DIR . '/dataphp.class.php';
require_once P2_LIB_DIR . '/session.class.php';
require_once P2_LIB_DIR . '/login.class.php';
require_once P2_LIB_DIR . '/UA.php';

// }}}
// {{{ PEAR::PHP_Compat��PHP5�݊��̊֐���ǂݍ���

if (version_compare(phpversion(), '5.0.0', '<')) {
    PHP_Compat::loadFunction('file_put_contents');
    //PHP_Compat::loadFunction('clone');
    PHP_Compat::loadFunction('scandir');
    PHP_Compat::loadFunction('http_build_query');
    //PHP_Compat::loadFunction('array_walk_recursive');
}

// }}}
// {{{ �t�H�[������̓��͂��ꊇ�ŃT�j�^�C�Y

/**
 * �t�H�[������̓��͂��ꊇ�ŃN�H�[�g�����������R�[�h�ϊ�
 * �t�H�[����accept-encoding������UTF-8(Safari�n) or Shift_JIS(���̑�)�ɂ��A
 * �����hidden�v�f�Ŕ����e�[�u���̕������d���ނ��ƂŌ딻������炷
 * �ϊ�������eucJP-win������̂�HTTP���͂̕����R�[�h��EUC�Ɏ����ϊ������T�[�o�̂���
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (get_magic_quotes_gpc()) {
        $_POST = array_map('stripslashesR', $_POST);
    }
    mb_convert_variables('SJIS-win', 'UTF-8,eucJP-win,SJIS-win', $_POST);
    $_POST = array_map('nullfilterR', $_POST);
}
if (!empty($_GET)) {
    if (get_magic_quotes_gpc()) {
        $_GET = array_map('stripslashesR', $_GET);
    }
    mb_convert_variables('SJIS-win', 'UTF-8,eucJP-win,SJIS-win', $_GET);
    $_GET = array_map('nullfilterR', $_GET);
}

// }}}

// �Ǘ��җp�ݒ��ǂݍ���
if (!include_once './conf/conf_admin.inc.php') {
    P2Util::printSimpleHtml("p2 error: �Ǘ��җp�ݒ�t�@�C����ǂݍ��߂܂���ł����B");
    die;
}

// �Ǘ��p�ۑ��f�B���N�g�� (�p�[�~�b�V������707)
$_conf['admin_dir'] = $_conf['data_dir'] . '/admin';

// cache �ۑ��f�B���N�g�� (�p�[�~�b�V������707)
$_conf['cache_dir'] = $_conf['data_dir'] . '/cache'; // 2005/6/29 $_conf['pref_dir'] . '/p2_cache' ���ύX

// �e���|�����f�B���N�g�� (�p�[�~�b�V������707)
$_conf['tmp_dir'] = $_conf['data_dir'] . '/tmp';

$_conf['doctype'] = '';
$_conf['accesskey'] = 'accesskey';

$_conf['meta_charset_ht'] = '<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">'."\n";

// {{{ �[������

$_conf['login_check_ip']  = 1; // ���O�C������IP�A�h���X�����؂���

// ��{�iPC�j
$_conf['ktai'] = false;
$_conf['disable_cookie'] = false;

if (UA::isSafariGroup()) {
    $_conf['accept_charset'] = 'UTF-8';
} else {
    $_conf['accept_charset'] = 'Shift_JIS';
}

$mobile =& Net_UserAgent_Mobile::singleton();
if (PEAR::isError($mobile)) {
    trigger_error($mobile->toString(), E_USER_WARNING);

// �g��
} elseif ($mobile and !$mobile->isNonMobile()) {

    require_once P2_LIB_DIR . '/hostcheck.class.php';
    
    $_conf['ktai'] = true;
    $_conf['accept_charset'] = 'Shift_JIS';

    // �x���_����
    // DoCoMo i-Mode
    if ($mobile->isDoCoMo()) {
        if ($_conf['login_check_ip'] && !HostCheck::isAddrDocomo()) {
            P2Util::printSimpleHtml("p2 error: UA��DoCoMo�ł����AIP�A�h���X�ш悪�}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
            die;
        }
        $_conf['disable_cookie'] = true;
        
    // EZweb (au or Tu-Ka)
    } elseif ($mobile->isEZweb()) {
        if ($_conf['login_check_ip'] && !HostCheck::isAddrAu()) {
            P2Util::printSimpleHtml("p2 error: UA��EZweb�ł����AIP�A�h���X�ш悪�}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
            die;
        }
        $_conf['disable_cookie'] = FALSE;
        
    // Vodafone Live!
    } elseif ($mobile->isVodafone()) {
        //$_conf['accesskey'] = 'DIRECTKEY';
        // W�^�[����3GC�^�[����Cookie���g����
        if ($mobile->isTypeW() || $mobile->isType3GC()) {
            $_conf['disable_cookie'] = FALSE;
        } else {
            $_conf['disable_cookie'] = TRUE;
            if ($_conf['login_check_ip'] && !HostCheck::isAddrSoftBank()) {
                P2Util::printSimpleHtml("p2 error: UA��SoftBank�ł����AIP�A�h���X�ш悪�}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
                die;
            }
        }

    // AirH" Phone
    } elseif ($mobile->isAirHPhone()) {
        /*
        // AirH"�ł͒[��ID�F�؂��s��Ȃ��̂ŁA�R�����g�A�E�g
        if ($_conf['login_check_ip'] && !HostCheck::isAddrWillcom()) {
            P2Util::printSimpleHtml("p2 error: UA��AirH&quot;�ł����AIP�A�h���X�ш悪�}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
            die;
        }
        */
        $_conf['disable_cookie'] = FALSE;
        
    // ���̑�
    } else {
        $_conf['disable_cookie'] = TRUE;
    }

// �g�ѕ\���Ώۃ��o�C��
} elseif (UA::isMobile()) {
    $_conf['ktai'] = true;
}

// }}}
// {{{ �N�G���[�ɂ�鋭���r���[�w��

// b=pc �͂܂������N�悪���S�łȂ�
// output_add_rewrite_var() �͕֗������A�o�͂��o�b�t�@����đ̊����x��������̂���_�B�B
// �̊����x�𗎂Ƃ��Ȃ��ǂ����@�Ȃ����ȁH

$b = UA::getQueryKey();

// ���݊��p
if (!empty($_GET['k']) || !empty($_POST['k'])) {
    $_REQUEST[$b] = $_GET[$b] = 'k';
}

$_conf[$b] = UA::getQueryValue();

$_conf['k_at_q'] = '';
$_conf['k_input_ht'] = '';

// ����PC�r���[�w��ib=pc�j
if (UA::isPCByQuery()) {
    $_conf['ktai'] = false;

// �����g�уr���[�w��ib=k�j
} elseif (UA::isMobileByQuery()) {
    $_conf['ktai'] = true;
}

if ($_conf[$b]) {
    //output_add_rewrite_var($b, htmlspecialchars($_conf[$b], ENT_QUOTES));

    $b_hs = htmlspecialchars($_conf['b'], ENT_QUOTES);
    $_conf['k_at_a'] = "&amp;{$b}={$b_hs}";
    $_conf['k_at_q'] = "?{$b}={$b_hs}";
    $_conf['k_input_ht'] = '<input type="hidden" name="' . $b . '" value="' . $b_hs . '">';

} else {
    $_conf['k_at_a'] = '';
    $_conf['k_at_q'] = '';
    $_conf['k_input_ht'] = '';
}

// }}}

$_conf['k_to_index_ht'] = <<<EOP
<a {$_conf['accesskey']}="0" href="index.php{$_conf['k_at_q']}">0.TOP</a>
EOP;

// {{{ DOCTYPE HTML �錾

$ie_strict = false;
if (!$_conf['ktai']) {
    if ($ie_strict) {
        $_conf['doctype'] = <<<EODOC
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">\n
EODOC;
    } else {
        $_conf['doctype'] = <<<EODOC
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">\n
EODOC;
    }
}

// }}}

//======================================================================

// {{{ ���[�U�ݒ� �Ǎ�

// �f�t�H���g�ݒ�iconf_user_def.inc.php�j��ǂݍ���
require_once './conf/conf_user_def.inc.php';
$_conf = array_merge($_conf, $conf_user_def);

// ���[�U�ݒ肪����Γǂݍ���
$_conf['conf_user_file'] = $_conf['pref_dir'] . '/conf_user.srd.cgi';

// ���`���t�@�C�����R�s�[
$conf_user_file_old = $_conf['pref_dir'] . '/conf_user.inc.php';
if (!file_exists($_conf['conf_user_file']) && file_exists($conf_user_file_old)) {
    $old_cont = DataPhp::getDataPhpCont($conf_user_file_old);
    FileCtl::make_datafile($_conf['conf_user_file'], $_conf['conf_user_perm']);
    file_put_contents($_conf['conf_user_file'], $old_cont);
}

$conf_user = array();
if (file_exists($_conf['conf_user_file'])) {
    if ($cont = file_get_contents($_conf['conf_user_file'])) {
        $conf_user = unserialize($cont);
        $_conf = array_merge($_conf, $conf_user);
    }
}

// }}}

if (file_exists("./conf/conf_user_style.inc.php")) {
    include_once "./conf/conf_user_style.inc.php"; // �f�U�C���ݒ� �Ǎ�
}

// {{{ �f�t�H���g�ݒ�

if (!is_dir($_conf['pref_dir']))    { $_conf['pref_dir'] = "./data"; }
if (!is_dir($_conf['dat_dir']))     { $_conf['dat_dir'] = "./data"; }
if (!is_dir($_conf['idx_dir']))     { $_conf['idx_dir'] = "./data"; }
if (!isset($_conf['rct_rec_num']))  { $_conf['rct_rec_num'] = 20; }
if (!isset($_conf['res_hist_rec_num'])) { $_conf['res_hist_rec_num'] = 20; }
if (!isset($_conf['posted_rec_num'])) { $_conf['posted_rec_num'] = 1000; }
if (!isset($_conf['before_respointer'])) { $_conf['before_respointer'] = 20; }
if (!isset($_conf['sort_zero_adjust'])) { $_conf['sort_zero_adjust'] = 0.1; }
if (!isset($_conf['display_threads_num'])) { $_conf['display_threads_num'] = 150; }
if (!isset($_conf['cmp_dayres_midoku'])) { $_conf['cmp_dayres_midoku'] = 1; }
if (!isset($_conf['k_sb_disp_range'])) { $_conf['k_sb_disp_range'] = 30; }
if (!isset($_conf['k_rnum_range'])) { $_conf['k_rnum_range'] = 10; }
if (!isset($_conf['pre_thumb_height'])) { $_conf['pre_thumb_height'] = "32"; }
if (!isset($_conf['quote_res_view'])) { $_conf['quote_res_view'] = 1; }
if (!isset($_conf['res_write_rec'])) { $_conf['res_write_rec'] = 1; }

if (!isset($STYLE['post_pop_size'])) { $STYLE['post_pop_size'] = "610,350"; }
if (!isset($STYLE['post_msg_rows'])) { $STYLE['post_msg_rows'] = 10; }
if (!isset($STYLE['post_msg_cols'])) { $STYLE['post_msg_cols'] = 70; }
if (!isset($STYLE['info_pop_size'])) { $STYLE['info_pop_size'] = "600,380"; }

// }}}
// {{{ ���[�U�ݒ�̒�������

$_conf['ext_win_target_at'] = '';
$_conf['ext_win_target'] && $_conf['ext_win_target_at'] = " target=\"{$_conf['ext_win_target']}\"";
$_conf['bbs_win_target_at'] = '';
$_conf['bbs_win_target'] && $_conf['bbs_win_target_at'] = " target=\"{$_conf['bbs_win_target']}\"";

if ($_conf['get_new_res']) {
    if ($_conf['get_new_res'] == 'all') {
        $_conf['get_new_res_l'] = $_conf['get_new_res'];
    } else {
        $_conf['get_new_res_l'] = 'l'.$_conf['get_new_res'];
    }
} else {
    $_conf['get_new_res_l'] = 'l200';
}

// }}}

if ($_conf['mobile.match_color']) {
    $_conf['k_filter_marker'] = "<font color=\"" . htmlspecialchars($_conf['mobile.match_color']) . "\">\\1</font>";
} else {
    $_conf['k_filter_marker'] = null;
}

//======================================================================
// �ϐ��ݒ�
//======================================================================
$_conf['rct_file'] =            $_conf['pref_dir'] . '/p2_recent.idx';
$_conf['p2_res_hist_dat'] =     $_conf['pref_dir'] . '/p2_res_hist.dat'; // �������݃��O�t�@�C���idat�j
$_conf['p2_res_hist_dat_php'] = $_conf['pref_dir'] . '/p2_res_hist.dat.php'; // �������݃��O�t�@�C���i�f�[�^PHP�j��
// �������݃��O�t�@�C���idat�j �Z�L�����e�B�ʕ�p
$_conf['p2_res_hist_dat_secu'] = $_conf['pref_dir'] . '/p2_res_hist.secu.cgi';
$_conf['cookie_dir'] =          $_conf['pref_dir'] . '/p2_cookie'; // cookie �ۑ��f�B���N�g��
$_conf['favlist_file'] =        $_conf['pref_dir'] . "/p2_favlist.idx";
$_conf['favita_path'] =         $_conf['pref_dir'] . "/p2_favita.brd";
$_conf['idpw2ch_php'] =         $_conf['pref_dir'] . "/p2_idpw2ch.php";
$_conf['sid2ch_php'] =          $_conf['pref_dir'] . "/p2_sid2ch.php";
$_conf['auth_user_file'] =      $_conf['pref_dir'] . "/p2_auth_user.php";
$_conf['auth_ez_file'] =        $_conf['pref_dir'] . "/p2_auth_ez.php";
$_conf['auth_jp_file'] =        $_conf['pref_dir'] . "/p2_auth_jp.php";
$_conf['auth_docomo_file'] =    $_conf['pref_dir'] . '/p2_auth_docomo.php';
$_conf['login_log_file'] =      $_conf['pref_dir'] . "/p2_login.log.php";
$_conf['login_failed_log_file'] = $_conf['pref_dir'] . '/p2_login_failed.dat.php';

// saveMatomeCache() �̂��߂� $_conf['pref_dir'] ���΃p�X�ɕϊ�����
define('P2_PREF_DIR_REAL_PATH', File_Util::realPath($_conf['pref_dir']));

$_conf['matome_cache_path'] = P2_PREF_DIR_REAL_PATH . DIRECTORY_SEPARATOR . 'matome_cache';
$_conf['matome_cache_ext'] = '.htm';
$_conf['matome_cache_max'] = 3; // �\���L���b�V���̐�

// {{{ ���肦�Ȃ������̃G���[

// �V�K���O�C���ƃ����o�[���O�C���̓����w��͂��肦�Ȃ��̂ŁA�G���[�o��
if (isset($_POST['submit_new']) && isset($_POST['submit_member'])) {
    P2Util::printSimpleHtml("p2 Error: ������URL�ł��B");
    die;
}

// }}}
// {{{ �z�X�g�`�F�b�N

if ($_conf['secure']['auth_host'] || $_conf['secure']['auth_bbq']) {
    require_once P2_LIB_DIR . '/hostcheck.class.php';
    if (($_conf['secure']['auth_host'] && HostCheck::getHostAuth() == FALSE) ||
        ($_conf['secure']['auth_bbq'] && HostCheck::getHostBurned() == TRUE)
    ) {
        HostCheck::forbidden();
    }
}

// }}}
// {{{ �Z�b�V����

// ���O�́A�Z�b�V�����N�b�L�[��j������Ƃ��̂��߂ɁA�Z�b�V�������p�̗L���Ɋւ�炸�ݒ肷��
session_name('PS');

// �Z�b�V�����f�[�^�ۑ��f�B���N�g�����K��
if ($_conf['session_save'] == 'p2' and session_module_name() == 'files') {
    // $_conf['data_dir'] ���΃p�X�ɕϊ�����
    define('P2_DATA_DIR_REAL_PATH', File_Util::realPath($_conf['data_dir']));
    $_conf['session_dir'] = P2_DATA_DIR_REAL_PATH . DIRECTORY_SEPARATOR . 'session';
}


// css.php �͓��ʂɃZ�b�V��������O���B
//if (basename($_SERVER['SCRIPT_NAME']) != 'css.php') {
    if ($_conf['use_session'] == 1 or ($_conf['use_session'] == 2 && empty($_COOKIE['cid']))) { 
    
        // {{{ �Z�b�V�����f�[�^�ۑ��f�B���N�g����ݒ�
        
        if ($_conf['session_save'] == 'p2' and session_module_name() == 'files') {
        
            if (!is_dir($_conf['session_dir'])) {
                require_once P2_LIB_DIR . '/filectl.class.php';
                FileCtl::mkdirFor($_conf['session_dir'] . '/dummy_filename');
            } elseif (!is_writable($_conf['session_dir'])) {
                die("Error: �Z�b�V�����f�[�^�ۑ��f�B���N�g�� ({$_conf['session_dir']}) �ɏ������݌���������܂���B");
            }

            session_save_path($_conf['session_dir']);

            // session.save_path �̃p�X�̐[����2���傫���ƃK�[�x�b�W�R���N�V�������s���Ȃ��̂�
            // ���O�ŃK�[�x�b�W�R���N�V��������
            P2Util::session_gc();
        }
        
        // }}}

        $_p2session =& new Session();
    }
//}

// }}}

// ���O�C���N���X�̃C���X�^���X�����i���O�C�����[�U���w�肳��Ă��Ȃ���΁A���̎��_�Ń��O�C���t�H�[���\���Ɂj
require_once P2_LIB_DIR . '/login.class.php';
$_login =& new Login();


//=====================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//=====================================================================

/**
 * �ċA�I��stripslashes��������
 * GET/POST/COOKIE�ϐ��p�Ȃ̂ŃI�u�W�F�N�g�̃v���p�e�B�ɂ͑Ή����Ȃ�
 * (ExUtil)
 *
 * @return  array|string
 */
function stripslashesR($var, $r = 0)
{
    $rlimit = 10;
    if (is_array($var) && $r < $rlimit) {
        foreach ($var as $key => $value) {
            $var[$key] = stripslashesR($value, ++$r);
        }
    } elseif (is_string($var)) {
        $var = stripslashes($var);
    }
    return $var;
}

/**
 * �ċA�I�Ƀk���������폜����
 * mbstring�ŕϊ��e�[�u���ɂȂ�(?)�O����ϊ������
 * NULL(0x00)�ɂȂ��Ă��܂����Ƃ�����̂ŏ�������
 * (ExUtil)
 *
 * @return  array|string
 */
function nullfilterR($var, $r = 0)
{
    $rlimit = 10;
    if (is_array($var) && $r < $rlimit) {
        foreach ($var as $key => $value) {
            $var[$key] = nullfilterR($value, ++$r);
        }
    } elseif (is_string($var)) {
        $var = str_replace(chr(0), '', $var);
    }
    return $var;
}

/**
 * �������̎g�p�ʂ�\������
 *
 * @return void
 */
function printMemoryUsage()
{
    $kb = memory_get_usage() / 1024;
    $kb = number_format($kb, 2, '.', '');
    
    echo 'Memory Usage: ' . $kb . 'KB';
}

//=====================================================================
// �O���[�o���֐�
//=====================================================================
/**
 * htmlspecialchars �̕ʖ��݂����Ȃ���
 *
 * @param   string  $alt  �l����̂Ƃ��̑�֕�����
 * @return  string
 */
function hs($str, $alt = '', $quoteStyle = ENT_QUOTES)
{
    return (isset($str) && strlen($str) > 0) ? htmlspecialchars($str, $quoteStyle) : $alt;
}

/**
 * notice �̗}�������Ă���� hs()
 * �Q�ƂŒl���󂯎��̂̓C�}�C�`�����A�������Ȃ����notice�̗}�����ł��Ȃ�
 *
 * @param   &string  $str  ������ϐ��̎Q��
 * @return  string
 */
function hsi(&$str, $alt = '', $quoteStyle = ENT_QUOTES)
{
    return (isset($str) && strlen($str) > 0) ? htmlspecialchars($str, $quoteStyle) : $alt;
}

/**
 * echo hs()
 *
 * @return  void
 */
function eh($str, $alt = '', $quoteStyle = ENT_QUOTES)
{
    echo hs($str, $alt, $quoteStyle);
}

/**
 * echo hs() �inotice��}������j
 *
 * @param   &string  $str  ������ϐ��̎Q��
 * @return  void
 */
function ehi(&$str, $alt = '', $quoteStyle = ENT_QUOTES)
{
    echo hs($str, $alt, $quoteStyle);
}

/**
 * ���݂��Ȃ��ϐ��� notice ���o�����ɁA�ϐ��̒l���擾����
 *
 * @return  mixed
 */
function geti(&$var, $alt = null)
{
    return isset($var) ? $var : $alt;
}

/**
 * ���s��t���ĕ�������o�͂���icli��web�ŏo�͂��ς��j
 *
 * @return  void
 */
function echoln($str = '')
{
    if (php_sapi_name() == 'cli') {
        echo $str . "\n";
    } else {
        echo $str . "<br>";
    }
}

/**
 * p2 error ���b�Z�[�W��\�����ďI��
 *
 * @param   string  $err    �G���[�T�v
 * @param   string  $msg    �ڍׂȐ���
 * @param   boolean $raw    �ڍׂȐ������G�X�P�[�v���邩�ۂ�
 * @return  void
 */
function p2die($err, $msg = null, $raw = false)
{
    echo '<html><head><title>p2 error</title></head><body>';
    echo '<h3>p2 error: ', htmlspecialchars($err, ENT_QUOTES), '</h3>';
    if ($msg !== null) {
        if ($raw) {
            echo '<p>', nl2br(htmlspecialchars($msg, ENT_QUOTES)), '</p>';
        } else {
            echo $msg;
        }
    }
    echo '</body></html>';
    
    exit;
}

/**
 * conf_user �Ƀf�[�^���Z�b�g�L�^����
 * maru_kakiko
 *
 * @return  true|null|false
 */
function setConfUser($k, $v)
{
    global $_conf;
    
    // validate
    if ($k == 'k_use_aas') {
        if ($v != 0 && $v != 1) {
            return null;
        }
    }
    
    if (false === P2Util::updateArraySrdFile(array($k => $v), $_conf['conf_user_file'])) {
        return false;
    }
    $_conf[$k] = $v;
    
    return true;
}
