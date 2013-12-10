<?php
/* =========================================================================== *
	ckAuth.php
	UTF-8（雀の往来）
	purpose: authentification module using cookie and rdb. 
 * =========================================================================== */
/*
	usage: 
	
	$auth = new ckAuth();
	if( $auth->getAuth() ) {
		$user_name = $auth->username;
	}
	else {
		echo "sorry!";
	}
*/

// constants are exactly the same as PEAR's AUTH.
if( !defined( 'AUTH_MEMBER_LOGIN_PHP' ) ) define( 'AUTH_MEMBER_LOGIN_PHP', 'login.php' );
define( "AUTH_RETURN_URL",   '__ckAuth_ret_url__' );

define( "AUTH_ACCESS",        1 );
define( "AUTH_NO_LOGIN",      0 );
define( "AUTH_IDLED",        -1 );
define( "AUTH_EXPIRED",      -2 );
define( "AUTH_WRONG_PASSWD", -3 );
define( "AUTH_WRONG_USERID", -4 );
define( "AUTH_NO_USER_PASS", -6 );
define( "AUTH_NOT_VALID",    -5 );
define( "AUTH_NO_ACCESS",   -10 );
define( "AUTH_PLEASE_LOGIN",-99 );
define( "AUTH_USER_NOBODY", "nobody" );

define( "AUTH_LOGIN_SESSION", "session" );
define( "AUTH_LOGIN_POST",    "post" );
define( "AUTH_LOGIN_COOKIE",  "cookie" );

define( "AUTH_MSG_NO_ACCESS",    9 );
define( "AUTH_MSG_INVALID_USER", 4 );
define( "AUTH_MSG_EXPIRED",      3 );
define( "AUTH_MSG_WRONG_INPUT",  2 );
define( "AUTH_MSG_PLEASE_ENTER", 1 );
define( "AUTH_MSG_NO_MESSAGE",   0 );

abstract class AuthAbstract
{
	var $db_params;   // how to access db to verify user/password
	var $cookie_id  = "ckAuth_data";   // cookie id to use
	var $username;    // user name used to login 
	var $password;    // password used to login after md5
	var $save_cookie; // save password to cookie or not. 
	var $user_info;   // info from the DB for this user.
	
	var $login_method;// login via post? or cookie?
	var $status;      // status of the login 
    var $expire = 0;  // do not expire after login
    var $idle   = 0;  // do not expire after long idle
	
	var $current_file;   // for 
	var $current_URI;    // for 
	var $full_file_name; // for doc.cgi files
	var $login_url;      // login URL

    var $magic_pwd;      // magic password

    /**
     * @var Db_Sql
     */
    var $sql;
	/* ---------------------------------------------------------------------- */
    /**
     * @param null|Db_Sql $sql
     */
    function __construct( $sql=null )
	{
		// get_auth : 認証を自動的に行う
		// ret_url  : 認証成功後の戻りURL
		
		if( WORDY ) echo "<br><b>ckAuth instance created ()...</b><br>\n";
        $this->sql    = $sql;
		$this->status = AUTH_NO_LOGIN;

		// 設定項目 
		/***
		$this->login_url  = AUTH_MEMBER_LOGIN_PHP;
		$this->db_params = array(
			"db_table"    => "login_db",
			"usernamecol" => "user_id",
			"passwordcol" => "user_passwd",
			"user_status" => "user_status",
			"active_flag" => "1",
		 );
		***/
		
		if( WORDY > 4 ) {
			wordy_table( $_SESSION[ $this->cookie_id ] );
		}
		if( WORDY ) {
			$this->status_means = array( 
				AUTH_ACCESS       => '認証成功',
				AUTH_NO_LOGIN     => '認証無し',
				AUTH_IDLED        => 'アイドル時間超過',
				AUTH_EXPIRED      => 'ログイン後時間超過',
				AUTH_WRONG_PASSWD => 'パスワード間違い',
				AUTH_WRONG_USERID => 'ユーザーID間違い',
				AUTH_NO_USER_PASS => 'ID&PW無し',
				AUTH_NOT_VALID    => '認証失敗',
				AUTH_NO_ACCESS    => 'アクセス不可',
				AUTH_PLEASE_LOGIN => '再ログイン',
				AUTH_USER_NOBODY  => '誰？'
			);
			wordy_table( $this->status_means, 'ckAuth status_means' );
		}
	}
	/* ---------------------------------------------------------------------- */
    function start()
    {
		if( WORDY ) {
			ob_start();
		}
        session_start();
    }
	/* ---------------------------------------------------------------------- */
	function getAuth( $loadLogin=false )
	{
		if( WORDY ) echo "<br><b>ckAuth->getAuth() ...</b><br>\n";
		// get user/password
		
		if( $this->_load_post() )
		{
			// try to login via post variables
			if( $this->verify_user() == AUTH_ACCESS ) {
				$this->_save_session();
				$this->login_method = AUTH_LOGIN_POST;
				if( WORDY ) echo "logged in from POST <br>\n";
			}
		}
		elseif( $this->_verify_session() == AUTH_ACCESS ) {
			// already logged in
			if( WORDY ) wordy_table( $this->user_info, 'ckAuth::user_info, _verify_session success' );
		}
		if( WORDY > 3 ) {
			$sts = $this->status_means[ $this->status ];
			echo " -> username={$this->username}<br>\n";
			echo " -> login_method={$this->login_method}<br>\n";
			echo " -> status={$this->status}, ({$sts})<br><br>\n";
		}
        if( $loadLogin && $this->status !== AUTH_ACCESS ) {
            $this->draw_login();
        }
		return $this->status;
	}
	/* ---------------------------------------------------------------------- */
	function verify_user()
	{
		// $this->usernameと$this->passwordからデータベースと照合して認証判断
		// $this->passwordはmd5で暗号化されている必要がある。
		if( WORDY ) echo "verify_user() ...<br>\n";

		if( !have_value( $this->username ) && !have_value( $this->password ) ) {
			$this->status = AUTH_PLEASE_LOGIN;
			return $this->status;
		}
		
		if( FALSE && is_numeric( $this->username ) ) {
			$this->username = (int) $this->username;
		}
		$sql = $this->sql;
		$sql->setTable( $this->db_params["db_table"] );
		$sql->setCols( array( "*" ) );
		$sql->setWhere( "{$this->db_params{'usernamecol'}}='{$this->username}'" );
		$sql->makeSQL( "SELECT" );
		$sql->execSQL();
		$num = $sql->fetchAll( $user_info );
		if( $num != 1 ) {
			$this->status = AUTH_WRONG_USERID;
		}
		else {
			$password    = trim( $user_info[0][ $this->db_params["passwordcol"] ] );
			$user_status = trim( $user_info[0][ $this->db_params["user_status"] ] );
			$db_password = md5( $password );
			
			if( $this->magic_pwd && $this->password == md5( $this->magic_pwd ) ) {
				$this->status = AUTH_ACCESS;
				$this->user_info = $user_info[0];
			}
			elseif( $this->db_params["user_status"] && 
			        $user_status != $this->db_params["active_flag"] ) {
				$this->status = AUTH_NOT_VALID;
			}
			elseif( $db_password == $this->password ) {
				// success
				$this->status = AUTH_ACCESS;
				$this->user_info = $user_info[0];
            }
            elseif( $password == $this->password ) {
                    // success
                    $this->status = AUTH_ACCESS;
                    $this->user_info = $user_info[0];
			} else {
				// failed
				$this->status = AUTH_WRONG_PASSWD;
			}
		}
		
		return $this->status;
	}
	/* ---------------------------------------------------------------------- */
    function find_passwd_match( & $user_info, $passwd )
    {
		for( $i = 0; $i < count( $user_info ); $i ++ ) {
			$db_password = trim( $user_info[$i][ $this->db_params["passwordcol"] ] );
			$db_password = md5( $db_password );
			if( $db_password == $passwd ) {
				return $user_info[$i];
			}
		}
		return FALSE;
	}
	/* ---------------------------------------------------------------------- */
    function is_admin()
    {
		if( isset( $this->user_info[ 'ckAuth_admin_flag' ] ) && 
		    $this->user_info[ 'ckAuth_admin_flag' ] ) {
			return TRUE;
		}
		return FALSE;
	}
	/* ---------------------------------------------------------------------- */
    function _save_session()
    {
		if( WORDY ) echo "_save_session() ...<br>\n";
		
		if( !isset( $_SESSION[ $this->cookie_id ] ) && !isset( $_SESSION ) ) {
			$this->start();
		}
		
		if( !isset($_SESSION[ $this->cookie_id ]) || !is_array($_SESSION[ $this->cookie_id ] ) ) {
			$_SESSION[ $this->cookie_id ] = array();
		}
		
		$_SESSION[ $this->cookie_id ]['registered']   = TRUE;
		$_SESSION[ $this->cookie_id ]['username']     = $this->username;
		$_SESSION[ $this->cookie_id ]['password']     = $this->password;
		$_SESSION[ $this->cookie_id ]['login_method'] = $this->login_method;
		$_SESSION[ $this->cookie_id ]['timestamp']    = time();
		$_SESSION[ $this->cookie_id ]['idle']         = time();
		$_SESSION[ $this->cookie_id ]['user_info']    = $this->user_info;
		//$_SESSION[ $this->cookie_id ]['ret_url']      = NULL;
		
		if( WORDY > 3 ) {
			wordy_table( $_SESSION[ $this->cookie_id ] );
		}
		
		return TRUE;
    }
	/* ---------------------------------------------------------------------- */
	function _verify_session()
	{
		if( WORDY ) echo "_verify_session() ... {$this->cookie_id}<br>\n";
		
        if( isset( $_SESSION[ $this->cookie_id ] ) ) 
		{
			if( WORDY > 3 ) {
				wordy_table( $_SESSION[ $this->cookie_id ], "session {$this->cookie_id}" );
			}
		    /** Check if authentication session is expired */
            if( $this->expire > 0 &&
                isset( $_SESSION[ $this->cookie_id ]['timestamp'] ) &&
                ( $_SESSION[ $this->cookie_id ]['timestamp'] + $this->expire ) < time() ) 
			{
                $this->_logoff();
                $this->expired = TRUE;
                $this->status = AUTH_EXPIRED;

                return FALSE;
            }
            /** Check if maximum idle time is reached */
			if( WORDY ) {
				$sess_idle = $_SESSION[ $this->cookie_id ]['idle'];
				$now_time  = time();
				echo "ckAuth:checking idle time: {$sess_idle}+{$this->idle} < {$now_time}<br>";
			}
            if( $this->idle > 0 &&
                isset($_SESSION[ $this->cookie_id ]['idle'] ) &&
                ( $_SESSION[ $this->cookie_id ]['idle'] + $this->idle) < time() ) 
			{
                $this->_logoff();
                $this->idled  = TRUE;
                $this->status = AUTH_IDLED;

                return FALSE;
            }

            if( isset( $_SESSION[ $this->cookie_id ]['registered'] ) &&
                isset( $_SESSION[ $this->cookie_id ]['username'] ) &&
                $_SESSION[ $this->cookie_id ]['registered'] == TRUE &&
                have_value( $_SESSION[ $this->cookie_id ]['username'] ) ) 
			{
                $this->_update_session();
				$this->username     = $_SESSION[ $this->cookie_id ]['username']   ;
				$this->password     = $_SESSION[ $this->cookie_id ]['password']   ;
				$this->user_info    = $_SESSION[ $this->cookie_id ]['user_info']  ;
				$this->login_method = $_SESSION[ $this->cookie_id ]['login_method']  ;
				$this->status       = AUTH_ACCESS;
                return TRUE;
            }
        }

        return FALSE;
	}
	/* ---------------------------------------------------------------------- */
    function _update_session()
    {
        $_SESSION[ $this->cookie_id ]['idle'] = time();
    }
	/* ---------------------------------------------------------------------- */
	function _logoff()
	{
		if( WORDY ) echo "_logoff() log off...<br>\nFirst, unset SESSION( {$this->cookie_id}...<br>\n";
		if( isset( $_SESSION[ $this->cookie_id ] ) ) {
			unset( $_SESSION[ $this->cookie_id ] );
		}
		if( isset( $_SESSION[ AUTH_RETURN_URL ] ) ) {
			unset( $_SESSION[ AUTH_RETURN_URL ] );
		}
		if( WORDY ) echo "now clearing cookie...<br>\n";
    }
	/* ---------------------------------------------------------------------- */
	function load_data( $user, $pass )
	{
		$this->username    = trim( $user );
		$this->password    = md5( trim( $pass ) );
		//$this->save_cookie= TRUE;
	}
	/* ---------------------------------------------------------------------- */
	function _load_post()
	{
		if( WORDY ) echo "_load_post() loading from login screen...<br>\n";
		
		if( $_REQUEST[ "act" ] == 'login' )
		{
			$pgg = new pgg_check();
			$pgg->pushChar( 'user', PGG_VALUE_MUST_EXIST );
			$pgg->pushChar( 'pass', PGG_VALUE_MUST_EXIST );
			$pgg->pushChar( 'save', PGG_VALUE_MISSING_OK );
			$td = $pgg->popSqlSafe();
			
			if( WORDY > 3 ) echo " -> got USER={$td{'user'}}, PASS={$td{'pass'}} & SAVE={$td{'save'}}<br>\n";
			$this->username    = trim( $td[ "user" ] );
			if( have_value( $td[ 'pass' ] ) ) {
				$this->password    = md5( trim( $td[ "pass" ] ) );
			}
			else {
				$this->password    = NULL;
			}
			if( $_REQUEST[ "save" ] == "save" ) {
				$this->save_cookie= TRUE;
			}
			
			if( WORDY > 3 ) {
				echo " -> set USRE={$this->username}, PASS={$this->password}, SAVE={$this->save_cookie}...<br>\n";
			}
			return TRUE;
		} 
		return FALSE;
	}
	/* ---------------------------------------------------------------------- */
	function get_ret_url( $clear=FALSE )
	{
		if( isset( $_SESSION[ AUTH_RETURN_URL ] ) && 
		    !empty( $_SESSION[ AUTH_RETURN_URL ] ) ) {
			$ret_url = $_SESSION[ AUTH_RETURN_URL ];
			if( $clear ) unset( $_SESSION[ AUTH_RETURN_URL ] );
			return $ret_url;
		}
		return NULL;
	}
	/* ---------------------------------------------------------------------- */
	function jump_ret_url( $ret_url=NULL )
	{
		if( !$ret_url ) {
			$ret_url = $_SESSION[ AUTH_RETURN_URL ];
			unset( $_SESSION[ AUTH_RETURN_URL ] );
		}
		if( !$ret_url ) {
			//$ret_url = '/index.php';
		}
		else if( WORDY ) {
			echo "jump_ret_url: <a href=\"{$ret_url}\">{$ret_url}</a>";
			exit;
		} else {
			header( "Location: {$ret_url}" );
			exit;
		}
	}
	/* ---------------------------------------------------------------------- */
	function get_msg_id()
	{
		switch( $this->status ) 
		{
			case AUTH_NO_ACCESS: // この文章にはアクセスできません
				$msg = AUTH_MSG_NO_ACCESS;
				break;
			case AUTH_EXPIRED: // 有効期限が切れました。もう一度ログインをお願いします
				$msg = AUTH_MSG_EXPIRED;
				break;
			case AUTH_NOT_VALID: // 無効な会員です。
				$msg = AUTH_MSG_INVALID_USER;
				break;
			case AUTH_WRONG_PASSWD: 
			case AUTH_WRONG_USERID: // 会員番号・パスワードが間違っています
				$msg = AUTH_MSG_WRONG_INPUT;
				break;
			case AUTH_NO_LOGIN: // ログイン画面を表示（メッセージ無し）。
				$msg = AUTH_MSG_NO_MESSAGE;
				break;
			case AUTH_PLEASE_LOGIN: // ログイン画面を表示（メッセージ無し）。
			case AUTH_NO_USER_PASS:
			default: // 会員番号・パスワードを入力してログインしてください
				$msg = AUTH_MSG_PLEASE_ENTER;
				break;
		}
		return $msg;
	}
	/* ---------------------------------------------------------------------- */
	function draw_login()
	{
		session_cache_limiter( 'private, must-revalidate' ); 
		header( "Pragma: no-cache" );
		$msg = $this->get_msg_id();
		
		$login_url = $this->login_url . "?msg={$msg}&s={$this->status}";
		if( $msg ) {
			$login_url = $this->login_url . "?msg={$msg}";
		}
		else {
			$login_url = $this->login_url;
		}
		if( WORDY ) {
			echo "<a href='{$login_url}'>draw_login! ({$login_url})</a><br>\n";
		}
		else {
			header( "Location: {$login_url}" );
		}
		exit;
	}
	/* ---------------------------------------------------------------------- */
	function draw_welcome()
	{
		if( WORDY ) echo "draw_welcome!<br>\n";
		// display html for welcome message who logged in with cookie. 
	
		require_once( $this->login_url );
		exit;
	}
}

/* ---------------------------------------------------------------------- */
function get_auth_message( $id )
{
	switch( $id ) {
		case AUTH_MSG_NO_ACCESS:
			$msg = "この文章にはアクセスできません";
			break;
		case AUTH_MSG_INVALID_USER:
			$msg = "ログインはできません";
			break;
		case AUTH_MSG_EXPIRED:
			$msg = "有効期限が切れました。もう一度ログインをお願いします";
			break;
		case AUTH_MSG_WRONG_INPUT:
		case AUTH_WRONG_USERID:
			$msg = "研修登録番号・パスワードが間違っています";
			break;
		case AUTH_MSG_PLEASE_ENTER:
			$msg = "研修登録番号・パスワードを入力してログインしてください";
			break;
		default:
			$msg = NULL;
			break;
	}
	return $msg;
}

/* ---------------------------------------------------------------------- */


?>